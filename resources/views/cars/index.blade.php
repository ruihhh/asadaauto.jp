@extends('layouts.site')

@php
    // $isLatest はコントローラーから渡される（sort=latest が URL に明示されているとき true）
    $hasFilter = collect($filters)->filter(fn($v) => $v !== '' && $v !== null)->except('sort')->isNotEmpty();
    $makeLabel = $filters['make'] ? $filters['make'] . 'の' : '';
    $bodyLabel = $filters['body_type'] ? $filters['body_type'] . 'の' : '';
    $allCars   = $cars->getCollection(); // ページネーター内コレクション（featured用）
@endphp

@if($isLatest)
    @section('title', '新着中古車情報｜尼崎・兵庫の最新入荷（' . $cars->total() . '台）')
    @section('meta_description', '【新着入荷情報】尼崎市アサダオートサポートの最新中古車入荷情報。' . $cars->total() . '台掲載中。人気車は数日で売約済みになります。気になる車はお早めに。')
@else
    @section('title', $hasFilter
        ? ($makeLabel . $bodyLabel . '中古車在庫一覧｜尼崎・兵庫')
        : '尼崎の中古車在庫一覧｜兵庫県（' . $cars->total() . '台掲載中）')
    @section('meta_description', $hasFilter
        ? ('【尼崎・兵庫の中古車】' . $makeLabel . $bodyLabel . '中古車 ' . $cars->total() . '台掲載中。兵庫県尼崎市のアサダオートサポート。')
        : '【尼崎の中古車】兵庫県尼崎市のアサダオートサポートが' . $cars->total() . '台掲載中。軽自動車・SUV・ミニバンなどメーカー・価格帯・走行距離で絞り込み検索。')
@endif

@if($hasFilter || $cars->currentPage() > 1)
@section('meta_robots', 'noindex, follow')
@endif
@section('canonical', $isLatest ? route('cars.index', ['sort' => 'latest']) : route('cars.index'))

@section('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        {"@type":"ListItem","position":1,"name":"ホーム","item":"{{ url('/') }}"},
        @if($isLatest)
        {"@type":"ListItem","position":2,"name":"新着車両","item":"{{ route('cars.index', ['sort' => 'latest']) }}"}
        @else
        {"@type":"ListItem","position":2,"name":"中古車在庫一覧","item":"{{ route('cars.index') }}"}
        @endif
    ]
}
</script>
@if(!$hasFilter && !$isLatest)
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "ItemList",
    "name": "尼崎・兵庫県の中古車在庫一覧",
    "description": "兵庫県尼崎市のアサダオートサポートが提供する中古車在庫一覧",
    "url": "{{ route('cars.index') }}",
    "numberOfItems": {{ $cars->total() }},
    "itemListElement": [
        @foreach($cars->take(5) as $i => $car)
        {
            "@type": "ListItem",
            "position": {{ $i + 1 }},
            "url": "{{ route('cars.show', $car) }}",
            "name": "{{ $car->make }} {{ $car->model }} {{ $car->model_year }}年式"
        }@if(!$loop->last),@endif
        @endforeach
    ]
}
</script>
@endif
@endsection

@section('content')

{{-- ════════════════════════════════════════════════════════════ --}}
{{--  ヒーローバナー                                               --}}
{{-- ════════════════════════════════════════════════════════════ --}}
<section class="hero">
    <div class="container">
        <div class="hero-inner">

            @if($isLatest)
            {{-- ── 新着車両 ヒーロー ─────────────────────────────── --}}
            <p class="hero-eyebrow">🆕 最新入荷情報</p>
            <h1 class="hero-title">新着中古車<span>入荷情報</span></h1>
            <p class="hero-subtitle">気になる車はお早めに ― 人気車は数日で売約済みになります</p>
            <div class="hero-stats">
                <div class="hero-stat">
                    <span class="hero-stat-val">{{ number_format($cars->total()) }}</span>
                    <span class="hero-stat-lbl">台 掲載中</span>
                </div>
                <div class="hero-stat-sep"></div>
                <div class="hero-stat">
                    <span class="hero-stat-val">随時</span>
                    <span class="hero-stat-lbl">入荷更新</span>
                </div>
                <div class="hero-stat-sep"></div>
                <div class="hero-stat">
                    <span class="hero-stat-val">即日</span>
                    <span class="hero-stat-lbl">来店可</span>
                </div>
            </div>

            @else
            {{-- ── 在庫一覧 ヒーロー ─────────────────────────────── --}}
            <p class="hero-eyebrow">兵庫県尼崎市の中古車販売店</p>
            <h1 class="hero-title">
                @if(!$hasFilter)
                    尼崎・兵庫の<span>中古車</span>在庫一覧
                @else
                    {{ $makeLabel }}{{ $bodyLabel }}<span>中古車</span>在庫一覧
                @endif
            </h1>
            <p class="hero-subtitle">
                @if(!$hasFilter)
                    兵庫県尼崎市アサダオートサポート ― 全{{ number_format($cars->total()) }}台掲載中
                @else
                    絞り込み結果 {{ number_format($cars->total()) }}台
                @endif
            </p>

            <div class="hero-stats">
                <div class="hero-stat">
                    <span class="hero-stat-val">{{ number_format($cars->total()) }}</span>
                    <span class="hero-stat-lbl">台 在庫</span>
                </div>
                <div class="hero-stat-sep"></div>
                <div class="hero-stat">
                    <span class="hero-stat-val">{{ count($makes) }}</span>
                    <span class="hero-stat-lbl">メーカー</span>
                </div>
                <div class="hero-stat-sep"></div>
                <div class="hero-stat">
                    <span class="hero-stat-val">0円</span>
                    <span class="hero-stat-lbl">仲介手数料</span>
                </div>
            </div>

            <div class="hero-search">
                <p class="hero-search-title">クルマを探す</p>
                <form method="get" action="{{ route('cars.index') }}" class="hero-search-form">
                    <div class="hero-search-field">
                        <label>メーカー</label>
                        <select name="make">
                            <option value="">すべてのメーカー</option>
                            @foreach ($makes as $make)
                                <option value="{{ $make }}" @selected($filters['make'] === $make)>{{ $make }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="hero-search-field">
                        <label>ボディタイプ</label>
                        <select name="body_type">
                            <option value="">すべてのタイプ</option>
                            @foreach ($bodyTypes as $bodyType)
                                <option value="{{ $bodyType }}" @selected($filters['body_type'] === $bodyType)>{{ $bodyType }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="hero-search-field">
                        <label>価格（下限）</label>
                        <input type="number" name="min_price" value="{{ $filters['min_price'] }}" placeholder="円〜" min="0">
                    </div>
                    <div class="hero-search-field">
                        <label>価格（上限）</label>
                        <input type="number" name="max_price" value="{{ $filters['max_price'] }}" placeholder="〜円" min="0">
                    </div>
                    <button type="submit" class="hero-search-btn">検索する</button>
                </form>
            </div>
            @endif

        </div>
    </div>
</section>


{{-- ════════════════════════════════════════════════════════════ --}}
{{--  ── 新着車両 専用セクション ──────────────────────────────── --}}
{{-- ════════════════════════════════════════════════════════════ --}}
@if($isLatest)

{{-- 緊急性バナー --}}
<div class="new-urgency-bar">
    <div class="container">
        <div class="new-urgency-inner">
            <span class="new-urgency-icon">⚡</span>
            <p class="new-urgency-text">
                人気車両は公開後 <strong>数日以内</strong> に売約済みになることがあります。
                気になる車はお早めにお問い合わせください。
            </p>
            <a href="{{ route('contact.index') }}" class="new-urgency-btn">今すぐ問い合わせ</a>
        </div>
    </div>
</div>

{{-- 注目の新着 フィーチャーカード --}}
@if($allCars->isNotEmpty())
<section class="new-featured-section">
    <div class="container">
        <div class="new-featured-header">
            <h2 class="new-featured-title">
                <span class="new-featured-badge">NEW</span>
                注目の新着車両
            </h2>
            <p class="new-featured-sub">最新入荷車両をピックアップ。詳細は各車両ページでご確認ください。</p>
        </div>
        <div class="new-featured-grid">
            @foreach($allCars->take(3) as $car)
            <article class="new-featured-card">
                <a href="{{ route('cars.show', $car) }}" class="new-featured-card-link">
                    <div class="new-featured-img-wrap">
                        @if($car->image_path)
                            <img src="{{ '/images/' . $car->image_path }}"
                                 alt="{{ $car->make }} {{ $car->model }}"
                                 loading="lazy">
                        @else
                            <div class="car-no-image">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M5 17H3a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h2"/><path d="M21 17h-2"/><path d="M13 3H5l-2 4v10h18V7l-2-4h-6z"/><circle cx="7.5" cy="14.5" r="1.5"/><circle cx="16.5" cy="14.5" r="1.5"/></svg>
                                <span>No Image</span>
                            </div>
                        @endif
                        <span class="new-fc-badge-new">NEW</span>
                        @if($car->featured)
                            <span class="new-fc-badge-pick">注目</span>
                        @endif
                        @if($car->status !== 'available')
                            <span class="new-fc-badge-sold">{{ match($car->status) { 'reserved' => '商談中', 'sold' => '売約済', default => $car->status } }}</span>
                        @endif
                    </div>
                    <div class="new-featured-body">
                        <p class="new-fc-no">No. {{ $car->stock_no }}</p>
                        <h3 class="new-fc-name">{{ $car->make }} {{ $car->model }}</h3>
                        @if($car->grade && $car->grade !== '—')
                            <p class="new-fc-grade">{{ $car->grade }}</p>
                        @endif
                        <div class="new-fc-specs">
                            <span>{{ $car->model_year }}年式</span>
                            <span>{{ number_format($car->mileage) }} km</span>
                            <span>{{ $car->body_type }}</span>
                            <span>{{ $car->transmission }}</span>
                        </div>
                        <div class="new-fc-price">
                            <span class="new-fc-price-label">総額</span>
                            <span class="new-fc-price-val">{{ number_format($car->price) }}</span>
                            <span class="new-fc-price-unit">円</span>
                        </div>
                        <span class="new-fc-cta">詳しく見る →</span>
                    </div>
                </a>
            </article>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- 入荷サイクル情報バー --}}
<div class="new-cycle-bar">
    <div class="container">
        <div class="new-cycle-inner">
            <div class="new-cycle-item">
                <span class="new-cycle-icon">🚗</span>
                <div>
                    <p class="new-cycle-ttl">随時入荷</p>
                    <p class="new-cycle-txt">新しい車両は随時入荷しています</p>
                </div>
            </div>
            <div class="new-cycle-sep"></div>
            <div class="new-cycle-item">
                <span class="new-cycle-icon">📸</span>
                <div>
                    <p class="new-cycle-ttl">入荷後即掲載</p>
                    <p class="new-cycle-txt">入荷後すぐにサイトを更新</p>
                </div>
            </div>
            <div class="new-cycle-sep"></div>
            <div class="new-cycle-item">
                <span class="new-cycle-icon">⚡</span>
                <div>
                    <p class="new-cycle-ttl">早い者勝ち</p>
                    <p class="new-cycle-txt">人気車は早期売約済みに</p>
                </div>
            </div>
            <div class="new-cycle-sep"></div>
            <div class="new-cycle-item">
                <span class="new-cycle-icon">📞</span>
                <div>
                    <p class="new-cycle-ttl">取り置き対応</p>
                    <p class="new-cycle-txt">お問い合わせで仮押さえ可</p>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- ════════════════════════════════════════════════════════════ --}}
{{--  ── 在庫一覧 専用セクション ──────────────────────────────── --}}
{{-- ════════════════════════════════════════════════════════════ --}}
@else

{{-- クイックフィルター チップス --}}
@if(!$hasFilter)
<div class="quick-filter-bar">
    <div class="container">
        <div class="quick-filter-inner">
            <div class="quick-filter-group">
                <span class="quick-filter-label">ボディタイプ</span>
                <div class="quick-filter-chips">
                    <a href="{{ route('cars.index', ['body_type' => 'SUV']) }}" class="quick-chip">🚙 SUV</a>
                    <a href="{{ route('cars.index', ['body_type' => 'ミニバン']) }}" class="quick-chip">🚐 ミニバン</a>
                    <a href="{{ route('cars.index', ['body_type' => 'セダン']) }}" class="quick-chip">🚗 セダン</a>
                    <a href="{{ route('cars.index', ['body_type' => 'ハッチバック']) }}" class="quick-chip">🚘 ハッチバック</a>
                    <a href="{{ route('cars.index', ['body_type' => '軽自動車']) }}" class="quick-chip">🔹 軽自動車</a>
                    <a href="{{ route('cars.index', ['body_type' => 'ワゴン']) }}" class="quick-chip">🚌 ワゴン</a>
                </div>
            </div>
            <div class="quick-filter-group">
                <span class="quick-filter-label">価格帯</span>
                <div class="quick-filter-chips">
                    <a href="{{ route('cars.index', ['max_price' => 1000000]) }}" class="quick-chip">〜100万円</a>
                    <a href="{{ route('cars.index', ['min_price' => 1000000, 'max_price' => 2000000]) }}" class="quick-chip">100〜200万円</a>
                    <a href="{{ route('cars.index', ['min_price' => 2000000, 'max_price' => 3000000]) }}" class="quick-chip">200〜300万円</a>
                    <a href="{{ route('cars.index', ['min_price' => 3000000]) }}" class="quick-chip">300万円〜</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

{{-- SEO イントロテキスト（フィルターなし時のみ） --}}
@if(!$hasFilter && $cars->currentPage() === 1)
<div class="container">
    <p class="inventory-intro-text">
        兵庫県尼崎市の中古車販売店「アサダオートサポート」の在庫一覧です。軽自動車・コンパクト・ミニバン・SUV・セダンなど、常時<strong>{{ number_format($cars->total()) }}台</strong>掲載中。メーカー・ボディタイプ・価格帯・走行距離で絞り込み検索が可能です。尼崎市・西宮市・伊丹市をはじめ、兵庫県・大阪府全域のお客様にご利用いただけます。
    </p>
</div>
@endif

@endif {{-- /isLatest --}}


{{-- ════════════════════════════════════════════════════════════ --}}
{{--  メインコンテンツ（共通）                                     --}}
{{-- ════════════════════════════════════════════════════════════ --}}
<div class="container" style="padding-top:28px;padding-bottom:40px;" x-data>

    {{-- 絞り込みパネル --}}
    <div class="filter-panel" x-data="{ open: false }">
        <button class="filter-panel-title" type="button" @click="open = !open">
            <span>詳細絞り込み</span>
            <svg class="filter-toggle-icon" :class="open && 'rotated'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        <form x-show="open"
              x-transition:enter="transition ease-out duration-200"
              x-transition:enter-start="opacity-0 -translate-y-2"
              x-transition:enter-end="opacity-100 translate-y-0"
              method="get" action="{{ route('cars.index') }}" class="filter-form"
              style="padding-top:16px;border-top:1px solid var(--line-light);margin-top:14px;">
            <label>
                キーワード
                <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="車種 / グレード / 管理番号">
            </label>
            <label>
                メーカー
                <select name="make">
                    <option value="">すべて</option>
                    @foreach ($makes as $make)
                        <option value="{{ $make }}" @selected($filters['make'] === $make)>{{ $make }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                ボディタイプ
                <select name="body_type">
                    <option value="">すべて</option>
                    @foreach ($bodyTypes as $bodyType)
                        <option value="{{ $bodyType }}" @selected($filters['body_type'] === $bodyType)>{{ $bodyType }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                最低価格(円)
                <input type="number" min="0" name="min_price" value="{{ $filters['min_price'] }}" placeholder="例: 500000">
            </label>
            <label>
                最高価格(円)
                <input type="number" min="0" name="max_price" value="{{ $filters['max_price'] }}" placeholder="例: 3000000">
            </label>
            <label>
                最大走行距離(km)
                <input type="number" min="0" name="max_mileage" value="{{ $filters['max_mileage'] }}" placeholder="例: 50000">
            </label>
            <label>
                並び順
                <select name="sort">
                    <option value="latest"      @selected($filters['sort'] === 'latest')>新着順</option>
                    <option value="price_asc"   @selected($filters['sort'] === 'price_asc')>価格が安い順</option>
                    <option value="price_desc"  @selected($filters['sort'] === 'price_desc')>価格が高い順</option>
                    <option value="mileage_asc" @selected($filters['sort'] === 'mileage_asc')>走行距離が少ない順</option>
                    <option value="year_desc"   @selected($filters['sort'] === 'year_desc')>年式が新しい順</option>
                </select>
            </label>
            <div class="filter-actions">
                <button type="submit">絞り込む</button>
                <a href="{{ $isLatest ? route('cars.index', ['sort' => 'latest']) : route('cars.index') }}">リセット</a>
            </div>
        </form>
    </div>

    {{-- 一覧ヘッダー --}}
    <div class="inventory-head">
        @if($isLatest)
            <h2>すべての新着車両</h2>
        @else
            <h2>在庫一覧</h2>
        @endif
        <p>{{ number_format($cars->total()) }} 台</p>
    </div>

    {{-- 車両グリッド --}}
    <section class="inventory-grid">
        @forelse ($cars as $car)
            <article class="car-card">
                <a href="{{ route('cars.show', $car) }}" class="car-card-link">
                    <div class="car-card-image">
                        @if($car->image_path)
                            <img src="{{ '/images/' . $car->image_path }}"
                                 alt="{{ $car->make }} {{ $car->model }}"
                                 loading="lazy">
                        @else
                            <div class="car-no-image">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M5 17H3a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h2"/><path d="M21 17h-2"/><path d="M13 3H5l-2 4v10h18V7l-2-4h-6z"/><circle cx="7.5" cy="14.5" r="1.5"/><circle cx="16.5" cy="14.5" r="1.5"/></svg>
                                <span>No Image</span>
                            </div>
                        @endif
                        <div class="car-card-badges">
                            @if($car->featured)
                                <span class="badge-featured">注目</span>
                            @endif
                            @if($car->published_at && $car->published_at->diffInDays(now()) <= 7)
                                <span class="badge-new">NEW</span>
                            @endif
                        </div>
                        <div class="car-card-image-footer">
                            @if($car->status !== 'available')
                                <span class="car-card-status">{{ match($car->status) { 'reserved' => '商談中', 'sold' => '売約済', default => $car->status } }}</span>
                            @endif
                            <p class="car-price-overlay">
                                <span class="cpo-label">総額</span>
                                <span class="cpo-num">{{ number_format($car->price) }}</span>
                                <span class="cpo-unit">円</span>
                            </p>
                        </div>
                    </div>

                    <div class="car-card-body">
                        <p class="stock-no">No. {{ $car->stock_no }}</p>
                        <h3>{{ $car->make }} {{ $car->model }}</h3>
                        @if($car->grade && $car->grade !== '—')
                            <p class="car-card-grade">{{ $car->grade }}</p>
                        @endif
                        <div class="car-card-specs">
                            <span>{{ $car->model_year }}年式</span>
                            <span>{{ number_format($car->mileage) }} km</span>
                            <span>{{ $car->body_type }}</span>
                            <span>{{ $car->transmission }}</span>
                        </div>
                    </div>
                </a>

                <div class="car-card-actions" x-data="favBtn({{ $car->id }})">
                    <button @click="toggle"
                            class="card-action-btn fav-btn"
                            :class="{ 'card-action-btn-active': fav }">
                        <svg class="fav-icon" :class="{ 'fav-pop': popping }" width="14" height="13" viewBox="0 0 24 22" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path :fill="fav ? 'currentColor' : 'none'" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                  d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                        </svg>
                        <span x-text="fav ? '登録済' : 'お気に入り'"></span>
                    </button>
                    <button @click="$store.compare.toggleCompare({{ $car->id }}, '{{ addslashes($car->make . ' ' . $car->model) }}')"
                            class="card-action-btn"
                            :class="{ 'card-action-btn-active': $store.compare.inCompare({{ $car->id }}) }"
                            :disabled="!$store.compare.inCompare({{ $car->id }}) && $store.compare.selected.length >= 3">
                        <span x-text="$store.compare.inCompare({{ $car->id }}) ? '✓ 比較中' : '+ 比較追加'"></span>
                    </button>
                </div>
            </article>
        @empty
            <p class="empty">条件に一致する在庫は見つかりませんでした。</p>
        @endforelse
    </section>

    {{-- ページネーション --}}
    @if ($cars->hasPages())
        <nav class="pagination" aria-label="ページネーション">
            @if ($cars->onFirstPage())
                <span class="disabled">前へ</span>
            @else
                <a href="{{ $cars->previousPageUrl() }}">前へ</a>
            @endif
            <span>{{ $cars->currentPage() }} / {{ $cars->lastPage() }} ページ</span>
            @if ($cars->hasMorePages())
                <a href="{{ $cars->nextPageUrl() }}">次へ</a>
            @else
                <span class="disabled">次へ</span>
            @endif
        </nav>
    @endif

    {{-- 比較フローティングバー --}}
    <div x-show="$store.compare.selected.length > 0" x-cloak class="compare-bar">
        <div class="compare-bar-inner">
            <span class="compare-bar-label">比較: <strong x-text="$store.compare.selected.length"></strong> 台</span>
            <div class="compare-bar-items">
                <template x-for="item in $store.compare.selected" :key="item.id">
                    <span class="compare-bar-item">
                        <span x-text="item.name"></span>
                        <button @click="$store.compare.toggleCompare(item.id, item.name)" class="compare-bar-remove">×</button>
                    </span>
                </template>
            </div>
            <a :href="$store.compare.compareUrl" class="btn-primary" style="font-size:13px;min-height:34px;padding:8px 18px;">比較する</a>
            <button @click="$store.compare.clearCompare()" class="compare-bar-clear">クリア</button>
        </div>
    </div>
</div>


{{-- ════════════════════════════════════════════════════════════ --}}
{{--  ページ固有フッターCTA                                        --}}
{{-- ════════════════════════════════════════════════════════════ --}}
@if($isLatest)

{{-- 新着: 入荷希望・通知CTA --}}
<section class="new-notify-section">
    <div class="container">
        <div class="new-notify-inner">
            <div class="new-notify-text">
                <h2 class="new-notify-title">お目当ての車が見つからない方へ</h2>
                <p class="new-notify-sub">
                    ご希望の車種・グレード・予算をお伝えいただければ、<strong>入荷次第ご連絡</strong>します。
                    お気軽にご相談ください。
                </p>
            </div>
            <div class="new-notify-actions">
                <a href="{{ route('contact.index') }}" class="btn-primary new-notify-btn">
                    ✉ 入荷希望を伝える
                </a>
                <a href="tel:06-4960-8765" class="new-notify-tel">
                    <span>📞</span>
                    <span>06-4960-8765</span>
                </a>
            </div>
        </div>
    </div>
</section>

@else

{{-- 在庫一覧: クルマ選び相談CTA --}}
<section class="advisor-cta-section">
    <div class="container">
        <h2 class="advisor-cta-title">どんなクルマが合うか迷ったら</h2>
        <p class="advisor-cta-sub">用途・予算・ライフスタイルに合ったクルマ選びをスタッフがサポートします。</p>
        <div class="advisor-use-grid">
            <div class="advisor-use-card">
                <span class="advisor-use-icon">👨‍👩‍👧‍👦</span>
                <p class="advisor-use-name">ファミリー向け</p>
                <p class="advisor-use-type">ミニバン・ワゴン</p>
                <a href="{{ route('cars.index', ['body_type' => 'ミニバン']) }}" class="advisor-use-link">在庫を見る →</a>
            </div>
            <div class="advisor-use-card">
                <span class="advisor-use-icon">⛽</span>
                <p class="advisor-use-name">燃費を重視</p>
                <p class="advisor-use-type">ハイブリッド・コンパクト</p>
                <a href="{{ route('cars.index', ['body_type' => 'ハッチバック']) }}" class="advisor-use-link">在庫を見る →</a>
            </div>
            <div class="advisor-use-card">
                <span class="advisor-use-icon">🏔</span>
                <p class="advisor-use-name">アウトドア・趣味</p>
                <p class="advisor-use-type">SUV・クロスオーバー</p>
                <a href="{{ route('cars.index', ['body_type' => 'SUV']) }}" class="advisor-use-link">在庫を見る →</a>
            </div>
            <div class="advisor-use-card">
                <span class="advisor-use-icon">💰</span>
                <p class="advisor-use-name">予算を抑えたい</p>
                <p class="advisor-use-type">軽自動車・コンパクト</p>
                <a href="{{ route('cars.index', ['body_type' => '軽自動車']) }}" class="advisor-use-link">在庫を見る →</a>
            </div>
        </div>
        <div class="advisor-cta-consult">
            <p>上記以外でも、<strong>「こんな車が欲しい」</strong>というご希望があればお気軽にご相談ください。</p>
            <a href="{{ route('contact.index') }}" class="btn-primary advisor-consult-btn">✉ スタッフに相談する</a>
        </div>
    </div>
</section>

@endif


<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('compare', {
        selected: [],
        inCompare(id) { return this.selected.some(s => s.id === id); },
        toggleCompare(id, name) {
            if (this.inCompare(id)) {
                this.selected = this.selected.filter(s => s.id !== id);
            } else if (this.selected.length < 3) {
                this.selected.push({ id, name });
            }
        },
        clearCompare() { this.selected = []; },
        get compareUrl() {
            return '{{ route('cars.compare') }}?ids=' + this.selected.map(s => s.id).join(',');
        }
    });
});
function favBtn(id) {
    return {
        id: id,
        fav: false,
        popping: false,
        init() {
            this.fav = JSON.parse(localStorage.getItem('car_favorites') || '[]').includes(this.id);
        },
        toggle() {
            let favs = JSON.parse(localStorage.getItem('car_favorites') || '[]');
            if (this.fav) {
                favs = favs.filter(v => v !== this.id);
            } else {
                favs.push(this.id);
            }
            localStorage.setItem('car_favorites', JSON.stringify(favs));
            this.fav = !this.fav;
            this.popping = true;
            setTimeout(() => { this.popping = false; }, 350);
        }
    };
}
</script>
@endsection
