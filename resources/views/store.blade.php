@extends('layouts.site')

@php
    use App\Support\BusinessHours;
    use Illuminate\Support\HtmlString;

    $shopName = config('shop.name');
    $tel = config('shop.tel');
    $telHref = config('shop.tel_href');
    $lineUrl = config('shop.line_url');
    $parking = (string) config('shop.parking');
    $closedLabel = (string) config('shop.closed_label');
    $directionsUrl = config('shop.directions_url');
    $hoursLabel = BusinessHours::hoursLabel();
    $hours = (array) config('shop.hours', []);
    $kobutsu = (array) config('shop.kobutsu', []);
    $operator = (array) config('shop.operator', []);
    $businessLines = array_values(array_filter((array) config('shop.business_lines', []), 'filled'));
    // 「中古車販売・買取・車検・…」を業務名の途中で改行しない形にする（「車／検」のような改行を防ぐ）
    $businessLinesText = new HtmlString(collect($businessLines)->map(fn ($line, $i) => '<span class="u-nowrap">'.e($line).($i < count($businessLines) - 1 ? '・' : '').'</span>')->implode(''));

    // 地図の埋め込み：config に URL があればそれを使い、なければ住所で検索した Google マップを埋め込む
    $mapEmbedUrl = config('shop.map_embed_url')
        ?: 'https://maps.google.com/maps?q='.rawurlencode((string) config('shop.address')).'&output=embed&z=16&hl=ja';

    $pageDescription = '兵庫県尼崎市下坂部の中古車販売店'.$shopName.'の店舗案内です。住所・地図、お車・電車・バスでの行き方、営業時間（'.BusinessHours::summary().'）、駐車場をご案内します。';

    // 語の単位でだけ折り返す（狭いスマホで「定休／日」のような語の途中の改行を防ぐ）。
    // 外側を1つの span で包む（横並びの部品の中に置いても、語と語の間にすき間ができないように）
    $nowrap = fn (array $parts) => new HtmlString('<span>'.collect($parts)->map(fn ($part) => '<span class="u-nowrap">'.e($part).'</span>')->implode('').'</span>');

    // ---- 営業時間・定休日（判定はすべて BusinessHours。日付は東京の暦日） ----
    $weekdayShort = ['日', '月', '火', '水', '木', '金', '土'];
    $today = \Carbon\CarbonImmutable::instance(now())->setTimezone(BusinessHours::TIMEZONE)->startOfDay();

    // 定休日の決まり（config の曜日から作る）：毎週 木曜日／毎月 第3日曜日
    $closedRules = [];
    foreach ((array) config('shop.closed_weekdays', []) as $weekday) {
        $closedRules[] = ['cycle' => '毎週', 'day' => $weekdayShort[(int) $weekday].'曜日'];
    }
    $nthRules = (array) config('shop.closed_nth_weekdays', []);
    foreach ($nthRules as [$nth, $weekday]) {
        $closedRules[] = ['cycle' => '毎月', 'day' => '第'.(int) $nth.$weekdayShort[(int) $weekday].'曜日'];
    }

    // 次の第3日曜（今日が第3日曜のときは営業時間表の「本日は定休日です」で伝わるので、明日以降から探す）
    $nthLabel = $nthRules !== [] ? '第'.(int) $nthRules[0][0].$weekdayShort[(int) $nthRules[0][1]].'曜' : null;
    $nextNthDates = $nthRules !== [] ? BusinessHours::upcomingNthClosed(3, $today->addDay()) : [];
    $holidays = BusinessHours::upcomingHolidays($today);

    // 営業日カレンダー（今月・来月。日曜はじまり）
    $calendars = [];
    foreach ([0, 1] as $offset) {
        $month = $today->startOfMonth()->addMonthsNoOverflow($offset);
        $cells = array_fill(0, $month->dayOfWeek, null);
        for ($day = 1; $day <= $month->daysInMonth; $day++) {
            $date = $month->setDay($day);
            $cells[] = [
                'day' => $day,
                'reason' => BusinessHours::closedReason($date),
                'today' => $date->isSameDay($today),
                'past' => $date->lt($today),
            ];
        }
        while (count($cells) % 7 !== 0) {
            $cells[] = null;
        }
        $calendars[] = [
            'tab' => ($offset === 0 ? '今月' : '来月').'（'.$month->month.'月）',
            'caption' => $month->year.'年'.$month->month.'月の営業日カレンダー（定休日に印を付けています）',
            'weeks' => array_chunk($cells, 7),
        ];
    }

    // ---- 当店でできること（看板の6業務。config の業務名から作る） ----
    $publicCount = \App\Models\Car::publicInventory()->count();
    $contactOther = route('contact.index', ['purpose' => 'other']);
    $serviceDefs = [
        '中古車販売' => ['icon' => 'car', 'tone' => 'red', 'href' => route('cars.index'), 'text' => '価格は税込の支払総額で掲載しています', 'more' => '在庫を見る', 'count' => $publicCount],
        '買取' => ['icon' => 'yen', 'tone' => 'yellow', 'href' => route('buy.index'), 'text' => '査定は無料です。査定だけでも大丈夫です', 'more' => '無料査定を申し込む'],
        '車検' => ['icon' => 'cal-check', 'tone' => 'black', 'href' => $contactOther, 'text' => '車検のご予約・ご相談', 'more' => '相談する'],
        '一般整備' => ['icon' => 'wrench', 'tone' => 'red', 'href' => $contactOther, 'text' => '点検・修理のご相談', 'more' => '相談する'],
        '板金' => ['icon' => 'bankin', 'tone' => 'yellow', 'href' => $contactOther, 'text' => 'キズ・へこみの修理のご相談', 'more' => '相談する'],
        '保険' => ['icon' => 'shield', 'tone' => 'black', 'href' => $contactOther, 'text' => 'お車の保険のご相談', 'more' => '相談する'],
    ];
    $services = array_map(fn (string $name) => ['name' => $name] + ($serviceDefs[$name] ?? [
        'icon' => 'check', 'tone' => 'red', 'href' => $contactOther, 'text' => $name.'のご相談', 'more' => '相談する',
    ]), $businessLines);

    // ---- 会社概要（値が null の行は出さない） ----
    $representative = $operator['representative'] ?? null;
    if (filled($representative) && filled($operator['representative_kana'] ?? null)) {
        $representative .= '（'.$operator['representative_kana'].'）';
    }
    $kobutsuText = filled($kobutsu['number'] ?? null)
        ? trim(($kobutsu['authority'] ?? '').' 第'.$kobutsu['number'].'号'.(filled($kobutsu['holder'] ?? null) ? '（'.$kobutsu['holder'].'）' : ''))
        : null;

    // ---- よくある質問（店舗について）。画面と FAQPage の構造化データを同じ配列から出す ----
    $faqItems = [
        [
            'q' => '予約をしないでお店に行ってもいいですか？',
            'a' => 'はい、予約なしでもご来店いただけます。ただし、在庫の車の中には当店以外の場所で保管している車もあります。見たい車が決まっている場合は、ご来店の前にお電話かお問い合わせフォームで、車の展示場所をご確認ください。',
        ],
        [
            'q' => '駐車場はありますか？',
            'a' => 'はい、無料の駐車場があります。お車でそのままお越しください。',
        ],
        [
            'q' => '電車で行く場合、駅まで迎えに来てもらえますか？',
            'a' => 'はい、事前にご連絡いただければ、駅までお迎えに参ります。ご来店の前に、お電話（'.$tel.'）でお知らせください。',
        ],
        [
            'q' => '土曜・日曜や祝日も営業していますか？',
            'a' => 'はい、土曜・日曜・祝日も'.$hoursLabel.'で営業しています。定休日は'.$closedLabel.'です。',
        ],
    ];

    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'ホーム', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => '店舗案内・アクセス', 'item' => route('store')],
        ],
    ];
@endphp

@section('title', '店舗案内・アクセス')
@section('meta_description', $pageDescription)
@section('og_title', '店舗案内・アクセス | '.$shopName)
@section('og_description', $pageDescription)
@section('og_image', asset('images/store-hero-bg.png'))
@section('canonical', route('store'))
@section('body_class', 'p-store')

@push('structured_data')
    <script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@push('head')
    <link rel="preload" as="image" href="{{ asset('images/store-hero-bg.png.webp') }}" type="image/webp">
@endpush

@section('content')

<div class="p-store-crumb">
    <div class="l-container">
        <x-site.breadcrumb :items="[['label' => '店舗案内・アクセス']]" />
    </div>
</div>

{{-- 1. 写真ヒーロー：看板入りの店舗外観に、斜め帯の見出し・住所の札・営業状況のピル --}}
<section class="c-hero p-store-top" aria-labelledby="store-title">
    <picture>
        <source type="image/webp" srcset="{{ asset('images/store-hero-bg.png.webp') }}">
        <img class="c-hero__img" src="{{ asset('images/store-hero-bg.png') }}" alt="{{ $shopName }}の店舗外観。「車検・中古車・買取・一般整備・板金・保険」の看板の前に、展示車が並んでいる" width="1584" height="672" fetchpriority="high">
    </picture>
    <div class="l-container c-hero__inner">
        <p class="c-hero__tag"><x-site.icon name="map-pin" /><span><x-site.address postal /></span></p>
        <h1 class="c-hero__catch" id="store-title">
            <span class="c-slant c-slant--black">店舗案内・</span>
            <span class="c-slant c-slant--red">アクセス</span>
        </h1>
        <p class="c-slant c-slant--white c-hero__sub"><span class="u-nowrap"><em>お車</em>でも、</span><span class="u-nowrap"><em>電車・バス</em>でも、</span><span class="u-nowrap">お越しいただけます。</span></p>
        <p class="c-hero__pill"><x-site.open-status />{{ $hoursLabel }}｜{{ $closedLabel }}定休</p>
        <p class="c-hero__caption">写真：店舗外観（この看板が目印です）</p>
    </div>
</section>

{{-- 2. お店の基本情報：ヒーローの下端に重ねる白いパネル（看板の拡大写真＋住所・営業状況・営業時間・定休日・電話・駐車場） --}}
<section class="p-store-basic" aria-labelledby="store-basic-title">
    <div class="l-container">
        <div class="p-store-deck">
            <figure class="p-store-sign">
                <div class="p-store-sign__frame">
                    <picture>
                        <source type="image/webp" srcset="{{ asset('images/store-hero-bg.png.webp') }}">
                        <img class="p-store-sign__img" src="{{ asset('images/store-hero-bg.png') }}" width="1584" height="672" loading="lazy"
                             alt="店舗の看板を大きく写した写真。「車検・中古車・買取・一般整備・板金・保険」「Asada Auto Support」と電話番号が書かれている">
                    </picture>
                </div>
                <ul class="p-store-sign__badges" role="list">
                    @if (str_contains($parking, '無料'))
                        <li><x-site.round-badge variant="yellow" top="無料" num="駐車場" bottom="あり" /></li>
                    @endif
                    <li><x-site.round-badge variant="yellow" top="駅まで" num="送迎" bottom="要事前連絡" /></li>
                </ul>
                <figcaption class="c-slant c-slant--yellow p-store-sign__label"><x-site.icon name="map-pin" />この看板が目印です</figcaption>
            </figure>

            <div class="p-store-info">
                <h2 class="p-store-info__label" id="store-basic-title">お店の基本情報</h2>
                <div>
                    <p class="p-store-info__name">{{ $shopName }}</p>
                    <p class="p-store-info__lead">兵庫県尼崎市下坂部の中古車販売店です。お車でも、電車・バスでもお越しいただけます。</p>
                </div>

                <x-site.open-status variant="badge" class="p-store-info__status" />

                <dl class="p-store-facts">
                    <div class="p-store-facts__row p-store-facts__row--wide">
                        <dt class="p-store-facts__label"><span class="p-store-facts__ic"><x-site.icon name="map-pin" /></span>住所</dt>
                        <dd class="p-store-facts__value"><x-site.address postal /></dd>
                    </div>
                    <div class="p-store-facts__row">
                        <dt class="p-store-facts__label"><span class="p-store-facts__ic"><x-site.icon name="clock" /></span>営業時間</dt>
                        <dd class="p-store-facts__value p-store-facts__value--num">{{ $hoursLabel }}</dd>
                    </div>
                    <div class="p-store-facts__row">
                        <dt class="p-store-facts__label"><span class="p-store-facts__ic p-store-facts__ic--closed"><x-site.icon name="calendar" /></span>定休日</dt>
                        <dd class="p-store-facts__value p-store-facts__value--closed">{{ $closedLabel }}</dd>
                    </div>
                    <div class="p-store-facts__row p-store-facts__row--wide">
                        <dt class="p-store-facts__label"><span class="p-store-facts__ic"><x-site.icon name="phone" /></span>電話</dt>
                        <dd class="p-store-facts__value">
                            <a class="p-store-facts__tel" href="{{ $telHref }}" aria-label="電話をかける {{ $tel }}">{{ $tel }}</a>
                        </dd>
                    </div>
                    <div class="p-store-facts__row p-store-facts__row--wide">
                        <dt class="p-store-facts__label"><span class="p-store-facts__ic"><x-site.icon name="parking" /></span>駐車場</dt>
                        <dd class="p-store-facts__value">{{ $parking }}</dd>
                    </div>
                </dl>
            </div>

            <div class="p-store-actions">
                <x-site.btn2 :href="$telHref" icon="phone" size="xl" block small="お電話でのご相談はこちら" big="電話する"
                             :aria-label="'電話をかける '.$tel" />
                <x-site.btn2 :href="$directionsUrl" variant="black" icon="route" size="xl" block target="_blank" rel="noopener"
                             small="今いる場所からの道順がわかります" :big="$nowrap(['地図アプリで', '道順を見る'])"
                             aria-label="地図アプリで道順を見る（新しいタブで開きます）" />
                @if ($lineUrl)
                    <a class="c-btn c-btn--line c-btn--lg c-btn--block p-store-actions__line" href="{{ $lineUrl }}" target="_blank" rel="noopener">
                        <x-site.icon name="line" />LINEで相談<span class="u-visually-hidden">（新しいタブで開きます）</span>
                    </a>
                @endif
            </div>

            <nav class="p-store-jump" aria-label="このページの内容">
                <p class="p-store-jump__label">このページの内容</p>
                <ul class="c-chips c-chips--grid p-store-jump__list" role="list">
                    <li><x-site.chip href="#map" icon="map-pin">地図・行き方</x-site.chip></li>
                    <li><x-site.chip href="#hours" icon="clock" tone="yellow">{{ $nowrap(['営業時間・', '定休日']) }}</x-site.chip></li>
                    <li><x-site.chip href="#service" icon="wrench" tone="black">{{ $nowrap(['当店で', 'できること']) }}</x-site.chip></li>
                    <li><x-site.chip href="#faq" icon="info">よくある質問</x-site.chip></li>
                </ul>
            </nav>
        </div>
    </div>
</section>

{{-- 3. 地図とアクセス：道順の大ボタン → 地図 → お車で・電車で・バスで → 駅までの送迎 --}}
<section class="l-section l-section--soft" id="map" aria-labelledby="store-map-title">
    <div class="l-container">
        <x-site.section-head id="store-map-title" title="地図とアクセス" en="ACCESS"
            lead="［地図アプリで道順を見る］を押すと、今いる場所からお店までの道順を地図アプリで確かめられます。" />

        <div class="p-store-map">
            <div class="p-store-map__bar">
                <p class="p-store-map__addr">
                    <span class="p-store-map__pin" aria-hidden="true"><x-site.icon name="map-pin" /></span>
                    <span class="p-store-map__addr-text"><span class="p-store-map__addr-label">住所</span><x-site.address postal /></span>
                </p>
                <x-site.btn2 :href="$directionsUrl" size="xl" icon="route" class="p-store-map__btn" target="_blank" rel="noopener"
                             bubble="Googleマップが開きます" small="今いる場所からお店まで" :big="$nowrap(['地図アプリで', '道順を見る'])"
                             aria-label="地図アプリで道順を見る（新しいタブで開きます）" />
            </div>
            <div class="p-store-map__body">
                <p class="p-store-map__fallback">地図を読み込んでいます。表示されない場合は、上の［地図アプリで道順を見る］をお使いください。</p>
                <iframe class="p-store-map__frame" src="{{ $mapEmbedUrl }}"
                        title="{{ $shopName }}の地図（Googleマップ）"
                        loading="lazy" allowfullscreen referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>

        <h3 class="c-subhead p-store-ways-title">お車・電車・バスでの行き方</h3>
        <ul class="p-store-ways" role="list">
            <li class="p-store-way p-store-way--car">
                <h4 class="p-store-way__title"><span class="p-store-way__ic" aria-hidden="true"><x-site.icon name="car" /></span>お車で</h4>
                <div class="p-store-way__body">
                    <p class="p-store-way__key">
                        <span class="p-store-way__from">{{ $nowrap(['阪神高速11号池田線', '「尼崎東」出口から']) }}</span>
                        <span class="p-store-way__time"><span class="p-store-way__mode">お車で</span>約<b>5</b>分</span>
                    </p>
                    <ul class="p-store-way__list" role="list">
                        <li>国道2号線から下坂部方面へお越しください。</li>
                    </ul>
                    <p class="p-store-way__tag"><x-site.icon name="parking" />{{ $parking }}</p>
                </div>
            </li>
            <li class="p-store-way p-store-way--train">
                <h4 class="p-store-way__title"><span class="p-store-way__ic" aria-hidden="true"><x-site.icon name="train" /></span>電車で</h4>
                <div class="p-store-way__body">
                    <p class="p-store-way__key">
                        <span class="p-store-way__from">JR尼崎駅から</span>
                        <span class="p-store-way__time"><span class="p-store-way__mode">タクシーで</span>約<b>10</b>分</span>
                    </p>
                    <p class="p-store-way__key">
                        <span class="p-store-way__from">阪神尼崎駅から</span>
                        <span class="p-store-way__time"><span class="p-store-way__mode">タクシーで</span>約<b>12</b>分</span>
                    </p>
                    <ul class="p-store-way__list" role="list">
                        <li>駅までのお迎えもできます（下の「駅までの送迎」をご覧ください）。</li>
                    </ul>
                </div>
            </li>
            <li class="p-store-way p-store-way--bus">
                <h4 class="p-store-way__title"><span class="p-store-way__ic" aria-hidden="true"><x-site.icon name="bus" /></span>バスで</h4>
                <div class="p-store-way__body">
                    <p class="p-store-way__key">
                        <span class="p-store-way__from">{{ $nowrap(['阪神バス', '「下坂部」停留所から']) }}</span>
                        <span class="p-store-way__time"><span class="p-store-way__mode">歩いて</span>約<b>3</b>分</span>
                    </p>
                    <ul class="p-store-way__list" role="list">
                        <li>阪神尼崎駅の北口から、阪神バスをご利用ください。</li>
                    </ul>
                </div>
            </li>
        </ul>

        <div class="p-store-pickup">
            <div class="p-store-pickup__art" aria-hidden="true"><x-site.illust name="f-store" /></div>
            <div class="p-store-pickup__body">
                <p class="c-slant c-slant--yellow p-store-pickup__kicker">電車でお越しの方へ</p>
                <h4 class="p-store-pickup__title">駅までの送迎</h4>
                <p class="p-store-pickup__text">事前にご連絡いただければ、駅までお迎えに参ります。ご希望の方は、ご来店の前にお電話でお知らせください。</p>
            </div>
            <x-site.btn2 :href="$telHref" variant="black" icon="phone" num block class="p-store-pickup__btn"
                         small="送迎のお申し込みはお電話で" :big="$tel" :aria-label="'電話をかける '.$tel" />
        </div>
    </div>
</section>

{{-- 4. 営業時間・定休日：黒い案内板（営業時間・定休日の決まり・次の第3日曜）→ 曜日ごとの表＋営業日カレンダー --}}
<section class="l-section" id="hours" aria-labelledby="store-hours-title">
    <div class="l-container">
        <x-site.section-head id="store-hours-title" title="営業時間・定休日" en="OPEN HOURS" :lead="'定休日は'.$closedLabel.'です。土曜・日曜・祝日も営業しています。'" />

        <div class="p-store-board">
            <div class="p-store-board__item">
                <p class="c-slant c-slant--yellow p-store-board__label"><x-site.icon name="clock" />営業時間</p>
                @if (filled($hours['open'] ?? null) && filled($hours['close'] ?? null))
                    <p class="p-store-board__time"><span class="u-num">{{ $hours['open'] }}</span><span class="p-store-board__tilde">〜</span><span class="u-num">{{ $hours['close'] }}</span></p>
                @else
                    <p class="p-store-board__time">{{ $hoursLabel }}</p>
                @endif
                <x-site.open-status variant="badge" />
            </div>
            @if ($closedRules !== [])
                <div class="p-store-board__item">
                    <p class="c-slant c-slant--yellow p-store-board__label"><x-site.icon name="calendar" />定休日</p>
                    <ul class="p-store-board__rules" role="list">
                        @foreach ($closedRules as $rule)
                            <li class="p-store-board__rule">
                                <span class="p-store-board__rule-ic" aria-hidden="true"><x-site.icon name="close" /></span>
                                <span><span class="p-store-board__cycle">{{ $rule['cycle'] }}</span>{{ $rule['day'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if ($nextNthDates !== [])
                @php $nextNth = $nextNthDates[0]; @endphp
                <div class="p-store-board__item">
                    <p class="c-slant c-slant--yellow p-store-board__label"><x-site.icon name="cal-check" />次の{{ $nthLabel }}（定休日）</p>
                    <p class="p-store-board__date">
                        <b class="u-num">{{ $nextNth->month }}</b>月<b class="u-num">{{ $nextNth->day }}</b>日<span class="p-store-board__wd">（{{ $weekdayShort[$nextNth->dayOfWeek] }}）</span>
                    </p>
                    @if (count($nextNthDates) > 1)
                        <p class="p-store-board__after">そのあとは {{ collect(array_slice($nextNthDates, 1))->map(fn ($date) => BusinessHours::dateLabel($date))->implode('・') }}</p>
                    @endif
                </div>
            @endif
        </div>

        @if ($holidays !== [])
            <div class="c-alert c-alert--warn p-store-holidays">
                <x-site.icon name="alert" />
                <div class="c-alert__body">
                    <p class="c-alert__title">臨時休業のお知らせ</p>
                    @foreach ($holidays as $holiday)
                        <p>{{ $holiday['label'].($holiday['reason'] !== '' ? '（'.$holiday['reason'].'）' : '') }}は休業します。</p>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="p-store-hours">
            <div class="p-store-hours__week">
                <h3 class="c-subhead">曜日ごとの営業時間</h3>
                <x-site.business-hours :notes="false" />
            </div>

            <div class="p-store-cal" x-data="{ tab: 0 }">
                <div class="p-store-cal__head">
                    <h3 class="c-subhead p-store-cal__title">営業日カレンダー</h3>
                    <div class="c-tabs p-store-cal__tabs" role="tablist" aria-label="表示する月">
                        @foreach ($calendars as $i => $calendar)
                            <button type="button" class="c-tabs__tab" role="tab" id="store-cal-tab-{{ $i }}" aria-controls="store-cal-{{ $i }}" x-ref="calTab{{ $i }}"
                                    aria-selected="{{ $i === 0 ? 'true' : 'false' }}" x-bind:aria-selected="tab === {{ $i }} ? 'true' : 'false'"
                                    tabindex="{{ $i === 0 ? '0' : '-1' }}" x-bind:tabindex="tab === {{ $i }} ? 0 : -1"
                                    x-on:click="tab = {{ $i }}"
                                    x-on:keydown.right.prevent="tab = (tab + 1) % {{ count($calendars) }}; $nextTick(() => $refs['calTab' + tab].focus())"
                                    x-on:keydown.left.prevent="tab = (tab + {{ count($calendars) - 1 }}) % {{ count($calendars) }}; $nextTick(() => $refs['calTab' + tab].focus())">{{ $calendar['tab'] }}</button>
                        @endforeach
                    </div>
                </div>

                @foreach ($calendars as $i => $calendar)
                    <div class="p-store-cal__panel" role="tabpanel" id="store-cal-{{ $i }}" aria-labelledby="store-cal-tab-{{ $i }}"
                         x-show="tab === {{ $i }}" @if ($i > 0) x-cloak @endif>
                        <table class="p-store-cal__table">
                            <caption class="u-visually-hidden">{{ $calendar['caption'] }}</caption>
                            <thead>
                                <tr>
                                    @foreach ($weekdayShort as $w => $name)
                                        <th scope="col" class="p-store-cal__wd p-store-cal__wd--{{ $w }}"><abbr class="p-store-cal__abbr" title="{{ $name }}曜日">{{ $name }}</abbr></th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($calendar['weeks'] as $week)
                                    <tr>
                                        @foreach ($week as $cell)
                                            @if ($cell === null)
                                                <td class="p-store-cal__cell is-empty"></td>
                                            @else
                                                <td class="p-store-cal__cell{{ $cell['reason'] !== null ? ' is-closed' : '' }}{{ $cell['today'] ? ' is-today' : '' }}{{ $cell['past'] ? ' is-past' : '' }}"@if ($cell['today']) aria-current="date"@endif>
                                                    <span class="p-store-cal__num">{{ $cell['day'] }}</span>
                                                    @if ($cell['reason'] !== null)
                                                        <span class="p-store-cal__mark"><x-site.icon name="close" />{{ $cell['reason'] === 'holiday' ? '休業' : '定休' }}</span>
                                                    @elseif ($cell['today'])
                                                        <span class="p-store-cal__mark p-store-cal__mark--today">今日</span>
                                                    @endif
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach

                <ul class="p-store-cal__legend" role="list">
                    <li class="p-store-cal__key"><span class="p-store-cal__swatch p-store-cal__swatch--closed" aria-hidden="true"><x-site.icon name="close" /></span>定休日</li>
                    <li class="p-store-cal__key"><span class="p-store-cal__swatch p-store-cal__swatch--today" aria-hidden="true"></span>今日</li>
                    <li class="p-store-cal__key"><span class="p-store-cal__swatch" aria-hidden="true"></span><span>営業日（<span class="u-nowrap">{{ $hoursLabel }}</span>）</span></li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- 5. 当店でできること（看板の6業務）：黒の斜線地にアイコンのタイル --}}
<section class="l-section l-section--dark" id="service" aria-labelledby="store-service-title">
    <div class="l-container">
        <x-site.band-title id="store-service-title" title="当店でできること" en="SERVICE"
            lead="看板にある業務です。お車のことは、まとめてご相談ください。" />
        <ul class="p-store-services" role="list">
            @foreach ($services as $service)
                <li>
                    <a class="p-store-service" href="{{ $service['href'] }}">
                        <span class="p-store-service__no" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="p-store-service__ic p-store-service__ic--{{ $service['tone'] }}" aria-hidden="true"><x-site.icon :name="$service['icon']" /></span>
                        <span class="p-store-service__name">{{ $service['name'] }}</span>
                        <span class="p-store-service__text">{{ $service['text'] }}</span>
                        @if (($service['count'] ?? 0) > 0)
                            <span class="p-store-service__count">いま<b class="u-num">{{ $service['count'] }}</b>台を掲載中</span>
                        @endif
                        <span class="p-store-service__more">{{ $service['more'] }}<x-site.icon name="chevron-right" /></span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</section>

{{-- 6. 会社概要（config が null の行は出さない）＋はじめての方への案内 --}}
<section class="l-section l-section--soft" id="company" aria-labelledby="store-company-title">
    <div class="l-container">
        <x-site.section-head id="store-company-title" title="会社概要" en="COMPANY" />

        <div class="p-store-company">
            <div class="c-table-wrap p-store-company__table">
                <table class="c-table">
                    <caption class="u-visually-hidden">{{ $shopName }}の会社概要</caption>
                    <tbody>
                        <tr>
                            <th scope="row" class="c-table__th">店名</th>
                            <td class="c-table__td p-store-company__name">{{ $shopName }}</td>
                        </tr>
                        @if (filled($operator['company'] ?? null))
                            <tr>
                                <th scope="row" class="c-table__th">運営会社</th>
                                <td class="c-table__td">{{ $operator['company'] }}</td>
                            </tr>
                        @endif
                        @if (filled($representative))
                            <tr>
                                <th scope="row" class="c-table__th">代表者</th>
                                <td class="c-table__td">{{ $representative }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th scope="row" class="c-table__th">所在地</th>
                            <td class="c-table__td"><x-site.address postal /></td>
                        </tr>
                        <tr class="p-store-company__tel-row">
                            <th scope="row" class="c-table__th">電話番号</th>
                            <td class="c-table__td"><a class="c-link c-link--block p-store-company__tel" href="{{ $telHref }}" aria-label="電話をかける {{ $tel }}">{{ $tel }}</a></td>
                        </tr>
                        <tr>
                            <th scope="row" class="c-table__th">営業時間</th>
                            <td class="c-table__td">{{ $hoursLabel }}</td>
                        </tr>
                        <tr>
                            <th scope="row" class="c-table__th">定休日</th>
                            <td class="c-table__td">{{ $closedLabel }}</td>
                        </tr>
                        <tr>
                            <th scope="row" class="c-table__th">事業内容</th>
                            <td class="c-table__td">{{ $businessLinesText }}</td>
                        </tr>
                        @if ($kobutsuText !== null)
                            <tr>
                                <th scope="row" class="c-table__th">古物商許可</th>
                                <td class="c-table__td">{{ $kobutsuText }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th scope="row" class="c-table__th">駐車場</th>
                            <td class="c-table__td">{{ $parking }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="c-card c-card--accent p-store-company__side">
                <h3 class="c-card__title p-store-company__side-title">はじめての方へ</h3>
                <p class="p-store-company__side-lead">当店が車の表示で守っていることと、ご購入までの流れをトップページでご案内しています。</p>
                <ul class="p-store-company__links" role="list">
                    <li><x-site.link-card :href="route('home').'#promise'" icon="tag" :title="new HtmlString('当店の<b>お約束</b>')" sub="支払総額・修復歴などの表示" /></li>
                    <li><x-site.link-card :href="route('home').'#flow'" icon="car" tone="black" :title="new HtmlString('ご購入の<b>流れ</b>')" sub="お問い合わせから納車まで" /></li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- 7. よくある質問（店舗について4問）＋ご来店の前に --}}
<section class="l-section" id="faq" aria-labelledby="store-faq-title">
    <div class="l-container">
        <x-site.section-head id="store-faq-title" title="店舗についてのよくある質問" en="FAQ" />

        <div class="p-store-faq">
            <div class="p-store-faq__main">
                <x-site.faq :items="$faqItems" jsonld />
                <a class="c-more p-store-faq__more" href="{{ route('home') }}#faq">ご購入についての質問（トップページ）<x-site.icon name="chevron-right" /></a>
            </div>

            <div class="c-card c-card--warm p-store-before">
                <h3 class="c-card__title p-store-before__title"><x-site.icon name="info" />ご来店の前に</h3>
                <ul class="p-store-before__list" role="list">
                    <li class="p-store-before__item"><x-site.icon name="check" /><span>土曜・日曜・祝日も営業しています<span class="u-nowrap">（定休日は{{ $closedLabel }}）。</span></span></li>
                    <li class="p-store-before__item"><x-site.icon name="check" /><span>年末年始・ゴールデンウィークなどは、営業時間が変わる場合があります。臨時休業は、このページで<span class="u-nowrap">お知らせします。</span></span></li>
                    <li class="p-store-before__item"><x-site.icon name="check" /><span>見たい車が決まっている場合は、展示場所をご来店の前に<span class="u-nowrap">ご確認ください。</span></span></li>
                    <li class="p-store-before__item"><x-site.icon name="check" /><span>営業時間外のご連絡は、お問い合わせフォーム<span class="u-nowrap">（24時間受付）</span>を<span class="u-nowrap">ご利用ください。</span></span></li>
                </ul>
                <a class="c-btn c-btn--primary c-btn--block p-store-before__btn" href="{{ route('contact.index', ['purpose' => 'visit']) }}">
                    <x-site.icon name="calendar" />フォームで来店を予約する
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
