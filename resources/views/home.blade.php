@extends('layouts.site')

@php
    // トップページ（案B「車選び型」：写真ヒーローと斜め帯）
    // 構成：写真ヒーロー(#top)＋白いパネル → 条件から在庫を探す(#search) → いま掲載中の車(#stock) → 当店の4つのお約束(#promise・黒)
    //       → ご購入の流れ(#flow) → お支払いシミュレーション(#loan) → 下取り・買取の帯(#buy) → お探しの車が見つからないとき(#request)
    //       → よくある質問(#faq) → 店舗案内(#access)（このあとにレイアウト共通のご相談帯・フッター）
    // 電話・住所・営業時間・LINE・保証などは config('shop.*') と BusinessHours から出し、直書きしない。台数はすべて DB の実数。
    // HomeController から受け取るもの：$totalPublic（公開在庫の台数）・$newArrivals（新しい順に最大6台）・$bodyTypeCounts・$makes・
    //   $budgetLimit と $budgetCount（使い方から選ぶ［予算を抑えたい］の上限と台数）

    $shopName = config('shop.name');
    $tel = config('shop.tel');
    $telHref = config('shop.tel_href');
    $lineUrl = config('shop.line_url');
    $hoursLabel = \App\Support\BusinessHours::hoursLabel();
    $totalLabel = number_format($totalPublic);
    $stockCars = $newArrivals->take(6);
    $stockLead = $totalPublic > $stockCars->count()
        ? '全'.$totalLabel.'台のうち、新しく掲載した'.$stockCars->count().'台を表示しています。'
        : '掲載中の全'.$totalLabel.'台を、新しい順に表示しています。';
    // 4台ちょうどなら PC で4列に1行、それ以外（6台など）は3列で行をそろえる
    $stockGrid = $stockCars->count() === 4 ? 'l-grid--4' : 'l-grid--3';

    // 条件から探す：その場で「条件に合う車」の台数を数えるための元データ（公開在庫だけ。コントローラは変えずにビューで取得する）
    // 絞り込みの条件は在庫一覧（CarController@index）と同じ：価格は応談・未入力を除いて上限以下、走行距離は未入力を除いて上限以下
    $searchCars = \App\Models\Car::publicInventory()->get(['make', 'body_type', 'price', 'price_negotiable', 'mileage']);
    $searchData = $searchCars->map(fn ($car) => [
        'm' => (string) $car->make,
        'b' => (string) $car->body_type,
        'p' => ($car->price_negotiable || $car->price === null) ? null : (int) $car->price,
        'k' => $car->mileage === null ? null : (int) $car->mileage,
    ])->values()->all();
    $countWhere = fn (callable $test): int => $searchCars->filter($test)->count();

    // STEP.1 ボディタイプ：在庫があるものだけ（値は DB の値のまま、表示名だけ CarText で変える）
    $bodyTypeOptions = collect($bodyTypeCounts)
        ->filter(fn ($count, $type) => filled($type) && (int) $count > 0)
        ->map(fn ($count) => (int) $count);
    $priceOptions = range(1000000, 5000000, 500000);            // 支払総額の上限：100万〜500万円を50万円刻み（value は円）
    $mileageOptions = [10000, 30000, 50000, 80000, 100000];     // 走行距離の上限（value は km）
    $makeCounts = $searchCars->filter(fn ($car) => filled($car->make))->countBy('make');
    $makeOptions = collect($makes)
        ->filter(fn ($make) => filled($make) && ($makeCounts[$make] ?? 0) > 0)
        ->mapWithKeys(fn ($make) => [$make => (int) $makeCounts[$make]]);

    // 使い方から選ぶ：在庫が0台の入口は出さない。4枚に満たないときは［すべての在庫から選ぶ］を足す
    $purposeCards = collect([
        ['title' => '通勤・お買い物に', 'sub' => '小回りのきく軽自動車', 'icon' => 'bag', 'tone' => 'red', 'count' => (int) ($bodyTypeCounts['軽自動車'] ?? 0), 'url' => route('cars.index', ['body_type' => '軽自動車'])],
        ['title' => '子育て・家族に', 'sub' => '広く使えるミニバン', 'icon' => 'family', 'tone' => 'yellow', 'count' => (int) ($bodyTypeCounts['ミニバン'] ?? 0), 'url' => route('cars.index', ['body_type' => 'ミニバン'])],
        ['title' => '休日・レジャーに', 'sub' => '荷物を積みやすいSUV', 'icon' => 'mountain', 'tone' => 'black', 'count' => (int) ($bodyTypeCounts['SUV'] ?? 0), 'url' => route('cars.index', ['body_type' => 'SUV'])],
        ['title' => '予算を抑えたい', 'sub' => '支払総額'.number_format($budgetLimit / 10000).'万円以下', 'icon' => 'yen', 'tone' => 'tint', 'count' => (int) $budgetCount, 'url' => route('cars.index', ['max_price' => $budgetLimit])],
    ])->filter(fn (array $card) => $card['count'] > 0)->values();
    if ($totalPublic > 0 && $purposeCards->count() < 4) {
        $purposeCards->push(['title' => 'すべての在庫から', 'sub' => '迷ったらここから', 'icon' => 'car', 'tone' => 'green', 'count' => (int) $totalPublic, 'url' => route('cars.index')]);
    }

    // メーカー・条件から探す：在庫一覧で実際に絞り込めるクエリだけ（make / max_mileage / max_price / sort）。台数は実数、0台のものは出さない
    $chipMileage = 30000;
    $chipPrice = 2000000;
    $chips = collect();
    foreach ($makeOptions as $make => $count) {
        $chips->push(['label' => $make, 'icon' => 'car', 'tone' => 'black', 'count' => $count, 'url' => route('cars.index', ['make' => $make])]);
    }
    $mileageCount = $countWhere(fn ($car) => $car->mileage !== null && (int) $car->mileage <= $chipMileage);
    if ($mileageCount > 0) {
        $chips->push(['label' => '走行'.($chipMileage / 10000).'万km以下', 'icon' => 'meter', 'tone' => 'red', 'count' => $mileageCount, 'url' => route('cars.index', ['max_mileage' => $chipMileage])]);
    }
    $priceCount = $countWhere(fn ($car) => ! $car->price_negotiable && $car->price !== null && (int) $car->price <= $chipPrice);
    if ($priceCount > 0) {
        $chips->push(['label' => '支払総額'.($chipPrice / 10000).'万円以下', 'icon' => 'yen', 'tone' => 'yellow', 'count' => $priceCount, 'url' => route('cars.index', ['max_price' => $chipPrice])]);
    }
    if ($totalPublic > 0) {
        $chips->push(['label' => '新しく掲載した順に見る', 'icon' => 'star', 'tone' => 'green', 'count' => null, 'url' => route('cars.index', ['sort' => 'latest'])]);
    }

    // 当店のお約束（保証・整備は config の値。未設定なら「お問い合わせください」）
    $warranty = \App\Support\CarText::warranty() ?? \App\Support\CarText::UNKNOWN;
    $maintenance = \App\Support\CarText::maintenance() ?? \App\Support\CarText::UNKNOWN;
    $requestTitle = new \Illuminate\Support\HtmlString('<span class="u-nowrap">お探しの車が</span><span class="u-nowrap">見つからないときは</span>');
    $accessLead = new \Illuminate\Support\HtmlString('<span class="u-nowrap">'.e(config('shop.pref').config('shop.area')).'の店舗です。</span><span class="u-nowrap">看板を目印にお越しください。</span>');
    $promiseRepairTitle = new \Illuminate\Support\HtmlString('<span class="u-nowrap">修復歴を</span><span class="u-nowrap">「あり・なし」で表示</span>');

    // ご購入の流れ（x-site.flow）
    $flowSteps = [
        ['title' => '在庫を探す・お問い合わせ', 'text' => '気になる車が見つかったら、お電話かフォームで在庫の確認を。見積もりは無料です。在庫番号をお伝えいただくとスムーズです。', 'illust' => 'f-search'],
        ['title' => 'ご来店・見学・試乗', 'text' => '実際の車を見て、座って、確かめてください。店舗とは別の場所で保管している車は、ご予約のうえでご案内します。', 'illust' => 'f-store'],
        ['title' => 'ご契約・書類', 'text' => 'お支払いの方法（現金・ローン）を決めて、必要な書類をご案内します。', 'illust' => 'f-contract'],
        ['title' => '納車', 'text' => '名義変更などの手続きのあと、お車をお渡しします。ご自宅への納車をご希望の場合はご相談ください（別途費用）。', 'illust' => 'f-key'],
    ];

    // 代表
    $representative = config('shop.operator.representative');
    $representativeKana = config('shop.operator.representative_kana');

    // 古物商許可（番号が設定されているときだけ表示）
    $kobutsu = config('shop.kobutsu');
    $kobutsuLabel = filled($kobutsu['number'] ?? null)
        ? ($kobutsu['authority'] ?? '').' 第'.$kobutsu['number'].'号'.(filled($kobutsu['holder'] ?? null) ? '（'.$kobutsu['holder'].'）' : '')
        : null;

    // よくある質問（画面と FAQPage の構造化データを同じ文面で出す）
    $faqItems = [
        [
            'q' => '表示されている「支払総額」には、何が含まれていますか？',
            'a' => \App\Support\CarText::priceNote()."\n任意保険の保険料や、希望ナンバーの費用も含みません。お見積もりのときに、費用の内訳をご説明します。",
        ],
        [
            'q' => '「修復歴」とは何ですか？',
            'a' => \App\Support\CarText::REPAIR_DEFINITION.'ドアやバンパーなど、骨格以外の部品を修理・交換しただけの場合は、修復歴にはあたりません。'."\n".'当店では、車ごとに「修復歴 なし」「修復歴 あり」を表示しています。修復歴がある車は、修理した部位をお問い合わせのときにお伝えします。',
        ],
        [
            'q' => '車を買うときに必要な書類は何ですか？',
            'a' => "一般的には、次の書類が必要です。\n普通車：印鑑登録証明書（発行から3か月以内のもの）、実印、車庫証明（自動車保管場所証明書）\n軽自動車：住民票の写し（発行から3か月以内のもの）、認印、保管場所の届出（地域によって必要）\nこのほかに、運転免許証をお持ちください。ローンを利用する場合などは、ほかの書類が必要になることがあります。ご契約の前にご案内します。",
        ],
        [
            'q' => 'ローンで買うことはできますか？',
            'a' => 'はい、ローンでのお支払いのご相談をお受けしています。このページの「お支払いシミュレーション（計算例）」で、月々のお支払いの目安を計算できます。実際の金利・回数・お支払い額は、ローン会社の審査により異なります。ご希望に添えない場合もあります。',
        ],
        [
            'q' => 'お店に行くときは、予約が必要ですか？',
            'a' => 'ご予約がなくてもご来店いただけます。見たい車が決まっている場合は、事前にお電話かお問い合わせフォームでお知らせください。店舗とは別の場所で保管している車もあるため、ご予約のうえでご案内します。試乗をご希望の場合は、運転免許証をお持ちください。'."\n".'営業時間は'.\App\Support\BusinessHours::summary().'です。',
        ],
        [
            'q' => '今乗っている車を、下取りに出せますか？',
            'a' => 'はい、お乗り換えのときの下取りも、買取だけのご相談もお受けしています。査定は無料で、査定だけでも大丈夫です（売らなくても費用はかかりません）。「買取査定」のページからお申し込みいただけます。',
        ],
        [
            'q' => '兵庫県の外（大阪府など）に住んでいても買えますか？',
            'a' => 'はい、ご購入いただけます。ただし、表示している支払総額は'.config('shop.price_condition').'の価格です。兵庫県の外で登録する場合や、ご自宅への納車をご希望の場合は、別に費用がかかります。お住まいの地域をお知らせいただければ、その費用を含めたお見積もりをお出しします。',
        ],
    ];

    // 構造化データ：WebSite（AutoDealer はレイアウトが全ページ共通で出す。FAQPage は x-site.faq が出す）
    $websiteSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        '@id' => url('/').'#website',
        'url' => url('/'),
        'name' => $shopName,
        'description' => '兵庫県尼崎市下坂部の中古車販売店。支払総額・修復歴・車検の期限まで、見てわかるように表示しています。',
        'inLanguage' => 'ja',
        'publisher' => ['@id' => url('/').'#organization'],
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => [
                '@type' => 'EntryPoint',
                'urlTemplate' => route('cars.index').'?q={search_term_string}',
            ],
            'query-input' => 'required name=search_term_string',
        ],
    ];
@endphp

@section('meta_description', '兵庫県尼崎市下坂部の中古車販売店'.config('shop.name').'。掲載中の中古車'.number_format($totalPublic).'台を、支払総額・修復歴・車検の期限とあわせてご覧いただけます。販売・買取・ローン・車検整備まで、お気軽にご相談ください。')
@section('og_description', '兵庫県尼崎市下坂部の中古車販売店。支払総額・修復歴・車検の期限まで、見てわかるように表示しています。販売・買取・ローン・車検整備までご相談ください。')
@section('og_image', asset('images/store-hero-bg.png'))
@section('canonical', route('home'))
@section('body_class', 'p-home')

@push('structured_data')
    <script type="application/ld+json">{!! json_encode($websiteSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@push('head')
    <link rel="preload" as="image" href="{{ asset('images/store-hero-bg.png.webp') }}" type="image/webp">
@endpush

@section('content')

{{-- ============================================================
     1. 写真ヒーロー（斜めの黒・赤帯のキャッチ・丸バッジ・営業状況）＋ ヒーロー下の白いパネル
     ============================================================ --}}
<section class="c-hero" id="top" aria-labelledby="home-hero-title">
    <picture>
        <source type="image/webp" srcset="{{ asset('images/store-hero-bg.png.webp') }}">
        <img class="c-hero__img" src="{{ asset('images/store-hero-bg.png') }}" width="1584" height="672"
             alt="{{ $shopName }}の店舗外観。事務所の壁に「車検・中古車・買取・一般整備・板金・保険」と書いた看板があり、前の展示場に車が並んでいます。"
             fetchpriority="high" decoding="async">
    </picture>
    <div class="l-container c-hero__inner">
        <p class="c-hero__tag"><x-site.icon name="map-pin" />{{ config('shop.pref') }}{{ config('shop.area') }}の中古車販売店</p>
        <h1 class="c-hero__catch" id="home-hero-title">
            <span class="c-slant c-slant--black">納得して選べる、</span>
            <span class="c-slant c-slant--red">尼崎の中古車。</span>
        </h1>
        <p class="c-slant c-slant--white c-hero__sub"><em>支払総額</em>・<em>修復歴</em>・<span class="u-nowrap"><em>車検の期限</em>まで、</span><span class="u-nowrap">見てわかる。</span></p>
        @if ($totalPublic > 0)
            <x-site.round-badge class="c-hero__badge" top="ただいま" :num="$totalLabel" unit="台" bottom="掲載中"
                :href="route('cars.index')" :label="'ただいま'.$totalLabel.'台掲載中。掲載中の車を見る'" />
        @endif
        <p class="c-hero__pill"><x-site.open-status />{{ $hoursLabel }}｜{{ config('shop.closed_label') }}定休</p>
        <p class="c-hero__caption">写真：店舗外観（看板が目印です）</p>
    </div>
</section>

<div class="l-container">
    <div class="c-deck">
        <div class="c-deck__actions">
            @if ($totalPublic > 0)
                <x-site.btn2 :href="route('cars.index')" size="xl" block bubble="価格は支払総額（税込）で表示"
                    small="支払総額・修復歴もひと目で" :big="'掲載中の車を見る（'.$totalLabel.'台）'" />
            @else
                <x-site.btn2 :href="route('contact.index', ['purpose' => 'search'])" size="xl" block
                    small="入荷したらご連絡します" big="探してほしい車を伝える" />
            @endif
            <x-site.btn2 :href="$telHref" variant="black" size="xl" block num icon="phone"
                :aria-label="'電話をかける '.$tel"
                :small="'電話で相談する（'.$hoursLabel.'）'" :big="$tel" />
        </div>
        <div class="c-deck__aside">
            <p class="c-deck__label">当店のお約束</p>
            <ul class="c-deck__badges" role="list">
                <li><span class="c-deck__badge-ic"><x-site.icon name="tag" /></span><span class="c-deck__badge-t">支払総額で<br>表示</span></li>
                <li><span class="c-deck__badge-ic"><x-site.icon name="frame" /></span><span class="c-deck__badge-t">修復歴を<br>明示</span></li>
                <li><span class="c-deck__badge-ic"><x-site.icon name="cal-check" /></span><span class="c-deck__badge-t">車検の<br>期限を表示</span></li>
                <li><span class="c-deck__badge-ic c-deck__badge-ic--yellow"><x-site.icon name="yen" /></span><span class="c-deck__badge-t">買取査定<br>無料</span></li>
            </ul>
        </div>
    </div>
</div>

{{-- ============================================================
     2. 条件から在庫を探す（STEP.1 ボディタイプ → STEP.2 予算 → STEP.3 走行距離・メーカー → 条件に合う車 N台）
     ============================================================ --}}
<section class="l-section p-home-search" id="search" aria-labelledby="home-search-title">
    <div class="l-container">
        <x-site.section-head id="home-search-title" title="条件から在庫を探す" en="SEARCH"
            lead="ボディタイプ・予算・走行距離を選ぶだけ。決まっていない項目は、そのままで探せます。価格はすべて税込の支払総額です。" />

        <form class="c-arrow-steps" method="GET" action="{{ route('cars.index') }}" role="search" aria-labelledby="home-search-title"
              x-data="homeSearch(@js($searchData))" x-on:change="update()" x-on:pageshow.window="update()">
            <x-site.arrow-step :no="1" title="ボディタイプを選ぶ" heading-id="home-step1" class="p-home-search__step1">
                <div class="c-type-choices" role="radiogroup" aria-labelledby="home-step1">
                    @foreach ($bodyTypeOptions as $type => $count)
                        <x-site.type-choice name="body_type" :value="$type" :label="\App\Support\CarText::bodyType((string) $type)"
                            :illust="\App\Support\CarText::bodyIllust((string) $type)" :count="number_format($count).'台'" />
                    @endforeach
                    <x-site.type-choice name="body_type" value="" label="すべて" illust="all" :count="$totalLabel.'台'" checked />
                </div>
                <p class="p-home-search__hint"><x-site.icon name="info" />選ぶと「条件に合う車」の台数が変わります。</p>
            </x-site.arrow-step>

            <x-site.arrow-step :no="2" title="予算を決める" illust="coins" split>
                <div>
                    <label class="c-arrow-step__label" for="home-max-price">支払総額の上限</label>
                    <select class="c-field__input c-field__input--select" id="home-max-price" name="max_price">
                        <option value="">上限なし</option>
                        @foreach ($priceOptions as $yen)
                            <option value="{{ $yen }}">{{ number_format($yen / 10000) }}万円以下</option>
                        @endforeach
                    </select>
                </div>
            </x-site.arrow-step>

            <x-site.arrow-step :no="3" title="さらに絞り込む" illust="meter" split>
                <div class="p-home-search__fields">
                    <div>
                        <label class="c-arrow-step__label" for="home-max-mileage">走行距離の上限</label>
                        <select class="c-field__input c-field__input--select" id="home-max-mileage" name="max_mileage">
                            <option value="">上限なし</option>
                            @foreach ($mileageOptions as $km)
                                <option value="{{ $km }}">{{ number_format($km / 10000) }}万km以下</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="c-arrow-step__label" for="home-make">メーカー</label>
                        <select class="c-field__input c-field__input--select" id="home-make" name="make">
                            <option value="">すべて</option>
                            @foreach ($makeOptions as $make => $count)
                                <option value="{{ $make }}">{{ $make }}（{{ number_format($count) }}台）</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </x-site.arrow-step>

            <x-site.arrow-step go>
                <p class="c-arrow-step__count-label" id="home-search-count-label">条件に合う車</p>
                <p class="c-arrow-step__count" aria-live="polite" aria-atomic="true"><span x-text="hits">{{ $totalLabel }}</span><small>台</small></p>
                <button class="c-btn c-btn--yellow" type="submit"><x-site.icon name="search" />この条件で探す</button>
                <p class="c-arrow-step__note" x-show="hits === 0" x-cloak>条件をゆるめるか、お気軽にご相談ください</p>
            </x-site.arrow-step>
        </form>

        @if ($purposeCards->isNotEmpty())
            <h3 class="c-subhead p-home-search__sub">使い方から選ぶ</h3>
            <ul class="c-link-cards" role="list">
                @foreach ($purposeCards as $card)
                    <li><x-site.link-card :href="$card['url']" :icon="$card['icon']" :tone="$card['tone']" :title="$card['title']" :sub="$card['sub']" :count="number_format($card['count'])" /></li>
                @endforeach
            </ul>
        @endif

        @if ($chips->isNotEmpty())
            <h3 class="c-subhead p-home-search__sub">メーカー・条件から探す</h3>
            <ul class="c-chips c-chips--grid" role="list">
                @foreach ($chips as $chip)
                    <li><x-site.chip :href="$chip['url']" :icon="$chip['icon']" :tone="$chip['tone']" :count="$chip['count'] !== null ? number_format($chip['count']).'台' : null">{{ $chip['label'] }}</x-site.chip></li>
                @endforeach
            </ul>
        @endif
    </div>
</section>

{{-- ============================================================
     3. いま掲載中の車
     ============================================================ --}}
<section class="l-section l-section--soft" id="stock" aria-labelledby="home-stock-title">
    <div class="l-container">
        <x-site.section-head id="home-stock-title" title="いま掲載中の車" en="STOCK" :lead="$stockCars->isNotEmpty() ? $stockLead : null">
            @if ($totalPublic > 0)
                <p class="c-count-pill">全<b>{{ $totalLabel }}</b>台</p>
                <a class="c-more" href="{{ route('cars.index') }}">在庫一覧を見る<x-site.icon name="chevron-right" /></a>
            @endif
        </x-site.section-head>

        @if ($stockCars->isNotEmpty())
            <div class="c-alert">
                <x-site.icon name="info" />
                <p>価格はすべて<b>税込の支払総額</b>です。{{ \App\Support\CarText::priceNote() }}</p>
            </div>

            <ul class="l-grid {{ $stockGrid }} p-home-stock__grid" role="list">
                @foreach ($stockCars as $car)
                    <li><x-site.car-card :car="$car" :loading="$loop->index < 4 ? 'eager' : 'lazy'" /></li>
                @endforeach
            </ul>

            <div class="c-cta-row">
                <x-site.btn2 :href="route('cars.index')" small="絞り込み・並べ替えもできます" :big="'すべての在庫を見る（'.$totalLabel.'台）'" />
            </div>
        @else
            <x-site.empty-state title="ただいま掲載中の車はありません" illust="empty" :heading-level="3">
                探している車をお伝えいただければ、入荷したときにご連絡します。
                <x-slot:actions>
                    <a class="c-btn c-btn--primary" href="{{ route('contact.index', ['purpose' => 'search']) }}">探してほしい車を伝える</a>
                </x-slot:actions>
            </x-site.empty-state>
        @endif
    </div>
</section>

{{-- ============================================================
     4. 当店の4つのお約束（黒の斜線地）
     ============================================================ --}}
<section class="l-section l-section--dark" id="promise" aria-labelledby="home-promise-title">
    <div class="l-container">
        <x-site.band-title id="home-promise-title" title="当店の4つのお約束" en="OUR PROMISE" lead="はじめての方にも、比べやすく・わかりやすく表示します。" />
        <ol class="c-promises p-home-promises">
            <x-site.promise :no="1" illust="p-total" title="支払総額で表示">
                表示価格は、税金・自賠責保険料・登録費用込みの支払総額（税込）です。車両本体価格もあわせて表示します。
                <x-slot:visual>
                    <div class="c-promise__box"><span class="c-tag c-tag--body">車両本体</span>＋<span class="c-tag c-tag--fee">諸費用</span>＝<span class="c-tag c-tag--total">支払総額</span></div>
                    <p class="c-promise__note">※県外での登録・ご自宅への納車・ご希望のオプションは別途です。</p>
                </x-slot:visual>
            </x-site.promise>
            <x-site.promise :no="2" illust="p-frame" :title="$promiseRepairTitle">
                すべての車に、修復歴の有無をはっきり表示します。
                <x-slot:visual>
                    <div class="c-promise__box"><span class="c-tag c-tag--ok">修復歴 なし</span><span class="c-tag c-tag--caution">修復歴 あり</span></div>
                    <p class="c-promise__note">{{ \App\Support\CarText::REPAIR_DEFINITION }}</p>
                </x-slot:visual>
            </x-site.promise>
            <x-site.promise :no="3" illust="p-shaken" title="車検の期限を表示">
                車検がいつまで残っているかを表示します。確認中の車は「要確認」と表示し、お問い合わせにお答えします。
                <x-slot:visual>
                    <div class="c-promise__box"><span class="c-promise__cap">表示の例</span><span class="c-tag c-tag--outline">車検 ○年○月まで</span><span class="c-tag c-tag--check">車検 要確認</span></div>
                </x-slot:visual>
            </x-site.promise>
            <x-site.promise :no="4" illust="p-explain" title="保証と整備の内容をご説明">
                保証の有無や内容、納車前の定期点検整備（法定整備）について、ご契約の前にご説明します。
                <x-slot:visual>
                    <ul class="c-promise__checks" role="list">
                        <li class="c-promise__check"><x-site.icon name="check" /><span class="p-home-promise__fact"><span class="p-home-promise__label">保証</span>{{ $warranty }}</span></li>
                        <li class="c-promise__check"><x-site.icon name="check" /><span class="p-home-promise__fact"><span class="p-home-promise__label">定期点検整備</span>{{ $maintenance }}</span></li>
                    </ul>
                </x-slot:visual>
            </x-site.promise>
        </ol>
    </div>
</section>

{{-- ============================================================
     5. ご購入の流れ（道路とメダル）
     ============================================================ --}}
<section class="l-section" id="flow" aria-labelledby="home-flow-title">
    <div class="l-container">
        <x-site.section-head id="home-flow-title" title="ご購入の流れ" en="FLOW" lead="お問い合わせから納車までの流れです。" />
        <x-site.flow :steps="$flowSteps" />
        <div class="c-cta-row">
            <x-site.btn2 :href="route('contact.index', ['purpose' => 'visit'])" small="見学・試乗のご希望はこちら" big="来店予約をする" />
            <x-site.btn2 href="#loan" variant="black" small="月々の目安を計算できます" big="ローンのご相談" />
        </div>
    </div>
</section>

{{-- ============================================================
     6. お支払いシミュレーション（計算例。金利・回数は config('shop.loan') の例の値）
     ============================================================ --}}
<section class="l-section l-section--soft" id="loan" aria-labelledby="home-loan-title">
    <div class="l-container">
        <x-site.section-head id="home-loan-title" :title="new \Illuminate\Support\HtmlString('お支払いシミュレーション<small>（計算例）</small>')" en="LOAN"
            lead="支払総額・頭金・回数・金利を動かすと、月々のお支払いの目安がわかります。" />
        <x-site.loan-sim :cars="$newArrivals" id-prefix="home-loan" />
    </div>
</section>

{{-- ============================================================
     7. 今のお車の下取り・買取（黒い写真地に斜めの赤い面）＋ 車検・整備などのご相談
     ============================================================ --}}
<section class="l-section p-home-buy" id="buy" aria-labelledby="home-buy-title">
    <div class="l-container">
        <div class="c-promo">
            <div class="c-promo__media">
                <picture>
                    <source type="image/webp" srcset="{{ asset('images/buy-hero-bg.jpg.webp') }}">
                    <img src="{{ asset('images/buy-hero-bg.jpg') }}" alt="" width="1400" height="560" loading="lazy" decoding="async">
                </picture>
            </div>
            <div class="c-promo__body">
                <p class="c-slant c-slant--yellow">今のお車の下取り・買取</p>
                <h2 class="c-promo__title" id="home-buy-title">クルマを売るなら、<br><em>まずは無料査定</em>から。</h2>
                <p class="c-promo__lead">年式や走行距離などをお聞きして査定します。お乗り換えのときの下取りも、買取だけのご相談もお受けしています。</p>
                <div class="c-promo__actions">
                    <x-site.btn2 :href="route('buy.index').'#appraisal-form'" variant="yellow" small="査定は無料です" big="無料査定を申し込む" />
                    <a class="c-promo__tel" href="{{ $telHref }}" aria-label="電話をかける {{ $tel }}"><small>お電話でも受付中（{{ $hoursLabel }}）</small><b><x-site.icon name="phone" />{{ $tel }}</b></a>
                </div>
            </div>
            <ul class="c-promo__badges" role="list">
                <li><x-site.round-badge variant="yellow" top="査定" num="無料" /></li>
                <li><x-site.round-badge variant="yellow" top="査定だけ" num="でもOK" /></li>
                {{-- 下取りを受けていることは「よくある質問」と同じ事実。「しつこい営業電話なし」などオーナー未確認の約束は出さない --}}
                <li><x-site.round-badge variant="yellow" top="お乗り換えの" num="下取り" bottom="もOK" /></li>
            </ul>
        </div>

        <ul class="c-link-cards c-link-cards--3 p-home-buy__services" role="list">
            <li><x-site.link-card :href="route('contact.index', ['purpose' => 'loan'])" icon="calc" :title="new \Illuminate\Support\HtmlString('ローンの<b>ご相談</b>')" sub="月々の目安から一緒に考えます" /></li>
            <li><x-site.link-card :href="route('contact.index', ['purpose' => 'other'])" icon="wrench" :title="new \Illuminate\Support\HtmlString('<b>車検</b>・一般整備')" sub="車検・点検・修理のご相談" /></li>
            <li><x-site.link-card :href="route('contact.index', ['purpose' => 'other'])" icon="bankin" :title="new \Illuminate\Support\HtmlString('<b>板金</b>・保険')" sub="キズ・へこみの修理、保険のご相談" /></li>
        </ul>
    </div>
</section>

{{-- ============================================================
     8. お探しの車が見つからないとき
     ============================================================ --}}
<section class="l-section l-section--soft" id="request" aria-labelledby="home-request-title">
    <div class="l-container">
        <div class="p-home-request">
            <div class="p-home-request__art" aria-hidden="true">
                <x-site.illust name="empty" class="p-home-request__ill" />
            </div>
            <div class="p-home-request__body">
                <p class="c-slant c-slant--yellow p-home-request__tag">入荷したらご連絡します</p>
                <x-site.section-head id="home-request-title" :title="$requestTitle"
                    lead="ご希望の車種・予算・色などをお伝えください。条件に合う車が入荷したら、ご連絡します。" />
                <p class="p-home-request__note">フォームは24時間受け付けています。お電話の受付は{{ $hoursLabel }}です<span class="u-nowrap">（定休日 {{ config('shop.closed_label') }}）</span>。</p>
            </div>
            <div class="p-home-request__actions">
                <x-site.btn2 :href="route('contact.index', ['purpose' => 'search'])" block small="フォームで24時間受付" big="探してほしい車を伝える" />
                <x-site.btn2 :href="$telHref" variant="black" block num icon="phone" :aria-label="'電話をかける '.$tel"
                    :small="'電話で相談する（'.$hoursLabel.'）'" :big="$tel" />
                @if ($lineUrl)
                    <a class="c-btn c-btn--line c-btn--lg c-btn--block" href="{{ $lineUrl }}" target="_blank" rel="noopener">
                        <x-site.icon name="line" />LINEで相談<span class="u-visually-hidden">（新しいタブで開きます）</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     9. よくある質問（FAQPage の構造化データは x-site.faq が同じ文面で出す）
     ============================================================ --}}
<section class="l-section" id="faq" aria-labelledby="home-faq-title">
    <div class="l-container">
        <x-site.section-head id="home-faq-title" title="よくある質問" en="FAQ" lead="質問を押すと、答えが開きます。" />
        <x-site.faq :items="$faqItems" jsonld />
    </div>
</section>

{{-- ============================================================
     10. 店舗案内（写真・店舗の情報・営業時間表・当店について・代表からのごあいさつ）
     ============================================================ --}}
<section class="l-section l-section--soft" id="access" aria-labelledby="home-access-title">
    <div class="l-container">
        <x-site.section-head id="home-access-title" title="店舗案内" en="ACCESS" :lead="$accessLead" />

        <div class="p-home-access">
            <div class="p-home-access__main">
                <figure class="p-home-access__photo">
                    <picture>
                        <source type="image/webp" srcset="{{ asset('images/store-hero-bg.png.webp') }}">
                        <img class="p-home-access__img" src="{{ asset('images/store-hero-bg.png') }}" width="1584" height="672" loading="lazy" decoding="async"
                             alt="{{ $shopName }}の看板。「車検・中古車・買取・一般整備・板金・保険」と書かれています。">
                    </picture>
                    <figcaption class="p-home-access__caption"><span class="c-slant c-slant--yellow">この看板が目印です</span></figcaption>
                </figure>

                <dl class="p-home-info">
                    <div class="p-home-info__row">
                        <dt class="p-home-info__term"><span class="p-home-info__ic" aria-hidden="true"><x-site.icon name="map-pin" /></span>住所</dt>
                        <dd class="p-home-info__desc"><x-site.address postal /></dd>
                    </div>
                    <div class="p-home-info__row">
                        <dt class="p-home-info__term"><span class="p-home-info__ic" aria-hidden="true"><x-site.icon name="phone" /></span>電話</dt>
                        <dd class="p-home-info__desc"><a class="p-home-info__tel" href="{{ $telHref }}" aria-label="電話をかける {{ $tel }}">{{ $tel }}</a></dd>
                    </div>
                    <div class="p-home-info__row">
                        <dt class="p-home-info__term"><span class="p-home-info__ic" aria-hidden="true"><x-site.icon name="parking" /></span>駐車場</dt>
                        <dd class="p-home-info__desc">{{ config('shop.parking') }}</dd>
                    </div>
                    <div class="p-home-info__row">
                        <dt class="p-home-info__term"><span class="p-home-info__ic" aria-hidden="true"><x-site.icon name="store" /></span>事業内容</dt>
                        <dd class="p-home-info__desc">@foreach ((array) config('shop.business_lines') as $line)<span class="u-nowrap">{{ $line }}@if (! $loop->last)・@endif</span>@endforeach</dd>
                    </div>
                    @if ($kobutsuLabel !== null)
                        <div class="p-home-info__row">
                            <dt class="p-home-info__term"><span class="p-home-info__ic" aria-hidden="true"><x-site.icon name="file" /></span>古物商許可</dt>
                            <dd class="p-home-info__desc">{{ $kobutsuLabel }}</dd>
                        </div>
                    @endif
                </dl>

                <div class="p-home-access__actions">
                    <a class="c-btn c-btn--black" href="{{ route('store') }}"><x-site.icon name="store" />店舗案内・アクセスを詳しく見る</a>
                    <a class="c-btn c-btn--secondary" href="{{ config('shop.directions_url') }}" target="_blank" rel="noopener"><x-site.icon name="route" />地図アプリで道順を見る<span class="u-visually-hidden">（新しいタブで開きます）</span></a>
                </div>
            </div>

            <div class="p-home-access__side">
                <div class="c-card c-card--accent">
                    <h3 class="c-subhead">営業時間・定休日</h3>
                    <p class="p-home-access__status"><x-site.open-status /></p>
                    <x-site.business-hours />
                </div>
            </div>
        </div>

        <div class="p-home-about">
            <div class="c-card c-card--accent">
                <h3 class="c-subhead">当店について</h3>
                <p class="p-home-about__text">{{ $shopName }}は、{{ config('shop.pref') }}{{ config('shop.area') }}にある中古車販売店です。{{ implode('・', (array) config('shop.business_lines')) }}のご相談を、ひとつのお店でお受けしています。{{ config('shop.city') }}のほか、西宮市・伊丹市・大阪市など近隣の方も、お気軽にお越しください。</p>
            </div>
            <div class="c-card c-card--accent p-home-greeting">
                <h3 class="c-subhead">代表からのごあいさつ</h3>
                <blockquote class="p-home-greeting__quote">
                    <p>「お客様に長く安心して乗っていただける一台を」。これが当店の信条です。尼崎の地で、地域のみなさまに支えられてきました。車のことはもちろん、ローンや保証のご相談まで、何でもお気軽にお声がけください。一台一台、誠実にご紹介します。</p>
                </blockquote>
                @if (filled($representative))
                    <p class="p-home-greeting__sign">代表　{{ $representative }}@if (filled($representativeKana))（{{ $representativeKana }}）@endif</p>
                @endif
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    // 条件から在庫を探す：選んだ条件に合う公開在庫の台数をその場で数える（在庫一覧の絞り込みと同じ条件）
    // cars: [{ m: メーカー, b: ボディタイプ（DB の値）, p: 支払総額（円。応談・未入力は null）, k: 走行距離（km。未入力は null） }]
    Alpine.data('homeSearch', (cars) => ({
        cars: Array.isArray(cars) ? cars : [],
        hits: Array.isArray(cars) ? cars.length : 0,
        init() {
            this.update();
        },
        update() {
            const data = new FormData(this.$el);
            const body = String(data.get('body_type') || '');
            const make = String(data.get('make') || '');
            const maxPrice = Number(data.get('max_price') || 0);
            const maxKm = Number(data.get('max_mileage') || 0);
            this.hits = this.cars.filter((car) =>
                (body === '' || car.b === body)
                && (make === '' || car.m === make)
                && (! maxPrice || (car.p !== null && car.p <= maxPrice))
                && (! maxKm || (car.k !== null && car.k <= maxKm))
            ).length;
        },
    }));
});
</script>
@endpush
