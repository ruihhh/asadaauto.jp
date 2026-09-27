@extends('layouts.site')

@php
    /*
     * お問い合わせ・来店予約（/contact）。案B「車選び型」。
     * 並び：見出し帯 → 電話のパネル（見出し帯に重ねる）→ お問い合わせフォーム（入力の流れ STEP.1〜3 → 白いカードのフォーム → 送信後の流れ）
     * 送信後の流れの図は x-site.medal-steps（送信完了ページの「このあとの流れ」と共通）。
     *
     * コントローラ（ContactController@index）から受け取るもの：$stock_no（?stock_no= の値）、$car（在庫番号に一致した車。なければ null）
     * ビューで読むクエリ：
     *   ?purpose= … ご用件の初期選択（stock / estimate / visit / loan / trade / search / favorites / other）
     *   ?list=    … お気に入りからまとめて問い合わせるときの在庫番号（英数字とハイフンだけ・最大10件）
     * ご用件の選択は送信しない（本文に書き方の見本を入れるためだけに使う）。フォームの name 属性は変えない。
     */
    use App\Support\BusinessHours;
    use App\Support\CarText;

    $car ??= null;
    // 掲載日が先の車（まだ公開していない車）は、在庫番号がわかっても車名・価格・写真を出さない
    // （ContactController@index は在庫番号だけで車を探すため、ビューで公開前の車を外す。売約済み・商談中は案内を出すので外さない）
    if ($car !== null && $car->published_at !== null && $car->published_at->isFuture()) {
        $car = null;
    }
    $shopName = config('shop.name');
    $shopTel = config('shop.tel');
    $lineUrl = config('shop.line_url');
    $closedLabel = config('shop.closed_label');
    $replyNote = config('shop.reply_note');
    $parking = config('shop.parking');
    $hoursSummary = BusinessHours::summaryHtml(); // 「（木曜・第3日曜定休）」を語の途中で折り返さない
    $hoursLabel = BusinessHours::hoursLabel();

    // ご用件の選択肢（イラスト付きのタイル。スマホ2列・600px 以上は3列）。label は狭い画面で <wbr> の位置でだけ折り返す
    $purposeChoices = [
        'stock' => ['label' => ['在庫の', '確認'], 'sub' => '今もあるか確かめたい', 'illust' => 'f-search'],
        'estimate' => ['label' => ['見積もり'], 'sub' => 'お支払い方法・下取りも含めて', 'illust' => 'p-total'],
        'visit' => ['label' => ['見学・', '試乗の', '予約'], 'sub' => '実車を見て、乗ってみたい', 'illust' => 'f-store'],
        'loan' => ['label' => ['ローンの', '相談'], 'sub' => '月々のお支払いを相談したい', 'illust' => 'loan'],
        'trade' => ['label' => ['下取り・', '買取'], 'sub' => '今のお車の乗り換え・売却', 'illust' => 'buy'],
        'other' => ['label' => ['その他'], 'sub' => '車検・整備・板金・保険など', 'illust' => 'service'],
    ];
    // ほかのページのボタンから来たときだけ出す選択肢（在庫一覧の［探してほしい車を伝える］・お気に入りの［まとめて問い合わせる］）
    $extraChoices = [
        'search' => ['label' => ['車を', '探して', 'ほしい'], 'sub' => 'ご希望の条件を伝える', 'illust' => 'empty'],
        'favorites' => ['label' => ['お気に入り', 'の車'], 'sub' => '選んだ車をまとめて相談', 'illust' => 'all'],
    ];

    $purposeQuery = request()->query('purpose');
    $purpose = is_string($purposeQuery) && array_key_exists($purposeQuery, $purposeChoices + $extraChoices) ? $purposeQuery : null;

    $listQuery = request()->query('list');
    $stockList = is_string($listQuery)
        ? collect(explode(',', $listQuery))
            ->map(fn ($value) => trim($value))
            ->filter(fn ($value) => preg_match('/\A[A-Za-z0-9-]{1,20}\z/', $value) === 1)
            ->unique()
            ->take(10)
            ->values()
            ->all()
        : [];
    if ($purpose === null && $stockList !== []) {
        $purpose = 'favorites';
    }
    if ($purpose !== null && isset($extraChoices[$purpose])) {
        // 「その他」はいつも最後に置く
        $purposeChoices = array_diff_key($purposeChoices, ['other' => true])
            + [$purpose => $extraChoices[$purpose]]
            + ['other' => $purposeChoices['other']];
    }

    // 本文の見本（【　】の後ろに書いてもらう）。車が決まっているときは「気になる車」の行を省く
    $carLine = $car ? '' : "【気になる車（車名や在庫番号）】\n";
    $templates = [
        'stock' => "【ご用件】在庫の確認\n".$carLine.'【ご質問・ご希望】',
        'estimate' => "【ご用件】見積もり\n".$carLine."【お支払い方法（現金・ローン）】\n【今のお車の下取り（あり・なし）】\n【お車を登録する地域（市区町村）】\n【ご質問・ご希望】",
        'visit' => "【ご用件】見学・試乗の予約\n".($car ? '' : "【見たい車（決まっていれば）】\n")."【ご希望の日時】\n第1希望：　月　日（　）　時ごろ\n第2希望：　月　日（　）　時ごろ",
        'loan' => "【ご用件】ローンの相談\n".$carLine."【頭金（あり・なし）】\n【ご質問・ご希望】",
        'trade' => "【ご用件】今の車の下取り・買取\n【今のお車（メーカー・車種・年式・走行距離）】\n【ご質問・ご希望】",
        'search' => "【ご用件】探してほしい車\n【車種・ボディタイプ】\n【ご予算（支払総額）】\n【ご希望の時期】",
        'favorites' => "【ご用件】お気に入りの車について\n【在庫番号】".implode('、', $stockList)."\n【ご質問・ご希望】",
        'other' => '',
    ];
    $messageValue = old('message') ?? ($purpose !== null ? $templates[$purpose] : '');

    // 対象の車（写真の上に店長おすすめのリボン・状態のタグ・在庫番号・ボディタイプを重ねる。車両カードと同じ見せ方）
    if ($car) {
        $carName = CarText::name($car);
        $carPhoto = filled($car->image_path) ? $car->image_path : $car->images->first()?->path;
        $carTags = CarText::tags($car);
        $carPick = collect($carTags)->contains(fn (array $tag): bool => $tag['variant'] === 'pick');
        $carStateTags = array_values(array_filter($carTags, fn (array $tag): bool => $tag['variant'] !== 'pick'));
        $carBodyType = filled($car->body_type) ? CarText::bodyType($car->body_type) : null;
        $carNotice = match ($car->status) {
            'sold' => ['title' => 'この車は売約済みです', 'text' => 'すでに次のオーナー様が決まっています。似た条件の車をお探しの方は、ご希望をお書きのうえ送信してください。'],
            'reserved' => ['title' => 'この車はただいま商談中です', 'text' => 'ほかのお客様と商談を進めています。状況はお問い合わせください。'],
            default => null,
        };
        $withoutCarUrl = route('contact.index', array_filter([
            'purpose' => $purpose,
            'list' => $stockList !== [] ? implode(',', $stockList) : null,
        ]));
    }

    // 入力の流れ（上の矢印と、フォームの中の各まとまりの見出しで同じ番号・色を使う）
    $steps = [
        1 => ['id' => 'contact-step1', 'label' => 'ご用件'],
        2 => ['id' => 'contact-step2', 'label' => 'お客様情報'],
        3 => ['id' => 'contact-step3', 'label' => '送信'],
    ];

    // 送信後の流れ（送信完了ページの「このあとの流れ」と同じ3つ。図は x-site.medal-steps）
    $afterSteps = [
        ['title' => '担当者が内容を確認します', 'illust' => 'p-explain'],
        // 定休日の「（木曜・第3日曜）」は語の途中で折り返さない
        ['title' => '電話またはメールでご連絡します', 'text' => new \Illuminate\Support\HtmlString('定休日<span class="u-nowrap">（'.e($closedLabel).'）</span>をはさむ場合は、翌営業日以降のご連絡になります。'), 'illust' => 'mail-check'],
        ['title' => 'ご来店・お見積もりなどをご案内します', 'illust' => 'f-store'],
    ];

    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'ホーム', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'お問い合わせ・来店予約', 'item' => route('contact.index')],
        ],
    ];

    $describedBy = fn (string $field, string ...$hints) => trim(implode(' ', $hints).($errors->has($field) ? ' '.$field.'-error' : ''));

    // エラー一覧は、画面の欄の並び（STEP.1 在庫番号・お問い合わせ内容 → STEP.2 お名前・お電話番号・メールアドレス）の順に出す
    // （検証ルールの順のままだと、一覧の順と画面の順が食い違うため。並びにない欄は x-site.form-errors が最後に置く）
    $errorOrder = ['stock_no', 'message', 'name', 'phone', 'email'];
@endphp

@section('title', 'お問い合わせ・来店予約')
@section('meta_description', $shopName.'（'.config('shop.pref').config('shop.area').'の中古車販売店）へのお問い合わせ・来店予約のページです。在庫の確認、見積もり、見学・試乗のご予約をフォームで24時間受け付けています。お電話 '.$shopTel.' でもどうぞ（営業時間 '.$hoursLabel.'、'.$closedLabel.'定休）。')
@section('canonical', route('contact.index'))
@section('body_class', 'p-contact')
@section('mbar', 'contact')
@section('contact_band', 'hide')

@push('structured_data')
    <script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
<x-site.page-header narrow class="p-contact-header" title="お問い合わせ・来店予約" en="CONTACT"
    lead="在庫の確認、見積もり、見学・試乗のご予約など、お気軽にどうぞ。" />

{{-- お急ぎの方はお電話で（見出し帯の下端に重ねる白いパネル。スマホでもフォームより上に出す） --}}
<div class="p-contact-top">
    <div class="l-container l-container--narrow">
        <section class="p-contact-deck" aria-labelledby="contact-quick-title">
            <h2 id="contact-quick-title" class="p-contact-deck__title"><span class="c-slant c-slant--red">お急ぎの方はお電話で</span></h2>
            <div class="p-contact-deck__body">
                <span class="p-contact-deck__ic" aria-hidden="true"><x-site.icon name="phone" :size="32" /></span>
                <div class="p-contact-deck__info">
                    <p class="p-contact-deck__text">在庫の確認や見学のご予約は、お電話でも承ります。</p>
                    <x-site.open-status class="p-contact-deck__status" />
                    <p class="p-contact-deck__hours"><x-site.icon name="clock" :size="18" /><span>営業時間 {{ $hoursSummary }}</span></p>
                </div>
            </div>
            <div class="p-contact-deck__actions">
                <x-site.btn2 class="p-contact-tel" :href="config('shop.tel_href')" variant="black" size="xl" block num icon="phone"
                    :small="'電話で相談する（'.$hoursLabel.'）'" :big="$shopTel" :aria-label="'電話をかける '.$shopTel" />
                @if ($car)
                    <p class="p-contact-deck__stock">お電話では在庫番号 <strong>{{ $car->stock_no }}</strong> とお伝えください</p>
                @endif
                @if ($lineUrl)
                    <a class="c-btn c-btn--line c-btn--lg c-btn--block" href="{{ $lineUrl }}" target="_blank" rel="noopener">
                        <x-site.icon name="line" />LINEで相談<span class="u-visually-hidden">（新しいタブで開きます）</span>
                    </a>
                @endif
            </div>
            <div class="p-contact-deck__aside">
                <p class="c-deck__label">お問い合わせのご案内</p>
                <ul class="c-deck__badges p-contact-deck__badges" role="list">
                    <li><span class="c-deck__badge-ic"><x-site.icon name="clock" /></span><span class="c-deck__badge-t">フォームは<br>24時間受付</span></li>
                    <li><span class="c-deck__badge-ic c-deck__badge-ic--yellow"><x-site.icon name="yen" /></span><span class="c-deck__badge-t">お見積もり<br>無料</span></li>
                    <li><span class="c-deck__badge-ic"><x-site.icon name="calendar" /></span><span class="c-deck__badge-t">見学・<br>試乗の予約</span></li>
                    @if (filled($parking))
                        <li><span class="c-deck__badge-ic"><x-site.icon name="parking" /></span><span class="c-deck__badge-t p-contact-deck__badge-auto">{{ $parking }}</span></li>
                    @endif
                </ul>
            </div>
        </section>
    </div>
</div>

{{-- お問い合わせフォーム --}}
<section class="l-section l-section--soft p-contact-main" aria-labelledby="contact-form-title">
    <div class="l-container l-container--narrow">
        <x-site.section-head id="contact-form-title" title="お問い合わせフォーム" en="FORM"
            lead="24時間受け付けています。必須の項目だけでも送信できます。" />

        {{-- 入力の流れ（押すとフォームの中のそのまとまりへ移動する） --}}
        <ol class="p-contact-steps" role="list" aria-label="入力の流れ">
            @foreach ($steps as $no => $step)
                <li class="p-contact-steps__item p-contact-steps__item--{{ $no }}">
                    <a class="p-contact-steps__link" href="#{{ $step['id'] }}">
                        <span class="p-contact-steps__no" aria-hidden="true" lang="en">STEP.<b>{{ $no }}</b></span>
                        <span class="p-contact-steps__label">{{ $step['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ol>

        {{-- ご用件の選択肢はこの空のフォームに属させて、お問い合わせの送信には含めない --}}
        <form id="contact-purpose" hidden></form>

        <div class="c-card c-card--accent p-contact-card">
            <form class="c-form p-contact-form" method="POST" action="{{ route('contact.send') }}" data-submit-once
                  x-data="contactForm(@js(['purpose' => $purpose ?? '', 'templates' => $templates]))">
                @csrf
                <x-site.form-errors :order="$errorOrder" />

                {{-- STEP.1 ご用件（ご用件の選択 → 対象の車／在庫番号 → お問い合わせ内容） --}}
                <div class="p-contact-block" id="{{ $steps[1]['id'] }}">
                    <h3 class="p-contact-block__title">
                        <span class="p-contact-block__no p-contact-block__no--1" aria-hidden="true" lang="en">STEP.<b>1</b></span>
                        <span class="u-visually-hidden">ステップ1：</span>ご用件
                    </h3>

                    <fieldset class="c-field" aria-describedby="contact-purpose-hint">
                        <legend class="c-field__label c-field__legend">ご用件を選ぶ <span class="c-badge-req c-badge-req--optional">任意</span></legend>
                        <p class="c-field__hint" id="contact-purpose-hint">選ぶと、下の「お問い合わせ内容」に書き方の見本が入ります。</p>
                        <div class="p-contact-purposes">
                            @foreach ($purposeChoices as $key => $choice)
                                <label class="p-contact-purpose">
                                    <input class="p-contact-purpose__input" type="radio" form="contact-purpose" name="contact_purpose" value="{{ $key }}" x-model="purpose" @checked($purpose === $key)>
                                    <span class="p-contact-purpose__box">
                                        <span class="p-contact-purpose__ill"><x-site.illust :name="$choice['illust']" /></span>
                                        <span class="p-contact-purpose__name">{!! collect($choice['label'])->map(fn ($part) => e($part))->implode('<wbr>') !!}</span>
                                        <span class="p-contact-purpose__sub">{{ $choice['sub'] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <p class="c-field__hint p-contact-note" x-show="purpose === 'visit'" @unless ($purpose === 'visit') x-cloak @endunless>
                            <x-site.icon name="calendar" :size="18" /><span>ご来店の日時は、営業時間 {{ $hoursLabel }}（{{ $closedLabel }}は定休日）の中でお選びください。</span>
                        </p>
                        <p class="c-field__hint p-contact-note" x-show="purpose === 'trade'" @unless ($purpose === 'trade') x-cloak @endunless>
                            <x-site.icon name="yen" :size="18" /><span>今のお車を売るだけの方は、<a class="c-link" href="{{ route('buy.index') }}">買取査定のページ</a>からもお申し込みいただけます。</span>
                        </p>
                        <p class="c-field__hint p-contact-notice" role="status" x-text="notice"></p>
                    </fieldset>

                    @if ($car)
                        <div class="c-field">
                            <p class="c-field__label" id="contact-car-label"><span class="p-contact-label-ic" aria-hidden="true"><x-site.icon name="car" /></span>お問い合わせの車</p>
                            <div class="p-contact-car" role="group" aria-labelledby="contact-car-label">
                                <div class="p-contact-car__photo">
                                    @if ($carPhoto)
                                        <img class="p-contact-car__img" src="{{ asset('images/'.$carPhoto) }}" alt="{{ $carName }}の写真" width="640" height="480" decoding="async">
                                    @else
                                        <span class="p-contact-car__noimg"><x-site.illust :name="CarText::bodyIllust($car->body_type)" />写真準備中</span>
                                    @endif
                                    @if ($carPick)
                                        <p class="c-ribbon p-contact-car__ribbon"><x-site.icon name="star" />店長おすすめ</p>
                                    @endif
                                    @if ($carStateTags !== [])
                                        <p class="p-contact-car__tags">
                                            @foreach ($carStateTags as $tag)
                                                <span class="c-tag c-tag--{{ $tag['variant'] }}">{{ $tag['label'] }}</span>
                                            @endforeach
                                        </p>
                                    @endif
                                    <p class="p-contact-car__stock">在庫番号 <b class="u-num">{{ $car->stock_no }}</b></p>
                                    @if ($carBodyType)
                                        <p class="p-contact-car__type">{{ $carBodyType }}</p>
                                    @endif
                                </div>
                                <div class="p-contact-car__body">
                                    <p class="p-contact-car__name">{{ $carName }}</p>
                                    @if (filled($car->grade))
                                        <p class="p-contact-car__grade">{{ $car->grade }}</p>
                                    @endif
                                    <x-site.price :car="$car" size="card" :note="false" class="p-contact-car__price" />
                                    <x-site.car-facts :car="$car" variant="card" :except="['transmission', 'fuel']" class="p-contact-car__facts" />
                                </div>
                                @if ($carNotice)
                                    <div class="c-alert c-alert--warn p-contact-car__notice">
                                        <x-site.icon name="alert" />
                                        <div class="c-alert__body">
                                            <p class="c-alert__title">{{ $carNotice['title'] }}</p>
                                            <p>{{ $carNotice['text'] }}</p>
                                        </div>
                                    </div>
                                @endif
                                <p class="p-contact-car__remove">
                                    <a class="c-link c-link--block" href="{{ $withoutCarUrl }}">この車を対象から外す</a>
                                </p>
                                <input type="hidden" name="stock_no" value="{{ $car->stock_no }}">
                            </div>
                        </div>
                    @else
                        <div class="c-field">
                            <label class="c-field__label" for="stock_no"><span class="p-contact-label-ic" aria-hidden="true"><x-site.icon name="tag" /></span>在庫番号 <span class="c-badge-req c-badge-req--optional">任意</span></label>
                            <p class="c-field__hint" id="stock_no-hint">在庫番号は、在庫一覧や車の詳しいページに載っています。わからない場合は空欄のままで大丈夫です。</p>
                            <p class="c-field__hint" id="stock_no-example">例）MZ4187</p>
                            <input class="c-field__input p-contact-input--short" type="text" id="stock_no" name="stock_no" value="{{ old('stock_no', is_string($stock_no ?? null) ? $stock_no : '') }}"
                                   autocomplete="off" autocapitalize="characters" spellcheck="false" maxlength="20"
                                   aria-describedby="{{ $describedBy('stock_no', 'stock_no-hint', 'stock_no-example') }}" @error('stock_no') aria-invalid="true" @enderror>
                            <x-site.field-error name="stock_no" />
                        </div>
                    @endif

                    <div class="c-field">
                        <label class="c-field__label" for="message"><span class="p-contact-label-ic" aria-hidden="true"><x-site.icon name="file" /></span>お問い合わせ内容 <span class="c-badge-req">必須</span></label>
                        <p class="c-field__hint" id="message-hint">ご質問やご希望を自由にお書きください。</p>
                        <textarea class="c-field__input c-field__input--textarea" id="message" name="message" rows="7" maxlength="2000" required x-ref="message"
                                  aria-describedby="{{ $describedBy('message', 'message-hint') }}" @error('message') aria-invalid="true" @enderror>{{ $messageValue }}</textarea>
                        <x-site.field-error name="message" />
                    </div>
                </div>

                {{-- STEP.2 お客様情報 --}}
                <div class="p-contact-block" id="{{ $steps[2]['id'] }}">
                    <h3 class="p-contact-block__title">
                        <span class="p-contact-block__no p-contact-block__no--2" aria-hidden="true" lang="en">STEP.<b>2</b></span>
                        <span class="u-visually-hidden">ステップ2：</span>お客様情報
                    </h3>

                    <div class="c-field">
                        <label class="c-field__label" for="name"><span class="p-contact-label-ic" aria-hidden="true"><x-site.icon name="user" /></span>お名前 <span class="c-badge-req">必須</span></label>
                        <p class="c-field__hint" id="name-hint">例）山田 太郎</p>
                        <input class="c-field__input p-contact-input--short" type="text" id="name" name="name" value="{{ old('name') }}"
                               autocomplete="name" maxlength="255" required
                               aria-describedby="{{ $describedBy('name', 'name-hint') }}" @error('name') aria-invalid="true" @enderror>
                        <x-site.field-error name="name" />
                    </div>

                    <div class="c-field">
                        <label class="c-field__label" for="phone"><span class="p-contact-label-ic" aria-hidden="true"><x-site.icon name="phone" /></span>お電話番号 <span class="c-badge-req c-badge-req--optional">任意</span></label>
                        <p class="c-field__hint" id="phone-hint">お電話での連絡をご希望の方はご記入ください。ハイフンはなくてもかまいません。<br>例）090-1234-5678</p>
                        <input class="c-field__input p-contact-input--short" type="tel" id="phone" name="phone" value="{{ old('phone') }}"
                               autocomplete="tel" inputmode="tel" maxlength="20"
                               aria-describedby="{{ $describedBy('phone', 'phone-hint') }}" @error('phone') aria-invalid="true" @enderror>
                        <x-site.field-error name="phone" />
                    </div>

                    <div class="c-field">
                        <label class="c-field__label" for="email"><span class="p-contact-label-ic" aria-hidden="true"><x-site.icon name="mail" /></span>メールアドレス <span class="c-badge-req">必須</span></label>
                        <p class="c-field__hint" id="email-hint">例）taro@example.com</p>
                        <input class="c-field__input" type="email" id="email" name="email" value="{{ old('email') }}"
                               autocomplete="email" inputmode="email" maxlength="255" required
                               aria-describedby="{{ $describedBy('email', 'email-hint', 'email-note') }}" @error('email') aria-invalid="true" @enderror>
                        <x-site.field-error name="email" />
                        <p class="c-field__hint" id="email-note">携帯電話会社のメール（docomo・au・SoftBank など）をお使いの方は、パソコンからのメールを受け取れる設定になっているかご確認ください。</p>
                    </div>
                </div>

                {{-- STEP.3 送信（同意 → 送信ボタン → 送信後のご案内・流れ） --}}
                <div class="p-contact-block" id="{{ $steps[3]['id'] }}">
                    <h3 class="p-contact-block__title">
                        <span class="p-contact-block__no p-contact-block__no--3" aria-hidden="true" lang="en">STEP.<b>3</b></span>
                        <span class="u-visually-hidden">ステップ3：</span>送信
                    </h3>

                    <div class="p-contact-send">
                        <x-site.privacy-consent />
                        <x-site.btn2 type="submit" size="xl" block class="p-contact-send__btn" bubble="お問い合わせは無料です"
                            small="入力内容をご確認のうえ" big="この内容で送信する" />
                        <div class="p-contact-send__after">
                            <p class="c-field__hint">送信後、担当者から電話またはメールでご連絡します。</p>
                            @if (filled($replyNote))
                                <p class="c-field__hint">{{ $replyNote }}</p>
                            @endif
                        </div>
                    </div>

                    <x-site.medal-steps title="送信後の流れ" :heading-level="4" :steps="$afterSteps" />
                </div>
            </form>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    // ご用件を選ぶと、お問い合わせ内容に書き方の見本を入れる。
    // 入れてよいのは、欄が空か、前に入れた見本のままのときだけ（ご自分で書いた文は上書きしない）
    Alpine.data('contactForm', (options = {}) => ({
        purpose: options.purpose || '',
        templates: options.templates || {},
        lastTemplate: null,
        notice: '',

        init() {
            const template = this.templateFor(this.purpose);
            if (template !== '' && this.normalize(this.$refs.message.value) === this.normalize(template)) {
                this.lastTemplate = template;
            }
            this.$watch('purpose', (value) => this.applyTemplate(value));
        },

        templateFor(key) {
            return (key && this.templates[key]) || '';
        },

        normalize(text) {
            return String(text).replace(/\r\n?/g, '\n').trim();
        },

        applyTemplate(key) {
            const box = this.$refs.message;
            const current = this.normalize(box.value);
            const untouched = current === '' || (this.lastTemplate !== null && current === this.normalize(this.lastTemplate));
            if (!untouched) {
                this.notice = '「お問い合わせ内容」はご記入済みのため、書きかえていません。';
                return;
            }
            const template = this.templateFor(key);
            box.value = template;
            this.lastTemplate = template;
            this.notice = template !== ''
                ? '下の「お問い合わせ内容」に書き方の見本を入れました。【　】の後ろにご記入ください。'
                : '「お問い合わせ内容」に、ご用件を自由にお書きください。';
        },
    }));
});
</script>
@endpush
