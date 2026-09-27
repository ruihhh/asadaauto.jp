@extends('layouts.site')

@php
    $shopName = config('shop.name');
    $shopArea = config('shop.pref').config('shop.area'); // 兵庫県尼崎市下坂部
    $tel = config('shop.tel');
    $telHref = config('shop.tel_href');
    $hoursLabel = \App\Support\BusinessHours::hoursLabel();
    $closedLabel = (string) config('shop.closed_label');
    $buyConfig = (array) config('shop.buy', []);
    $kobutsu = (array) config('shop.kobutsu', []);
    $paymentTiming = $buyConfig['payment_timing'] ?? null;
    $visitArea = $buyConfig['visit_area'] ?? null;

    // 見出し・ラベルを語の単位でだけ折り返す（狭いスマホで「受け付け／ました」のような改行を防ぐ）
    $nowrap = fn (array|string $parts) => new \Illuminate\Support\HtmlString(
        collect((array) $parts)->map(fn ($part) => '<span class="u-nowrap">'.e($part).'</span>')->implode('')
    );
    $hoursLine = $nowrap(array_filter(['営業時間 '.$hoursLabel, $closedLabel !== '' ? '（'.$closedLabel.'定休）' : null]));

    // メーカーの候補：主要な国産メーカー＋在庫にあるメーカー＋「輸入車」（重複は除く）
    $makeOptions = collect(['トヨタ', 'レクサス', '日産', 'ホンダ', 'マツダ', 'スバル', '三菱', 'スズキ', 'ダイハツ'])
        ->merge($makes ?? [])
        ->push('輸入車')
        ->map(fn ($make) => trim((string) $make))
        ->filter()
        ->unique()
        ->values();

    // 年式の選択肢（BuyController の検証と同じ範囲：来年〜1980年）。表示は和暦を併記する
    $yearOptions = range((int) date('Y') + 1, 1980);

    // フォームの3ステップ。サーバー側の検証エラーのときは、最初にエラーがあるステップを開いて表示する
    $stepLabels = [1 => ['お車の情報'], 2 => ['ご連絡先'], 3 => ['そのほか', '（任意）']];
    $stepFields = [
        1 => ['make', 'model', 'model_year', 'mileage', 'condition'],
        2 => ['name', 'phone', 'email'],
        3 => ['grade', 'color', 'zip', 'message'],
    ];
    $initialStep = 1;
    foreach ($stepFields as $number => $fields) {
        if ($errors->hasAny($fields)) {
            $initialStep = $number;
            break;
        }
    }

    // PC のフォームの左に置く「入力するのは、この3つです」（ステップと項目は上の $stepFields と同じ並び）
    $guide = [
        ['title' => 'お車の情報', 'optional' => false, 'fields' => [
            ['icon' => 'car', 'label' => 'メーカー・車種'],
            ['icon' => 'calendar', 'label' => '年式'],
            ['icon' => 'meter', 'label' => '走行距離'],
            ['icon' => 'bankin', 'label' => 'お車の状態'],
        ]],
        ['title' => 'ご連絡先', 'optional' => false, 'fields' => [
            ['icon' => 'user', 'label' => 'お名前'],
            ['icon' => 'phone', 'label' => '電話番号'],
            ['icon' => 'mail', 'label' => 'メールアドレス'],
        ]],
        ['title' => 'そのほか', 'optional' => true, 'fields' => [
            ['icon' => null, 'label' => 'グレード'],
            ['icon' => null, 'label' => 'ボディカラー'],
            ['icon' => null, 'label' => '郵便番号'],
            ['icon' => null, 'label' => 'ご要望'],
        ]],
    ];

    // お車の状態（3択。色だけでなくアイコンと文字で見分けられるようにする）
    $conditions = [
        'good' => ['label' => ['目立つ傷や', 'へこみはない'], 'icon' => 'check'],
        'normal' => ['label' => ['小さな傷や', 'へこみがある'], 'icon' => 'bankin'],
        'damaged' => ['label' => ['大きな傷・へこみや', '故障がある'], 'icon' => 'wrench'],
    ];

    // お約束。「しつこい営業電話はしません」はオーナー未確認のため載せない。
    // 「契約後の減額なし」は、条件をオーナーが確認して config を true にしたときだけ出す
    $promises = [
        ['illust' => 'coins', 'title' => '査定は無料です', 'text' => 'お車の査定に費用はかかりません。お電話やメールでのご相談も無料です。', 'visual' => 'zero'],
        ['illust' => 'buy', 'title' => ['査定だけでも', '大丈夫です'], 'text' => '査定額をお聞きになってから、売るかどうかをお決めください。', 'visual' => 'choice'],
    ];
    if (! empty($buyConfig['no_reduction_after_contract'])) {
        $promises[] = ['illust' => 'f-contract', 'title' => ['ご契約後の', '減額はしません'], 'text' => 'ご契約のあとで買取価格を下げることはありません。ただし、お聞きした内容と実際のお車が大きく異なる場合は除きます。', 'visual' => null];
    }
    $promises[] = ['illust' => 'p-explain', 'title' => ['ご契約の前に、', '手続きをご説明します'], 'text' => 'ご売却の手続きの流れと必要な書類を、ご契約の前にご説明します。わからないことは何でもお聞きください。', 'visual' => 'checks'];
    $promises[] = ['illust' => 'mail-check', 'title' => ['個人情報は、', '査定の連絡と手続きに', 'だけ使います'], 'text' => 'お預かりした情報は、査定のご連絡と買取の手続きのためにだけ使います。', 'visual' => 'privacy'];

    // こんなお車もご相談ください（イラストは x-site.illust。車検切れは「期限切れ」の札を重ねる）
    $cases = [
        ['illust' => 'service', 'title' => '事故車・故障車', 'text' => '事故で修理したお車や、動かないお車もご相談ください。'],
        ['illust' => 'p-shaken', 'title' => '車検切れ', 'text' => '車検が切れたまま置いてあるお車もご相談ください。', 'stamp' => '期限切れ'],
        ['illust' => 'meter', 'title' => ['走行距離が多い・', '年式が古い'], 'text' => '10万kmを超えたお車や、年式の古いお車もご相談ください。'],
        ['illust' => 'loan', 'title' => ['ローンが', '残っている'], 'text' => 'ローンの支払いが残っているお車もご相談ください。車検証の「所有者」の欄をご確認いただくと、お話が早く進みます。'],
    ];

    // ご売却の流れ（用語：おおよその査定額 → 査定額 → 買取価格）。道路とメダルの5ステップ（x-site.flow）
    $flow = [
        ['title' => $nowrap(['査定の', 'お申し込み']), 'text' => new \Illuminate\Support\HtmlString('このページのフォーム<span class="u-nowrap">（約3分）</span>か、お電話でお申し込みください。'), 'illust' => 'f-search'],
        ['title' => $nowrap(['おおよその', '査定額のご連絡']), 'text' => '担当者から電話またはメールで、おおよその査定額をお伝えします。', 'illust' => 'p-frame'],
        ['title' => $nowrap(['お車の査定']), 'text' => 'お車を見せていただき、査定額をお伝えします。ご来店のほか、出張査定もご相談ください。'.(filled($visitArea) ? '出張査定の地域：'.$visitArea : ''), 'illust' => 'f-store'],
        ['title' => $nowrap(['ご契約・', '書類のご準備']), 'text' => '査定額にご納得いただけたら、ご契約です。必要な書類は下の表をご覧ください。', 'illust' => 'f-contract'],
        ['title' => $nowrap(['お引き渡し・', 'お支払い']), 'text' => 'お車と書類をお預かりし、買取価格をお支払いします。'.(filled($paymentTiming) ? 'お支払いの時期：'.$paymentTiming : ''), 'illust' => 'f-key'],
    ];

    // ご売却に必要な書類（売る側に車庫証明は要らない）
    $documents = [
        ['caption' => '普通車の場合', 'illust' => 'sedan', 'rows' => [
            ['name' => ['自動車検査証', '（車検証）'], 'note' => 'ふだんは車の中に保管されています。'],
            ['name' => ['自賠責保険', '証明書'], 'note' => '車検証と一緒に保管されていることが多いです。'],
            ['name' => ['リサイクル券'], 'note' => '車検証と一緒に保管されていることが多いです。見当たらない場合はご相談ください。'],
            ['name' => ['自動車税', '（種別割）', '納税証明書'], 'note' => '今年度の分です。見当たらない場合はご相談ください。'],
            ['name' => ['印鑑登録', '証明書'], 'note' => 'お住まいの市区町村の窓口などで取ります（発行から3か月以内のもの）。'],
            ['name' => ['実印'], 'note' => '譲渡証明書と委任状に押していただきます。用紙はご契約のときにご案内します。'],
        ]],
        ['caption' => '軽自動車の場合', 'illust' => 'kei', 'rows' => [
            ['name' => ['自動車検査証', '（車検証）'], 'note' => 'ふだんは車の中に保管されています。'],
            ['name' => ['自賠責保険', '証明書'], 'note' => '車検証と一緒に保管されていることが多いです。'],
            ['name' => ['リサイクル券'], 'note' => '車検証と一緒に保管されていることが多いです。見当たらない場合はご相談ください。'],
            ['name' => ['軽自動車税', '（種別割）', '納税証明書'], 'note' => '今年度の分です。見当たらない場合はご相談ください。'],
            ['name' => ['認印'], 'note' => '申請依頼書に押していただきます。実印でなくても大丈夫です。用紙はご契約のときにご案内します。'],
        ]],
    ];

    // よくある質問（画面と FAQPage の構造化データを同じ items から出す）
    $faqItems = [
        ['q' => '査定は本当に無料ですか？', 'a' => 'はい、無料です。査定だけで売らなかった場合も、費用はかかりません。'],
        ['q' => '査定額を聞いてから、断ってもいいですか？', 'a' => 'はい、大丈夫です。査定額をお聞きになってから、売るかどうかをお決めください。'],
        ['q' => '事故車や動かない車も査定できますか？', 'a' => 'はい、ご相談ください。車検が切れたお車もご相談いただけます。お車の状態によっては買取できない場合もありますので、まずは状態をお聞かせください。'],
        ['q' => 'ローンが残っている車でも売れますか？', 'a' => 'ご相談ください。車検証の「所有者」の欄がローン会社やディーラーの名義になっている場合は、残りのお支払いを済ませて名義を変える手続きが必要です。残りの金額をお聞きして、進め方をご説明します。'],
        ['q' => '売却に必要な書類は何ですか？', 'a' => "普通車は、車検証・自賠責保険証明書・リサイクル券・自動車税の納税証明書・印鑑登録証明書・実印です。\n軽自動車は、車検証・自賠責保険証明書・リサイクル券・軽自動車税の納税証明書・認印です。\n査定のお申し込みの段階では、書類は必要ありません。"],
        ['q' => '出張査定はできますか？', 'a' => 'ご自宅などにうかがって査定する出張査定も、ご相談ください。フォームの「郵便番号」の欄にご記入いただくか、お電話でお知らせください。うかがえる地域と日時をご相談します。'.(filled($visitArea) ? "\n出張査定の地域：".$visitArea : '')],
    ];

    // 構造化データ（BreadcrumbList・Service）。名前と説明は画面の文面にそろえる
    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'ホーム', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => '買取査定', 'item' => route('buy.index')],
        ],
    ];
    $serviceSchema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'name' => '中古車の買取査定',
        'serviceType' => '中古車買取',
        'description' => '査定は無料です。査定額を聞いてから、売るかどうかを決められます。フォーム（約3分）またはお電話でお申し込みいただけます。',
        'url' => route('buy.index'),
        'provider' => ['@id' => url('/').'#organization'],
        'areaServed' => filled($visitArea) ? $visitArea : null,
        'offers' => [
            '@type' => 'Offer',
            'price' => '0',
            'priceCurrency' => 'JPY',
            'description' => '査定は無料です',
        ],
    ], fn ($value) => $value !== null);
    $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_PRETTY_PRINT;
@endphp

@section('title', '尼崎の中古車買取・無料査定')
@section('meta_description', $shopArea.'の中古車販売店'.$shopName.'の買取査定のご案内です。査定は無料で、査定額を聞いてから売るかどうかを決められます。フォーム（約3分）またはお電話でお申し込みください。')
@section('og_title', '尼崎の中古車買取・無料査定 | '.$shopName)
@section('og_description', '査定は無料です。査定額を聞いてから、売るかどうかを決められます。'.$shopArea.'の'.$shopName.'。')
@section('canonical', route('buy.index'))
@section('body_class', 'p-buy')
@section('mbar', 'buy')
@section('contact_band', 'hide')

@push('head')
    <link rel="preload" as="image" href="{{ asset('images/buy-cta-bg.jpg.webp') }}" type="image/webp">
@endpush

@push('structured_data')
    <script type="application/ld+json">{!! json_encode($breadcrumbSchema, $jsonFlags) !!}</script>
    <script type="application/ld+json">{!! json_encode($serviceSchema, $jsonFlags) !!}</script>
@endpush

@section('content')

<div class="p-buy-crumb">
    <div class="l-container">
        <x-site.breadcrumb :items="[['label' => '買取査定']]" />
    </div>
</div>

{{-- 1. ファーストビュー：写真の帯に斜めの見出しと黄の丸バッジ、右（スマホは下）にページで唯一の申し込みフォーム --}}
<section class="p-buy-hero" aria-labelledby="buy-title">
    <div class="l-container p-buy-hero__grid">
        {{-- 写真の帯（飾り）。暗い面を重ね、右寄りに斜めの赤い面と黄の線 --}}
        <div class="p-buy-hero__band" aria-hidden="true">
            <picture>
                <source type="image/webp" srcset="{{ asset('images/buy-cta-bg.jpg.webp') }}">
                <img class="p-buy-hero__img" src="{{ asset('images/buy-cta-bg.jpg') }}" alt="" width="1400" height="360" fetchpriority="high">
            </picture>
            <span class="p-buy-hero__slash"></span>
            <span class="p-buy-hero__line"></span>
        </div>

        <div class="p-buy-hero__intro">
            <p class="c-hero__tag"><x-site.icon name="map-pin" />兵庫県尼崎市下坂部の中古車販売店</p>
            <h1 class="c-hero__catch p-buy-hero__catch" id="buy-title">
                <span class="c-slant c-slant--black"><span class="u-nowrap">尼崎の中古車買取・</span></span>
                <span class="c-slant c-slant--red"><span class="u-nowrap">無料査定</span></span>
            </h1>
            <p class="c-slant c-slant--white p-buy-hero__sub"><em class="p-buy-hero__mark">査定無料</em><span class="u-nowrap">査定額を聞いてから、</span><span class="u-nowrap">売るかどうかを</span><span class="u-nowrap">決められます。</span></p>
            <ul class="p-buy-hero__badges" role="list">
                <li><x-site.round-badge variant="yellow" top="査定" num="無料" /></li>
                <li><x-site.round-badge variant="yellow" top="査定だけ" num="でもOK" /></li>
                <li><x-site.round-badge variant="yellow" top="入力" num="約3分" /></li>
            </ul>
        </div>

        <form id="appraisal-form" class="p-buy-form" method="POST" action="{{ route('buy.send') }}" data-submit-once
              aria-labelledby="appraisal-form-title"
              x-data="buyAppraisalForm({{ $initialStep }})"
              x-on:keydown.enter="onEnter($event)"
              x-on:click="onErrorLink($event)">
            @csrf

            <div class="p-buy-form__head">
                <p class="c-slant c-slant--yellow p-buy-form__kicker">かんたん3ステップ</p>
                <h2 id="appraisal-form-title" class="p-buy-form__title"><x-site.icon name="car" />無料査定のお申し込み<span class="u-nowrap">（約3分）</span></h2>
                <p class="p-buy-form__lead">必須の項目だけでも送信できます。年式や走行距離は、おおよそで大丈夫です。</p>
            </div>

            <div class="p-buy-form__body">
                {{-- エラー一覧は画面の欄の並び（STEP.1 お車の情報 → STEP.2 ご連絡先 → STEP.3 そのほか）の順に出す（検証ルールの順とは違うため） --}}
                <x-site.form-errors :order="['make', 'model', 'model_year', 'mileage', 'condition', 'name', 'phone', 'email', 'grade', 'color', 'zip', 'message']" />

                {{-- 進み具合（丸の番号と、それをつなぐ赤いバー） --}}
                <ol class="p-buy-progress" x-ref="progress" data-current="{{ $initialStep }}" x-bind:data-current="step" aria-label="お申し込みの手順（全3ステップ）">
                    @foreach ($stepLabels as $number => $label)
                        <li class="p-buy-progress__item{{ $number === $initialStep ? ' is-current' : ($number < $initialStep ? ' is-done' : '') }}"
                            @if ($number === $initialStep) aria-current="step" @endif
                            x-bind:class="{ 'is-current': step === {{ $number }}, 'is-done': step > {{ $number }} }"
                            x-bind:aria-current="step === {{ $number }} ? 'step' : null">
                            <span class="p-buy-progress__num"><span class="p-buy-progress__digit">{{ $number }}</span><x-site.icon name="check" class="p-buy-progress__check" /></span>
                            <span class="p-buy-progress__label">{{ $nowrap($label) }}</span>
                            <span class="u-visually-hidden" x-text="step > {{ $number }} ? '（入力済み）' : ''"></span>
                        </li>
                    @endforeach
                </ol>

                {{-- ステップ1：お車の情報 --}}
                <div class="c-form" data-step="1" role="group" aria-labelledby="buy-step1-title"
                     x-show="step === 1" @if ($initialStep !== 1) x-cloak @endif>
                    <h3 id="buy-step1-title" class="c-subhead p-buy-step__title" tabindex="-1" data-step-title>ステップ1：お車の情報</h3>

                    <div class="p-buy-form__row">
                        <div class="c-field">
                            <label class="c-field__label" for="make">メーカー <span class="c-badge-req">必須</span></label>
                            <p class="c-field__hint" id="make-hint">例）トヨタ（一覧から選ぶか、文字で入力）</p>
                            <input class="c-field__input" type="text" id="make" name="make" value="{{ old('make') }}" list="make-list" maxlength="100" autocomplete="off" required
                                   aria-describedby="make-hint @error('make') make-error @enderror" @error('make') aria-invalid="true" @enderror>
                            <datalist id="make-list">
                                @foreach ($makeOptions as $make)
                                    <option value="{{ $make }}"></option>
                                @endforeach
                            </datalist>
                            <x-site.field-error name="make" />
                        </div>

                        <div class="c-field">
                            <label class="c-field__label" for="model">車種 <span class="c-badge-req">必須</span></label>
                            <p class="c-field__hint" id="model-hint">例）プリウス、N-BOX</p>
                            <input class="c-field__input" type="text" id="model" name="model" value="{{ old('model') }}" maxlength="100" autocomplete="off" required
                                   aria-describedby="model-hint @error('model') model-error @enderror" @error('model') aria-invalid="true" @enderror>
                            <x-site.field-error name="model" />
                        </div>
                    </div>

                    <div class="c-field">
                        <label class="c-field__label" for="model_year">年式 <span class="c-badge-req">必須</span></label>
                        <p class="c-field__hint" id="model_year-hint">車検証の「初度登録年月」に書かれています。一覧には和暦（令和・平成）も書いてあります。</p>
                        <select class="c-field__input c-field__input--select" id="model_year" name="model_year" required
                                aria-describedby="model_year-hint @error('model_year') model_year-error @enderror" @error('model_year') aria-invalid="true" @enderror>
                            <option value="">選んでください</option>
                            @foreach ($yearOptions as $year)
                                <option value="{{ $year }}" @selected((string) old('model_year') === (string) $year)>{{ \App\Support\CarText::year($year, true) }}</option>
                            @endforeach
                        </select>
                        <x-site.field-error name="model_year" />
                    </div>

                    <div class="c-field">
                        <label class="c-field__label" for="mileage">走行距離 <span class="c-badge-req">必須</span></label>
                        <p class="c-field__hint" id="mileage-hint">おおよそで大丈夫です（例：45000）</p>
                        <div class="c-field__with-unit">
                            <input class="c-field__input" type="number" inputmode="numeric" id="mileage" name="mileage" value="{{ old('mileage') }}" min="0" max="9999999" step="1" autocomplete="off" required
                                   aria-describedby="mileage-hint @error('mileage') mileage-error @enderror" @error('mileage') aria-invalid="true" @enderror>
                            <span class="c-field__unit">km</span>
                        </div>
                        <x-site.field-error name="mileage" />
                    </div>

                    <fieldset class="c-field" aria-describedby="condition-hint @error('condition') condition-error @enderror">
                        <legend class="c-field__label c-field__legend">お車の状態 <span class="c-badge-req">必須</span></legend>
                        <p class="c-field__hint" id="condition-hint">いちばん近いものを選んでください。迷ったら「小さな傷やへこみがある」で大丈夫です。</p>
                        <div class="c-choice-group p-buy-cond">
                            @foreach ($conditions as $value => $condition)
                                <label class="c-choice">
                                    <input class="c-choice__input" type="radio" name="condition" value="{{ $value }}" required
                                           @if ($loop->first) id="condition" @endif
                                           @checked(old('condition', 'normal') === $value)
                                           @error('condition') aria-invalid="true" @enderror>
                                    <span class="c-choice__box">
                                        <span class="p-buy-cond__ic p-buy-cond__ic--{{ $value }}"><x-site.icon :name="$condition['icon']" :size="24" /></span>
                                        <span class="p-buy-cond__label">{{ $nowrap($condition['label']) }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <x-site.field-error name="condition" />
                    </fieldset>

                    <div class="p-buy-form__nav">
                        <x-site.btn2 block small="つぎは、ご連絡先の入力です" big="次へ進む" x-on:click="next()" />
                    </div>
                </div>

                {{-- ステップ2：ご連絡先 --}}
                <div class="c-form" data-step="2" role="group" aria-labelledby="buy-step2-title"
                     x-show="step === 2" @if ($initialStep !== 2) x-cloak @endif>
                    <h3 id="buy-step2-title" class="c-subhead p-buy-step__title" tabindex="-1" data-step-title>ステップ2：ご連絡先</h3>

                    <div class="c-field">
                        <label class="c-field__label" for="name">お名前 <span class="c-badge-req">必須</span></label>
                        <p class="c-field__hint" id="name-hint">例）山田 太郎</p>
                        <input class="c-field__input" type="text" id="name" name="name" value="{{ old('name') }}" maxlength="100" autocomplete="name" required
                               aria-describedby="name-hint @error('name') name-error @enderror" @error('name') aria-invalid="true" @enderror>
                        <x-site.field-error name="name" />
                    </div>

                    <div class="c-field">
                        <label class="c-field__label" for="phone">お電話番号 <span class="c-badge-req">必須</span></label>
                        <p class="c-field__hint" id="phone-hint">日中につながりやすい番号をご記入ください（例：090-1234-5678）</p>
                        <input class="c-field__input" type="tel" inputmode="tel" id="phone" name="phone" value="{{ old('phone') }}" maxlength="20" autocomplete="tel" required
                               aria-describedby="phone-hint @error('phone') phone-error @enderror" @error('phone') aria-invalid="true" @enderror>
                        <x-site.field-error name="phone" />
                    </div>

                    <div class="c-field">
                        <label class="c-field__label" for="email">メールアドレス <span class="c-badge-req">必須</span></label>
                        <p class="c-field__hint" id="email-hint">例）taro@example.com</p>
                        <input class="c-field__input" type="email" id="email" name="email" value="{{ old('email') }}" maxlength="255" autocomplete="email" required
                               aria-describedby="email-hint @error('email') email-error @enderror" @error('email') aria-invalid="true" @enderror>
                        <x-site.field-error name="email" />
                    </div>

                    <div class="p-buy-form__nav">
                        <x-site.btn2 block small="つぎは、そのほか（任意）です" big="次へ進む" x-on:click="next()" />
                        <button type="button" class="c-btn c-btn--secondary c-btn--block" x-on:click="back()"><x-site.icon name="chevron-left" />前に戻る</button>
                    </div>
                </div>

                {{-- ステップ3：そのほか（任意）＋同意＋送信 --}}
                <div class="c-form" data-step="3" role="group" aria-labelledby="buy-step3-title"
                     x-show="step === 3" @if ($initialStep !== 3) x-cloak @endif>
                    <h3 id="buy-step3-title" class="c-subhead p-buy-step__title" tabindex="-1" data-step-title>ステップ3：そのほか（任意）</h3>
                    <p class="p-buy-form__text">この欄は空いたままでも送信できます。わかる範囲でご記入ください。</p>

                    <div class="p-buy-form__row">
                        <div class="c-field">
                            <label class="c-field__label" for="grade">グレード <span class="c-badge-req c-badge-req--optional">任意</span></label>
                            <p class="c-field__hint" id="grade-hint">わからなければ空欄で大丈夫です（例：S、ハイブリッドG）</p>
                            <input class="c-field__input" type="text" id="grade" name="grade" value="{{ old('grade') }}" maxlength="100" autocomplete="off"
                                   aria-describedby="grade-hint @error('grade') grade-error @enderror" @error('grade') aria-invalid="true" @enderror>
                            <x-site.field-error name="grade" />
                        </div>

                        <div class="c-field">
                            <label class="c-field__label" for="color">ボディカラー <span class="c-badge-req c-badge-req--optional">任意</span></label>
                            <p class="c-field__hint" id="color-hint">例）パールホワイト、シルバー</p>
                            <input class="c-field__input" type="text" id="color" name="color" value="{{ old('color') }}" maxlength="60" autocomplete="off"
                                   aria-describedby="color-hint @error('color') color-error @enderror" @error('color') aria-invalid="true" @enderror>
                            <x-site.field-error name="color" />
                        </div>
                    </div>

                    <div class="c-field">
                        <label class="c-field__label" for="zip">郵便番号（出張査定をご希望の場合） <span class="c-badge-req c-badge-req--optional">任意</span></label>
                        <p class="c-field__hint" id="zip-hint">例）6610975（「-」はなくても大丈夫です）。ご住所は、あとでお電話でうかがいます。</p>
                        <input class="c-field__input" type="text" inputmode="numeric" id="zip" name="zip" value="{{ old('zip') }}" maxlength="10" autocomplete="postal-code"
                               aria-describedby="zip-hint @error('zip') zip-error @enderror" @error('zip') aria-invalid="true" @enderror>
                        <x-site.field-error name="zip" />
                    </div>

                    <div class="c-field">
                        <label class="c-field__label" for="message">お車について・ご要望 <span class="c-badge-req c-badge-req--optional">任意</span></label>
                        <p class="c-field__hint" id="message-hint">修復歴の有無、車検の期限、ローンが残っているかどうかをご記入いただくと、査定額をお伝えしやすくなります。{{ \App\Support\CarText::REPAIR_DEFINITION }}ご希望の連絡の時間帯もどうぞ。</p>
                        <textarea class="c-field__input c-field__input--textarea" id="message" name="message" maxlength="2000"
                                  aria-describedby="message-hint @error('message') message-error @enderror" @error('message') aria-invalid="true" @enderror>{{ old('message') }}</textarea>
                        <x-site.field-error name="message" />
                    </div>

                    <x-site.privacy-consent />

                    <div class="p-buy-form__nav">
                        <x-site.btn2 type="submit" block small="査定は無料です" big="無料査定を申し込む" data-busy-label="申し込んでいます…" x-on:click="beforeSubmit($event)" />
                        <p class="p-buy-form__note">送信後、担当者から電話またはメールでご連絡します。定休日（{{ $closedLabel }}）をはさむ場合は、翌営業日以降のご連絡になります。</p>
                        <button type="button" class="c-btn c-btn--secondary c-btn--block" x-on:click="back()"><x-site.icon name="chevron-left" />前に戻る</button>
                    </div>
                </div>
            </div>
        </form>

        {{-- 電話でも申し込める（PC はフォームの左、スマホはフォームの下） --}}
        <div class="p-buy-hero__aside">
            <section class="c-card c-card--accent p-buy-tel" aria-labelledby="buy-tel-title">
                <h2 id="buy-tel-title" class="p-buy-tel__title">{{ $nowrap(['お電話でも', 'お申し込みいただけます']) }}</h2>
                <a class="p-buy-tel__num" href="{{ $telHref }}" aria-label="電話をかける {{ $tel }}"><x-site.icon name="phone" /><span>{{ $tel }}</span></a>
                <p class="p-buy-tel__text">「買取の査定について」とお伝えください。お車の年式や走行距離をお聞きします。</p>
                <div class="p-buy-tel__status">
                    <p class="p-buy-tel__hours">{{ $hoursLine }}</p>
                    <x-site.open-status />
                </div>
                @if (config('shop.line_url'))
                    <a class="c-btn c-btn--line c-btn--block" href="{{ config('shop.line_url') }}" target="_blank" rel="noopener">
                        <x-site.icon name="line" />LINEで相談<span class="u-visually-hidden">（新しいタブで開きます）</span>
                    </a>
                @endif
            </section>

            {{-- 車検証のメモ（スマホ・タブレット。PC は下の「入力するのは、この3つ」の中に入れる） --}}
            <div class="p-buy-memo u-hide-pc">
                <span class="p-buy-memo__ic"><x-site.icon name="file" :size="28" /></span>
                <div class="p-buy-memo__body">
                    <p class="p-buy-memo__title">{{ $nowrap(['車検証があると、', '入力がかんたんです']) }}</p>
                    <p class="p-buy-memo__text">年式は、車検証の「初度登録年月」でわかります。お手元になくても、おおよそで大丈夫です。</p>
                </div>
            </div>

            {{-- PC だけ：フォームの左に、入力する項目の一覧（スマホはフォームのすぐ下なので出さない） --}}
            <section class="c-card p-buy-guide u-hide-sp" aria-labelledby="buy-guide-title">
                <p class="c-slant c-slant--yellow p-buy-guide__kicker">入力は約3分</p>
                <h2 id="buy-guide-title" class="p-buy-guide__title">{{ $nowrap(['入力するのは、', 'この3つです']) }}</h2>
                <ol class="p-buy-guide__list">
                    @foreach ($guide as $item)
                        <li class="p-buy-guide__item{{ $item['optional'] ? ' p-buy-guide__item--optional' : '' }}">
                            <span class="p-buy-guide__no" aria-hidden="true">{{ $loop->iteration }}</span>
                            <div class="p-buy-guide__body">
                                <p class="p-buy-guide__name">{{ $item['title'] }}
                                    <span class="c-badge-req{{ $item['optional'] ? ' c-badge-req--optional' : '' }}">{{ $item['optional'] ? '任意' : '必須' }}</span></p>
                                <ul class="p-buy-guide__chips">
                                    @foreach ($item['fields'] as $field)
                                        <li class="p-buy-guide__chip">@if (filled($field['icon']))<x-site.icon :name="$field['icon']" :size="18" />@endif{{ $field['label'] }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </li>
                    @endforeach
                </ol>
                <p class="p-buy-guide__memo"><span class="p-buy-memo__ic"><x-site.icon name="file" :size="24" /></span><span>車検証があると入力がかんたんです。年式は「初度登録年月」でわかります。</span></p>
            </section>
        </div>
    </div>
</section>

{{-- 2. お約束（黒の斜線地。1ページに1つ） --}}
<section class="l-section l-section--dark" id="promise" aria-labelledby="buy-promise-title">
    <div class="l-container">
        <x-site.band-title id="buy-promise-title" :title="'買取の'.count($promises).'つのお約束'" en="PROMISE"
            lead="安心してご相談いただくために、次のことをお約束します。" />
        <ol class="c-promises p-buy-promises">
            @foreach ($promises as $promise)
                <x-site.promise :no="$loop->iteration" :illust="$promise['illust']" :title="$nowrap($promise['title'])">
                    {{ $promise['text'] }}
                    <x-slot:visual>
                        @switch ($promise['visual'])
                            @case ('zero')
                                <p class="p-buy-zero"><span class="p-buy-zero__label">査定料</span><b class="p-buy-zero__num">0</b><span class="p-buy-zero__unit">円</span></p>
                                <p class="c-promise__note">売らなかった場合も、費用はかかりません。</p>
                                @break
                            @case ('choice')
                                <div class="c-promise__box p-buy-choice">
                                    <span class="c-tag c-tag--black">査定額を聞く</span>
                                    <x-site.icon name="chevron-right" class="p-buy-choice__arrow" />
                                    <span class="c-tag c-tag--red">売る</span>
                                    <span class="p-buy-choice__or">または</span>
                                    <span class="c-tag c-tag--outline">売らない</span>
                                </div>
                                <p class="c-promise__note">売らなくても費用はかかりません。</p>
                                @break
                            @case ('checks')
                                <ul class="c-promise__checks">
                                    <li class="c-promise__check"><x-site.icon name="check" />ご売却の手続きの流れ</li>
                                    <li class="c-promise__check"><x-site.icon name="check" />ご用意いただく書類</li>
                                </ul>
                                @break
                            @case ('privacy')
                                <p class="c-promise__box p-buy-privacy"><x-site.icon name="shield" />ほかの目的には使いません</p>
                                <p class="c-promise__note">くわしくは<a class="c-link" href="{{ route('privacy') }}">個人情報の取り扱い</a>をご覧ください。</p>
                                @break
                        @endswitch
                    </x-slot:visual>
                </x-site.promise>
            @endforeach
        </ol>
    </div>
</section>

{{-- 3. こんなお車もご相談ください --}}
<section class="l-section" id="cases" aria-labelledby="buy-cases-title">
    <div class="l-container">
        <x-site.section-head id="buy-cases-title" title="こんなお車もご相談ください" en="CONSULT" center
            lead="古いお車や動かないお車でも、まずはお気軽にご相談ください。" />
        <ul class="p-buy-cases" role="list">
            @foreach ($cases as $case)
                <li class="p-buy-case">
                    <div class="p-buy-case__art">
                        <x-site.illust :name="$case['illust']" class="p-buy-case__ill" />
                        @if (! empty($case['stamp']))
                            <span class="p-buy-case__stamp" aria-hidden="true">{{ $case['stamp'] }}</span>
                        @endif
                    </div>
                    <div class="p-buy-case__body">
                        <h3 class="p-buy-case__title">{{ $nowrap($case['title']) }}</h3>
                        <p class="p-buy-case__text">{{ $case['text'] }}</p>
                    </div>
                </li>
            @endforeach
        </ul>
        <div class="c-alert p-buy-cases__note">
            <x-site.icon name="info" />
            <p>お車の状態によっては、<b>買取できない場合</b>もあります。まずは状態をお聞かせください。</p>
        </div>
        <div class="c-cta-row">
            <x-site.btn2 href="#appraisal-form" small="査定は無料です" big="無料査定を申し込む" />
            <x-site.btn2 :href="$telHref" variant="black" icon="phone" num :small="'電話で相談する（'.$hoursLabel.'）'" :big="$tel" :aria-label="'電話をかける '.$tel" />
        </div>
    </div>
</section>

{{-- 4. ご売却の流れ（道路とメダルの5ステップ）＋必要な書類 --}}
<section class="l-section l-section--soft" id="flow" aria-labelledby="buy-flow-title">
    <div class="l-container">
        <x-site.section-head id="buy-flow-title" title="ご売却の流れ" en="FLOW" lead="お申し込みから、お車のお引き渡しまでの5つのステップです。" />
        <x-site.flow class="p-buy-flow" :steps="$flow" />

        <div class="p-buy-docs">
            <h3 class="c-subhead">ご売却に必要な書類</h3>
            <p class="p-buy-docs__lead">ご契約のときに、次の書類をご用意ください。<b>査定のお申し込みの段階では、書類は必要ありません。</b></p>
            <div class="p-buy-docs__grid">
                @foreach ($documents as $document)
                    <div class="c-table-wrap p-buy-doc">
                        <table class="c-table">
                            <caption class="p-buy-doc__caption">
                                <span class="p-buy-doc__art"><x-site.illust :name="$document['illust']" class="p-buy-doc__ill" /></span>{{ $document['caption'] }}
                            </caption>
                            <tbody>
                                @foreach ($document['rows'] as $row)
                                    <tr>
                                        <th scope="row" class="c-table__th">{{ $nowrap($row['name']) }}</th>
                                        <td class="c-table__td">{{ $row['note'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>
            <ul class="p-buy-docs__notes">
                <li>車検証の住所や氏名が今と違う場合（引っ越し・結婚など）は、住民票や戸籍の附票などが追加で必要です。</li>
                <li>車検証の「所有者」がローン会社やディーラーの場合は、名義を変えるための書類が必要です。お気軽にご相談ください。</li>
            </ul>
        </div>
    </div>
</section>

{{-- 5. よくある質問（FAQPage） --}}
<section class="l-section" id="faq" aria-labelledby="buy-faq-title">
    <div class="l-container l-container--narrow">
        <x-site.section-head id="buy-faq-title" title="よくある質問" en="FAQ" />
        <x-site.faq class="p-buy-faq" :items="$faqItems" jsonld />
    </div>
</section>

{{-- 6. お店での査定 --}}
<section class="l-section l-section--soft" id="shop" aria-labelledby="buy-shop-title">
    <div class="l-container">
        <x-site.section-head id="buy-shop-title" title="お店での査定もできます" en="SHOP"
            :lead="$nowrap(['お車で直接お越しいただいても', '査定できます。', 'ご来店の前にお電話いただくと', 'スムーズです。'])" />
        <div class="p-buy-shop">
            <figure class="p-buy-shop__photo">
                <picture>
                    <source srcset="{{ asset('images/store-hero-bg.png.webp') }}" type="image/webp">
                    <img class="p-buy-shop__img" src="{{ asset('images/store-hero-bg.png') }}" width="1584" height="672" loading="lazy"
                         alt="{{ $shopName }}の店舗外観。電話番号の入った看板と、展示中の車が並んでいます">
                </picture>
                <figcaption class="p-buy-shop__caption"><span class="c-slant c-slant--yellow">この看板が目印です</span></figcaption>
            </figure>

            <div class="c-card c-card--accent p-buy-shop__info">
                <p class="p-buy-shop__name"><x-site.icon name="store" />{{ $shopName }}</p>
                <div class="c-table-wrap">
                    <table class="c-table c-table--stack p-buy-shop__table">
                        <caption class="u-visually-hidden">店舗の情報</caption>
                        <tbody>
                            <tr>
                                <th scope="row" class="c-table__th"><x-site.icon name="map-pin" />住所</th>
                                <td class="c-table__td"><x-site.address postal /></td>
                            </tr>
                            <tr>
                                <th scope="row" class="c-table__th"><x-site.icon name="phone" />電話</th>
                                <td class="c-table__td"><a class="p-buy-shop__tel" href="{{ $telHref }}" aria-label="電話をかける {{ $tel }}">{{ $tel }}</a></td>
                            </tr>
                            <tr>
                                <th scope="row" class="c-table__th"><x-site.icon name="clock" />営業時間</th>
                                <td class="c-table__td">{{ $hoursLabel }}<br><x-site.open-status /></td>
                            </tr>
                            <tr>
                                <th scope="row" class="c-table__th"><x-site.icon name="calendar" />定休日</th>
                                <td class="c-table__td">{{ $closedLabel }}</td>
                            </tr>
                            @if (filled(config('shop.parking')))
                                <tr>
                                    <th scope="row" class="c-table__th"><x-site.icon name="parking" />駐車場</th>
                                    <td class="c-table__td">{{ config('shop.parking') }}</td>
                                </tr>
                            @endif
                            @if (filled($kobutsu['number'] ?? null))
                                <tr>
                                    <th scope="row" class="c-table__th"><x-site.icon name="shield" />古物商許可</th>
                                    <td class="c-table__td">{{ $kobutsu['authority'] ?? '' }} 第{{ $kobutsu['number'] }}号@if (filled($kobutsu['holder'] ?? null))（{{ $kobutsu['holder'] }}）@endif</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="p-buy-shop__actions">
                    <a class="c-btn c-btn--primary c-btn--block" href="{{ config('shop.directions_url') }}" target="_blank" rel="noopener">
                        <x-site.icon name="route" />地図アプリで道順を見る<span class="u-visually-hidden">（新しいタブで開きます）</span>
                    </a>
                    <a class="c-more" href="{{ route('store') }}">店舗案内・アクセスを詳しく見る<x-site.icon name="chevron-right" /></a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- 7. 最後の案内（黒い写真地に斜めの赤い面・黄の丸バッジ＋2段ボタン） --}}
<section class="l-section" id="apply" aria-labelledby="buy-final-title">
    <div class="l-container">
        <div class="c-promo p-buy-final">
            <div class="c-promo__media"><picture><source type="image/webp" srcset="{{ asset('images/buy-hero-bg.jpg.webp') }}"><img src="{{ asset('images/buy-hero-bg.jpg') }}" alt="" width="1400" height="560" loading="lazy"></picture></div>
            <div class="c-promo__body">
                <p class="c-slant c-slant--yellow">まずは無料査定から</p>
                <h2 class="c-promo__title" id="buy-final-title">お申し込みは、<br><em>フォームかお電話</em>で。</h2>
                <p class="c-promo__lead">フォームは24時間受け付けています。お電話は営業時間内（{{ $hoursLabel }}、{{ $closedLabel }}定休）にどうぞ。</p>
                <div class="c-promo__actions">
                    <x-site.btn2 href="#appraisal-form" variant="yellow" small="査定は無料です" big="無料査定を申し込む" />
                    <a class="c-promo__tel" href="{{ $telHref }}" aria-label="電話をかける {{ $tel }}"><small>お電話でも受付中（{{ $hoursLabel }}）</small><b><x-site.icon name="phone" />{{ $tel }}</b></a>
                </div>
            </div>
            <ul class="c-promo__badges" role="list">
                <li><x-site.round-badge variant="yellow" top="査定" num="無料" /></li>
                <li><x-site.round-badge variant="yellow" top="査定だけ" num="でもOK" /></li>
                <li><x-site.round-badge variant="yellow" top="フォームは" num="24時間" bottom="受付" /></li>
            </ul>
        </div>
    </div>
</section>

{{--
    買取実績（旧ページの直書き6件）は削除した。
    復活の条件：実際の取引の記録（買取の時期・車種・年式・走行距離・買取価格〈成約額〉）がそろい、掲載の同意を確認できたら、
    管理画面から登録したデータだけを「買取価格（成約額）」として、時期と一緒に表示する。評価の星・「他社より」の比較は載せない。
--}}

@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    // 買取査定フォーム（3ステップ）。送信はしないステップの切り替えと、隠れたステップにある欄の検証・エラーへの移動を受け持つ
    Alpine.data('buyAppraisalForm', (initialStep = 1) => ({
        step: initialStep,
        total: 3,
        form: null,

        init() {
            this.form = this.$root;
        },

        stepBox(number) {
            return this.form.querySelector('[data-step="' + number + '"]');
        },

        stepOf(element) {
            const box = element.closest('[data-step]');
            return box ? Number(box.dataset.step) : null;
        },

        firstInvalid(scope) {
            return Array.from(scope.querySelectorAll('input, select, textarea'))
                .find((element) => element.willValidate && !element.checkValidity()) || null;
        },

        // 表示中のステップに未入力・誤りがあれば、その欄でブラウザの案内を出して止める
        next() {
            const invalid = this.firstInvalid(this.stepBox(this.step));
            if (invalid) {
                invalid.focus();
                invalid.reportValidity();
                return;
            }
            this.go(this.step + 1);
        },

        back() {
            this.go(this.step - 1);
        },

        go(number, field = null, report = false) {
            this.step = Math.min(Math.max(number, 1), this.total);
            // x-show は次の描画のタイミングで表示を切り替えるので、フォーカスはその後に移す
            const later = document.visibilityState === 'visible' ? window.requestAnimationFrame : window.setTimeout;
            this.$nextTick(() => later(() => {
                if (field) {
                    // 欄の上の項目名も見えるよう、画面の中ほどに出してからフォーカスする
                    field.scrollIntoView({ block: 'center' });
                    field.focus({ preventScroll: true });
                    if (report) field.reportValidity();
                    return;
                }
                this.$refs.progress.scrollIntoView({ block: 'start' });
                const title = this.stepBox(this.step).querySelector('[data-step-title]');
                if (title) title.focus({ preventScroll: true });
            }));
        },

        // 入力欄で Enter を押したときは、途中のステップなら送信せずに「次へ進む」と同じ動きにする（日本語の変換確定は除く）
        onEnter(event) {
            if (event.isComposing || event.keyCode === 229) return;
            const target = event.target;
            if (this.step >= this.total || !(target instanceof HTMLInputElement)) return;
            if (['button', 'submit', 'checkbox'].includes(target.type)) return;
            event.preventDefault();
            this.next();
        },

        // 送信の前に、別のステップにある欄の未入力・誤りを探し、あればそのステップを開いて知らせる
        beforeSubmit(event) {
            const invalid = this.firstInvalid(this.form);
            if (!invalid) return;
            const number = this.stepOf(invalid);
            if (number !== null && number !== this.step) {
                event.preventDefault();
                this.go(number, invalid, true);
            }
        },

        // エラー一覧のリンク：隠れたステップの欄なら、そのステップを開いてから欄へ移動する
        onErrorLink(event) {
            const link = event.target instanceof Element ? event.target.closest('.c-form-errors__link') : null;
            if (!link) return;
            const field = document.getElementById(decodeURIComponent(link.hash.slice(1)));
            if (!field) return;
            event.preventDefault();
            this.go(this.stepOf(field) || this.step, field);
        },
    }));
});
</script>
@endpush
