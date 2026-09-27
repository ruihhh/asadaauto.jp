@extends('layouts.site')

@php
    use App\Models\Car;
    use App\Support\CarText;
    use Illuminate\Support\HtmlString;

    // URL の ids（コントローラと同じく先頭の3台まで）。同じ車が2回あっても1列にする
    $requestedIds = collect(array_slice(array_filter(array_map('intval', explode(',', (string) request()->query('ids', '')))), 0, 3))
        ->filter(fn (int $id) => $id > 0)
        ->unique()
        ->values();
    $compareCars = $cars->unique(fn (Car $car) => $car->id)->values();
    $count = $compareCars->count();

    // 表示できない車（売約済み・掲載終了）。比較の一覧（$store.compare）からも外す
    $missingIds = $requestedIds->diff($compareCars->pluck('id'))->values();

    // 「いちばん安い／少ない／新しい」の判定に使う値（比べられる車が2台以上あり、値に差があるときだけ印を付ける）
    $metrics = $compareCars->map(fn (Car $car) => [
        'id' => (int) $car->id,
        'name' => CarText::name($car),
        'price' => ($car->price_negotiable || $car->price === null) ? null : (int) $car->price,
        'mileage' => $car->mileage !== null ? (int) $car->mileage : null,
        'year' => $car->model_year ? (int) $car->model_year : null,
    ])->values();
    $bestIds = function (string $key, bool $max = false) use ($metrics): array {
        $values = $metrics->filter(fn (array $m) => $m[$key] !== null)->pluck($key, 'id');
        if ($values->count() < 2 || $values->unique()->count() < 2) {
            return [];
        }
        $target = $max ? $values->max() : $values->min();

        return $values->filter(fn (int $value) => $value === $target)->keys()->map(fn ($id) => (int) $id)->all();
    };
    $best = [
        'price' => $bestIds('price'),
        'mileage' => $bestIds('mileage'),
        'year' => $bestIds('year', true),
    ];
    // 印の文言と色・アイコン（支払総額＝赤、走行距離＝黒、年式＝黄）
    $bestBadges = [
        'price' => ['label' => 'いちばん安い', 'icon' => 'yen', 'what' => '支払総額'],
        'mileage' => ['label' => 'いちばん少ない', 'icon' => 'meter', 'what' => '走行距離'],
        'year' => ['label' => 'いちばん新しい', 'icon' => 'calendar', 'what' => '年式'],
    ];

    // 金額のセル（「300.0」＋「万円」）。未入力は「お問い合わせください」
    $moneyCell = function (?int $yen): array {
        if ($yen === null) {
            return ['text' => CarText::UNKNOWN];
        }
        $parts = CarText::priceParts($yen);

        return ['num' => $parts['value'], 'unit' => $parts['unit']];
    };

    // スマホの狭い列で語の途中で折り返さないよう、折り返してよい位置に <wbr> を入れる（CarText::breakable。CSS の keep-all と組み合わせる。入りきらないときだけ overflow-wrap で折る）
    $breaks = fn (string $text): HtmlString => CarText::breakable($text);

    // 表の行（業界ルールの並び：価格の内訳 → 保証・整備 → 年式・走行距離・車検・修復歴 → そのほか）。値はすべて CarText で作る
    // label の <wbr> は、スマホの狭い項目名の列で折り返してよい位置
    $rows = [
        ['key' => 'base_price', 'label' => '車両本体<wbr>価格', 'icon' => 'yen', 'cell' => fn (Car $car) => $moneyCell($car->base_price !== null ? (int) $car->base_price : null)],
        ['key' => 'fees', 'label' => '諸費用', 'icon' => 'calc', 'cell' => fn (Car $car) => $moneyCell(CarText::fees($car))],
        ['key' => 'warranty', 'label' => '保証', 'icon' => 'shield', 'cell' => fn (Car $car) => [
            'text' => CarText::warranty() ?? CarText::UNKNOWN,
        ]],
        ['key' => 'maintenance', 'label' => '定期点検<wbr>整備<wbr>（法定整備）', 'icon' => 'wrench', 'cell' => fn (Car $car) => [
            'text' => CarText::maintenance() ?? CarText::UNKNOWN,
        ]],
        ['key' => 'year', 'label' => '年式', 'icon' => 'calendar', 'best' => 'year', 'cell' => function (Car $car) {
            $text = CarText::year($car->model_year ? (int) $car->model_year : null, true);
            // 「2023年（令和5年）」→ 西暦を大きな数字、和暦を小さく（つなげて読むと CarText::year() と同じ文字）
            if (preg_match('/^(\d{4})(年)(（[^（）]+）)$/u', $text, $m)) {
                return ['num' => $m[1], 'unit' => $m[2], 'sub' => $m[3]];
            }

            return ['text' => $text];
        }],
        ['key' => 'mileage', 'label' => '走行距離', 'icon' => 'meter', 'best' => 'mileage', 'cell' => function (Car $car) {
            $parts = CarText::mileageParts($car->mileage !== null ? (int) $car->mileage : null);

            return $parts !== null ? ['num' => $parts['value'], 'unit' => $parts['unit']] : ['text' => CarText::mileage(null)];
        }],
        ['key' => 'inspection', 'label' => '車検', 'icon' => 'cal-check', 'cell' => function (Car $car) {
            $inspection = CarText::inspection($car);
            // 期限あり・付き＝緑、要確認・残りあり＝琥珀、なし・期限切れ＝橙（車両カードと同じ）
            $tone = match ($inspection['tone']) {
                'ok' => 'ok',
                'caution' => 'caution',
                default => 'check',
            };

            return ['state' => $inspection['label'], 'tone' => $tone, 'icon' => $tone === 'ok' ? 'check' : ($tone === 'caution' ? 'alert' : 'cal-check')];
        }],
        ['key' => 'repair', 'label' => '修復歴', 'icon' => 'frame', 'cell' => function (Car $car) {
            $repair = CarText::repair($car);
            if ($repair['tone'] === 'neutral') {
                return ['text' => $repair['label']];
            }

            return ['state' => $repair['label'], 'tone' => $repair['tone'], 'icon' => $repair['tone'] === 'ok' ? 'check' : 'alert', 'note' => $repair['note']];
        }],
        ['key' => 'record', 'label' => '点検記録簿', 'icon' => 'file', 'cell' => fn (Car $car) => [
            'text' => $car->has_service_record ? 'あり' : CarText::UNKNOWN,
        ]],
        ['key' => 'transmission', 'label' => 'ミッション', 'icon' => 'gear', 'cell' => fn (Car $car) => [
            'text' => CarText::transmission($car->transmission),
        ]],
        ['key' => 'fuel', 'label' => '燃料', 'icon' => 'fuel', 'cell' => fn (Car $car) => [
            'text' => CarText::fuel($car->fuel_type),
        ]],
        ['key' => 'body_type', 'label' => 'ボディ<wbr>タイプ', 'icon' => 'car', 'cell' => fn (Car $car) => filled($car->body_type)
            ? ['text' => CarText::bodyType($car->body_type), 'illust' => CarText::bodyIllust($car->body_type)]
            : ['text' => CarText::UNKNOWN]],
        ['key' => 'color', 'label' => 'ボディ<wbr>カラー', 'icon' => 'drop', 'cell' => fn (Car $car) => [
            'text' => filled($car->color) ? $breaks($car->color) : CarText::UNKNOWN,
        ]],
        ['key' => 'equipment', 'label' => '主な装備', 'icon' => 'eq-check', 'cell' => function (Car $car) {
            $items = CarText::equipmentHighlights($car, 6);

            return $items === [] ? ['text' => CarText::UNKNOWN] : ['list' => $items];
        }],
        ['key' => 'location', 'label' => '展示場所', 'icon' => 'map-pin', 'cell' => function (Car $car) use ($breaks) {
            $place = CarText::location($car);
            // 「◯◯で保管中（現車確認はご予約ください）」は、場所を太字・かっこの中を小さく分けて出す（つなげて読むと CarText::location() と同じ文字）
            $note = null;
            if (preg_match('/^(.+?)(（[^（）]+）)$/u', $place, $m)) {
                [$place, $note] = [$m[1], $m[2]];
            }

            return ['place' => $breaks($place), 'place_note' => $note !== null ? $breaks($note) : null, 'tone' => CarText::atShop($car) ? 'shop' : 'caution'];
        }],
        ['key' => 'stock_no', 'label' => '在庫番号', 'icon' => 'tag', 'cell' => fn (Car $car) => filled($car->stock_no)
            ? ['stock' => $car->stock_no]
            : ['text' => CarText::UNKNOWN]],
    ];

    // 価格の時点（車ごとの更新日）。全車が同じ日なら1行にまとめる
    $asOf = $compareCars->mapWithKeys(fn (Car $car) => [CarText::name($car).'（'.$car->stock_no.'）' => CarText::priceAsOf($car)])->filter();

    $photoOf = function (Car $car): ?string {
        if (filled($car->image_path)) {
            return $car->image_path;
        }

        return $car->images->first()?->path;
    };

    // 比較表の見出し（外したら Alpine で数え直す）
    $tableTitle = new HtmlString('比較表（<span x-text="count">'.$count.'</span>台）');

    // 比較のしかた（STEP 図）の見出し。狭い枠でも語の途中で折り返さないよう、区切りを u-nowrap で決める
    $howtoTitles = [
        1 => new HtmlString('<span class="u-nowrap">在庫一覧で</span><span class="u-nowrap">車を探す</span>'),
        2 => new HtmlString('<span class="u-nowrap">［比較に追加］</span><span class="u-nowrap">を押す</span>'),
        3 => new HtmlString('<span class="u-nowrap">画面の下の</span><span class="u-nowrap">［比較する］</span><span class="u-nowrap">を押す</span>'),
    ];

    // 空のときの「比較のしかた」の最後に出す、いま掲載中の台数（実数）
    // （車があるときは空の表示を出さないので数えない。すべて外すとページを読み直す）
    $publicCount = $count === 0 ? Car::publicInventory()->count() : 0;

    $pageData = [
        'url' => route('cars.compare'),
        'cars' => $metrics->all(),
        'missing' => $missingIds->map(fn ($id) => (int) $id)->all(),
    ];

    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'ホーム', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => '中古車在庫一覧', 'item' => route('cars.index')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => '車両比較', 'item' => route('cars.compare')],
        ],
    ];
@endphp

@section('title', '車両比較')
@section('meta_description', '気になる中古車を最大3台まで並べて、支払総額・年式・走行距離・車検・修復歴などを比べられます。')
@section('meta_robots', 'noindex, follow')
@section('canonical', route('cars.compare'))
@section('body_class', 'p-compare')

@push('structured_data')
    <script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
<div x-data="comparePage(@js($pageData))">
    <x-site.page-header class="p-compare-header" title="車両比較" en="COMPARE" lead="最大3台まで並べて比べられます。"
        :breadcrumbs="[['label' => '中古車在庫一覧', 'url' => route('cars.index')], ['label' => '車両比較']]">
        {{-- 比べている車の枠（3つ。車があればボディタイプの絵と車名、なければ「空き」） --}}
        <ul class="p-compare-slots" role="list" aria-label="比べている車（最大3台）" x-show="! redirecting">
            @foreach ($compareCars as $car)
                <li class="p-compare-slot" x-show="visible({{ (int) $car->id }})">
                    <span class="p-compare-slot__no u-num" aria-hidden="true" x-text="order({{ (int) $car->id }})">{{ $loop->iteration }}</span>
                    <x-site.illust :name="CarText::bodyIllust($car->body_type)" class="p-compare-slot__ill" />
                    <span class="p-compare-slot__name">{{ CarText::name($car) }}</span>
                </li>
            @endforeach
            @for ($i = 1; $i <= 3; $i++)
                {{-- 空きの枠は「3 − 比べている台数」だけ出す（i 番目の空きは台数が 3 − i 以下のとき） --}}
                <li class="p-compare-slot p-compare-slot--empty" x-show="count <= {{ 3 - $i }}" @if ($count > 3 - $i) x-cloak @endif>
                    <span class="p-compare-slot__plus" aria-hidden="true"><x-site.icon name="car" /></span>
                    <span class="p-compare-slot__name">空き</span>
                </li>
            @endfor
        </ul>
    </x-site.page-header>

    <div class="l-section p-compare-main">
        <div class="l-container">
            {{-- URL に ids がなく、比較に選んだ車があるときは ids 付きの URL に移る（そのあいだだけ出す） --}}
            <p class="p-compare-loading" role="status" x-show="redirecting" x-cloak>比較する車を読み込んでいます…</p>

            @if ($missingIds->isNotEmpty())
                <div class="c-alert p-compare-notice" x-show="! redirecting">
                    <x-site.icon name="info" />
                    <div class="c-alert__body">
                        <p class="c-alert__title">{{ $missingIds->count() }}台は<span class="u-nowrap">売約済み・掲載終了のため</span><span class="u-nowrap">表示できません</span></p>
                        <p>探している車をお知らせいただければ、入荷したときにご連絡します。</p>
                        <p><a class="c-btn c-btn--secondary c-btn--sm" href="{{ route('contact.index', ['purpose' => 'search']) }}">探してほしい車を伝える</a></p>
                    </div>
                </div>
            @endif

            @if ($count > 0)
                <section aria-labelledby="compare-title" x-show="! redirecting && count > 0">
                    <x-site.section-head id="compare-title" :title="$tableTitle" en="COMPARE TABLE"
                        lead="支払総額・年式・走行距離・車検・修復歴などを、横に並べて見比べられます。" tabindex="-1" x-ref="title" />

                    {{-- 色の印の見かた（2台以上のとき） --}}
                    <div class="p-compare-legend" x-show="count >= 2" @if ($count < 2) x-cloak @endif>
                        <p class="p-compare-legend__title">色の印の見かた</p>
                        <ul class="p-compare-legend__list" role="list">
                            @foreach ($bestBadges as $key => $badge)
                                <li class="p-compare-legend__item">
                                    <span class="p-compare-badge p-compare-badge--{{ $key }}"><x-site.icon :name="$badge['icon']" />{{ $badge['label'] }}</span>
                                    <span class="p-compare-legend__what">{{ $badge['what'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <p class="p-compare-legend__note">比べる車が2台以上あり、値にちがいがあるときに付きます。</p>
                    </div>

                    <p class="p-compare-hint" x-show="wide" @if ($count < 3) x-cloak @endif>
                        <span class="p-compare-hint__ic" aria-hidden="true"><x-site.icon name="chevron-left" /><x-site.icon name="chevron-right" /></span>
                        表を指で左にずらすと、3台目が見られます。
                    </p>

                    <div @class(['p-compare-board', 'is-wide' => $count >= 3, 'is-count-'.$count])
                         :class="{ 'is-wide': wide, 'is-count-1': count === 1, 'is-count-2': count === 2, 'is-count-3': count === 3 }">

                        {{-- スマホ用：表の見出し行が画面の上に隠れたら、車名と支払総額を画面の上に固定して出す（見出し行の写し。読み上げない） --}}
                        <div class="p-compare-float" x-ref="float" x-show="floating" x-cloak aria-hidden="true">
                            <div class="p-compare-float__label"><span class="u-nowrap">車名・</span><span class="u-nowrap">支払総額</span></div>
                            <div class="p-compare-float__track" x-ref="floatTrack">
                                <div class="p-compare-float__inner">
                                    @foreach ($compareCars as $car)
                                        <div class="p-compare-float__car" x-show="visible({{ (int) $car->id }})">
                                            <span class="p-compare-float__name">{{ CarText::name($car) }}</span>
                                            <span class="p-compare-float__price">
                                                @if ($car->price_negotiable)
                                                    価格は応談
                                                @elseif ($car->price === null)
                                                    価格はお問い合わせ
                                                @else
                                                    {{ CarText::price((int) $car->price) }}
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="c-table-wrap p-compare-scroll" x-ref="scroll" x-on:scroll.passive="syncScroll()" role="region" aria-labelledby="compare-title" :tabindex="scrollable ? 0 : null">
                            <table class="c-table p-compare-table" x-ref="table">
                                <caption class="u-visually-hidden">車両比較表。列が車、行が項目です。</caption>
                                <thead>
                                    <tr class="p-compare-table__photo-row">
                                        <td class="p-compare-table__photo p-compare-table__corner">
                                            <span class="p-compare-corner" aria-hidden="true">
                                                <span class="p-compare-corner__ic"><x-site.icon name="compare" /></span>
                                                <span class="p-compare-corner__text">比べる車</span>
                                            </span>
                                        </td>
                                        @foreach ($compareCars as $car)
                                            @php $photo = $photoOf($car); $id = (int) $car->id; @endphp
                                            <td class="p-compare-table__photo" x-show="visible({{ $id }})">
                                                <a class="p-compare-photo" href="{{ route('cars.show', $car) }}" tabindex="-1" aria-hidden="true">
                                                    @if ($photo)
                                                        <img class="p-compare-photo__img" src="{{ asset('images/'.$photo) }}" alt="{{ CarText::name($car) }}の写真" width="640" height="480" decoding="async">
                                                    @else
                                                        <span class="p-compare-photo__none"><x-site.illust :name="CarText::bodyIllust($car->body_type)" />写真準備中</span>
                                                    @endif
                                                    <span class="p-compare-photo__no"><b class="u-num" x-text="order({{ $id }})">{{ $loop->iteration }}</b>台目</span>
                                                </a>
                                            </td>
                                        @endforeach
                                    </tr>
                                    <tr x-ref="headRow">
                                        <th class="c-table__th c-table__th--col p-compare-table__corner p-compare-table__sticky" scope="row">車名・<br><span class="u-nowrap">支払総額</span><span class="u-nowrap">（税込）</span></th>
                                        @foreach ($compareCars as $car)
                                            @php
                                                $id = (int) $car->id;
                                                $ask = $car->price_negotiable || $car->price === null;
                                                $parts = $ask ? null : CarText::priceParts((int) $car->price);
                                            @endphp
                                            <th class="c-table__th c-table__th--col p-compare-table__car p-compare-table__sticky" scope="col" x-show="visible({{ $id }})">
                                                <a class="p-compare-car__name" href="{{ route('cars.show', $car) }}">{{ CarText::name($car) }}</a>
                                                @if (filled($car->grade))
                                                    <span class="p-compare-car__grade">{{ $car->grade }}</span>
                                                @endif
                                                @if ($ask)
                                                    <span class="p-compare-plate p-compare-plate--ask">{{ $car->price_negotiable ? '価格はお問い合わせください（応談）' : '価格はお問い合わせください' }}</span>
                                                @else
                                                    <span class="p-compare-plate">
                                                        <span class="p-compare-plate__label"><span class="u-nowrap">支払総額</span><small class="u-nowrap">（税込）</small></span>
                                                        <span class="p-compare-plate__price"><span class="p-compare-plate__num">{{ $parts['value'] }}</span><span class="p-compare-plate__unit">{{ $parts['unit'] }}</span></span>
                                                    </span>
                                                @endif
                                                <span class="p-compare-best">
                                                    <span class="p-compare-badge p-compare-badge--price" x-show="best('price', {{ $id }})" @unless (in_array($id, $best['price'], true)) x-cloak @endunless><x-site.icon name="yen" />{{ $bestBadges['price']['label'] }}</span>
                                                </span>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rows as $row)
                                        <tr class="p-compare-table__row p-compare-table__row--{{ $row['key'] }}">
                                            <th class="c-table__th p-compare-table__label" scope="row">
                                                <span class="p-compare-label">
                                                    <span class="p-compare-label__ic" aria-hidden="true"><x-site.icon :name="$row['icon']" /></span>
                                                    <span class="p-compare-label__text">{!! $row['label'] !!}</span>
                                                </span>
                                            </th>
                                            @foreach ($compareCars as $car)
                                                @php
                                                    $id = (int) $car->id;
                                                    $cell = $row['cell']($car);
                                                    $bestKey = $row['best'] ?? null;
                                                @endphp
                                                <td class="c-table__td p-compare-table__value" x-show="visible({{ $id }})">
                                                    @if (isset($cell['list']))
                                                        <ul class="p-compare-equips" role="list">
                                                            @foreach ($cell['list'] as $item)
                                                                <li class="p-compare-equips__item"><span class="p-compare-equips__ic" aria-hidden="true"><x-site.icon :name="CarText::equipmentIcon($item)" /></span><span class="p-compare-equips__name">{{ $breaks($item) }}</span></li>
                                                            @endforeach
                                                        </ul>
                                                    @elseif (isset($cell['num']))
                                                        <span class="p-compare-value"><span class="p-compare-value__num">{{ $cell['num'] }}</span><span class="p-compare-value__unit">{{ $cell['unit'] }}</span>@isset($cell['sub'])<span class="p-compare-value__sub u-nowrap">{{ $cell['sub'] }}</span>@endisset</span>
                                                    @elseif (isset($cell['state']))
                                                        <span class="p-compare-state p-compare-state--{{ $cell['tone'] }}"><x-site.icon :name="$cell['icon']" />{{ $cell['state'] }}</span>
                                                        @if (! empty($cell['note']))
                                                            <span class="p-compare-table__note">{{ $cell['note'] }}</span>
                                                        @endif
                                                    @elseif (isset($cell['place']))
                                                        <span class="p-compare-place p-compare-place--{{ $cell['tone'] }}"><x-site.icon name="map-pin" /><span class="p-compare-place__text">{{ $cell['place'] }}@if ($cell['place_note'] !== null)<small class="p-compare-place__note">{{ $cell['place_note'] }}</small>@endif</span></span>
                                                    @elseif (isset($cell['stock']))
                                                        <span class="p-compare-stock u-num">{{ $cell['stock'] }}</span>
                                                    @elseif ($cell['text'] === CarText::UNKNOWN)
                                                        <span class="p-compare-table__unknown"><span class="u-nowrap">お問い合わせ</span><span class="u-nowrap">ください</span></span>
                                                    @elseif (isset($cell['illust']))
                                                        <span class="p-compare-body"><x-site.illust :name="$cell['illust']" class="p-compare-body__ill" />{{ $cell['text'] }}</span>
                                                    @else
                                                        <span class="p-compare-text">{{ $cell['text'] }}</span>
                                                    @endif
                                                    @if ($bestKey !== null)
                                                        <span class="p-compare-best">
                                                            <span class="p-compare-badge p-compare-badge--{{ $bestKey }}" x-show="best('{{ $bestKey }}', {{ $id }})" @unless (in_array($id, $best[$bestKey], true)) x-cloak @endunless><x-site.icon :name="$bestBadges[$bestKey]['icon']" />{{ $bestBadges[$bestKey]['label'] }}</span>
                                                        </span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                    <tr class="p-compare-table__row p-compare-table__row--actions">
                                        <th class="c-table__th p-compare-table__label" scope="row"><span class="u-visually-hidden">この車について</span></th>
                                        @foreach ($compareCars as $car)
                                            @php $id = (int) $car->id; @endphp
                                            <td class="c-table__td p-compare-table__value" x-show="visible({{ $id }})">
                                                <div class="p-compare-actions">
                                                    <a class="c-btn c-btn--primary c-btn--block" href="{{ route('contact.index', ['stock_no' => $car->stock_no, 'purpose' => 'stock']) }}"><span><span class="u-nowrap">この車の</span><span class="u-nowrap">在庫確認・</span><span class="u-nowrap">見積もり</span><span class="u-nowrap">（無料）</span></span></a>
                                                    <a class="c-btn c-btn--secondary c-btn--block" href="{{ route('cars.show', $car) }}">詳しく見る</a>
                                                    <button type="button" class="c-btn c-btn--ghost c-btn--sm c-btn--block" x-on:click="remove({{ $id }})" aria-label="{{ CarText::name($car) }}を比較から外す">比較から外す</button>
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- 車の追加・入れかえ（点線の枠） --}}
                    <div class="p-compare-add">
                        <span class="p-compare-add__ic" aria-hidden="true"><x-site.icon name="compare" /></span>
                        <div class="p-compare-add__body">
                            <p class="p-compare-add__text" x-show="count < 3" @if ($count >= 3) x-cloak @endif>あと<b class="p-compare-add__num" x-text="3 - count">{{ max(0, 3 - $count) }}</b>台まで<span class="u-nowrap">追加して</span><span class="u-nowrap">比べられます。</span></p>
                            <p class="p-compare-add__text" x-show="count >= 3" @if ($count < 3) x-cloak @endif>ほかの車と入れかえるときは、［比較から外す］で1台外してから選んでください。</p>
                            <div class="l-cluster p-compare-add__actions">
                                <a class="c-btn c-btn--secondary" href="{{ route('cars.index') }}"><x-site.icon name="search" />在庫一覧で車を選ぶ</a>
                                <a class="c-btn c-btn--secondary" href="{{ route('cars.favorites') }}" x-show="$store.favorites.count > 0" x-cloak><x-site.icon name="heart" />お気に入りから選ぶ</a>
                            </div>
                        </div>
                    </div>

                    <div class="c-alert p-compare-notes">
                        <x-site.icon name="info" />
                        <div class="c-alert__body">
                            <p>価格はすべて<b>税込の支払総額</b>です。{{ CarText::priceNote() }}</p>
                            @if ($asOf->unique()->count() === 1)
                                <p>{{ $asOf->first() }}</p>
                            @elseif ($asOf->isNotEmpty())
                                <ul class="p-compare-notes__list" role="list">
                                    @foreach ($asOf as $name => $text)
                                        <li>{{ $name }}：{{ $text }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </section>
            @endif

            {{-- 0件のとき（読み込み前は見せない） --}}
            <div x-show="! redirecting && count === 0" x-cloak>
                <x-site.empty-state icon="compare" title="比較する車が選ばれていません">
                    在庫一覧やお気に入りのページで、気になる車の［比較に追加］を押してください。最大3台まで、支払総額・年式・走行距離などを表で並べて比べられます。
                    <x-slot:actions>
                        <a class="c-btn c-btn--primary" href="{{ route('cars.index') }}"><x-site.icon name="car" />在庫一覧を見る</a>
                        <a class="c-btn c-btn--secondary" href="{{ route('cars.favorites') }}" x-show="$store.favorites.count > 0" x-cloak><x-site.icon name="heart" />お気に入りから選ぶ</a>
                    </x-slot:actions>
                </x-site.empty-state>
            </div>
        </div>
    </div>

    @if ($count > 0)
        {{-- 表の言葉の説明 --}}
        <section class="l-section l-section--soft" aria-labelledby="compare-terms-title" x-show="! redirecting && count > 0">
            <div class="l-container">
                <x-site.section-head id="compare-terms-title" title="表の言葉の説明" en="GLOSSARY" />
                <dl class="p-compare-terms">
                    <div class="p-compare-terms__row">
                        <dt class="p-compare-terms__term"><span class="p-compare-terms__ic" aria-hidden="true"><x-site.icon name="calc" /></span>諸費用</dt>
                        <dd class="p-compare-terms__desc">支払総額と車両本体価格の差で、税金・自賠責保険料・登録などの手続き費用です。</dd>
                    </div>
                    <div class="p-compare-terms__row">
                        <dt class="p-compare-terms__term"><span class="p-compare-terms__ic" aria-hidden="true"><x-site.icon name="wrench" /></span>定期点検整備（法定整備）</dt>
                        <dd class="p-compare-terms__desc">法律で決められた項目にそって行う点検・整備です。</dd>
                    </div>
                    <div class="p-compare-terms__row">
                        <dt class="p-compare-terms__term"><span class="p-compare-terms__ic" aria-hidden="true"><x-site.icon name="frame" /></span>修復歴</dt>
                        <dd class="p-compare-terms__desc">{{ CarText::REPAIR_DEFINITION_SHORT }}</dd>
                    </div>
                    <div class="p-compare-terms__row">
                        <dt class="p-compare-terms__term"><span class="p-compare-terms__ic" aria-hidden="true"><x-site.icon name="file" /></span>点検記録簿</dt>
                        <dd class="p-compare-terms__desc">これまでの点検・整備の内容を記録した冊子です。</dd>
                    </div>
                    <div class="p-compare-terms__row">
                        <dt class="p-compare-terms__term"><span class="p-compare-terms__ic" aria-hidden="true"><x-site.icon name="gear" /></span>ミッション</dt>
                        <dd class="p-compare-terms__desc">AT はオートマチック車（CVT も AT の一種です）、MT はマニュアル車です。</dd>
                    </div>
                </dl>
            </div>
        </section>
    @endif

    {{-- 0件のとき：比較のしかた（STEP.1 → 2 → 3 の矢印の図） --}}
    <section class="l-section l-section--soft" aria-labelledby="compare-howto-title" x-show="! redirecting && count === 0" @if ($count > 0) x-cloak @endif>
        <div class="l-container">
            <x-site.section-head id="compare-howto-title" title="比較のしかた" en="HOW TO" lead="3つの手順で、最大3台を表に並べて比べられます。" />
            <div class="c-arrow-steps c-arrow-steps--even p-compare-howto">
                <x-site.arrow-step :no="1" :title="$howtoTitles[1]" illust="f-search">
                    <p class="p-compare-howto__text">在庫一覧やお気に入りのページを開きます。</p>
                </x-site.arrow-step>
                <x-site.arrow-step :no="2" :title="$howtoTitles[2]">
                    <div class="p-compare-demo" aria-hidden="true">
                        <span class="p-compare-demo__btn"><x-site.icon name="compare" />比較に追加</span>
                        <x-site.icon name="chevron-down" class="p-compare-demo__arrow" />
                        <span class="p-compare-demo__btn p-compare-demo__btn--on"><x-site.icon name="check" />比較中</span>
                    </div>
                    <p class="p-compare-howto__text">気になる車の［比較に追加］を押します。3台まで選べます。</p>
                </x-site.arrow-step>
                <x-site.arrow-step :no="3" :title="$howtoTitles[3]">
                    <div class="p-compare-demo" aria-hidden="true">
                        <span class="p-compare-demo__tray"><span class="p-compare-demo__tray-text"><x-site.icon name="compare" />比較する車（2台）</span><span class="p-compare-demo__go">比較する</span></span>
                    </div>
                    <p class="p-compare-howto__text">車を選ぶと、画面の下に［比較する］が出ます。押すと、このページで並べて比べられます。</p>
                </x-site.arrow-step>
                <x-site.arrow-step go>
                    @if ($publicCount > 0)
                        <p class="c-arrow-step__count-label">掲載中の車</p>
                        <p class="c-arrow-step__count">{{ $publicCount }}<small>台</small></p>
                    @endif
                    <a class="c-btn c-btn--yellow" href="{{ route('cars.index') }}"><x-site.icon name="search" />在庫一覧を見る</a>
                </x-site.arrow-step>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    // 車両比較ページ：URL と比較の一覧（$store.compare）の同期、列の取り外し、「いちばん◯◯」の判定、スマホで横にずらしたときの車名の固定表示
    Alpine.data('comparePage', (config) => ({
        cars: config.cars,
        removed: [],
        redirecting: false,
        floating: false,
        scrollable: false,
        init() {
            const have = new URLSearchParams(window.location.search).get('ids') || '';
            if (have === '' && this.$store.compare.count > 0) {
                this.redirecting = true;
                window.location.replace(this.$store.compare.url);
                return;
            }

            // 売約済み・掲載終了で表示できない車は、比較の一覧からも外す
            if (config.missing.length > 0) {
                config.missing.forEach((id) => this.$store.compare.remove(id));
                this.replaceUrl();
            }

            if (this.$refs.scroll) {
                const update = () => this.updateFloat();
                window.addEventListener('scroll', update, { passive: true });
                window.addEventListener('resize', update, { passive: true });
                this.$nextTick(update);
            }
        },
        get shown() {
            return this.cars.filter((car) => !this.removed.includes(car.id));
        },
        get count() {
            return this.shown.length;
        },
        get wide() {
            return this.count >= 3;
        },
        visible(id) {
            return !this.removed.includes(id);
        },
        // 表の左から何台目か（写真の「1台目」と見出し帯の枠の番号）
        order(id) {
            return this.shown.findIndex((car) => car.id === id) + 1;
        },
        // price・mileage はいちばん小さい車、year はいちばん新しい車。比べられる車が2台以上あり、値に差があるときだけ
        best(key, id) {
            const list = this.shown.filter((car) => car[key] !== null);
            const values = list.map((car) => car[key]);
            if (values.length < 2 || new Set(values).size < 2) return false;
            const target = key === 'year' ? Math.max(...values) : Math.min(...values);
            return list.some((car) => car.id === id && car[key] === target);
        },
        remove(id) {
            const car = this.cars.find((item) => item.id === id);
            if (!car) return;
            const wasInStore = this.$store.compare.has(id);
            this.removed = this.removed.concat([id]);
            this.$store.compare.remove(id);
            if (this.count === 0) {
                window.location.replace(config.url);
                return;
            }
            this.replaceUrl();
            this.$store.toast.show(car.name + 'を比較から外しました', { label: '元に戻す', handler: () => this.restore(id, wasInStore) });
            this.$nextTick(() => {
                if (this.$refs.title) this.$refs.title.focus();
                this.updateFloat();
            });
        },
        restore(id, wasInStore) {
            const car = this.cars.find((item) => item.id === id);
            this.removed = this.removed.filter((value) => value !== id);
            if (car && wasInStore && !this.$store.compare.has(id)) this.$store.compare.toggle(id, car.name);
            this.replaceUrl();
            this.$nextTick(() => this.updateFloat());
        },
        replaceUrl() {
            const ids = this.shown.map((car) => car.id);
            window.history.replaceState(null, '', config.url + (ids.length > 0 ? '?ids=' + ids.join(',') : ''));
        },
        syncScroll() {
            if (this.$refs.floatTrack && this.$refs.scroll) this.$refs.floatTrack.scrollLeft = this.$refs.scroll.scrollLeft;
        },
        // スマホでは、見出し行（写真・車名・支払総額）が画面の上に隠れたら、車名と支払総額だけを上に固定して出す（PC・タブレットは見出し行そのものを CSS で固定）
        updateFloat() {
            const scroll = this.$refs.scroll;
            const row = this.$refs.headRow;
            const table = this.$refs.table;
            if (!scroll || !row || !table) return;
            const phone = !window.matchMedia('(min-width: 600px)').matches;
            // 横にずらせるときだけ、キーボードでも表にフォーカスして左右に動かせるようにする
            this.scrollable = scroll.scrollWidth > scroll.clientWidth + 1;
            const barHeight = this.$refs.float ? (this.$refs.float.offsetHeight || 64) : 64;
            this.floating = phone
                && row.getBoundingClientRect().bottom < 0
                && table.getBoundingClientRect().bottom > barHeight * 2;
            this.$nextTick(() => this.syncScroll());
        },
    }));
});
</script>
@endpush
