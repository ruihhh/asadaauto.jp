@extends('layouts.site')

@section('title', $car->make . ' ' . $car->model . ' ' . $car->model_year . '年式｜' . ($car->price_negotiable ? '応談' : number_format($car->price) . '円'))
@php $priceLabel = $car->price_negotiable ? '応談' : number_format($car->price) . '円'; @endphp
@section('meta_robots', $car->status === 'available' ? 'index, follow' : 'noindex, follow')
@section('meta_description', $car->make . ' ' . $car->model . '（' . $car->model_year . '年式・走行' . number_format($car->mileage) . 'km）' . $priceLabel . '。' . ($car->body_type ?? '') . '・' . ($car->transmission ?? '') . '・整備履歴' . ($car->has_service_record ? 'あり' : 'なし') . '。兵庫県尼崎市のアサダオートサポート。')
@section('og_type', 'product')
@section('og_title', $car->make . ' ' . $car->model . ' ' . $car->model_year . '年式 ' . $priceLabel . ' | ' . config('app.name'))
@section('og_description', $car->make . ' ' . $car->model . ' ' . $car->model_year . '年式・走行' . number_format($car->mileage) . 'km・' . $priceLabel . '。整備履歴' . ($car->has_service_record ? 'あり' : 'なし') . '。')
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
        @if(!$car->price_negotiable && $car->price !== null)
        "price": {{ $car->price }},
        @endif
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

    {{-- 売約済み・商談中バナー --}}
    @if($car->status !== 'available')
    <div class="sold-notice">
        <div class="sold-notice-icon">
            {{ $car->status === 'sold' ? '🚗' : '💬' }}
        </div>
        <div class="sold-notice-body">
            <p class="sold-notice-title">
                {{ $car->status === 'sold' ? 'この車両は売約済みです' : 'この車両は現在商談中です' }}
            </p>
            <p class="sold-notice-sub">
                {{ $car->status === 'sold'
                    ? '同メーカーの在庫や他の車両もぜひご覧ください。'
                    : '商談が成立しない場合、再度販売することがあります。お問い合わせください。' }}
            </p>
        </div>
        <a href="{{ route('cars.index', ['make' => $car->make]) }}" class="sold-notice-btn">
            他の在庫を見る
        </a>
    </div>
    @endif

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
                            <img :src="src" :alt="`{{ $car->make }} {{ $car->model }} {{ $car->model_year }}年式 - 画像${i + 1}`"
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
                @if($car->price_negotiable)
                <div class="detail-price detail-price-negotiable">
                    <span class="detail-price-negotiable-badge">応談</span>
                    <span class="detail-price-negotiable-text">価格はお問い合わせください</span>
                </div>
                @else
                <div class="detail-price">
                    <span class="detail-price-label">支払総額</span>
                    <span class="detail-price-value">{{ number_format($car->price) }}</span>
                    <span class="detail-price-unit">円（税込）</span>
                </div>
                @if($car->base_price)
                <div class="detail-base-price">
                    <span class="detail-base-price-label">車両本体価格</span>
                    <span class="detail-base-price-value">{{ number_format($car->base_price) }}円</span>
                </div>
                @endif
                @endif

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
                @if($car->status === 'available')
                <a href="{{ route('contact.index', ['stock_no' => $car->stock_no]) }}"
                   class="btn-primary"
                   style="width:100%;justify-content:center;margin-top:4px;">
                    この車両について問い合わせる ›
                </a>
                @else
                <a href="{{ route('cars.index', ['make' => $car->make]) }}"
                   class="btn-primary"
                   style="width:100%;justify-content:center;margin-top:4px;background:var(--muted);">
                    同メーカーの在庫を見る ›
                </a>
                @endif

                {{-- SNSシェア --}}
                @php
                    $shareUrl = urlencode(request()->url());
                    $shareText = urlencode($car->make . ' ' . $car->model . ' ' . ($car->price_negotiable ? '応談' : number_format($car->price) . '円'));
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

        {{-- 価格内訳 --}}
        @if(!$car->price_negotiable && $car->base_price)
        <div class="detail-section">
            <h3 class="detail-section-title">価格内訳</h3>
            <table class="price-table">
                <tbody>
                    <tr class="price-table-total">
                        <th>支払総額（税込）</th>
                        <td>{{ number_format($car->price) }} <span>円</span></td>
                    </tr>
                    <tr>
                        <th>車両本体価格</th>
                        <td>{{ number_format($car->base_price) }} <span>円</span></td>
                    </tr>
                    <tr>
                        <th>諸費用目安</th>
                        <td>約 {{ number_format($car->price - $car->base_price) }} <span>円</span></td>
                    </tr>
                </tbody>
            </table>
            <p class="price-table-note">※諸費用には登録手数料・自動車税・自賠責保険・整備費用などが含まれます。詳細はお問い合わせください。</p>
        </div>
        @endif

        {{-- 車両のポイント --}}
        <div class="detail-section">
            <h3 class="detail-section-title">車両のポイント</h3>
            <div class="car-points-grid">
                <div class="car-point {{ $car->accident_count === 0 ? 'car-point-good' : 'car-point-caution' }}">
                    <span class="car-point-icon">{{ $car->accident_count === 0 ? '✓' : '!' }}</span>
                    <div>
                        <p class="car-point-label">事故歴</p>
                        <p class="car-point-value">{{ $car->accident_count === 0 ? 'なし' : $car->accident_count . '回あり' }}</p>
                    </div>
                </div>
                <div class="car-point {{ $car->has_service_record ? 'car-point-good' : '' }}">
                    <span class="car-point-icon">{{ $car->has_service_record ? '✓' : '—' }}</span>
                    <div>
                        <p class="car-point-label">整備記録簿</p>
                        <p class="car-point-value">{{ $car->has_service_record ? 'あり' : 'なし' }}</p>
                    </div>
                </div>
                @if($car->inspection_expiry)
                <div class="car-point {{ $car->inspection_expiry->isFuture() ? 'car-point-good' : '' }}">
                    <span class="car-point-icon">{{ $car->inspection_expiry->isFuture() ? '✓' : '—' }}</span>
                    <div>
                        <p class="car-point-label">車検</p>
                        <p class="car-point-value">{{ $car->inspection_expiry->format('Y年m月') }}まで</p>
                    </div>
                </div>
                @endif
                <div class="car-point">
                    <span class="car-point-icon">✓</span>
                    <div>
                        <p class="car-point-label">走行距離</p>
                        <p class="car-point-value">{{ number_format($car->mileage) }} km</p>
                    </div>
                </div>
                @if($car->location)
                <div class="car-point">
                    <span class="car-point-icon">📍</span>
                    <div>
                        <p class="car-point-label">保管場所</p>
                        <p class="car-point-value">{{ $car->location }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- 購入の流れ --}}
        <div class="detail-section">
            <h3 class="detail-section-title">ご購入の流れ</h3>
            <div class="purchase-flow">
                <div class="purchase-step">
                    <span class="purchase-step-num">01</span>
                    <div class="purchase-step-body">
                        <p class="purchase-step-title">お問い合わせ</p>
                        <p class="purchase-step-desc">お電話またはWebフォームからお気軽にご連絡ください。</p>
                    </div>
                </div>
                <div class="purchase-step-arrow">›</div>
                <div class="purchase-step">
                    <span class="purchase-step-num">02</span>
                    <div class="purchase-step-body">
                        <p class="purchase-step-title">ご来店・現車確認</p>
                        <p class="purchase-step-desc">実車をご覧いただきながら詳細をご説明します。試乗もお気軽にどうぞ。</p>
                    </div>
                </div>
                <div class="purchase-step-arrow">›</div>
                <div class="purchase-step">
                    <span class="purchase-step-num">03</span>
                    <div class="purchase-step-body">
                        <p class="purchase-step-title">お申込み・手続き</p>
                        <p class="purchase-step-desc">ローンや各種手続きのご相談もお任せください。</p>
                    </div>
                </div>
                <div class="purchase-step-arrow">›</div>
                <div class="purchase-step">
                    <span class="purchase-step-num">04</span>
                    <div class="purchase-step-body">
                        <p class="purchase-step-title">納車</p>
                        <p class="purchase-step-desc">整備・点検完了後、ご自宅へのお届けも承ります。</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- 問い合わせCTA --}}
        @if($car->status === 'available')
        <div class="detail-cta">
            <div class="detail-cta-inner">
                <div class="detail-cta-text">
                    <p class="detail-cta-title">この車両が気になったら</p>
                    <p class="detail-cta-sub">在庫確認・試乗・ローン相談など、お気軽にどうぞ</p>
                </div>
                <div class="detail-cta-actions">
                    <a href="{{ route('contact.index', ['stock_no' => $car->stock_no]) }}" class="btn-primary detail-cta-btn">
                        Webで問い合わせる
                    </a>
                </div>
            </div>
        </div>
        @else
        <div class="detail-cta" style="background:var(--bg);">
            <div class="detail-cta-inner">
                <div class="detail-cta-text">
                    <p class="detail-cta-title">他の在庫もご覧ください</p>
                    <p class="detail-cta-sub">{{ $car->make }}の他の車両や、全在庫一覧からお探しいただけます</p>
                </div>
                <div class="detail-cta-actions">
                    <a href="{{ route('cars.index', ['make' => $car->make]) }}" class="btn-primary detail-cta-btn">
                        在庫一覧を見る
                    </a>
                </div>
            </div>
        </div>
        @endif

        {{-- 安心ポイント --}}
        <div class="detail-section">
            <h3 class="detail-section-title">アサダオートサポートの安心保証</h3>
            <div class="assurance-grid">
                <div class="assurance-item">
                    <span class="assurance-icon">🔍</span>
                    <p class="assurance-title">納車前点検整備</p>
                    <p class="assurance-desc">すべての車両を納車前に点検・整備して安心してお乗りいただけます。</p>
                </div>
                <div class="assurance-item">
                    <span class="assurance-icon">📋</span>
                    <p class="assurance-title">車両状態の透明開示</p>
                    <p class="assurance-desc">事故歴・整備記録・車検情報を正直にご案内します。</p>
                </div>
                <div class="assurance-item">
                    <span class="assurance-icon">💬</span>
                    <p class="assurance-title">ローン・下取り相談</p>
                    <p class="assurance-desc">各種ローンのご相談や、現在お乗りの車の下取りも承ります。</p>
                </div>
                <div class="assurance-item">
                    <span class="assurance-icon">🚚</span>
                    <p class="assurance-title">全国納車対応</p>
                    <p class="assurance-desc">兵庫県外のお客様にも陸送にてご納車いたします。</p>
                </div>
            </div>
        </div>

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
                        @if($related->price_negotiable)
                        <p class="price-negotiable-stamp">応 談</p>
                        @else
                        <p class="price">{{ number_format($related->price) }}</p>
                        @if($related->base_price)
                        <p class="car-card-base-price">本体 {{ number_format($related->base_price) }}円</p>
                        @endif
                        @endif
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
