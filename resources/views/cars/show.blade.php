@extends('layouts.site')

@section('title', $car->make . ' ' . $car->model . ' ' . $car->model_year . '年式｜' . number_format($car->price) . '円')
@section('meta_description', $car->make . ' ' . $car->model . '（' . $car->model_year . '年式・走行' . number_format($car->mileage) . 'km）総額' . number_format($car->price) . '円。' . ($car->body_type ?? '') . '・' . ($car->transmission ?? '') . '・整備履歴' . ($car->has_service_record ? 'あり' : 'なし') . '。兵庫県尼崎市のアサダオートサポート。')
@section('og_type', 'product')
@section('og_title', $car->make . ' ' . $car->model . ' ' . $car->model_year . '年式 ' . number_format($car->price) . '円 | ' . config('app.name'))
@section('og_description', $car->make . ' ' . $car->model . ' ' . $car->model_year . '年式・走行' . number_format($car->mileage) . 'km・総額' . number_format($car->price) . '円。整備履歴' . ($car->has_service_record ? 'あり' : 'なし') . '。')
@php
    $allImages = collect();
    if ($car->image_path) $allImages->push('/images/' . $car->image_path);
    foreach ($car->images as $img) $allImages->push('/images/' . $img->path);
@endphp
@if($allImages->isNotEmpty())
@section('og_image', url($allImages->first()))
@endif

@section('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "Vehicle",
    "@id": "{{ route('cars.show', $car) }}#vehicle",
    "name": "{{ $car->make }} {{ $car->model }}",
    "description": "{{ $car->description ? Str::limit($car->description, 200) : ($car->make . ' ' . $car->model . ' ' . $car->model_year . '年式。走行' . number_format($car->mileage) . 'km。') }}",
    "brand": {
        "@type": "Brand",
        "name": "{{ $car->make }}"
    },
    "model": "{{ $car->model }}",
    "modelDate": "{{ $car->model_year }}",
    "mileageFromOdometer": {
        "@type": "QuantitativeValue",
        "value": {{ $car->mileage }},
        "unitCode": "KMT"
    },
    "vehicleTransmission": "{{ $car->transmission ?? '' }}",
    "bodyType": "{{ $car->body_type ?? '' }}",
    "color": "{{ $car->color ?? '' }}",
    "fuelType": "{{ $car->fuel_type ?? '' }}",
    "vehicleIdentificationNumber": "{{ $car->stock_no }}",
    "numberOfForwardGears": null,
    "offers": {
        "@type": "Offer",
        "url": "{{ route('cars.show', $car) }}",
        "priceCurrency": "JPY",
        "price": {{ $car->price }},
        "availability": "{{ $car->status === 'available' ? 'https://schema.org/InStock' : 'https://schema.org/SoldOut' }}",
        "seller": {
            "@type": "AutoDealer",
            "name": "{{ config('app.name') }}",
            "url": "{{ url('/') }}"
        }
    }
    @if($allImages->isNotEmpty())
    ,"image": @json($allImages->map(fn($p) => url($p))->values())
    @endif
}
</script>
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        {"@type":"ListItem","position":1,"name":"ホーム","item":"{{ url('/') }}"},
        {"@type":"ListItem","position":2,"name":"在庫一覧","item":"{{ route('cars.index') }}"},
        {"@type":"ListItem","position":3,"name":"{{ $car->make }} {{ $car->model }}","item":"{{ route('cars.show', $car) }}"}
    ]
}
</script>
@endsection

@section('content')
<div class="container" style="padding-top:20px;padding-bottom:40px;">

    {{-- パンくず --}}
    <div class="breadcrumb">
        <span><a href="{{ route('home') }}">ホーム</a></span>
        <span><a href="{{ route('cars.index') }}">在庫一覧</a></span>
        <span>{{ $car->make }} {{ $car->model }}</span>
    </div>

    <div class="card-lg" style="overflow:hidden;">

        {{-- メイン2カラム（画像 + 情報） --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0;">

            {{-- 左：画像ギャラリー --}}
            <div style="border-right:1px solid var(--line);">
                @if($allImages->isNotEmpty())
                <div x-data="{ current: 0, images: @json($allImages->values()) }" class="detail-gallery">
                    <div class="detail-gallery-main" style="height:340px;">
                        <template x-for="(src, i) in images" :key="i">
                            <img :src="src" :alt="'車両画像 ' + (i + 1)"
                                 x-show="current === i"
                                 style="width:100%;height:100%;object-fit:cover;display:block;">
                        </template>
                        @if($allImages->count() > 1)
                        <button @click="current = (current - 1 + images.length) % images.length" class="gallery-arrow gallery-arrow-left">&#8249;</button>
                        <button @click="current = (current + 1) % images.length" class="gallery-arrow gallery-arrow-right">&#8250;</button>
                        <div class="gallery-counter" x-text="(current + 1) + ' / ' + images.length"></div>
                        @endif
                    </div>
                    @if($allImages->count() > 1)
                    <div class="detail-gallery-thumbs">
                        <template x-for="(src, i) in images" :key="i">
                            <img :src="src" @click="current = i"
                                 :class="current === i ? 'gallery-thumb thumb-active' : 'gallery-thumb'"
                                 :alt="'サムネイル ' + (i + 1)">
                        </template>
                    </div>
                    @endif
                </div>
                @else
                <div class="detail-image" style="height:340px;">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>No Image Available</span>
                </div>
                @endif
            </div>

            {{-- 右：詳細情報 --}}
            <div style="padding:24px;">
                {{-- バッジ行 --}}
                <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px;">
                    @if($car->status === 'available')
                        <span class="status-badge status-badge-available">販売中</span>
                    @else
                        <span class="status-badge status-badge-default">
                            {{ match($car->status) { 'reserved' => '商談中', 'sold' => '売約済', default => $car->status } }}
                        </span>
                    @endif
                    @if($car->published_at && $car->published_at->diffInDays(now()) <= 7)
                        <span class="badge-new">新着</span>
                    @endif
                    @if($car->featured)
                        <span class="badge-featured">注目車両</span>
                    @endif
                </div>

                {{-- 車名 --}}
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <h1 style="margin:0 0 4px;font-size:clamp(1.3rem,2vw,1.8rem);font-weight:900;line-height:1.2;color:var(--text);">
                            {{ $car->make }} {{ $car->model }}
                        </h1>
                        <p style="margin:0 0 4px;color:var(--muted);font-size:13px;">{{ $car->grade ?: '—' }}</p>
                        <p style="margin:0;font-size:12px;color:var(--muted);">在庫番号: {{ $car->stock_no }}</p>
                    </div>
                    {{-- お気に入りボタン --}}
                    <div x-data="favBtn({{ $car->id }})">
                        <button @click="toggle" style="background:none;border:1px solid var(--line);border-radius:3px;padding:6px 12px;cursor:pointer;font-size:20px;line-height:1;">
                            <span x-text="isFav ? '❤' : '🤍'"></span>
                        </button>
                    </div>
                </div>

                {{-- 価格 --}}
                <div class="detail-price">
                    <span class="detail-price-value">{{ number_format($car->price) }}</span>
                    <span class="detail-price-unit">円（税込）</span>
                </div>

                {{-- スペックグリッド --}}
                <div class="spec-grid">
                    <div class="spec-item">
                        <span class="spec-label">年式</span>
                        <span class="spec-value">{{ $car->model_year }}年</span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">走行距離</span>
                        <span class="spec-value">{{ number_format($car->mileage) }} km</span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">ボディタイプ</span>
                        <span class="spec-value">{{ $car->body_type }}</span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">燃料</span>
                        <span class="spec-value">{{ $car->fuel_type }}</span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">変速機</span>
                        <span class="spec-value">{{ $car->transmission }}</span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">車体色</span>
                        <span class="spec-value">{{ $car->color ?: '—' }}</span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">事故歴</span>
                        <span class="spec-value">{{ $car->accident_count > 0 ? $car->accident_count . '回' : 'なし' }}</span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">整備記録</span>
                        <span class="spec-value">{{ $car->has_service_record ? 'あり' : 'なし' }}</span>
                    </div>
                    @if($car->inspection_expiry)
                    <div class="spec-item" style="grid-column: span 2;">
                        <span class="spec-label">車検期限</span>
                        <span class="spec-value">{{ $car->inspection_expiry->format('Y年m月') }}</span>
                    </div>
                    @endif
                </div>

                {{-- 問い合わせボタン --}}
                <a href="{{ route('contact.index', ['stock_no' => $car->stock_no]) }}"
                   class="btn-primary"
                   style="width:100%;justify-content:center;margin-top:4px;">
                    この車両について問い合わせる ›
                </a>

                {{-- SNSシェア --}}
                @php
                    $shareUrl = urlencode(request()->url());
                    $shareText = urlencode($car->make . ' ' . $car->model . ' ' . number_format($car->price) . '円');
                @endphp
                <div style="margin-top:12px;display:flex;gap:8px;">
                    <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareText }}"
                       target="_blank" rel="noopener" class="btn-share btn-share-x">𝕏 シェア</a>
                    <a href="https://social-plugins.line.me/lineit/share?url={{ $shareUrl }}"
                       target="_blank" rel="noopener" class="btn-share btn-share-line">LINE シェア</a>
                </div>
            </div>
        </div>

        {{-- 車両説明 --}}
        @if($car->description)
        <div class="detail-description">
            <h3>車両詳細・コメント</h3>
            <div class="detail-description-body">
                {!! nl2br(e($car->description)) !!}
            </div>
        </div>
        @endif

        {{-- 戻るリンク --}}
        <div style="padding:20px 24px;border-top:1px solid var(--line);">
            <a href="{{ route('cars.index') }}" class="back-link">
                ← 在庫一覧に戻る
            </a>
        </div>
    </div>

    {{-- 関連車両 --}}
    @if($relatedCars->isNotEmpty())
    <section style="margin-top:32px;">
        <div class="inventory-head">
            <h2>{{ $car->make }}の他の在庫</h2>
            <a href="{{ route('cars.index', ['make' => $car->make]) }}"
               style="font-size:13px;color:var(--red);font-weight:700;">
                もっと見る ›
            </a>
        </div>
        <div class="inventory-grid">
            @foreach($relatedCars as $related)
            <article class="car-card">
                <a href="{{ route('cars.show', $related) }}" class="car-card-link">
                    <div class="car-card-image">
                        @php $rImg = $related->image_path ?? $related->images->first()?->path; @endphp
                        @if($rImg)
                            <img src="{{ '/images/' . $rImg }}" alt="{{ $related->make }} {{ $related->model }}" loading="lazy">
                        @else
                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#e8e8e8;color:#aaa;font-size:12px;">No Image</div>
                        @endif
                        <div class="car-card-badges">
                            @if($related->published_at && $related->published_at->diffInDays(now()) <= 7)
                                <span class="badge-new">新着</span>
                            @endif
                        </div>
                    </div>
                    <div class="car-card-body">
                        <h3>{{ $related->make }} {{ $related->model }}</h3>
                        <p class="grade">{{ $related->grade ?: '—' }}</p>
                        <p class="price">{{ number_format($related->price) }}</p>
                        <dl>
                            <div><dt>年式</dt><dd>{{ $related->model_year }}年</dd></div>
                            <div><dt>走行距離</dt><dd>{{ number_format($related->mileage) }} km</dd></div>
                        </dl>
                    </div>
                </a>
            </article>
            @endforeach
        </div>
    </section>
    @endif
</div>

<script>
function favBtn(id) {
    return {
        id: id,
        get isFav() {
            return JSON.parse(localStorage.getItem('car_favorites') || '[]').includes(this.id);
        },
        toggle() {
            let favs = JSON.parse(localStorage.getItem('car_favorites') || '[]');
            if (favs.includes(this.id)) { favs = favs.filter(v => v !== this.id); }
            else { favs.push(this.id); }
            localStorage.setItem('car_favorites', JSON.stringify(favs));
        }
    };
}
</script>
@endsection
