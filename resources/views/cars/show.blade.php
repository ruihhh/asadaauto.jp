@extends('layouts.site')

@use('App\Support\CarText')
@use('App\Support\BusinessHours')
@use('Illuminate\Support\HtmlString')

@php
    // 車両の表記はすべて CarText、店舗の情報は config('shop.*') と BusinessHours から作る（直書きしない）
    $name = CarText::name($car);
    $fullName = trim($name.' '.($car->grade ?? ''));
    $carUrl = route('cars.show', $car);
    $shopName = config('shop.name');
    $tel = config('shop.tel');
    $lineUrl = config('shop.line_url');

    $ask = $car->price_negotiable || $car->price === null;
    $price = $ask ? null : (int) $car->price;
    $fees = CarText::fees($car);
    $showBreakdown = ! $ask && $car->base_price !== null && $fees !== null;

    $yearLabel = CarText::year($car->model_year !== null ? (int) $car->model_year : null);
    $mileageLabel = CarText::mileage($car->mileage !== null ? (int) $car->mileage : null);
    $repair = CarText::repair($car);
    $inspection = CarText::inspection($car);
    $warranty = CarText::warranty();
    $maintenance = CarText::maintenance();
    $tags = CarText::tags($car);
    $isPick = collect($tags)->contains(fn (array $tag): bool => $tag['variant'] === 'pick');
    $highlights = CarText::equipmentHighlights($car, 6);
    $equipment = CarText::equipmentByCategory($car);
    $equipmentCount = array_sum(array_map('count', $equipment));
    $atShop = CarText::atShop($car);

    // 「お問い合わせください」「2027年3月まで（令和9年3月）」などを、語の区切り（「お問い合わせ｜ください」「（」の前）でだけ改行させる
    $nowrapParts = function (string $text): HtmlString {
        $parts = $text === CarText::UNKNOWN
            ? ['お問い合わせ', 'ください']
            : preg_split('/(?=（)/u', $text, 2, PREG_SPLIT_NO_EMPTY);

        return new HtmlString(implode('', array_map(fn (string $part): string => '<span class="u-nowrap">'.e($part).'</span>', $parts)));
    };

    // 状態の色：なし・期限あり＝緑（ok）、要確認＝琥珀（check）、あり・期限切れ＝橙（caution）、値がない＝色なし（neutral）
    $inspectionTone = match ($inspection['tone']) {
        'ok' => 'ok',
        'caution' => 'caution',
        default => 'check',
    };
    $repairTone = $repair['tone'];

    // 写真（image_path → images の順）
    $photoPaths = collect();
    if (filled($car->image_path)) {
        $photoPaths->push($car->image_path);
    }
    foreach ($car->images as $image) {
        if (filled($image->path)) {
            $photoPaths->push($image->path);
        }
    }
    $photoPaths = $photoPaths->unique()->values();
    $photoCount = $photoPaths->count();
    $photos = $photoPaths->map(fn (string $path, int $i): array => [
        'src' => asset('images/'.$path),
        'alt' => $name.'の写真（'.($i + 1).'枚目）',
    ])->all();
    // サムネイルは5枠（スマホは4枠）。写真が足りない枠は「写真準備中」
    $placeholderCount = $photoCount > 0 ? max(0, 5 - $photoCount) : 0;

    // 重要4項目（価格のすぐ下に必ず出す）
    $keyfacts = [
        ['key' => 'repair', 'label' => '修復歴', 'icon' => 'frame', 'value' => $repair['label'], 'tone' => $repairTone, 'sub' => $repair['note']],
        ['key' => 'inspection', 'label' => '車検', 'icon' => 'cal-check', 'value' => $inspection['label'], 'tone' => $inspectionTone, 'sub' => null],
        ['key' => 'warranty', 'label' => '保証', 'icon' => 'shield', 'value' => $warranty ?? CarText::UNKNOWN, 'tone' => 'neutral', 'sub' => null],
        ['key' => 'maintenance', 'label' => new HtmlString('<span class="u-nowrap">定期点検整備</span><span class="u-nowrap">（法定整備）</span>'), 'icon' => 'wrench', 'value' => $maintenance ?? CarText::UNKNOWN, 'tone' => 'neutral', 'sub' => null],
    ];

    // 車両の状態・仕様のタイル（年式［和暦］・走行距離・車検・修復歴・点検記録簿・ミッション・燃料・ボディタイプ・ボディカラー・展示場所・掲載日・在庫番号）
    $yearLong = CarText::year($car->model_year !== null ? (int) $car->model_year : null, true);
    // 「2023年｜（令和5年）」の区切りでだけ改行させ、西暦の数字だけを大きな書体にする
    $yearValue = preg_match('/^(\d{4})(年)(（.*）)?$/u', $yearLong, $m)
        ? new HtmlString('<span class="u-nowrap"><span class="p-detail-spec__num">'.e($m[1]).'</span>'.e($m[2]).'</span>'
            .(isset($m[3]) && $m[3] !== '' ? '<span class="u-nowrap">'.e($m[3]).'</span>' : ''))
        : $yearLong;
    // 掲載日は「2026年｜3月22日」の区切りでだけ改行させる
    $publishedLabel = CarText::date($car->published_at ?? $car->created_at);
    $publishedValue = $publishedLabel !== null
        ? new HtmlString(implode('', array_map(fn (string $part): string => '<span class="u-nowrap">'.e($part).'</span>', preg_split('/(?<=年)/u', $publishedLabel, 2, PREG_SPLIT_NO_EMPTY))))
        : CarText::UNKNOWN;
    $mileageParts = CarText::mileageParts($car->mileage !== null ? (int) $car->mileage : null);
    $mileageValue = $mileageParts !== null
        ? new HtmlString('<span class="p-detail-spec__num">'.e($mileageParts['value']).'</span>'.e($mileageParts['unit']))
        : CarText::UNKNOWN;

    // ボディカラーの色見本（色名に含まれる語から、近い色の系統を選ぶ。どれにも当たらなければ出さない）
    $colorName = trim((string) $car->color);
    $swatch = null;
    foreach ([
        'red' => ['レッド', '赤', 'ワイン', 'ボルドー'],
        'blue' => ['ブルー', '青', 'ネイビー', '紺'],
        'green' => ['グリーン', '緑', 'カーキ'],
        'yellow' => ['イエロー', '黄'],
        'orange' => ['オレンジ', '橙'],
        'brown' => ['ブラウン', '茶', 'ベージュ', 'ゴールド'],
        'black' => ['ブラック', '黒'],
        'gray' => ['グレー', 'グレイ', '灰', 'ガンメタ'],
        'silver' => ['シルバー', '銀'],
        'white' => ['ホワイト', '白', 'パール'],
    ] as $swatchKey => $words) {
        foreach ($words as $word) {
            if ($colorName !== '' && str_contains($colorName, $word)) {
                $swatch = $swatchKey;
                break 2;
            }
        }
    }
    // 並び：1枠の項目を先に、長い値の項目（展示場所）を最後に置く（タイルは横幅いっぱいまで伸びるので、最後の行に小さな1枠だけが残らない）
    // wide：常に2枠分（展示場所）／wide-sp：スマホだけ横幅いっぱい（ボディタイプのイラスト・ボディカラーの長い色名）
    $specRows = [
        'year' => ['label' => '年式', 'icon' => 'calendar', 'value' => $yearValue],
        'mileage' => ['label' => '走行距離', 'icon' => 'meter', 'value' => $mileageValue],
        'inspection' => ['label' => '車検', 'icon' => 'cal-check', 'value' => $nowrapParts($inspection['label']), 'tone' => $inspectionTone],
        'repair' => ['label' => '修復歴', 'icon' => 'frame', 'value' => $repair['label'] === CarText::UNKNOWN ? $nowrapParts($repair['label']) : $repair['label'], 'tone' => $repairTone, 'note' => $repair['note']],
    ];
    if ($car->has_service_record) {
        $specRows['record'] = ['label' => '点検記録簿', 'icon' => 'file', 'value' => 'あり', 'tone' => 'ok'];
    }
    $specRows += [
        'transmission' => ['label' => 'ミッション', 'icon' => 'gear', 'value' => CarText::transmission($car->transmission)],
        'fuel' => ['label' => '燃料', 'icon' => 'fuel', 'value' => CarText::fuel($car->fuel_type)],
        'body_type' => ['label' => 'ボディタイプ', 'icon' => 'car', 'value' => CarText::bodyType($car->body_type), 'illust' => filled($car->body_type) ? CarText::bodyIllust($car->body_type) : null, 'size' => 'wide-sp'],
        // ボディカラーは、アイコンの丸を色見本にする（色名は必ず文字でも出す。系統がわからない色はしずくのアイコン）
        'color' => ['label' => 'ボディカラー', 'icon' => 'drop', 'value' => $colorName !== '' ? $colorName : CarText::UNKNOWN, 'swatch' => $swatch, 'size' => 'wide-sp'],
        'published' => ['label' => '掲載日', 'icon' => 'clock', 'value' => $publishedValue],
        'stock_no' => ['label' => '在庫番号', 'icon' => 'tag', 'value' => filled($car->stock_no) ? new HtmlString('<span class="p-detail-spec__num">'.e($car->stock_no).'</span>') : CarText::UNKNOWN],
        'location' => ['label' => '展示場所', 'icon' => 'map-pin', 'value' => $nowrapParts(CarText::location($car)), 'tone' => $atShop ? null : 'caution', 'size' => 'wide'],
    ];

    // <title>・meta（layouts/site が「 | 店名」を付ける）
    $priceTitle = match (true) {
        (bool) $car->price_negotiable => '価格応談',
        $price === null => '価格はお問い合わせください',
        default => '支払総額'.CarText::price($price),
    };
    $pageTitle = $fullName.' '.$yearLabel.'・'.$priceTitle;
    $priceSentence = match (true) {
        (bool) $car->price_negotiable => '価格はお問い合わせください（応談）。',
        $price === null => '価格はお問い合わせください。',
        default => '支払総額'.CarText::price($price).'（税込）。',
    };
    $metaDescription = $yearLabel.'・走行'.$mileageLabel.'・'.$repair['text'].'。'.$priceSentence.'兵庫県尼崎市の中古車販売店'.$shopName.'。';

    // お問い合わせフォームへのリンク（在庫番号とご用件を付ける）
    $estimateUrl = route('contact.index', ['stock_no' => $car->stock_no, 'purpose' => 'estimate']);
    $visitUrl = route('contact.index', ['stock_no' => $car->stock_no, 'purpose' => 'visit']);
    $loanUrl = route('contact.index', ['stock_no' => $car->stock_no, 'purpose' => 'loan']);

    // この車のお支払い例（支払総額を万円の整数にしてシミュレーターの初期値にする）
    $loanPrice = $price !== null ? max(1, (int) round($price / 10000)) : null;
    $loanRounded = $price !== null && $price % 10000 !== 0;

    // 関連車両がないときの「似た条件の車を探す」（同じボディタイプ／価格帯 ±50万円（下限0）／すべての在庫。台数は公開在庫から数える）
    // あわせて、同じボディタイプか近い価格帯の公開在庫（この車を除く・新しい順に最大4台）を車両カードで見せる
    $similar = [];
    $similarCars = collect();
    if ($relatedCars->isEmpty()) {
        $inventory = \App\Models\Car::query()->publicInventory();
        if (filled($car->body_type) || $price !== null) {
            $similarCars = (clone $inventory)
                ->where('id', '!=', $car->id)
                ->where(function ($query) use ($car, $price): void {
                    if (filled($car->body_type)) {
                        $query->orWhere('body_type', $car->body_type);
                    }
                    if ($price !== null) {
                        $query->orWhere(fn ($inner) => $inner->where('price_negotiable', false)->whereBetween('price', [max(0, $price - 500000), $price + 500000]));
                    }
                })
                ->with('images')
                ->latest('published_at')
                ->limit(4)
                ->get();
        }
        if (filled($car->body_type)) {
            $similar[] = [
                'href' => route('cars.index', ['body_type' => $car->body_type]),
                'icon' => 'car',
                'tone' => 'red',
                'title' => new HtmlString('同じ<b>ボディタイプ</b>'),
                'sub' => CarText::bodyType($car->body_type).'の在庫を見る',
                'count' => (clone $inventory)->where('body_type', $car->body_type)->count(),
            ];
        }
        if ($price !== null) {
            $min = max(0, $price - 500000);
            $max = $price + 500000;
            $minMan = CarText::priceMan($min);
            $maxMan = CarText::priceMan($max);
            $range = match (true) {
                $min === 0 => CarText::price($max).'まで',
                $minMan !== null && $maxMan !== null => $minMan.'万〜'.$maxMan.'万円',
                default => CarText::price($min).'〜'.CarText::price($max),
            };
            $similar[] = [
                'href' => route('cars.index', ['min_price' => $min, 'max_price' => $max]),
                'icon' => 'yen',
                'tone' => 'yellow',
                'title' => new HtmlString('近い<b>価格帯</b>'),
                'sub' => '支払総額 '.$range,
                'count' => (clone $inventory)->where('price_negotiable', false)->whereBetween('price', [$min, $max])->count(),
            ];
        }
        $similar[] = [
            'href' => route('cars.index'),
            'icon' => 'search',
            'tone' => 'black',
            'title' => new HtmlString('<b>すべて</b>の在庫'),
            'sub' => '掲載中の車をすべて見る',
            'count' => (clone $inventory)->count(),
        ];
    }

    // 見出しは語の区切りでだけ改行させる（「お支払い｜例」のような途中の改行を防ぐ）
    $commentTitle = new HtmlString('<span class="u-nowrap">この車について</span><span class="u-nowrap">（スタッフより）</span>');
    $loanTitle = new HtmlString('<span class="u-nowrap">この車の</span><span class="u-nowrap">お支払い例</span><small>（計算例）</small>');

    // 装備一覧（開閉）は、主な装備のアイコンに出しきれない装備があるときだけ出す
    $hasEquipment = $highlights !== [] || $equipment !== [];
    $showEquipmentList = $equipment !== [] && $equipmentCount > count($highlights);
    $hasComment = filled($car->description);

    // 上段（写真・概要・仕様。白）の下の区画の背景（灰と白を交互に。出さない区画があってもそろう）
    $sectionOrder = array_keys(array_filter([
        'equip' => $hasEquipment || $hasComment,
        'breakdown' => $showBreakdown,
        'loan' => $loanPrice !== null,
        'flow' => true,
        'shop' => true,
        'related' => true,
    ]));
    $bg = fn (string $key): string => array_search($key, $sectionOrder, true) % 2 === 0 ? ' l-section--soft' : '';

    // 構造化データ（Vehicle と BreadcrumbList）。画面の表記と同じ文字列を使う
    $vehicleSchema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Vehicle',
        '@id' => $carUrl.'#vehicle',
        'name' => $name,
        'url' => $carUrl,
        'sku' => $car->stock_no,
        'brand' => ['@type' => 'Brand', 'name' => $car->make],
        'model' => $car->model,
        'vehicleConfiguration' => filled($car->grade) ? $car->grade : null,
        'vehicleModelDate' => $car->model_year ? (string) $car->model_year : null,
        'mileageFromOdometer' => $car->mileage !== null
            ? ['@type' => 'QuantitativeValue', 'value' => (int) $car->mileage, 'unitCode' => 'KMT']
            : null,
        'vehicleTransmission' => filled($car->transmission) ? CarText::transmission($car->transmission) : null,
        'bodyType' => filled($car->body_type) ? CarText::bodyType($car->body_type) : null,
        'color' => $colorName !== '' ? $colorName : null,
        'fuelType' => filled($car->fuel_type) ? CarText::fuel($car->fuel_type) : null,
        'knownVehicleDamages' => $repair['has'] !== null ? $repair['text'] : null,
        'itemCondition' => 'https://schema.org/UsedCondition',
        'description' => filled($car->description)
            ? \Illuminate\Support\Str::limit($car->description, 200)
            : $fullName.'。'.$yearLabel.'・走行'.$mileageLabel.'・'.$repair['text'].'。',
        'image' => $photos !== [] ? array_column($photos, 'src') : null,
        'offers' => array_filter([
            '@type' => 'Offer',
            'url' => $carUrl,
            'priceCurrency' => 'JPY',
            'price' => $price,
            'availability' => $car->status === 'available' ? 'https://schema.org/InStock' : 'https://schema.org/SoldOut',
            'itemCondition' => 'https://schema.org/UsedCondition',
            'seller' => ['@id' => url('/').'#organization'], // AutoDealer はレイアウトが出す
        ], fn ($value) => $value !== null),
    ], fn ($value) => $value !== null);

    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'ホーム', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => '中古車在庫一覧', 'item' => route('cars.index')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $name, 'item' => $carUrl],
        ],
    ];
    $jsonFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG;
@endphp

@section('title', $pageTitle)
@section('meta_description', $metaDescription)
@section('meta_robots', $car->status === 'available' ? 'index, follow' : 'noindex, follow')
@section('canonical', $carUrl)
@section('og_type', 'product')
@section('og_title', $pageTitle.' | '.$shopName)
@section('og_description', $metaDescription)
@if ($photos !== [])
    @section('og_image', $photos[0]['src'])
@endif
@section('body_class', 'p-detail')

@push('structured_data')
    <script type="application/ld+json">{!! json_encode($vehicleSchema, $jsonFlags) !!}</script>
    <script type="application/ld+json">{!! json_encode($breadcrumbSchema, $jsonFlags) !!}</script>
@endpush

@section('content')
{{-- パンくず（h1 は概要欄に置くため、x-site.page-header ではなくパンくずだけを出す） --}}
<div class="p-detail-crumbs">
    <div class="l-container">
        <x-site.breadcrumb :items="[['label' => '中古車在庫一覧', 'url' => route('cars.index')], ['label' => $name]]" />
    </div>
</div>

{{-- 写真と概要（PC は 7:5。スマホは 写真 → 車名 → 価格 → 重要4項目 → 問い合わせ の順） --}}
<div class="p-detail-top">
    <div class="l-container">
        <div class="p-detail-main">

            {{-- 写真ギャラリー（4:3・下を切らない・拡大・スワイプ・枚数） --}}
            <div class="p-detail-gallery" x-data="detailGallery(@js($photos))" x-on:keydown.escape.window="close()">
                @if ($photoCount > 0)
                    <div class="p-detail-gallery__stage" x-on:touchstart.passive="touchStart($event)" x-on:touchend="touchEnd($event)">
                        <img class="p-detail-gallery__img" src="{{ $photos[0]['src'] }}" alt="{{ $photos[0]['alt'] }}"
                             :src="current.src" :alt="current.alt"
                             width="800" height="600" fetchpriority="high" decoding="async"
                             x-on:click="open($refs.zoom)">
                        @if ($isPick)
                            <p class="c-ribbon p-detail-gallery__ribbon"><x-site.icon name="star" />店長おすすめ</p>
                        @endif
                        @if ($photoCount > 1)
                            <button type="button" class="p-detail-gallery__nav p-detail-gallery__nav--prev" aria-label="前の写真" x-on:click="prev()">
                                <x-site.icon name="chevron-left" :size="28" />
                            </button>
                            <button type="button" class="p-detail-gallery__nav p-detail-gallery__nav--next" aria-label="次の写真" x-on:click="next()">
                                <x-site.icon name="chevron-right" :size="28" />
                            </button>
                        @endif
                        <button type="button" class="p-detail-gallery__zoom" x-ref="zoom" x-on:click="open($el)">
                            <x-site.icon name="zoom" />写真を拡大する
                        </button>
                        <p class="p-detail-gallery__count" aria-live="polite">写真 <span x-text="index + 1">1</span> / {{ $photoCount }}</p>
                    </div>

                    <ul class="p-detail-gallery__thumbs" role="list" aria-label="写真の一覧">
                        @foreach ($photos as $i => $photo)
                            <li class="p-detail-gallery__thumb-item">
                                <button type="button" class="p-detail-gallery__thumb" aria-label="{{ $i + 1 }}枚目の写真を表示"
                                        aria-pressed="{{ $i === 0 ? 'true' : 'false' }}" :aria-pressed="index === {{ $i }} ? 'true' : 'false'"
                                        x-on:click="show({{ $i }})">
                                    <img class="p-detail-gallery__thumb-img" src="{{ $photo['src'] }}" alt="" width="160" height="120" loading="lazy" decoding="async">
                                </button>
                            </li>
                        @endforeach
                        @for ($i = 0; $i < $placeholderCount; $i++)
                            <li class="p-detail-gallery__thumb-item p-detail-gallery__thumb-item--empty" aria-hidden="true">
                                <span class="p-detail-gallery__empty"><x-site.icon name="camera" />写真準備中</span>
                            </li>
                        @endfor
                    </ul>

                    {{-- 全画面表示（Esc・閉じる・左右のスワイプ・矢印キーに対応） --}}
                    <div class="p-detail-viewer" x-show="isOpen" x-cloak x-ref="viewer"
                         role="dialog" aria-modal="true" aria-label="{{ $name }}の写真の拡大表示"
                         x-on:keydown.tab="trap($event)" x-on:keydown.arrow-left="prev()" x-on:keydown.arrow-right="next()">
                        <div class="p-detail-viewer__head">
                            <p class="p-detail-viewer__count">写真 <span x-text="index + 1">1</span> / {{ $photoCount }}</p>
                            <button type="button" class="c-btn c-btn--yellow c-btn--sm" x-ref="close" x-on:click="close()">
                                <x-site.icon name="close" />閉じる
                            </button>
                        </div>
                        <div class="p-detail-viewer__stage" x-on:touchstart.passive="touchStart($event)" x-on:touchend="touchEnd($event)">
                            <img class="p-detail-viewer__img" :src="isOpen ? current.src : ''" :alt="current.alt" alt="" width="800" height="600">
                        </div>
                        @if ($photoCount > 1)
                            <div class="p-detail-viewer__foot">
                                <button type="button" class="c-btn c-btn--secondary c-btn--block" x-on:click="prev()"><x-site.icon name="chevron-left" />前の写真</button>
                                <button type="button" class="c-btn c-btn--secondary c-btn--block" x-on:click="next()">次の写真<x-site.icon name="chevron-right" /></button>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="p-detail-gallery__noimg">
                        <x-site.illust :name="CarText::bodyIllust($car->body_type)" class="p-detail-gallery__noimg-illust" />
                        <span class="p-detail-gallery__noimg-text"><x-site.icon name="camera" />写真準備中</span>
                        @if ($isPick)
                            <p class="c-ribbon p-detail-gallery__ribbon"><x-site.icon name="star" />店長おすすめ</p>
                        @endif
                    </div>
                @endif
            </div>

            {{-- 概要：タグ → 車名 → グレード → 在庫番号 → 価格 → 重要4項目 → 問い合わせ → お気に入り・比較 --}}
            <div class="p-detail-summary">
                <div class="p-detail-heading">
                    <div class="c-tags">
                        @foreach ($tags as $tag)
                            <span class="c-tag c-tag--{{ $tag['variant'] }}">{{ $tag['label'] }}</span>
                        @endforeach
                        @if (filled($car->body_type))
                            <span class="c-tag c-tag--red">{{ CarText::bodyType($car->body_type) }}</span>
                        @endif
                        @if (filled($car->fuel_type))
                            <span class="c-tag c-tag--black">{{ CarText::fuel($car->fuel_type) }}</span>
                        @endif
                    </div>
                    <h1 class="c-page-title">{{ $name }}</h1>
                    @if (filled($car->grade))
                        <p class="p-detail-grade">{{ $car->grade }}</p>
                    @endif
                    <p class="p-detail-stock">在庫番号 <strong class="p-detail-stock__no">{{ $car->stock_no }}</strong><span class="u-nowrap">（お電話の際にお伝えください）</span></p>
                </div>

                @if ($car->status === 'reserved')
                    <div class="c-alert c-alert--warn">
                        <x-site.icon name="alert" />
                        <div class="c-alert__body"><p class="c-alert__title">ただいま商談中です</p><p>商談の状況はお問い合わせください。</p></div>
                    </div>
                @elseif ($car->status === 'sold')
                    <div class="c-alert c-alert--warn">
                        <x-site.icon name="alert" />
                        <div class="c-alert__body"><p class="c-alert__title">この車は売約済みです</p><p>ほかの車は、在庫一覧からご覧ください。</p></div>
                    </div>
                @endif

                <div class="p-detail-price">
                    <x-site.price :car="$car" size="detail" />
                    @if ($showBreakdown)
                        <a class="c-more p-detail-price__more" href="#breakdown">支払総額に含まれる費用を見る<x-site.icon name="chevron-right" /></a>
                    @endif
                </div>

                {{-- 価格の近くに必ず出す4項目（修復歴・車検・保証・定期点検整備） --}}
                <div class="p-detail-keyfacts-wrap">
                    <p class="p-detail-keyfacts__cap"><x-site.icon name="check" />ご購入の前に確かめたい4項目</p>
                    <dl class="p-detail-keyfacts">
                        @foreach ($keyfacts as $fact)
                            <div class="p-detail-keyfacts__item p-detail-keyfacts__item--{{ $fact['tone'] }}">
                                <dt class="p-detail-keyfacts__label"><span class="p-detail-keyfacts__ic" aria-hidden="true"><x-site.icon :name="$fact['icon']" /></span>{{ $fact['label'] }}</dt>
                                <dd class="p-detail-keyfacts__value p-detail-keyfacts__value--{{ $fact['tone'] }}{{ $fact['value'] === CarText::UNKNOWN || mb_strlen($fact['value']) > 6 ? ' p-detail-keyfacts__value--long' : '' }}">
                                    @if ($fact['tone'] === 'ok')<x-site.icon name="check" class="p-detail-keyfacts__mark" />@endif{{ $nowrapParts($fact['value']) }}
                                    @if (filled($fact['sub']))
                                        <span class="p-detail-keyfacts__sub">{{ $fact['sub'] }}</span>
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                    <p class="p-detail-note">{{ CarText::REPAIR_DEFINITION }}定期点検整備（法定整備）とは、法律で決められた項目の点検・整備のことです。</p>
                </div>

                {{-- お問い合わせ（在庫確認・見積もり／見学・試乗の予約／電話） --}}
                <div class="p-detail-actions">
                    <x-site.btn2 :href="$estimateUrl" size="xl" block icon="mail"
                                 small="お問い合わせは無料です" :big="$car->price_negotiable ? '価格を問い合わせる' : 'この車の在庫確認・見積もり'" />
                    <div class="p-detail-actions__row">
                        <x-site.btn2 :href="$visitUrl" variant="yellow" block icon="calendar"
                                     small="ご希望の日時をお知らせください" big="見学・試乗を予約する" />
                        <x-site.btn2 :href="config('shop.tel_href')" variant="black" block num icon="phone"
                                     :small="'電話で問い合わせる（'.BusinessHours::hoursLabel().'）'" :big="$tel"
                                     :aria-label="'電話をかける '.$tel" />
                    </div>
                    @if ($lineUrl)
                        <a class="c-btn c-btn--line c-btn--lg c-btn--block" href="{{ $lineUrl }}" target="_blank" rel="noopener">
                            <x-site.icon name="line" />LINEで相談<span class="u-visually-hidden">（新しいタブで開きます）</span>
                        </a>
                    @endif
                    <div class="p-detail-actions__info">
                        <p class="p-detail-actions__tel-note"><x-site.icon name="phone" /><span>お電話では<span class="u-nowrap">「在庫番号 <b class="u-num">{{ $car->stock_no }}</b>」</span><span class="u-nowrap">とお伝えください</span></span></p>
                        <div class="p-detail-actions__status">
                            <x-site.open-status />
                            <span class="p-detail-actions__closed">定休日 {{ config('shop.closed_label') }}</span>
                        </div>
                    </div>
                </div>

                {{-- お気に入り・比較（$store は x-site.scripts） --}}
                <div class="p-detail-toggles-wrap">
                    <div class="p-detail-toggles" x-data="{ id: {{ (int) $car->id }}, name: @js($name) }">
                        <button type="button" class="c-btn c-btn--ghost c-btn--block" aria-pressed="false"
                                :aria-pressed="$store.favorites.has(id) ? 'true' : 'false'" x-on:click="$store.favorites.toggle(id)">
                            <x-site.icon name="heart" x-show="! $store.favorites.has(id)" />
                            <x-site.icon name="heart-fill" x-show="$store.favorites.has(id)" x-cloak />
                            <span x-text="$store.favorites.has(id) ? 'お気に入り済み' : 'お気に入り'">お気に入り</span>
                        </button>
                        <button type="button" class="c-btn c-btn--ghost c-btn--block" aria-pressed="false"
                                :aria-pressed="$store.compare.has(id) ? 'true' : 'false'" x-on:click="$store.compare.toggle(id, name)">
                            <x-site.icon name="compare" x-show="! $store.compare.has(id)" />
                            <x-site.icon name="check" x-show="$store.compare.has(id)" x-cloak />
                            <span x-text="$store.compare.has(id) ? '比較中' : '比較に追加'">比較に追加</span>
                        </button>
                    </div>
                    <p class="p-detail-note">お気に入りと比較は、この端末に保存されます。</p>
                </div>
            </div>

            {{-- 車両の状態・仕様（アイコンの丸つきのタイル。PC は写真の下の列、スマホは問い合わせの後） --}}
            <section class="p-detail-specs" id="spec" aria-labelledby="detail-spec-title">
                <h2 id="detail-spec-title" class="c-subhead p-detail-specs__title">車両の状態・仕様</h2>
                <dl class="p-detail-spec">
                    @foreach ($specRows as $key => $row)
                        @php $tone = $row['tone'] ?? null; @endphp
                        <div class="p-detail-spec__row p-detail-spec__row--{{ $key }}{{ isset($row['size']) ? ' p-detail-spec__row--'.$row['size'] : '' }}{{ filled($row['illust'] ?? null) ? ' p-detail-spec__row--illust' : '' }}">
                            <dt class="p-detail-spec__label">
                                @if (filled($row['illust'] ?? null))
                                    <x-site.illust :name="$row['illust']" class="p-detail-spec__illust" />
                                @elseif (filled($row['swatch'] ?? null))
                                    <span class="p-detail-spec__ic p-detail-spec__ic--swatch p-detail-swatch--{{ $row['swatch'] }}" aria-hidden="true"></span>
                                @else
                                    <span class="p-detail-spec__ic{{ $tone ? ' p-detail-spec__ic--'.$tone : '' }}" aria-hidden="true"><x-site.icon :name="$row['icon']" /></span>
                                @endif
                                {{ $row['label'] }}
                            </dt>
                            <dd class="p-detail-spec__value{{ $tone ? ' p-detail-spec__value--'.$tone : '' }}">
                                @if ($tone === 'ok' && $key === 'repair')<x-site.icon name="check" class="p-detail-spec__mark" />@endif{{ $row['value'] === CarText::UNKNOWN ? $nowrapParts(CarText::UNKNOWN) : $row['value'] }}
                                @if (filled($row['note'] ?? null))
                                    <span class="p-detail-spec__note">{{ $row['note'] }}</span>
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        </div>
    </div>
</div>

{{-- 主な装備（アイコン）・装備一覧（装備している項目だけ。開閉式）・この車について（スタッフより） --}}
@if ($hasEquipment || $hasComment)
    <div class="l-section{{ $bg('equip') }}">
        <div class="l-container p-detail-extra">
            @if ($hasEquipment)
                <section class="p-detail-highlights" id="equipment" aria-labelledby="detail-equipment-title">
                    <x-site.section-head id="detail-equipment-title" title="主な装備" en="EQUIPMENT" lead="この車に付いている装備だけを載せています。">
                        <p class="c-count-pill">全<b>{{ $equipmentCount }}</b>項目</p>
                    </x-site.section-head>
                    <x-site.equip-list :items="$highlights" />
                    @if ($showEquipmentList)
                        <details class="p-detail-equip__details" id="detail-equipment-list">
                            <summary class="p-detail-equip__summary">
                                <span class="p-detail-equip__summary-ic" aria-hidden="true"><x-site.icon name="eq-check" /></span>
                                <span class="p-detail-equip__summary-text">装備をすべて見る（{{ $equipmentCount }}項目）</span>
                                <x-site.icon name="chevron-down" class="p-detail-equip__chev" />
                            </summary>
                            <div class="p-detail-equip__body">
                                @foreach ($equipment as $category => $items)
                                    <div class="p-detail-equip__group">
                                        <h3 class="c-subhead p-detail-equip__cat">{{ $category }}<span class="p-detail-equip__cat-n">{{ count($items) }}項目</span></h3>
                                        <ul class="p-detail-equip__list" role="list">
                                            @foreach ($items as $item)
                                                <li class="p-detail-equip__item"><span class="p-detail-equip__ic" aria-hidden="true"><x-site.icon :name="CarText::equipmentIcon($item)" /></span><span>{{ $item }}</span></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                                <p class="p-detail-note">ここにない装備については、お問い合わせください。</p>
                            </div>
                        </details>
                    @endif
                </section>
            @endif

            @if ($hasComment)
                <section class="p-detail-comment" id="comment" aria-labelledby="detail-comment-title">
                    <x-site.section-head id="detail-comment-title" :title="$commentTitle" en="COMMENT" />
                    <div class="p-detail-comment__body">
                        <span class="p-detail-comment__ic" aria-hidden="true"><x-site.icon name="user" /></span>
                        <p class="p-detail-comment__text">{!! nl2br(e($car->description)) !!}</p>
                    </div>
                </section>
            @endif
        </div>
    </div>
@endif

{{-- 支払総額の内訳（表・含まれるもの・含まれないもの） --}}
@if ($showBreakdown)
    <section class="l-section{{ $bg('breakdown') }}" id="breakdown" aria-labelledby="detail-breakdown-title">
        <div class="l-container">
            <x-site.section-head id="detail-breakdown-title" title="支払総額の内訳" en="PRICE"
                :lead="'支払総額は'.config('shop.price_condition').'の価格です。'" />
            <div class="p-detail-breakdown__grid">
                <div class="c-table-wrap p-detail-breakdown__table-wrap">
                    <table class="c-table p-detail-breakdown__table">
                        <caption class="u-visually-hidden">支払総額の内訳</caption>
                        <tbody>
                            <tr>
                                <th scope="row" class="c-table__th p-detail-breakdown__th"><span class="c-tag c-tag--total">支払総額（税込）</span></th>
                                <td class="c-table__td p-detail-breakdown__td"><strong class="p-detail-breakdown__num p-detail-breakdown__num--total">{{ CarText::price($price) }}</strong><span class="p-detail-breakdown__yen">{{ CarText::yen($price) }}</span></td>
                            </tr>
                            <tr>
                                <th scope="row" class="c-table__th p-detail-breakdown__th"><span class="c-tag c-tag--body">車両本体価格</span></th>
                                <td class="c-table__td p-detail-breakdown__td"><span class="p-detail-breakdown__num">{{ CarText::price((int) $car->base_price) }}</span><span class="p-detail-breakdown__yen">{{ CarText::yen((int) $car->base_price) }}</span></td>
                            </tr>
                            <tr>
                                <th scope="row" class="c-table__th p-detail-breakdown__th"><span class="c-tag c-tag--fee">諸費用</span></th>
                                <td class="c-table__td p-detail-breakdown__td"><span class="p-detail-breakdown__num">{{ CarText::price($fees) }}</span><span class="p-detail-breakdown__yen">{{ CarText::yen($fees) }}</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="p-detail-costs p-detail-costs--in">
                    <h3 class="p-detail-costs__title"><span class="p-detail-costs__badge" aria-hidden="true"><x-site.icon name="check" /></span>支払総額に含まれるもの</h3>
                    <ul class="p-detail-costs__list" role="list">
                        <li class="p-detail-costs__item"><span class="p-detail-costs__ic" aria-hidden="true"><x-site.icon name="yen" /></span><span>税金</span></li>
                        <li class="p-detail-costs__item"><span class="p-detail-costs__ic" aria-hidden="true"><x-site.icon name="shield" /></span><span>自賠責保険料<small>（法律で加入が義務づけられている保険）</small></span></li>
                        <li class="p-detail-costs__item"><span class="p-detail-costs__ic" aria-hidden="true"><x-site.icon name="file" /></span><span>登録などの手続き費用<small>（名義変更などの手続き）</small></span></li>
                    </ul>
                </div>

                <div class="p-detail-costs p-detail-costs--out">
                    <h3 class="p-detail-costs__title"><span class="p-detail-costs__badge" aria-hidden="true"><x-site.icon name="alert" /></span>支払総額に含まれないもの</h3>
                    <ul class="p-detail-costs__list" role="list">
                        <li class="p-detail-costs__item"><span class="p-detail-costs__ic" aria-hidden="true"><x-site.icon name="shield" /></span><span>任意保険の保険料</span></li>
                        <li class="p-detail-costs__item"><span class="p-detail-costs__ic" aria-hidden="true"><x-site.icon name="tag" /></span><span>希望ナンバーの費用</span></li>
                        <li class="p-detail-costs__item"><span class="p-detail-costs__ic" aria-hidden="true"><x-site.icon name="map-pin" /></span><span>県外で登録する場合の費用</span></li>
                        <li class="p-detail-costs__item"><span class="p-detail-costs__ic" aria-hidden="true"><x-site.icon name="route" /></span><span>ご自宅への納車の費用</span></li>
                        <li class="p-detail-costs__item"><span class="p-detail-costs__ic" aria-hidden="true"><x-site.icon name="wrench" /></span><span>ご希望のオプションの費用<small>（追加の装備など）</small></span></li>
                    </ul>
                    <p class="p-detail-note">県外での登録やご自宅への納車をご希望の方は、お見積もりの際にお知らせください。</p>
                </div>
            </div>
        </div>
    </section>
@endif

{{-- この車のお支払い例（支払総額を初期値にしたシミュレーター。計算例） --}}
@if ($loanPrice !== null)
    <section class="l-section{{ $bg('loan') }}" id="loan" aria-labelledby="detail-loan-title">
        <div class="l-container">
            <x-site.section-head id="detail-loan-title" :title="$loanTitle" en="LOAN"
                :lead="'この車の支払総額 '.CarText::price($price).($loanRounded ? '（1万円未満は四捨五入）' : '').'で計算しています。頭金・回数・金利を動かすと、月々の目安がわかります。'" />
            <x-site.loan-sim :price="$loanPrice" :cars="[$car]" cars-label="この車の支払総額に戻す：" id-prefix="detail-loan"
                             :min="min(30, $loanPrice)" :max="max(500, (int) ceil($loanPrice / 50) * 50)" />
        </div>
    </section>
@endif

{{-- ご購入の流れ（短縮版） --}}
<section class="l-section{{ $bg('flow') }}" id="flow" aria-labelledby="detail-flow-title">
    <div class="l-container">
        <x-site.section-head id="detail-flow-title" title="ご購入の流れ" en="FLOW" lead="お問い合わせから納車まで、4つのステップです。">
            <a class="c-more" href="{{ route('home') }}#flow">くわしく見る<x-site.icon name="chevron-right" /></a>
        </x-site.section-head>
        <x-site.flow :steps="[
            ['title' => '在庫確認・お問い合わせ', 'text' => 'お電話またはフォームで、在庫の確認・お見積もりを受け付けています。', 'illust' => 'f-search'],
            ['title' => 'ご来店・現車確認', 'text' => '実車をご覧いただきながら、車の状態をご説明します。試乗のご希望もお伝えください。', 'illust' => 'f-store'],
            ['title' => 'ご契約・お手続き', 'text' => 'お支払い方法（現金・ローン）を決めて、必要な書類をご案内します。', 'illust' => 'f-contract'],
            ['title' => 'ご納車', 'text' => '準備が整いしだい、店頭でお渡しします。ご自宅への納車はご相談ください（別途費用）。', 'illust' => 'f-key'],
        ]" />
        <div class="c-cta-row p-detail-flow__cta">
            <x-site.btn2 :href="$visitUrl" small="この車を見に行く" big="見学・試乗を予約する" />
            <x-site.btn2 :href="$loanUrl" variant="black" small="月々のお支払いの相談も" big="ローンのご相談" />
        </div>
    </div>
</section>

{{-- この車が見られる店舗 --}}
<section class="l-section{{ $bg('shop') }}" id="shop" aria-labelledby="detail-shop-title">
    <div class="l-container">
        <x-site.section-head id="detail-shop-title" :title="$atShop ? 'この車が見られる店舗' : '店舗のご案内'" en="SHOP" />
        @unless ($atShop)
            <div class="c-alert c-alert--warn p-detail-shop__alert">
                <x-site.icon name="alert" />
                <div class="c-alert__body">
                    <p class="c-alert__title">この車は店舗に展示していません</p>
                    <p>{{ CarText::location($car) }}。見学・試乗をご希望の方は、お電話またはフォームでご予約ください。</p>
                </div>
            </div>
        @endunless
        <div class="p-detail-shop">
            <figure class="p-detail-shop__photo">
                <picture>
                    <source type="image/webp" srcset="{{ asset('images/store-hero-bg.png.webp') }}">
                    <img class="p-detail-shop__img" src="{{ asset('images/store-hero-bg.png') }}" alt="{{ $shopName }}の店舗外観。看板の前に展示車が並んでいる" width="1584" height="672" loading="lazy">
                </picture>
                <figcaption class="c-slant c-slant--black p-detail-shop__caption"><x-site.icon name="store" />この看板が目印です</figcaption>
            </figure>
            <div class="c-card c-card--accent p-detail-shop__card">
                <h3 class="p-detail-shop__name">{{ $shopName }}</h3>
                <ul class="p-detail-shop__list" role="list">
                    <li class="p-detail-shop__row">
                        <span class="p-detail-shop__ic" aria-hidden="true"><x-site.icon name="map-pin" /></span>
                        <div class="p-detail-shop__body">
                            <p class="p-detail-shop__label">住所</p>
                            <p><x-site.address postal /></p>
                            <a class="c-btn c-btn--secondary c-btn--sm p-detail-shop__route" href="{{ config('shop.directions_url') }}" target="_blank" rel="noopener"><x-site.icon name="route" />地図アプリで道順を見る<span class="u-visually-hidden">（新しいタブで開きます）</span></a>
                        </div>
                    </li>
                    <li class="p-detail-shop__row">
                        <span class="p-detail-shop__ic" aria-hidden="true"><x-site.icon name="clock" /></span>
                        <div class="p-detail-shop__body">
                            <p class="p-detail-shop__label">営業時間・定休日</p>
                            <p>{{ BusinessHours::summaryHtml() }}</p>
                            <x-site.open-status />
                        </div>
                    </li>
                    <li class="p-detail-shop__row">
                        <span class="p-detail-shop__ic" aria-hidden="true"><x-site.icon name="phone" /></span>
                        <div class="p-detail-shop__body">
                            <p class="p-detail-shop__label">お電話</p>
                            <a class="p-detail-shop__tel" href="{{ config('shop.tel_href') }}" aria-label="電話をかける {{ $tel }}">{{ $tel }}</a>
                        </div>
                    </li>
                    <li class="p-detail-shop__row">
                        <span class="p-detail-shop__ic" aria-hidden="true"><x-site.icon name="parking" /></span>
                        <div class="p-detail-shop__body">
                            <p class="p-detail-shop__label">駐車場</p>
                            <p>{{ config('shop.parking') }}</p>
                        </div>
                    </li>
                </ul>
                <a class="c-more" href="{{ route('store') }}">店舗案内・アクセスを見る<x-site.icon name="chevron-right" /></a>
            </div>
        </div>
    </div>
</section>

{{-- 同じメーカーのほかの在庫（0台なら「似た条件の車を探す」） --}}
<section class="l-section{{ $bg('related') }}" id="related" aria-labelledby="detail-related-title">
    <div class="l-container">
        @if ($relatedCars->isNotEmpty())
            <x-site.section-head id="detail-related-title" :title="$car->make.'のほかの在庫'" en="STOCK">
                <p class="c-count-pill"><b>{{ $relatedCars->count() }}</b>台</p>
                <a class="c-more" href="{{ route('cars.index', ['make' => $car->make]) }}">{{ $car->make }}の在庫をすべて見る<x-site.icon name="chevron-right" /></a>
            </x-site.section-head>
            <ul class="l-grid l-grid--4" role="list">
                @foreach ($relatedCars as $related)
                    <li><x-site.car-card :car="$related" :actions="false" :heading-level="3" /></li>
                @endforeach
            </ul>
        @else
            <x-site.section-head id="detail-related-title" title="似た条件の車を探す" en="SEARCH"
                :lead="$car->make.'のほかの在庫は、いまは掲載していません。ボディタイプや価格が近い車もご覧ください。'" />
            @if ($similarCars->isNotEmpty())
                <h3 class="c-subhead">ボディタイプや価格が近い在庫<span class="p-detail-related__n">（{{ $similarCars->count() }}台）</span></h3>
                <ul class="l-grid l-grid--4 p-detail-related__cars" role="list">
                    @foreach ($similarCars as $similarCar)
                        <li><x-site.car-card :car="$similarCar" :actions="false" :heading-level="4" /></li>
                    @endforeach
                </ul>
                <h3 class="c-subhead">条件を広げて探す</h3>
            @endif
            <ul class="c-link-cards c-link-cards--3" role="list">
                @foreach ($similar as $item)
                    <li><x-site.link-card :href="$item['href']" :icon="$item['icon']" :tone="$item['tone']" :title="$item['title']" :sub="$item['sub']" :count="$item['count']" /></li>
                @endforeach
            </ul>
            <p class="p-detail-related__ask">お探しの車が見つからないときは、<a class="c-link" href="{{ route('contact.index', ['purpose' => 'search']) }}">探してほしい車をお知らせください</a>。</p>
        @endif
    </div>
</section>

{{-- この車を家族に送る（LINE の共有・URL のコピー。店への連絡ではない） --}}
<section class="l-section{{ $bg('related') }} p-detail-share" id="share" aria-labelledby="detail-share-title" x-data="detailShare(@js($carUrl))">
    <div class="l-container">
        <div class="p-detail-share__box">
            <div class="p-detail-share__text">
                <h2 id="detail-share-title" class="p-detail-share__title"><x-site.icon name="family" />この車を家族に送る</h2>
                <p class="p-detail-share__lead">ご家族と相談するときなどに、この車のページをLINEで送ったり、URLをコピーしたりできます。</p>
            </div>
            <div class="p-detail-share__actions">
                <a class="c-btn c-btn--secondary" href="https://social-plugins.line.me/lineit/share?url={{ rawurlencode($carUrl) }}" target="_blank" rel="noopener">
                    <x-site.icon name="line" />この車を家族にLINEで送る<span class="u-visually-hidden">（新しいタブで開きます）</span>
                </a>
                <button type="button" class="c-btn c-btn--secondary" x-on:click="copy()"><x-site.icon name="file" />URLをコピーする</button>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    // 車両詳細の写真ギャラリー（前後・サムネイル・全画面表示・スワイプ）
    window.Alpine.data('detailGallery', (photos) => {
        let opener = null; // DOM 要素はリアクティブなデータに入れない

        return {
            photos: Array.isArray(photos) ? photos : [],
            index: 0,
            isOpen: false,
            startX: null,
            startY: null,
            get current() {
                return this.photos[this.index] || { src: '', alt: '' };
            },
            show(i) {
                const n = this.photos.length;
                if (n > 0) this.index = ((i % n) + n) % n;
            },
            next() {
                this.show(this.index + 1);
            },
            prev() {
                this.show(this.index - 1);
            },
            open(from = null) {
                if (this.photos.length === 0) return;
                opener = from || document.activeElement;
                this.isOpen = true;
                document.body.classList.add('is-gallery-open');
                this.$nextTick(() => this.$refs.close && this.$refs.close.focus());
            },
            close() {
                if (!this.isOpen) return;
                this.isOpen = false;
                document.body.classList.remove('is-gallery-open');
                const target = opener;
                opener = null;
                if (target && typeof target.focus === 'function') target.focus();
            },
            touchStart(event) {
                const t = event.changedTouches[0];
                this.startX = t.clientX;
                this.startY = t.clientY;
            },
            touchEnd(event) {
                if (this.startX === null) return;
                const t = event.changedTouches[0];
                const dx = t.clientX - this.startX;
                const dy = t.clientY - this.startY;
                this.startX = null;
                if (this.photos.length > 1 && Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) {
                    if (dx < 0) this.next(); else this.prev();
                }
            },
            trap(event) {
                const buttons = Array.from(this.$refs.viewer.querySelectorAll('button')).filter((b) => b.offsetParent !== null);
                if (buttons.length === 0) return;
                const first = buttons[0];
                const last = buttons[buttons.length - 1];
                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            },
        };
    });

    // 「この車を家族に送る」の URL コピー
    window.Alpine.data('detailShare', (url) => ({
        copy() {
            const toast = window.Alpine.store('toast');
            const failed = () => toast.show('コピーできませんでした。画面上部のアドレス欄のURLをお使いください。');
            if (!navigator.clipboard || !window.isSecureContext) {
                failed();
                return;
            }
            navigator.clipboard.writeText(url).then(() => toast.show('この車のページのURLをコピーしました'), failed);
        },
    }));
});
</script>
@endpush
