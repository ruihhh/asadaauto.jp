@extends('layouts.site')

@section('title', '尼崎の中古車販売｜兵庫県尼崎市')
@section('meta_description', '【尼崎の中古車】兵庫県尼崎市の中古車販売店アサダオートサポート。現在' . $totalPublic . '台展開中。第三者機関検査済み・総額表示・整備履歴公開で安心の中古車をご提供。軽自動車・SUV・ミニバンなど多数。')
@section('og_title', '尼崎の中古車販売｜兵庫県尼崎市 | ' . config('app.name'))
@section('og_description', '第三者機関検査済み・総額表示・整備履歴公開の中古車販売。兵庫県尼崎市のアサダオートサポート。')
@section('canonical', url('/'))

@section('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "WebSite",
    "@id": "{{ url('/') }}#website",
    "url": "{{ url('/') }}",
    "name": "{{ config('app.name') }}",
    "description": "兵庫県尼崎市の中古車販売店。第三者機関検査済み・総額表示・整備履歴公開。",
    "inLanguage": "ja",
    "potentialAction": {
        "@type": "SearchAction",
        "target": {
            "@type": "EntryPoint",
            "urlTemplate": "{{ url('/cars') }}?q={search_term_string}"
        },
        "query-input": "required name=search_term_string"
    }
}
</script>
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "AutoDealer",
    "@id": "{{ url('/') }}#organization",
    "name": "{{ config('app.name') }}",
    "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "5.0",
        "reviewCount": "3",
        "bestRating": "5",
        "worstRating": "1"
    },
    "review": [
        {
            "@type": "Review",
            "reviewRating": {"@type": "Rating", "ratingValue": "5", "bestRating": "5"},
            "author": {"@type": "Person", "name": "T.S 様"},
            "reviewBody": "子供が増えて大きな車が必要になり相談しました。予算内でとても良い一台を見つけていただけました。スタッフの方も親切で安心して購入できました！"
        },
        {
            "@type": "Review",
            "reviewRating": {"@type": "Rating", "ratingValue": "5", "bestRating": "5"},
            "author": {"@type": "Person", "name": "A.K 様"},
            "reviewBody": "初めての車購入で不安でしたが、丁寧に説明してもらい納得して購入できました。アフターサービスも充実していて大満足です！"
        },
        {
            "@type": "Review",
            "reviewRating": {"@type": "Rating", "ratingValue": "5", "bestRating": "5"},
            "author": {"@type": "Person", "name": "M.T 様"},
            "reviewBody": "アウトドアが趣味でSUVを探していました。こだわりの条件をしっかり聞いてもらい、理想の一台に出会えました。また次もここで買います！"
        }
    ]
}
</script>
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        {
            "@type": "Question",
            "name": "尼崎市でおすすめの中古車販売店はどこですか？",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "兵庫県尼崎市下坂部4丁目5-1のアサダオートサポートをおすすめします。第三者機関検査済み・総額表示・整備履歴公開で安心の中古車を常時多数展開しています。尼崎市をはじめ西宮市・伊丹市・神戸市など兵庫県全域のお客様にご利用いただいています。"
            }
        },
        {
            "@type": "Question",
            "name": "兵庫県で中古車を選ぶポイントを教えてください。",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "中古車を選ぶ際は①修復歴・整備履歴の開示、②諸費用込みの総額表示、③第三者機関による検査の有無を確認することが重要です。アサダオートサポートでは全車両でこれらを明示し、兵庫県・大阪府のお客様に安心してご購入いただける環境を整えています。"
            }
        },
        {
            "@type": "Question",
            "name": "試乗はできますか？予約は必要ですか？",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "在庫車両のほとんどで試乗可能です。予約は不要ですが、事前にお電話（06-4960-8765）またはWebフォームでご連絡いただくとスムーズです。運転免許証をご持参ください。営業時間は11:00〜21:00（木曜・第3日曜定休）です。"
            }
        },
        {
            "@type": "Question",
            "name": "ローンは利用できますか？頭金なしでも購入できますか？",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "はい、頭金0円からのローンプランをご用意しています。複数の提携ローン会社と連携しており、他社で断られた方もご相談いただけます。審査は最短即日回答。月々の返済額シミュレーションも無料で行っています。"
            }
        },
        {
            "@type": "Question",
            "name": "愛車の下取り・買取はしてもらえますか？",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "はい、メーカー・年式・走行距離を問わず買取査定を行っています。査定料は無料です。尼崎市・西宮市・伊丹市など近隣エリアへの出張査定にも対応しています。まずはお電話またはWebフォームからお問い合わせください。"
            }
        }
    ]
}
</script>
@endsection

@section('content')

{{-- ============================================================
     ① ヒーロー（カーセンサー風 明るい左右分割レイアウト）
     ============================================================ --}}
<section class="home-hero-v2">
    <div class="container">
        <div class="home-hero-v2-inner">

            {{-- 左: ブランド + 在庫数 + クイックリンク --}}
            <div class="home-hero-v2-left">
                <p class="home-hero-v2-eyebrow">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    兵庫県尼崎市の中古車販売店
                </p>
                <h1 class="home-hero-v2-title">尼崎の中古車を<br><span>透明な価格</span>で</h1>
                <p class="home-hero-v2-desc">兵庫県尼崎市から、整備履歴・修復歴を正直に開示。<br>諸費用込みの総額で安心してお選びいただけます。</p>

                <div class="home-hero-v2-count">
                    <div class="home-hero-v2-count-inner">
                        <span class="home-hero-v2-count-num">{{ number_format($totalPublic) }}</span>
                        <span class="home-hero-v2-count-unit">台</span>
                    </div>
                    <p class="home-hero-v2-count-label">現在の在庫台数</p>
                </div>

                <div class="home-hero-v2-tags">
                    <a href="{{ route('cars.index', ['body_type' => '軽自動車']) }}" class="hero-tag">軽自動車</a>
                    <a href="{{ route('cars.index', ['body_type' => 'コンパクトカー']) }}" class="hero-tag">コンパクト</a>
                    <a href="{{ route('cars.index', ['body_type' => 'ミニバン']) }}" class="hero-tag">ミニバン</a>
                    <a href="{{ route('cars.index', ['body_type' => 'SUV']) }}" class="hero-tag">SUV</a>
                    <a href="{{ route('cars.index', ['body_type' => 'セダン']) }}" class="hero-tag">セダン</a>
                    <a href="{{ route('cars.index', ['sort' => 'latest']) }}" class="hero-tag hero-tag-new">✨ 新着車両</a>
                </div>

                <div class="home-hero-v2-actions">
                    <a href="{{ route('cars.index') }}" class="btn-primary">在庫一覧を見る &rsaquo;</a>
                    <a href="{{ route('buy.index') }}" class="btn-secondary">無料買取査定</a>
                </div>
            </div>

            {{-- 中央: 車の画像（デコレーション） --}}
            <div class="home-hero-v2-car">
                <img src="/images/hero-car.jpg"
                     onerror="this.style.display='none'"
                     alt="Asada Auto 中古車"
                     class="home-hero-v2-car-img">
            </div>

            {{-- 右: 検索フォームカード --}}
            <div class="home-hero-v2-right">
                <div class="home-hero-search-card">
                    <div class="home-hero-search-card-head">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                        クルマを探す
                        <span class="search-card-count">{{ number_format($totalPublic) }}台から</span>
                    </div>
                    <div class="home-hero-search-card-body">
                        <form method="get" action="{{ route('cars.index') }}">
                            <div class="hero-form-field">
                                <label for="h-make">メーカー</label>
                                <select name="make" id="h-make">
                                    <option value="">すべてのメーカー</option>
                                    @foreach ($makes as $make)
                                        <option value="{{ $make }}">{{ $make }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="hero-form-field">
                                <label for="h-body">ボディタイプ</label>
                                <select name="body_type" id="h-body">
                                    <option value="">すべてのタイプ</option>
                                    @foreach ($bodyTypes as $bt)
                                        <option value="{{ $bt }}">{{ $bt }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="hero-form-row">
                                <div class="hero-form-field">
                                    <label for="h-price">価格（上限）</label>
                                    <select name="max_price" id="h-price">
                                        <option value="">上限なし</option>
                                        <option value="500000">50万円以下</option>
                                        <option value="1000000">100万円以下</option>
                                        <option value="1500000">150万円以下</option>
                                        <option value="2000000">200万円以下</option>
                                        <option value="3000000">300万円以下</option>
                                        <option value="5000000">500万円以下</option>
                                    </select>
                                </div>
                                <div class="hero-form-field">
                                    <label for="h-mileage">走行距離（上限）</label>
                                    <select name="max_mileage" id="h-mileage">
                                        <option value="">上限なし</option>
                                        <option value="10000">1万km以下</option>
                                        <option value="30000">3万km以下</option>
                                        <option value="50000">5万km以下</option>
                                        <option value="80000">8万km以下</option>
                                        <option value="100000">10万km以下</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="hero-search-submit">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                                検索する
                            </button>
                        </form>
                        <div class="hero-search-shortcuts">
                            <span>よく使われる条件：</span>
                            <a href="{{ route('cars.index', ['max_price' => 1000000]) }}">100万円以下</a>
                            <a href="{{ route('cars.index', ['max_mileage' => 30000]) }}">3万km以下</a>
                            <a href="{{ route('cars.index', ['sort' => 'latest']) }}">新着順</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ============================================================
     ② 実績統計バー
     ============================================================ --}}
<div class="home-stats-bar">
    <div class="container">
        <div class="home-stats-inner">
            <div class="home-stat-item">
                <span class="home-stat-number">{{ number_format($totalPublic) }}</span>
                <span class="home-stat-unit">台</span>
                <p class="home-stat-label">現在の在庫台数</p>
            </div>
            <div class="home-stat-div"></div>
            <div class="home-stat-item">
                <span class="home-stat-number">10</span>
                <span class="home-stat-unit">年+</span>
                <p class="home-stat-label">地域での実績</p>
            </div>
            <div class="home-stat-div"></div>
            <div class="home-stat-item">
                <span class="home-stat-number">0</span>
                <span class="home-stat-unit">円</span>
                <p class="home-stat-label">仲介手数料</p>
            </div>
            <div class="home-stat-div"></div>
            <div class="home-stat-item">
                <span class="home-stat-number">98</span>
                <span class="home-stat-unit">%</span>
                <p class="home-stat-label">顧客満足度</p>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     ③ ボディタイプ別クイックアクセス
     ============================================================ --}}
<section class="section section-white">
    <div class="container">
        <div class="section-head-row">
            <h2 class="section-heading">ボディタイプから探す</h2>
        </div>
        <div class="body-type-grid">
            <a href="{{ route('cars.index', ['body_type' => '軽自動車']) }}" class="body-type-card">
                <div class="body-type-icon-wrap">{!! file_get_contents(public_path('images/icons/body-types/kei.svg')) !!}</div>
                <div class="body-type-name">軽自動車</div>
                <div class="body-type-count">{{ $bodyTypeCounts->get('軽自動車', 0) }}台</div>
            </a>
            <a href="{{ route('cars.index', ['body_type' => 'コンパクトカー']) }}" class="body-type-card">
                <div class="body-type-icon-wrap">{!! file_get_contents(public_path('images/icons/body-types/compact.svg')) !!}</div>
                <div class="body-type-name">コンパクト</div>
                <div class="body-type-count">{{ $bodyTypeCounts->filter(fn($v,$k) => str_contains($k,'コンパクト'))->sum() }}台</div>
            </a>
            <a href="{{ route('cars.index', ['body_type' => 'ミニバン']) }}" class="body-type-card">
                <div class="body-type-icon-wrap">{!! file_get_contents(public_path('images/icons/body-types/minivan.svg')) !!}</div>
                <div class="body-type-name">ミニバン</div>
                <div class="body-type-count">{{ $bodyTypeCounts->filter(fn($v,$k) => str_contains($k,'ミニバン'))->sum() }}台</div>
            </a>
            <a href="{{ route('cars.index', ['body_type' => 'SUV']) }}" class="body-type-card">
                <div class="body-type-icon-wrap">{!! file_get_contents(public_path('images/icons/body-types/suv.svg')) !!}</div>
                <div class="body-type-name">SUV・四駆</div>
                <div class="body-type-count">{{ $bodyTypeCounts->filter(fn($v,$k) => str_contains($k,'SUV') || str_contains($k,'クロカン'))->sum() }}台</div>
            </a>
            <a href="{{ route('cars.index', ['body_type' => 'セダン']) }}" class="body-type-card">
                <div class="body-type-icon-wrap">{!! file_get_contents(public_path('images/icons/body-types/sedan.svg')) !!}</div>
                <div class="body-type-name">セダン</div>
                <div class="body-type-count">{{ $bodyTypeCounts->filter(fn($v,$k) => str_contains($k,'セダン'))->sum() }}台</div>
            </a>
            <a href="{{ route('cars.index', ['body_type' => 'ハッチバック']) }}" class="body-type-card">
                <div class="body-type-icon-wrap">{!! file_get_contents(public_path('images/icons/body-types/hatchback.svg')) !!}</div>
                <div class="body-type-name">ハッチバック</div>
                <div class="body-type-count">{{ $bodyTypeCounts->filter(fn($v,$k) => str_contains($k,'ハッチ'))->sum() }}台</div>
            </a>
            <a href="{{ route('cars.index', ['body_type' => 'スポーツ']) }}" class="body-type-card body-type-sport">
                <div class="body-type-icon-wrap">{!! file_get_contents(public_path('images/icons/body-types/sports.svg')) !!}</div>
                <div class="body-type-name">スポーツ</div>
                <div class="body-type-count">{{ $bodyTypeCounts->filter(fn($v,$k) => str_contains($k,'スポーツ') || str_contains($k,'クーペ'))->sum() }}台</div>
            </a>
            <a href="{{ route('cars.index') }}" class="body-type-card body-type-card-all">
                <div class="body-type-icon-wrap">{!! file_get_contents(public_path('images/icons/body-types/all.svg')) !!}</div>
                <div class="body-type-name">すべて見る</div>
                <div class="body-type-count">{{ number_format($totalPublic) }}台</div>
            </a>
        </div>
    </div>
</section>

{{-- ============================================================
     ④ 新着中古車
     ============================================================ --}}
<section class="section section-gray">
    <div class="container">
        <div class="section-head-row">
            <h2 class="section-heading">新着中古車</h2>
            <a href="{{ route('cars.index', ['sort' => 'latest']) }}" class="section-more-link">もっと見る &rsaquo;</a>
        </div>
        <div class="inventory-grid">
            @forelse ($newArrivals as $car)
            <article class="car-card">
                <a href="{{ route('cars.show', $car) }}" class="car-card-link">
                    <div class="car-card-image">
                        @php $img = $car->image_path ?? $car->images->first()?->path; @endphp
                        @if($img)
                            <img src="{{ '/images/' . $img }}" alt="{{ $car->make }} {{ $car->model }}" loading="lazy">
                        @else
                            <div class="car-no-image">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M5 17H3a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h2"/><path d="M21 17h-2"/><path d="M13 3H5l-2 4v10h18V7l-2-4h-6z"/><circle cx="7.5" cy="14.5" r="1.5"/><circle cx="16.5" cy="14.5" r="1.5"/></svg>
                                <span>No Image</span>
                            </div>
                        @endif
                        <div class="car-card-badges">
                            @if($car->published_at && $car->published_at->diffInDays(now()) <= 7)
                                <span class="badge-new">NEW</span>
                            @endif
                            @if($car->featured)
                                <span class="badge-featured">注目</span>
                            @endif
                        </div>
                        <div class="car-card-image-footer">
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
            </article>
            @empty
            <p class="empty">現在、在庫はありません。</p>
            @endforelse
        </div>
        <div class="section-btn-center">
            <a href="{{ route('cars.index') }}" class="btn-primary">在庫一覧をすべて見る &rsaquo;</a>
        </div>
    </div>
</section>

{{-- ============================================================
     ⑤ メーカーから探す
     ============================================================ --}}
<section class="section section-white">
    <div class="container">
        <div class="section-head-row">
            <h2 class="section-heading">メーカーから探す</h2>
        </div>
        <div class="make-links">
            @foreach ($makes as $make)
            <a href="{{ route('cars.index', ['make' => $make]) }}" class="make-link-btn">{{ $make }}</a>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================
     ⑥ 選ばれる理由
     ============================================================ --}}
<section class="section section-gray">
    <div class="container">
        <div class="section-head-row">
            <h2 class="section-heading">選ばれる理由</h2>
        </div>
        <div class="point-grid">
            <div class="point-card">
                <div class="point-icon-wrap">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/><path d="M8 11h6M11 8v6"/></svg>
                </div>
                <div class="point-num">01</div>
                <h3 class="point-title">徹底した品質チェック</h3>
                <p class="point-body">すべての車両を専門スタッフが一台一台丁寧に点検。事故歴・修復歴も正確に開示します。</p>
            </div>
            <div class="point-card">
                <div class="point-icon-wrap">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
                <div class="point-num">02</div>
                <h3 class="point-title">明瞭な価格設定</h3>
                <p class="point-body">諸費用込みの総額をわかりやすく表示。隠れた費用は一切ありません。安心してご購入いただけます。</p>
            </div>
            <div class="point-card">
                <div class="point-icon-wrap">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                </div>
                <div class="point-num">03</div>
                <h3 class="point-title">充実のアフターサービス</h3>
                <p class="point-body">整備記録の完備した車両を多数取り揃え。購入後も安心のサポートをご提供します。</p>
            </div>
            <div class="point-card">
                <div class="point-icon-wrap">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1.27h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.91a16 16 0 0 0 6 6l.94-.94a2 2 0 0 1 2.25-.45 12.8 12.8 0 0 0 2.6.56 2 2 0 0 1 1.7 2.02z"/></svg>
                </div>
                <div class="point-num">04</div>
                <h3 class="point-title">親切・丁寧な対応</h3>
                <p class="point-body">お客様のご要望をしっかりヒアリング。ご予算や用途に合ったベストな一台をご提案します。</p>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     ⑦ ご購入の流れ（NEW）
     ============================================================ --}}
<section class="section section-white home-flow">
    <div class="container">
        <div class="section-head-row">
            <h2 class="section-heading">ご購入の流れ</h2>
            <p class="section-sub-text">はじめての方も安心。4ステップで簡単にご購入いただけます。</p>
        </div>
        <div class="flow-steps">
            <div class="flow-step">
                <div class="flow-step-num">STEP <span>01</span></div>
                <div class="flow-step-icon">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                </div>
                <h3>在庫をチェック</h3>
                <p>サイトの検索機能でご希望のメーカー・ボディタイプ・価格帯で絞り込んで お好みの車両を見つけてください。</p>
            </div>
            <div class="flow-arrow">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </div>
            <div class="flow-step">
                <div class="flow-step-num">STEP <span>02</span></div>
                <div class="flow-step-icon">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <h3>お問い合わせ</h3>
                <p>気になる車両が見つかったら、お電話またはWebフォームでご連絡ください。詳細なご説明をいたします。</p>
            </div>
            <div class="flow-arrow">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </div>
            <div class="flow-step">
                <div class="flow-step-num">STEP <span>03</span></div>
                <div class="flow-step-icon">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M1 3h15v13H1z"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                </div>
                <h3>ご来店・試乗</h3>
                <p>ご来店いただき実車をご確認いただけます。試乗も可能ですので、ぜひ走りをお確かめください。</p>
            </div>
            <div class="flow-arrow">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </div>
            <div class="flow-step">
                <div class="flow-step-num">STEP <span>04</span></div>
                <div class="flow-step-icon">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M9 12l2 2 4-4"/><path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9c1.66 0 3.21.45 4.54 1.23"/></svg>
                </div>
                <h3>ご契約・納車</h3>
                <p>ご契約手続き後、整備・車検を行い、ご指定の場所へ納車いたします。ご自宅への配送も対応可能です。</p>
            </div>
        </div>
        <div class="section-btn-center">
            <a href="{{ route('contact.index') }}" class="btn-primary">まずはお問い合わせ &rsaquo;</a>
        </div>
    </div>
</section>

{{-- ============================================================
     ⑧ 安心のサービス（NEW）
     ============================================================ --}}
<section class="section section-gray home-services">
    <div class="container">
        <div class="section-head-row">
            <h2 class="section-heading">安心のサービス</h2>
        </div>
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon service-icon-red">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                </div>
                <h3>ローン対応</h3>
                <p>頭金0円〜の低金利ローンプランをご用意。月々の返済額をシミュレーションしながらご提案します。</p>
                <span class="service-tag">審査最短即日</span>
            </div>
            <div class="service-card">
                <div class="service-icon service-icon-blue">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <h3>保証サービス</h3>
                <p>購入後の故障も安心。主要部品を対象とした充実の保証プランをご提供します。</p>
                <span class="service-tag">最長3年保証</span>
            </div>
            <div class="service-card">
                <div class="service-icon service-icon-green">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                </div>
                <h3>下取り・買取</h3>
                <p>今お乗りのお車の下取り・買取も積極的に対応。高価買取で乗り換えをサポートします。</p>
                <span class="service-tag">無料査定</span>
            </div>
            <div class="service-card">
                <div class="service-icon service-icon-orange">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                <h3>納車サービス</h3>
                <p>遠方の方も安心。全国への陸送・自社配送に対応。ご自宅まで大切にお届けします。</p>
                <span class="service-tag">全国対応</span>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     ⑨ 人気車種ランキング（タブ切替）
     ============================================================ --}}
<section class="section section-white">
    <div class="container">
        <div class="section-head-row">
            <h2 class="section-heading">人気車種ランキング <span class="section-heading-sub">新着順</span></h2>
        </div>

        <div x-data="{ tab: 'ALL' }">
            <div class="ranking-tabs">
                <button @click="tab = 'ALL'" :class="tab === 'ALL' ? 'ranking-tab-active' : ''" class="ranking-tab">ALL</button>
                @foreach ($rankingTypes as $type)
                <button @click="tab = '{{ $type }}'" :class="tab === '{{ $type }}' ? 'ranking-tab-active' : ''" class="ranking-tab">{{ $type }}</button>
                @endforeach
            </div>

            <div x-show="tab === 'ALL'">
                <div class="ranking-grid">
                    @foreach ($rankings['ALL'] as $i => $car)
                    <a href="{{ route('cars.show', $car) }}" class="ranking-card">
                        <span class="ranking-num ranking-num-{{ $i < 3 ? ($i + 1) : 'other' }}">{{ $i + 1 }}</span>
                        <div class="ranking-img">
                            @php $ri = $car->image_path ?? $car->images->first()?->path; @endphp
                            @if($ri)
                                <img src="{{ '/images/' . $ri }}" alt="{{ $car->make }} {{ $car->model }}" loading="lazy">
                            @else
                                <div class="car-no-image" style="height:100%;">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M13 3H5l-2 4v10h18V7l-2-4h-6z"/><circle cx="7.5" cy="14.5" r="1.5"/><circle cx="16.5" cy="14.5" r="1.5"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="ranking-body">
                            <p class="ranking-name">{{ $car->make }} {{ $car->model }}</p>
                            <p class="ranking-price">{{ number_format($car->price) }}円</p>
                            <p class="ranking-spec">{{ $car->model_year }}年 / {{ number_format($car->mileage) }}km</p>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>

            @foreach ($rankingTypes as $type)
            <div x-show="tab === '{{ $type }}'">
                @if($rankings[$type]->isNotEmpty())
                <div class="ranking-grid">
                    @foreach ($rankings[$type] as $i => $car)
                    <a href="{{ route('cars.show', $car) }}" class="ranking-card">
                        <span class="ranking-num ranking-num-{{ $i < 3 ? ($i + 1) : 'other' }}">{{ $i + 1 }}</span>
                        <div class="ranking-img">
                            @php $ri = $car->image_path ?? $car->images->first()?->path; @endphp
                            @if($ri)
                                <img src="{{ '/images/' . $ri }}" alt="{{ $car->make }} {{ $car->model }}" loading="lazy">
                            @else
                                <div class="car-no-image" style="height:100%;">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M13 3H5l-2 4v10h18V7l-2-4h-6z"/><circle cx="7.5" cy="14.5" r="1.5"/><circle cx="16.5" cy="14.5" r="1.5"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="ranking-body">
                            <p class="ranking-name">{{ $car->make }} {{ $car->model }}</p>
                            <p class="ranking-price">{{ number_format($car->price) }}円</p>
                            <p class="ranking-spec">{{ $car->model_year }}年 / {{ number_format($car->mileage) }}km</p>
                        </div>
                    </a>
                    @endforeach
                </div>
                @else
                <div class="empty">{{ $type }}の在庫は現在ありません。</div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================
     ⑩ 注目車両（フィーチャード）
     ============================================================ --}}
@if($featuredCars->isNotEmpty())
<section class="section section-gray">
    <div class="container">
        <div class="section-head-row">
            <h2 class="section-heading">注目車両</h2>
            <a href="{{ route('cars.index') }}?featured=1" class="section-more-link">もっと見る &rsaquo;</a>
        </div>
        <div class="inventory-grid">
            @foreach ($featuredCars as $car)
            <article class="car-card">
                <a href="{{ route('cars.show', $car) }}" class="car-card-link">
                    <div class="car-card-image">
                        @php $img = $car->image_path ?? $car->images->first()?->path; @endphp
                        @if($img)
                            <img src="{{ '/images/' . $img }}" alt="{{ $car->make }} {{ $car->model }}" loading="lazy">
                        @else
                            <div class="car-no-image">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M13 3H5l-2 4v10h18V7l-2-4h-6z"/><circle cx="7.5" cy="14.5" r="1.5"/><circle cx="16.5" cy="14.5" r="1.5"/></svg>
                                <span>No Image</span>
                            </div>
                        @endif
                        <div class="car-card-badges">
                            <span class="badge-featured">注目</span>
                            @if($car->published_at && $car->published_at->diffInDays(now()) <= 7)
                                <span class="badge-new">NEW</span>
                            @endif
                        </div>
                        <div class="car-card-image-footer">
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
            </article>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============================================================
     ⑪ お知らせ・店舗情報
     ============================================================ --}}
<section class="section section-white">
    <div class="container">
        <div class="news-store-grid">

            {{-- お知らせ --}}
            <div>
                <h2 class="section-heading">お知らせ</h2>
                <div class="news-list">
                    <div class="news-item">
                        <span class="news-date">2026.03.20</span>
                        <span class="news-cat news-cat-info">お知らせ</span>
                        <a href="#">春の新生活応援フェア開催中！</a>
                    </div>
                    <div class="news-item">
                        <span class="news-date">2026.03.15</span>
                        <span class="news-cat news-cat-info">お知らせ</span>
                        <a href="#">新着車両を多数入荷しました</a>
                    </div>
                    <div class="news-item">
                        <span class="news-date">2026.03.10</span>
                        <span class="news-cat news-cat-event">イベント</span>
                        <a href="#">3月の定休日のご案内</a>
                    </div>
                    <div class="news-item">
                        <span class="news-date">2026.03.01</span>
                        <span class="news-cat news-cat-info">お知らせ</span>
                        <a href="#">サイトをリニューアルしました</a>
                    </div>
                    <div class="news-item">
                        <span class="news-date">2026.02.20</span>
                        <span class="news-cat news-cat-event">イベント</span>
                        <a href="#">試乗会開催のご案内</a>
                    </div>
                </div>
            </div>

            {{-- 店舗情報 --}}
            <div>
                <h2 class="section-heading">店舗情報</h2>
                <div class="store-info-card">
                    <div class="store-info-row">
                        <span class="store-info-label">店舗名</span>
                        <span class="store-info-val">{{ config('app.name', 'Asada Auto') }}</span>
                    </div>
                    <div class="store-info-row">
                        <span class="store-info-label">住所</span>
                        <span class="store-info-val">〒661-0975 兵庫県尼崎市下坂部4丁目5-1</span>
                    </div>
                    <div class="store-info-row">
                        <span class="store-info-label">電話</span>
                        <span class="store-info-val"><a href="tel:06-4960-8765" style="color:var(--red);font-weight:700;">06-4960-8765</a></span>
                    </div>
                    <div class="store-info-row">
                        <span class="store-info-label">営業時間</span>
                        <span class="store-info-val">11:00〜21:00</span>
                    </div>
                    <div class="store-info-row">
                        <span class="store-info-label">定休日</span>
                        <span class="store-info-val">木曜日・第3日曜日</span>
                    </div>
                    <a href="{{ route('store') }}" class="btn-secondary" style="width:100%;justify-content:center;font-size:13px;min-height:38px;margin-top:16px;">
                        アクセスマップを見る &rsaquo;
                    </a>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ============================================================
     ⑫ オーナーズボイス
     ============================================================ --}}
<section class="section section-gray">
    <div class="container">
        <div class="section-head-row">
            <h2 class="section-heading">オーナーズボイス</h2>
            <p class="section-sub-text">実際にご購入いただいたお客様の声</p>
        </div>
        <div class="owners-grid">
            <div class="owner-card">
                <div class="owner-stars">★★★★★</div>
                <p class="owner-comment">「子供が増えて大きな車が必要になり相談しました。予算内でとても良い一台を見つけていただけました。スタッフの方も親切で安心して購入できました！」</p>
                <div class="owner-meta">
                    <div class="owner-avatar">T</div>
                    <div>
                        <p class="owner-name">T.S 様</p>
                        <p class="owner-car">ミニバン / 30代ファミリー</p>
                    </div>
                </div>
            </div>
            <div class="owner-card">
                <div class="owner-stars">★★★★★</div>
                <p class="owner-comment">「初めての車購入で不安でしたが、丁寧に説明してもらい納得して購入できました。アフターサービスも充実していて大満足です！」</p>
                <div class="owner-meta">
                    <div class="owner-avatar">A</div>
                    <div>
                        <p class="owner-name">A.K 様</p>
                        <p class="owner-car">軽自動車 / 20代女性</p>
                    </div>
                </div>
            </div>
            <div class="owner-card">
                <div class="owner-stars">★★★★★</div>
                <p class="owner-comment">「アウトドアが趣味でSUVを探していました。こだわりの条件をしっかり聞いてもらい、理想の一台に出会えました。また次もここで買います！」</p>
                <div class="owner-meta">
                    <div class="owner-avatar">M</div>
                    <div>
                        <p class="owner-name">M.T 様</p>
                        <p class="owner-car">SUV / 40代男性</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     ⑬ よくある質問（FAQ）
     ============================================================ --}}
<section class="section section-white">
    <div class="container">
        <div class="section-head-row">
            <h2 class="section-heading">よくある質問</h2>
            <p class="section-sub-text">尼崎・兵庫の中古車購入について</p>
        </div>
        <div class="home-faq-list">

            <details class="home-faq-item">
                <summary class="home-faq-q">
                    <span class="home-faq-icon">Q</span>
                    尼崎市でおすすめの中古車販売店はどこですか？
                    <svg class="home-faq-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="home-faq-a">
                    <span class="home-faq-a-icon">A</span>
                    <p>兵庫県尼崎市下坂部4丁目5-1の<strong>アサダオートサポート</strong>をおすすめします。第三者機関検査済み・総額表示・整備履歴公開で安心の中古車を常時多数展開。尼崎市をはじめ西宮市・伊丹市・神戸市など兵庫県全域のお客様にご利用いただいています。</p>
                </div>
            </details>

            <details class="home-faq-item">
                <summary class="home-faq-q">
                    <span class="home-faq-icon">Q</span>
                    兵庫県で中古車を選ぶポイントを教えてください。
                    <svg class="home-faq-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="home-faq-a">
                    <span class="home-faq-a-icon">A</span>
                    <p>①<strong>修復歴・整備履歴の開示</strong>、②<strong>諸費用込みの総額表示</strong>、③<strong>第三者機関による検査</strong>の有無を確認することが重要です。アサダオートサポートでは全車両でこれらを明示し、兵庫県・大阪府のお客様に安心してご購入いただける環境を整えています。</p>
                </div>
            </details>

            <details class="home-faq-item">
                <summary class="home-faq-q">
                    <span class="home-faq-icon">Q</span>
                    試乗はできますか？予約は必要ですか？
                    <svg class="home-faq-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="home-faq-a">
                    <span class="home-faq-a-icon">A</span>
                    <p>在庫車両のほとんどで<strong>試乗可能</strong>です。予約不要でお気軽にご来店いただけますが、事前に<a href="tel:06-4960-8765">06-4960-8765</a>またはWebフォームでご連絡いただくとよりスムーズです。運転免許証をご持参ください（営業時間：11:00〜21:00 / 木曜・第3日曜定休）。</p>
                </div>
            </details>

            <details class="home-faq-item">
                <summary class="home-faq-q">
                    <span class="home-faq-icon">Q</span>
                    ローンは利用できますか？頭金なしでも購入できますか？
                    <svg class="home-faq-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="home-faq-a">
                    <span class="home-faq-a-icon">A</span>
                    <p><strong>頭金0円</strong>からのローンプランをご用意しています。複数の提携ローン会社と連携しており、他社で断られた方もぜひご相談ください。審査は<strong>最短即日回答</strong>。月々の返済額シミュレーションも無料で行っています。</p>
                </div>
            </details>

            <details class="home-faq-item">
                <summary class="home-faq-q">
                    <span class="home-faq-icon">Q</span>
                    愛車の下取り・買取はしてもらえますか？
                    <svg class="home-faq-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="home-faq-a">
                    <span class="home-faq-a-icon">A</span>
                    <p>はい、メーカー・年式・走行距離を問わず<strong>買取査定は無料</strong>で行っています。尼崎市・西宮市・伊丹市など近隣エリアへの出張査定にも対応。まずは<a href="{{ route('buy.index') }}">買取査定フォーム</a>またはお電話でお問い合わせください。</p>
                </div>
            </details>

        </div>
    </div>
</section>

{{-- ============================================================
     ⑭ SEO テキスト＋対応エリア
     ============================================================ --}}
<section class="section section-white seo-local-section">
    <div class="container">
        <div class="seo-local-inner">

            {{-- 左: 店舗紹介テキスト --}}
            <div class="seo-local-text">
                <h2 class="seo-local-heading">兵庫県尼崎市の中古車販売店「アサダオートサポート」</h2>
                <p>アサダオートサポートは、<strong>兵庫県尼崎市</strong>下坂部に拠点を置く地域密着の中古車販売店です。尼崎市をはじめ、西宮市・伊丹市・宝塚市・神戸市、さらに大阪市・豊中市・吹田市など<strong>兵庫県・大阪府</strong>全域のお客様に、良質な中古車を透明な価格でご提供しています。</p>
                <p>軽自動車・コンパクトカー・ミニバン・SUV・セダンなど常時<strong>{{ $totalPublic }}台以上</strong>の在庫を展開。全車両に第三者機関による車両検査を実施し、修復歴・整備履歴を正直に開示。諸費用込みの総額価格表示で、余計な費用は一切かかりません。</p>
                <p>「<strong>尼崎で中古車</strong>を買うなら」「<strong>兵庫県でコスパの良い中古車</strong>を探したい」とお考えの方は、ぜひアサダオートサポートへ。査定・ローン相談・試乗はすべて無料。まずはお気軽にお問い合わせください。</p>
                <a href="{{ route('cars.index') }}" class="btn-primary" style="display:inline-flex;margin-top:8px;">在庫一覧を見る &rsaquo;</a>
            </div>

            {{-- 右: 対応エリア --}}
            <div class="seo-local-area">
                <h3 class="seo-area-title">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    対応エリア
                </h3>
                <div class="seo-area-block">
                    <p class="seo-area-pref">兵庫県</p>
                    <div class="seo-area-cities">
                        <span class="seo-area-city seo-area-main">尼崎市</span>
                        <span class="seo-area-city">西宮市</span>
                        <span class="seo-area-city">伊丹市</span>
                        <span class="seo-area-city">宝塚市</span>
                        <span class="seo-area-city">川西市</span>
                        <span class="seo-area-city">神戸市</span>
                        <span class="seo-area-city">明石市</span>
                        <span class="seo-area-city">姫路市</span>
                        <span class="seo-area-city">加古川市</span>
                        <span class="seo-area-city">三田市 他</span>
                    </div>
                </div>
                <div class="seo-area-block">
                    <p class="seo-area-pref">大阪府</p>
                    <div class="seo-area-cities">
                        <span class="seo-area-city">大阪市</span>
                        <span class="seo-area-city">豊中市</span>
                        <span class="seo-area-city">吹田市</span>
                        <span class="seo-area-city">池田市</span>
                        <span class="seo-area-city">箕面市</span>
                        <span class="seo-area-city">茨木市 他</span>
                    </div>
                </div>
                <p class="seo-area-nationwide">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 3h15v13H1z"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    全国への陸送納車にも対応
                </p>
            </div>

        </div>
    </div>
</section>

{{-- ============================================================
     ⑭ CTA バナー
     ============================================================ --}}
<div class="home-cta-banner">
    <div class="home-cta-bg"></div>
    <div class="container" style="position:relative;z-index:1;">
        <div class="home-cta-inner">
            <div class="home-cta-text">
                <p class="home-cta-eyebrow">CONTACT US</p>
                <p class="home-cta-title">お探しの車が<br>見つからない場合は</p>
                <p class="home-cta-sub">お気軽にお問い合わせください。ご要望に合ったお車をご提案いたします。</p>
            </div>
            <div class="home-cta-actions">
                <a href="{{ route('contact.index') }}" class="btn-primary" style="font-size:16px;min-height:52px;padding:14px 36px;">お問い合わせ &rsaquo;</a>
                <a href="tel:06-4960-8765" class="home-cta-tel">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 1.27h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.91a16 16 0 0 0 6 6l.94-.94a2 2 0 0 1 2.25-.45 12.8 12.8 0 0 0 2.6.56 2 2 0 0 1 1.7 2.02z"/></svg>
                    06-4960-8765
                </a>
            </div>
        </div>
    </div>
</div>

@endsection
