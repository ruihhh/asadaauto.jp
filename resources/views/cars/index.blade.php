@extends('layouts.site')

@php
    use App\Models\Car;
    use App\Support\CarText;

    // 在庫一覧（/cars）と新着の中古車（/cars?sort=latest）。案B「車選び型」。
    // $isLatest はコントローラから渡される（URL に sort=latest があるときだけ「新着の中古車」ページ）

    // ---- いまの条件（コントローラの $filters のうち、値のあるものだけ）----
    $filterKeys = ['q', 'make', 'body_type', 'min_price', 'max_price', 'max_mileage', 'inspection'];
    $active = collect($filterKeys)
        ->mapWithKeys(fn ($key) => [$key => trim((string) ($filters[$key] ?? ''))])
        ->filter(fn ($value, $key) => $value !== '' && ($key !== 'inspection' || in_array($value, Car::INSPECTION_TYPES, true)));
    $hasFilter = $active->isNotEmpty();
    // STEP.2 の入力欄は最初は閉じておく（開いたままだと、スマホでは結果が1画面以上下に押し下げられるため）。
    // いまの条件は結果の上の札で見えるので、STEP.2 の見出しには「指定中 N件」（ボディタイプ以外の条件の数）だけを出す
    $extraCount = $active->except('body_type')->count();

    // いまの URL のクエリ（空の値と page を除く）。条件を外すリンク・タイル・hidden の引き継ぎに使う
    $query = collect(request()->query())
        ->filter(fn ($value) => is_string($value) && trim($value) !== '')
        ->except('page');

    // ---- 並び替え（先頭の「新着順（標準）」は value 空。sort=latest は新着ページ用なので、通常の一覧では送らない）----
    $sortOptions = [
        '' => '新着順（標準）',
        'price_asc' => '支払総額が安い順',
        'price_desc' => '支払総額が高い順',
        'mileage_asc' => '走行距離が少ない順',
        'year_desc' => '年式が新しい順',
    ];
    $sortParam = (string) request()->query('sort', '');
    $sortValue = array_key_exists($sortParam, $sortOptions) ? $sortParam : '';

    // ---- セレクトの選択肢（送る値は円・km の整数のまま）----
    $priceSteps = [
        500000 => '50万円', 1000000 => '100万円', 1500000 => '150万円', 2000000 => '200万円', 2500000 => '250万円',
        3000000 => '300万円', 3500000 => '350万円', 4000000 => '400万円', 4500000 => '450万円', 5000000 => '500万円',
    ];
    $mileageSteps = [10000 => '1万km', 30000 => '3万km', 50000 => '5万km', 70000 => '7万km', 100000 => '10万km'];
    $priceLabel = fn ($value) => $priceSteps[(int) $value] ?? CarText::price(max(0, (int) $value));
    $mileageLabel = fn ($value) => $mileageSteps[(int) $value] ?? CarText::mileage(max(0, (int) $value));
    // 車検は値（3年付／2年付／1年付／あり／なし）を変えず、表示名だけ CarText の言い方にする
    $inspectionLabels = collect(Car::INSPECTION_TYPES)
        ->mapWithKeys(fn ($type) => [$type => CarText::inspection((new Car)->forceFill(['inspection_type' => $type]))['short']]);

    $conditionLabels = [
        'q' => fn ($value) => 'キーワード：'.$value,
        'make' => fn ($value) => 'メーカー：'.$value,
        'body_type' => fn ($value) => 'ボディタイプ：'.CarText::bodyType($value),
        'min_price' => fn ($value) => '支払総額：'.$priceLabel($value).'以上',
        'max_price' => fn ($value) => '支払総額：'.$priceLabel($value).'以下',
        'max_mileage' => fn ($value) => '走行距離：'.$mileageLabel($value).'以下',
        'inspection' => fn ($value) => '車検：'.$inspectionLabels[$value],
    ];
    // × を押すと、その条件と page だけを外した URL に移る（移った先では、見出し帯と絞り込みを飛ばして結果の頭から見せる）
    $resultHash = '#cars-result';
    $conditions = $active->map(fn ($value, $key) => [
        'label' => $conditionLabels[$key]($value),
        'url' => route('cars.index', $query->except($key)->all()).$resultHash,
    ]);
    $clearUrl = $isLatest ? route('cars.index', ['sort' => 'latest']) : route('cars.index');

    // ---- 台数（ビューで公開在庫から数える。コントローラは変えない。条件の当て方は CarController@index と同じ）----
    $applyFilters = function ($builder, array $except = []) use ($active) {
        foreach ($active->except($except) as $key => $value) {
            match ($key) {
                'q' => $builder->where(function ($inner) use ($value): void {
                    $inner->where('make', 'like', "%{$value}%")
                        ->orWhere('model', 'like', "%{$value}%")
                        ->orWhere('grade', 'like', "%{$value}%")
                        ->orWhere('stock_no', 'like', "%{$value}%");
                }),
                'make', 'body_type' => $builder->where($key, $value),
                'min_price' => $builder->where('price_negotiable', false)->where('price', '>=', max(0, (int) $value)),
                'max_price' => $builder->where('price_negotiable', false)->where('price', '<=', max(0, (int) $value)),
                'max_mileage' => $builder->where('mileage', '<=', max(0, (int) $value)),
                'inspection' => $builder->where('inspection_type', $value),
            };
        }

        return $builder;
    };
    $countBy = fn ($builder, string $column) => $builder
        ->selectRaw($column.', COUNT(*) AS n')
        ->groupBy($column)
        ->pluck('n', $column)
        ->map(fn ($n) => (int) $n);

    $stockTotal = Car::publicInventory()->count();
    $typeCountsAll = $countBy(Car::publicInventory(), 'body_type');
    // STEP.1 のタイル：在庫のあるボディタイプだけ。台数は、ボディタイプ以外のいまの条件を当てた数（リンク先の件数と同じ）
    $typeCounts = $countBy($applyFilters(Car::publicInventory(), ['body_type']), 'body_type');
    $types = collect($bodyTypes)
        ->filter(fn ($type) => filled($type))
        ->sortBy(fn ($type) => [-($typeCountsAll[$type] ?? 0), $type])
        ->values();
    $allTypesCount = $typeCounts->sum();

    // 掲載から7日以内（CarText::isNew と同じ判定。published_at が空なら created_at）の台数
    $newSince = CarText::newSince()->setTimezone(config('app.timezone'));
    $newCount = Car::publicInventory()
        ->where(function ($q) use ($newSince): void {
            $q->where('published_at', '>=', $newSince)
                ->orWhere(fn ($inner) => $inner->whereNull('published_at')->where('created_at', '>=', $newSince));
        })
        ->count();

    // STEP.2 の「条件に合う車 N台」をその場で数えるための元データ（公開在庫だけ・絞り込みに使う項目だけ）
    $finderCars = $isLatest ? collect() : Car::publicInventory()
        ->get(['make', 'model', 'grade', 'stock_no', 'body_type', 'price', 'price_negotiable', 'mileage', 'inspection_type'])
        ->map(fn ($car) => [
            'make' => (string) $car->make,
            'words' => [(string) $car->make, (string) $car->model, (string) $car->grade, (string) $car->stock_no],
            'body' => (string) $car->body_type,
            'price' => $car->price !== null ? (int) $car->price : null,
            'neg' => (bool) $car->price_negotiable,
            'km' => $car->mileage !== null ? (int) $car->mileage : null,
            'insp' => (string) $car->inspection_type,
        ])
        ->values();

    // ---- どの車が合うか迷ったら：使い方から選ぶ（在庫のあるボディタイプだけ。値は DB のまま、表示名は CarText::bodyType）----
    $useCaseTexts = [
        'ミニバン' => ['title' => '家族や友人と大勢で乗りたい', 'sub' => '3列シートの車が多いミニバン', 'icon' => 'family', 'tone' => 'yellow'],
        'SUV' => ['title' => 'レジャーや遠出に使いたい', 'sub' => '荷物を積みやすく、見晴らしのよいSUV', 'icon' => 'mountain', 'tone' => 'black'],
        '軽自動車' => ['title' => '維持費を抑えたい', 'sub' => '税金が安く、小回りのきく軽自動車', 'icon' => 'bag', 'tone' => 'red'],
        'コンパクト' => ['title' => '運転しやすい大きさがいい', 'sub' => '狭い道や駐車場でも扱いやすいコンパクトカー', 'icon' => 'car', 'tone' => 'tint'],
        'ハッチバック' => ['title' => '街乗りで気軽に使いたい', 'sub' => '後ろから荷物を出し入れしやすいハッチバック', 'icon' => 'car', 'tone' => 'green'],
        'ワゴン' => ['title' => '荷物をたくさん積みたい', 'sub' => '荷室の広いステーションワゴン', 'icon' => 'bag', 'tone' => 'tint'],
        'セダン' => ['title' => '乗り心地や静かさを重視したい', 'sub' => '揺れや音が少ない車が多いセダン', 'icon' => 'car', 'tone' => 'black'],
    ];
    $useCases = $types
        ->filter(fn ($type) => isset($useCaseTexts[$type]))
        ->map(fn ($type) => $useCaseTexts[$type] + ['type' => $type, 'count' => $typeCountsAll[$type] ?? 0])
        ->values();

    // 予算・走行距離・メーカーから探す：一覧で実際に絞り込めるクエリだけ。0台の項目は出さず、同じ台数が続く項目はまとめる
    $quickChips = collect();
    if (! $isLatest) {
        // 文言はフッターと同じ「新着の車」（「新着の中古車」は新着ページの h1 の言い方なので、通常の一覧には出さない）
        $quickChips->push(['label' => '新着の車', 'url' => route('cars.index', ['sort' => 'latest']), 'icon' => 'calendar', 'tone' => 'red', 'count' => $newCount > 0 ? $newCount.'台' : null]);
    }
    $lastCount = null;
    foreach ([1000000, 2000000, 3000000] as $yen) {
        $n = Car::publicInventory()->where('price_negotiable', false)->where('price', '<=', $yen)->count();
        if ($n > 0 && $n !== $lastCount) {
            $quickChips->push(['label' => '支払総額'.($yen / 10000).'万円以下', 'url' => route('cars.index', ['max_price' => $yen]), 'icon' => 'yen', 'tone' => 'black', 'count' => $n.'台']);
        }
        $lastCount = $n > 0 ? $n : $lastCount;
    }
    $lastCount = null;
    foreach ([30000, 50000] as $km) {
        $n = Car::publicInventory()->where('mileage', '<=', $km)->count();
        if ($n > 0 && $n !== $lastCount) {
            $quickChips->push(['label' => '走行'.($km / 10000).'万km以下', 'url' => route('cars.index', ['max_mileage' => $km]), 'icon' => 'meter', 'tone' => 'red', 'count' => $n.'台']);
        }
        $lastCount = $n > 0 ? $n : $lastCount;
    }
    foreach ($countBy(Car::publicInventory(), 'make')->sortDesc() as $make => $n) {
        if (filled($make)) {
            $quickChips->push(['label' => $make, 'url' => route('cars.index', ['make' => $make]), 'icon' => 'car', 'tone' => 'yellow', 'count' => $n.'台']);
        }
    }

    // ---- 新着ページ：7日以内に掲載した車があるか（掲載日の新しい順なので、1ページ目で判定できる）----
    $latestCar = $cars->getCollection()->first();
    $lastPublished = $latestCar ? CarText::date($latestCar->published_at ?? $latestCar->created_at) : null;
    $hasNewCars = $cars->getCollection()->contains(fn ($car) => CarText::isNew($car));
    $showNoNewNotice = $isLatest && $cars->currentPage() === 1 && ! $hasNewCars;

    // 0件の見出し：「条件に合う車が／見つかりませんでした」の区切りでだけ改行する
    $emptyTitle = new \Illuminate\Support\HtmlString('<span class="u-nowrap">条件に合う車が</span><span class="u-nowrap">見つかりませんでした</span>');

    // ---- メタ ----
    $total = $cars->total();
    $shopName = config('shop.name');
    $makeLabel = $active->has('make') ? $active['make'].'の' : '';
    $bodyLabel = $active->has('body_type') ? CarText::bodyType($active['body_type']).'の' : '';
    $bodyTypeList = $types->map(fn ($type) => CarText::bodyType($type))->unique()->take(4)->implode('・');

    if ($isLatest) {
        $metaTitle = '新着の中古車｜尼崎・兵庫（'.$total.'台掲載中）';
        $metaDescription = '兵庫県尼崎市の中古車販売店'.$shopName.'に新しく掲載した中古車を、掲載日の新しい順にご紹介します。価格はすべて税込の支払総額です。';
    } elseif ($hasFilter) {
        $metaTitle = $makeLabel.$bodyLabel.'中古車在庫一覧｜尼崎・兵庫';
        $metaDescription = '兵庫県尼崎市の中古車販売店'.$shopName.'の'.$makeLabel.$bodyLabel.'中古車（'.$total.'台）。価格はすべて税込の支払総額です。';
    } else {
        $metaTitle = '尼崎の中古車在庫一覧｜兵庫県（'.$total.'台掲載中）';
        $metaDescription = '兵庫県尼崎市の中古車販売店'.$shopName.'の在庫一覧です。'.($bodyTypeList !== '' ? $bodyTypeList.'など' : '').$total.'台を掲載中。価格はすべて税込の支払総額で、年式・走行距離・車検・修復歴を一覧で比べられます。';
    }

    // ---- 構造化データ（パンくずは画面の表示と同じ並び・文言）----
    $breadcrumbItems = [
        ['name' => 'ホーム', 'item' => route('home')],
        ['name' => '中古車在庫一覧', 'item' => route('cars.index')],
    ];
    if ($isLatest) {
        $breadcrumbItems[] = ['name' => '新着の中古車', 'item' => route('cars.index', ['sort' => 'latest'])];
    }
    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => collect($breadcrumbItems)->values()->map(fn ($item, $i) => [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'name' => $item['name'],
            'item' => $item['item'],
        ])->all(),
    ];
    $itemListSchema = (! $hasFilter && ! $isLatest && $cars->currentPage() === 1 && $cars->isNotEmpty()) ? [
        '@context' => 'https://schema.org',
        '@type' => 'ItemList',
        'name' => '中古車在庫一覧',
        'url' => route('cars.index'),
        'numberOfItems' => $total,
        'itemListElement' => $cars->getCollection()->values()->map(fn ($car, $i) => [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'url' => route('cars.show', $car),
            'name' => CarText::name($car),
        ])->all(),
    ] : null;
@endphp

@section('title', $metaTitle)
@section('meta_description', $metaDescription)
@if ($hasFilter || $cars->currentPage() > 1)
    @section('meta_robots', 'noindex, follow')
@endif
@section('canonical', $isLatest ? route('cars.index', ['sort' => 'latest']) : route('cars.index'))
@section('body_class', 'p-cars')
{{-- ページの最後はレイアウトのご相談帯（電話・フォーム・店舗）。ページ側には連絡先の区画を重ねて作らない --}}

@push('structured_data')
    <script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    @if ($itemListSchema)
        <script type="application/ld+json">{!! json_encode($itemListSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    @endif
@endpush

@section('content')

{{-- 1. ページ見出し帯（灰の斜線地・黒い斜め帯の h1・英字の飾り）。
       通常：右に3台のイラスト（600px 以上は丸いバッジ「ただいま N台 掲載中」を重ねる）／新着：黄の「NEW!」の札（英字は飾り。意味は h1 で伝える） --}}
@if ($isLatest)
    <x-site.page-header class="p-cars-head p-cars-head--new" title="新着の中古車" en="NEW ARRIVALS"
        :lead="'新しく掲載した順に並べています。価格はすべて税込の支払総額です（'.config('shop.price_condition').'）。'"
        :breadcrumbs="[['label' => '中古車在庫一覧', 'url' => route('cars.index')], ['label' => '新着の中古車']]">
        <div class="p-cars-head__deco">
            <span class="p-cars-head__new" aria-hidden="true" lang="en">NEW!</span>
            @if ($newCount > 0)
                <x-site.round-badge class="p-cars-head__badge" top="新着" :num="$newCount" unit="台" :bottom="CarText::NEW_DAYS.'日以内'" />
            @elseif ($stockTotal > 0)
                <x-site.round-badge class="p-cars-head__badge" top="ただいま" :num="$stockTotal" unit="台" bottom="掲載中" />
            @endif
        </div>
    </x-site.page-header>
@else
    <x-site.page-header class="p-cars-head" title="中古車在庫一覧" en="STOCK LIST"
        :lead="'価格はすべて税込の支払総額です（'.config('shop.price_condition').'）。'">
        @if ($stockTotal > 0)
            <div class="p-cars-head__deco">
                <x-site.round-badge class="p-cars-head__badge" top="ただいま" :num="$stockTotal" unit="台" bottom="掲載中" />
                <x-site.illust name="all" class="p-cars-head__ill" />
            </div>
        @endif
    </x-site.page-header>
@endif

{{-- 2. 絞り込み（白）。STEP.1 ボディタイプのタイル（押すとすぐに絞り込む）→ 下向きの矢印 → STEP.2 条件を追加（開くと入力欄と赤い結果パネル）。
       見出し帯の下端に少し重ねて浮かせる。新着ページでは、代わりに並び順の説明（7日以内の掲載がなければ入荷のお知らせの案内） --}}
@if ($isLatest)
    <section class="l-section p-cars-search p-cars-search--new" aria-labelledby="cars-new-title">
        <div class="l-container">
            <div class="p-cars-newinfo{{ $showNoNewNotice ? ' is-empty' : '' }}">
                <span class="p-cars-newinfo__ic" aria-hidden="true"><x-site.icon name="calendar" :size="28" /></span>
                <div class="p-cars-newinfo__body">
                    @if ($showNoNewNotice)
                        <h2 class="p-cars-newinfo__title" id="cars-new-title">この{{ CarText::NEW_DAYS }}日間に新しく掲載した車はありません</h2>
                        <p class="p-cars-newinfo__text">{{ $lastPublished ? '最後に掲載したのは'.$lastPublished.'です。' : '' }}お探しの車をお伝えいただければ、入荷したときにご連絡します。</p>
                    @else
                        <h2 class="p-cars-newinfo__title" id="cars-new-title">掲載日の新しい順に並べています</h2>
                        <p class="p-cars-newinfo__text">掲載から{{ CarText::NEW_DAYS }}日以内の車には「新着」の印が付きます。</p>
                    @endif
                </div>
                <div class="p-cars-newinfo__actions">
                    @if ($showNoNewNotice)
                        <a class="c-btn c-btn--primary" href="{{ route('contact.index', ['purpose' => 'search']) }}"><x-site.icon name="mail" />入荷のお知らせを希望する</a>
                    @endif
                    <a class="c-btn c-btn--secondary" href="{{ route('cars.index') }}"><x-site.icon name="search" />条件を指定して在庫を探す</a>
                </div>
            </div>
        </div>
    </section>
@else
    <section class="l-section p-cars-search" aria-labelledby="cars-search-title">
        <div class="l-container">
            <h2 class="u-visually-hidden" id="cars-search-title">条件で絞り込む</h2>
            <div class="p-cars-steps" role="search" aria-labelledby="cars-search-title">

                <div class="p-cars-step p-cars-step--types">
                    <div class="p-cars-step__intro">
                        <p class="p-cars-step__head" id="cars-step1"><span class="p-cars-step__no" aria-hidden="true">STEP.<b>1</b></span><span>ボディタイプで選ぶ</span></p>
                        <p class="p-cars-step__hint">押すと、すぐに絞り込みます</p>
                    </div>
                    <ul class="p-cars-types" role="list" aria-labelledby="cars-step1">
                        <li>
                            <a class="p-cars-type" href="{{ route('cars.index', $query->except('body_type')->all()).$resultHash }}"
                               @unless ($active->has('body_type')) aria-current="true" @endunless>
                                <x-site.illust name="all" class="p-cars-type__ill" />
                                <span class="p-cars-type__name">すべて</span>
                                <span class="p-cars-type__count"><b>{{ $allTypesCount }}</b>台</span>
                            </a>
                        </li>
                        @foreach ($types as $bodyType)
                            @php
                                $isCurrent = ($active['body_type'] ?? null) === $bodyType;
                                $typeCount = $typeCounts[$bodyType] ?? 0;
                            @endphp
                            <li>
                                @if ($typeCount > 0 || $isCurrent)
                                    <a class="p-cars-type" href="{{ route('cars.index', array_merge($query->except('body_type')->all(), ['body_type' => $bodyType])).$resultHash }}"
                                       @if ($isCurrent) aria-current="true" @endif>
                                        <x-site.illust :name="CarText::bodyIllust($bodyType)" class="p-cars-type__ill" />
                                        <span class="p-cars-type__name">{{ CarText::bodyType($bodyType) }}</span>
                                        <span class="p-cars-type__count"><b>{{ $typeCount }}</b>台</span>
                                    </a>
                                @else
                                    {{-- ほかの条件では0台：押しても0件になるのでリンクにしない --}}
                                    <span class="p-cars-type is-empty">
                                        <x-site.illust :name="CarText::bodyIllust($bodyType)" class="p-cars-type__ill" />
                                        <span class="p-cars-type__name">{{ CarText::bodyType($bodyType) }}</span>
                                        <span class="p-cars-type__count"><b>0</b>台<span class="u-visually-hidden">（いまの条件では該当なし）</span></span>
                                    </span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="p-cars-step p-cars-step--more">
                    <details class="p-cars-more">
                        <summary class="p-cars-more__toggle">
                            <span class="p-cars-step__intro">
                                <span class="p-cars-step__head"><span class="p-cars-step__no" aria-hidden="true">STEP.<b>2</b></span><span>条件を追加して絞り込む</span></span>
                                <span class="p-cars-step__hint">
                                    @if ($extraCount > 0)
                                        <span class="p-cars-more__set">指定中 <b>{{ $extraCount }}</b>件</span>
                                    @endif
                                    予算（支払総額）・走行距離・メーカー・車検・キーワード
                                </span>
                            </span>
                            <span class="p-cars-more__ills" aria-hidden="true">
                                <x-site.illust name="coins" class="p-cars-more__ill" />
                                <x-site.illust name="meter" class="p-cars-more__ill" />
                            </span>
                            <span class="p-cars-more__btn">
                                <span class="p-cars-more__when-closed"><x-site.icon name="search" />条件を追加する</span>
                                <span class="p-cars-more__when-open">入力欄を閉じる</span>
                                <x-site.icon name="chevron-down" class="p-cars-more__chev" />
                            </span>
                        </summary>

                        <form class="p-cars-finder" method="get" action="{{ route('cars.index') }}#cars-result"
                              x-data="carsFinder" x-on:submit.prevent="go($event.target)" x-on:change="recount()" x-on:input.debounce.250ms="recount()">
                            <p class="c-field__hint">当てはまる条件だけ選んでください。選ばなかった項目は「指定なし」で探します。</p>

                            <div class="p-cars-finder__fields">
                                <div class="c-field">
                                    <label class="c-field__label" for="min_price"><x-site.icon name="yen" class="p-cars-finder__ic" />支払総額（下限）</label>
                                    <select class="c-field__input c-field__input--select" id="min_price" name="min_price">
                                        <option value="">下限なし</option>
                                        @if ($active->has('min_price') && ! isset($priceSteps[(int) $active['min_price']]))
                                            <option value="{{ $active['min_price'] }}" selected>{{ $priceLabel($active['min_price']) }}以上</option>
                                        @endif
                                        @foreach ($priceSteps as $yen => $label)
                                            <option value="{{ $yen }}" @selected($active->has('min_price') && (int) $active['min_price'] === $yen)>{{ $label }}以上</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="c-field">
                                    <label class="c-field__label" for="max_price"><x-site.icon name="yen" class="p-cars-finder__ic" />支払総額（上限）</label>
                                    <select class="c-field__input c-field__input--select" id="max_price" name="max_price">
                                        <option value="">上限なし</option>
                                        @if ($active->has('max_price') && ! isset($priceSteps[(int) $active['max_price']]))
                                            <option value="{{ $active['max_price'] }}" selected>{{ $priceLabel($active['max_price']) }}以下</option>
                                        @endif
                                        @foreach ($priceSteps as $yen => $label)
                                            <option value="{{ $yen }}" @selected($active->has('max_price') && (int) $active['max_price'] === $yen)>{{ $label }}以下</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="c-field">
                                    <label class="c-field__label" for="max_mileage"><x-site.icon name="meter" class="p-cars-finder__ic" />走行距離（上限）</label>
                                    <select class="c-field__input c-field__input--select" id="max_mileage" name="max_mileage">
                                        <option value="">上限なし</option>
                                        @if ($active->has('max_mileage') && ! isset($mileageSteps[(int) $active['max_mileage']]))
                                            <option value="{{ $active['max_mileage'] }}" selected>{{ $mileageLabel($active['max_mileage']) }}以下</option>
                                        @endif
                                        @foreach ($mileageSteps as $km => $label)
                                            <option value="{{ $km }}" @selected($active->has('max_mileage') && (int) $active['max_mileage'] === $km)>{{ $label }}以下</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="c-field">
                                    <label class="c-field__label" for="make"><x-site.icon name="car" class="p-cars-finder__ic" />メーカー</label>
                                    <select class="c-field__input c-field__input--select" id="make" name="make">
                                        <option value="">指定なし</option>
                                        @foreach ($makes as $make)
                                            <option value="{{ $make }}" @selected(($active['make'] ?? '') === $make)>{{ $make }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="c-field">
                                    <label class="c-field__label" for="body_type"><x-site.icon name="car" class="p-cars-finder__ic" />ボディタイプ</label>
                                    <select class="c-field__input c-field__input--select" id="body_type" name="body_type">
                                        <option value="">指定なし</option>
                                        @foreach ($types as $bodyType)
                                            <option value="{{ $bodyType }}" @selected(($active['body_type'] ?? '') === $bodyType)>{{ CarText::bodyType($bodyType) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="c-field">
                                    <label class="c-field__label" for="inspection"><x-site.icon name="cal-check" class="p-cars-finder__ic" />車検</label>
                                    <select class="c-field__input c-field__input--select" id="inspection" name="inspection" aria-describedby="inspection-hint">
                                        <option value="">指定なし</option>
                                        @foreach ($inspectionLabels as $type => $label)
                                            <option value="{{ $type }}" @selected(($active['inspection'] ?? '') === $type)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <p class="c-field__hint" id="inspection-hint">「納車時に取得」は、ご納車の前に新しく車検を受ける車です。「残りあり」は、前の車検の期限が残っている車です。</p>
                                </div>

                                <div class="c-field p-cars-finder__wide">
                                    <label class="c-field__label" for="q"><x-site.icon name="search" class="p-cars-finder__ic" />キーワード</label>
                                    <input class="c-field__input" type="search" id="q" name="q" value="{{ $active['q'] ?? '' }}"
                                           enterkeyhint="search" autocomplete="off" aria-describedby="q-hint">
                                    <p class="c-field__hint" id="q-hint">例）CX-5、MZ4187（車種名・グレード・在庫番号で探せます）</p>
                                </div>
                            </div>

                            {{-- 並び替えを選んでいるときは引き継ぐ（sort=latest は送らない＝新着ページに切り替わらない） --}}
                            @if ($sortValue !== '')
                                <input type="hidden" name="sort" value="{{ $sortValue }}">
                            @endif
                            @foreach ($query->except(array_merge($filterKeys, ['sort'])) as $name => $value)
                                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                            @endforeach

                            {{-- 赤い結果パネル：条件に合う台数（選ぶたびに数え直す。JavaScript が動かないときは、いまの一覧の台数） --}}
                            <div class="p-cars-go">
                                <p class="p-cars-go__result" aria-live="polite">
                                    <span class="p-cars-go__label">条件に合う車</span>
                                    <span class="p-cars-go__count"><span x-text="hits">{{ $total }}</span><small>台</small></span>
                                </p>
                                <p class="p-cars-go__note" x-show="hits === 0" @if ($total > 0) x-cloak @endif>条件をゆるめるか、お気軽にご相談ください。</p>
                                <div class="p-cars-go__actions">
                                    <button type="submit" class="c-btn c-btn--yellow c-btn--lg c-btn--block"><x-site.icon name="search" />この条件で探す</button>
                                    <a class="p-cars-go__clear" href="{{ route('cars.index') }}">条件をすべて解除する</a>
                                </div>
                            </div>
                        </form>
                    </details>
                </div>
            </div>
        </div>
    </section>
@endif

{{-- 3. 結果（灰）：いまの条件 → 該当台数の黒い帯＋並び替え → 価格の注記 → 車両カード（スマホ1列・600px 2列・960px 3列）→ ページ送り／0件の空状態 --}}
<section class="l-section l-section--soft p-cars-result" id="cars-result" aria-labelledby="cars-result-title">
    <div class="l-container">
        @if ($conditions->isNotEmpty())
            <div class="p-cars-current">
                <p class="p-cars-current__title" id="cars-current-title"><x-site.icon name="search" class="p-cars-current__ic" />いまの条件<span class="p-cars-current__help">（× を押すと、その条件を外せます）</span></p>
                <ul class="p-cars-current__list" aria-labelledby="cars-current-title">
                    @foreach ($conditions as $condition)
                        <li>
                            <a class="p-cars-cond" href="{{ $condition['url'] }}" aria-label="{{ $condition['label'] }} の条件を外す">
                                <span>{{ $condition['label'] }}</span>
                                <span class="p-cars-cond__x" aria-hidden="true"><x-site.icon name="close" :size="16" /></span>
                            </a>
                        </li>
                    @endforeach
                    @if ($conditions->count() >= 2)
                        <li><a class="c-link c-link--block p-cars-current__clear" href="{{ $clearUrl }}">条件をすべて解除する</a></li>
                    @endif
                </ul>
            </div>
        @endif

        <div class="p-cars-bar">
            <h2 class="p-cars-bar__title" id="cars-result-title">
                <span class="p-cars-bar__label">{{ $isLatest ? '掲載中の車' : '該当' }}</span>
                <span class="p-cars-bar__num">{{ number_format($total) }}</span><span class="p-cars-bar__unit">台</span>
            </h2>
            @if (! $isLatest && $hasFilter)
                <p class="p-cars-bar__of">（掲載中 全{{ $stockTotal }}台のうち）</p>
            @endif

            @if ($isLatest)
                <p class="p-cars-bar__of">掲載日の新しい順</p>
            @elseif ($total > 0)
                <form class="p-cars-sort" method="get" action="{{ route('cars.index') }}#cars-result"
                      x-data="carsQueryForm" x-on:submit.prevent="go($event.target)">
                    @foreach ($query->except('sort') as $name => $value)
                        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endforeach
                    <label class="p-cars-sort__label" for="cars-sort">並び替え</label>
                    <select class="c-field__input c-field__input--select p-cars-sort__select" id="cars-sort" name="sort"
                            aria-describedby="cars-sort-hint"
                            x-on:change="$el.form.requestSubmit ? $el.form.requestSubmit() : $el.form.submit()">
                        @foreach ($sortOptions as $value => $label)
                            <option value="{{ $value }}" @selected($sortValue === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <span class="u-visually-hidden" id="cars-sort-hint">選ぶと、すぐに並び替わります</span>
                    <noscript><button type="submit" class="c-btn c-btn--secondary c-btn--sm">並び替える</button></noscript>
                </form>
            @endif
        </div>

        @if ($total > 0)
            <div class="c-alert p-cars-note">
                <x-site.icon name="info" />
                <p>価格はすべて<b>税込の支払総額</b>です。{{ CarText::priceNote() }}</p>
            </div>

            <ul class="l-grid l-grid--3 p-cars-grid{{ $isLatest ? ' p-cars-grid--dated' : '' }}" role="list">
                @foreach ($cars as $car)
                    @php $published = $isLatest ? CarText::date($car->published_at ?? $car->created_at) : null; @endphp
                    <li class="p-cars-item">
                        @if ($published)
                            {{-- 新着ページ：カードの上に掲載日の札（掲載7日以内は赤、それより前は黒） --}}
                            <p class="p-cars-item__date{{ CarText::isNew($car) ? ' is-new' : '' }}"><x-site.icon name="calendar" :size="16" />掲載日 {{ $published }}</p>
                        @endif
                        <x-site.car-card :car="$car" :loading="$loop->index < 3 ? 'eager' : 'lazy'" />
                    </li>
                @endforeach
            </ul>

            <x-site.pagination :paginator="$cars" unit="台" />
        @else
            <x-site.empty-state class="p-cars-empty" :title="$emptyTitle" illust="empty" :heading-level="3">
                @if ($hasFilter)
                    条件を減らすか、すべての在庫をご覧ください。探してほしい車をお伝えいただければ、入荷したときにご連絡します。
                @else
                    ただいま掲載している車はありません。探してほしい車をお伝えいただければ、入荷したときにご連絡します。
                @endif
                <x-slot:actions>
                    @if ($hasFilter && ! $isLatest)
                        {{-- 通常の一覧では「条件の解除」と「すべての在庫」が同じ行き先なので1つにまとめる --}}
                        <a class="c-btn c-btn--primary" href="{{ route('cars.index') }}">条件を外して、すべての在庫を見る</a>
                    @elseif ($hasFilter)
                        <a class="c-btn c-btn--primary" href="{{ $clearUrl }}">条件をすべて解除する</a>
                        <a class="c-btn c-btn--secondary" href="{{ route('cars.index') }}">すべての在庫を見る</a>
                    @endif
                    <a class="c-btn c-btn--secondary" href="{{ route('contact.index', ['purpose' => 'search']) }}"><x-site.icon name="mail" />探してほしい車を伝える</a>
                </x-slot:actions>
            </x-site.empty-state>
        @endif
    </div>
</section>

{{-- 4. どの車が合うか迷ったら（白）：使い方・予算・走行距離・メーカーから探す → このあとにレイアウトのご相談帯（電話・フォーム・店舗） --}}
<section class="l-section p-cars-guide" id="consult" aria-labelledby="cars-consult-title">
    <div class="l-container">
        <x-site.section-head id="cars-consult-title" title="どの車が合うか迷ったら" en="GUIDE"
            lead="使い方やご予算から探せます。決めきれないときは、すぐ下の「ご相談・ご来店はこちら」から、お電話・フォームでお気軽にご相談ください。" />

        @if ($useCases->isNotEmpty())
            <h3 class="c-subhead">使い方から選ぶ</h3>
            <ul class="c-link-cards" role="list">
                @foreach ($useCases as $useCase)
                    <li><x-site.link-card :href="route('cars.index', ['body_type' => $useCase['type']])" :icon="$useCase['icon']" :tone="$useCase['tone']"
                            :title="$useCase['title']" :sub="$useCase['sub']" :count="$useCase['count']" /></li>
                @endforeach
            </ul>
        @endif

        @if ($quickChips->isNotEmpty())
            <h3 class="c-subhead{{ $useCases->isNotEmpty() ? ' p-cars-guide__next' : '' }}">予算・走行距離・メーカーから探す</h3>
            <ul class="c-chips c-chips--grid" role="list">
                @foreach ($quickChips as $chip)
                    <li><x-site.chip :href="$chip['url']" :icon="$chip['icon']" :tone="$chip['tone']" :count="$chip['count']">{{ $chip['label'] }}</x-site.chip></li>
                @endforeach
            </ul>
        @endif

        {{-- ページ末尾の説明文 --}}
        <p class="p-cars-about">{{ $shopName }}は、{{ config('shop.pref') }}{{ config('shop.area') }}の中古車販売店です。掲載している車は、支払総額（税込）に加えて、年式・走行距離・車検・修復歴を一覧で見比べられます。気になる車があれば、在庫の確認・見積もり・見学のご予約を、お電話またはフォームで承ります。</p>
    </div>
</section>

@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    // 値のない項目を URL に載せずに移動する（JavaScript が動かないときは通常の送信）
    const go = (form) => {
        const params = new URLSearchParams();
        for (const [name, value] of new FormData(form)) {
            if (String(value).trim() !== '') params.append(name, value);
        }
        const [base, hash] = form.getAttribute('action').split('#');
        const search = params.toString();
        window.location.assign(base + (search ? '?' + search : '') + (hash ? '#' + hash : ''));
    };

    // 並び替えのフォーム
    Alpine.data('carsQueryForm', () => ({ go }));

    // STEP.2 の条件のフォーム：選ぶたびに「条件に合う車 N台」を数え直す（数え方は CarController@index と同じ）
    Alpine.data('carsFinder', () => ({
        cars: @js($finderCars),
        inspections: @js(Car::INSPECTION_TYPES),
        hits: {{ (int) $total }},
        go,
        init() {
            this.recount();
        },
        recount() {
            const data = new FormData(this.$root);
            const val = (name) => String(data.get(name) ?? '').trim();
            // 全角・半角、大文字・小文字、カタカナ・ひらがなの違いを無視して比べる
            const norm = (text) => String(text ?? '').normalize('NFKC').toLowerCase()
                .replace(/[ァ-ヶ]/g, (c) => String.fromCharCode(c.charCodeAt(0) - 0x60));
            const num = (name) => Math.max(0, parseInt(val(name), 10) || 0);
            const q = norm(val('q'));

            this.hits = this.cars.filter((car) => {
                if (q !== '' && ! car.words.some((word) => norm(word).includes(q))) return false;
                if (val('make') !== '' && car.make !== val('make')) return false;
                if (val('body_type') !== '' && car.body !== val('body_type')) return false;
                if ((val('min_price') !== '' || val('max_price') !== '') && (car.neg || car.price === null)) return false;
                if (val('min_price') !== '' && car.price < num('min_price')) return false;
                if (val('max_price') !== '' && car.price > num('max_price')) return false;
                if (val('max_mileage') !== '' && (car.km === null || car.km > num('max_mileage'))) return false;
                if (this.inspections.includes(val('inspection')) && car.insp !== val('inspection')) return false;
                return true;
            }).length;
        },
    }));
});
</script>
@endpush
