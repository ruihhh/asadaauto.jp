<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | アサダオートサポート</title>
    <meta name="description" content="@yield('meta_description', '状態の良い中古車を、価格と整備履歴の透明性で選べる在庫サイト。兵庫県尼崎市のアサダオートサポート。')">
    <meta name="robots" content="@yield('meta_robots', 'index, follow')">

    {{-- Canonical --}}
    <link rel="canonical" href="@yield('canonical', url()->current())">

    {{-- OGP --}}
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="{{ config('app.name', 'Asada Auto Support') }}">
    <meta property="og:locale" content="ja_JP">
    <meta property="og:title" content="@yield('og_title', config('app.name', 'Asada Auto') . ' | 兵庫県尼崎市の中古車販売')">
    <meta property="og:description" content="@yield('og_description', '状態の良い中古車を、価格と整備履歴の透明性で選べる在庫サイト。兵庫県尼崎市のアサダオートサポート。')">
    <meta property="og:url" content="{{ url()->current() }}">
    @hasSection('og_image')
    <meta property="og:image" content="@yield('og_image')">
    @endif

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', config('app.name', 'Asada Auto') . ' | 兵庫県尼崎市の中古車販売')">
    <meta name="twitter:description" content="@yield('og_description', '状態の良い中古車を、価格と整備履歴の透明性で選べる在庫サイト。')">

    {{-- Noto Sans JP (日本語フォント) --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=noto-sans-jp:400,500,700,900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ time() }}">

    {{-- AutoDealer 基本構造化データ (全ページ共通) --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "AutoDealer",
        "@id": "{{ url('/') }}#organization",
        "name": "{{ config('app.name', 'Asada Auto Support') }}",
        "url": "{{ url('/') }}",
        "logo": "{{ url('/images/logo.png') }}",
        "image": "{{ url('/images/store-hero-bg.png') }}",
        "description": "兵庫県尼崎市の中古車販売店。第三者機関検査済み・総額表示・整備履歴公開で安心の中古車をご提供します。",
        "telephone": "06-4960-8765",
        "email": "info@asadaauto.jp",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "下坂部4丁目5-1",
            "addressLocality": "尼崎市",
            "addressRegion": "兵庫県",
            "postalCode": "661-0975",
            "addressCountry": "JP"
        },
        "geo": {
            "@type": "GeoCoordinates",
            "latitude": 34.7167,
            "longitude": 135.4167
        },
        "openingHoursSpecification": [
            {
                "@type": "OpeningHoursSpecification",
                "dayOfWeek": ["Monday","Tuesday","Wednesday","Friday","Saturday","Sunday"],
                "opens": "11:00",
                "closes": "21:00"
            }
        ],
        "priceRange": "¥¥",
        "hasMap": "https://maps.google.com/?q=兵庫県尼崎市下坂部4丁目5-1",
        "keywords": "中古車,尼崎,兵庫県,自動車販売,中古車販売,尼崎市中古車,兵庫県中古車",
        "areaServed": [
            {"@type": "City", "name": "尼崎市", "containedInPlace": {"@type": "State", "name": "兵庫県"}},
            {"@type": "City", "name": "西宮市"},
            {"@type": "City", "name": "伊丹市"},
            {"@type": "City", "name": "宝塚市"},
            {"@type": "City", "name": "川西市"},
            {"@type": "City", "name": "神戸市"},
            {"@type": "City", "name": "明石市"},
            {"@type": "City", "name": "姫路市"},
            {"@type": "City", "name": "加古川市"},
            {"@type": "City", "name": "大阪市"},
            {"@type": "City", "name": "豊中市"},
            {"@type": "City", "name": "吹田市"},
            {"@type": "City", "name": "池田市"},
            {"@type": "City", "name": "箕面市"},
            {"@type": "AdministrativeArea", "name": "兵庫県"},
            {"@type": "AdministrativeArea", "name": "大阪府"}
        ],
        "sameAs": []
    }
    </script>

    {{-- ページ固有の構造化データ --}}
    @yield('structured_data')
</head>
<body class="font-sans antialiased">

{{-- ユーティリティバー --}}
<div class="site-utility-bar">
    <div class="container">
        <a href="{{ route('store') }}">店舗情報・アクセス</a>
        <a href="{{ route('cars.favorites') }}">❤ お気に入り</a>
        <a href="{{ route('contact.index') }}">お問い合わせ</a>
        @auth
            <a href="{{ route('admin.dashboard') }}">管理画面</a>
        @else
            <a href="{{ route('login') }}">管理者ログイン</a>
        @endauth
    </div>
</div>

{{-- メインヘッダー --}}
<header class="site-header">
    <div class="container">
        <div class="site-header-inner">
            <div class="site-logo-wrap">
                <p class="site-logo-eyebrow">中古車販売店</p>
                <a href="{{ route('home') }}" class="site-logo-name">{{ config('app.name', 'Asada Auto') }}</a>
                <p class="site-logo-sub">状態の良い中古車を、価格と整備履歴の透明性で選べる在庫サイト</p>
            </div>
            <div class="site-header-contact">
                <div class="site-header-tel"><a href="tel:06-4960-8765" style="color:inherit;text-decoration:none;">06-4960-8765</a></div>
                <div class="site-header-hours">受付時間：11:00〜21:00（木曜・第3日曜定休）</div>
            </div>
        </div>
    </div>
</header>

{{-- 安心バー --}}
<div class="trust-bar">
    <div class="container">
        <div class="trust-bar-inner">
            <div class="trust-item">
                <span class="trust-item-icon trust-red">✅</span>
                <span>第三者機関による車両検査済み</span>
            </div>
            <div class="trust-divider"></div>
            <div class="trust-item">
                <span class="trust-item-icon">🔒</span>
                <span>安心の保証付き</span>
            </div>
            <div class="trust-divider"></div>
            <div class="trust-item">
                <span class="trust-item-icon trust-blue">📋</span>
                <span>整備履歴・修復歴の明示</span>
            </div>
            <div class="trust-divider"></div>
            <div class="trust-item">
                <span class="trust-item-icon trust-red">💰</span>
                <span>明瞭な総額価格表示</span>
            </div>
        </div>
    </div>
</div>

{{-- グローバルナビ --}}
<nav class="site-nav" x-data="{ open: false, scrolled: false }" @scroll.window="scrolled = window.scrollY > 60" :class="{ 'site-nav--scrolled': scrolled }">
    <div class="container">
        <div class="site-nav-inner" :class="open ? 'open' : ''">
            <a href="{{ route('cars.index') }}" class="{{ request()->routeIs('cars.*') && !request()->routeIs('cars.compare') && request('sort') !== 'latest' ? 'active' : '' }}">
                🚗 在庫一覧
            </a>
            <a href="{{ route('cars.index', ['sort' => 'latest']) }}" class="{{ request()->routeIs('cars.index') && request('sort') === 'latest' ? 'active' : '' }}">
                ✨ 新着車両
            </a>
            <a href="{{ route('cars.compare') }}" class="{{ request()->routeIs('cars.compare') ? 'active' : '' }}">
                ⚖ 車両比較
            </a>
            <a href="{{ route('store') }}" class="{{ request()->routeIs('store') ? 'active' : '' }}">
                📍 店舗情報
            </a>
            <a href="{{ route('buy.index') }}" class="{{ request()->routeIs('buy.*') ? 'active' : '' }}"
               style="{{ request()->routeIs('buy.*') ? '' : 'background:rgba(255,255,255,.12);' }}">
                💰 買取査定
            </a>
            <a href="{{ route('contact.index') }}" class="{{ request()->routeIs('contact.*') ? 'active' : '' }}">
                ✉ お問い合わせ
            </a>
            <button class="nav-mobile-toggle" @click="open = !open" style="background:none;border:none;color:#fff;font-size:20px;cursor:pointer;padding:0 16px;margin-left:auto;min-height:46px;">
                ☰
            </button>
        </div>
    </div>
</nav>

<main>
    @yield('content')
</main>

{{-- フッター --}}
<footer class="site-footer">
    <div class="site-footer-nav">
        <div class="container">
            <div class="site-footer-nav-inner">
                <div class="site-footer-col">
                    <p class="site-footer-col-title">クルマを探す</p>
                    <ul>
                        <li><a href="{{ route('cars.index') }}">在庫一覧</a></li>
                        <li><a href="{{ route('cars.index', ['sort' => 'latest']) }}">新着車両</a></li>
                        <li><a href="{{ route('cars.index') }}?featured=1">注目車両</a></li>
                        <li><a href="{{ route('cars.compare') }}">車両比較</a></li>
                        <li><a href="{{ route('cars.favorites') }}">お気に入り</a></li>
                    </ul>
                </div>
                <div class="site-footer-col">
                    <p class="site-footer-col-title">ボディタイプから探す</p>
                    <ul>
                        <li><a href="{{ route('cars.index', ['body_type' => 'セダン']) }}">セダン</a></li>
                        <li><a href="{{ route('cars.index', ['body_type' => 'SUV']) }}">SUV・クロスオーバー</a></li>
                        <li><a href="{{ route('cars.index', ['body_type' => 'ミニバン']) }}">ミニバン</a></li>
                        <li><a href="{{ route('cars.index', ['body_type' => 'ハッチバック']) }}">ハッチバック</a></li>
                        <li><a href="{{ route('cars.index', ['body_type' => '軽自動車']) }}">軽自動車</a></li>
                    </ul>
                </div>
                <div class="site-footer-col">
                    <p class="site-footer-col-title">お役立ち情報</p>
                    <ul>
                        <li><a href="{{ route('buy.index') }}">愛車の買取査定</a></li>
                        <li><a href="{{ route('store') }}">店舗情報・アクセス</a></li>
                        <li><a href="{{ route('contact.index') }}">お問い合わせ</a></li>
                        <li><a href="{{ route('sitemap') }}">サイトマップ</a></li>
                    </ul>
                </div>
                <div class="site-footer-col">
                    <p class="site-footer-col-title">{{ config('app.name', 'Asada Auto') }}</p>
                    <ul>
                        <li><a href="https://maps.google.com/?q=兵庫県尼崎市下坂部4丁目5-1" target="_blank">〒661-0975 兵庫県尼崎市下坂部4丁目5-1</a></li>
                        <li><a href="tel:06-4960-8765">TEL: 06-4960-8765</a></li>
                        <li><a href="#">営業: 11:00〜21:00（木曜・第3日曜定休）</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="site-footer-bottom">
        <div class="container">
            <div class="site-footer-bottom-links">
                <a href="{{ route('store') }}">店舗情報</a>
                <a href="{{ route('contact.index') }}">お問い合わせ</a>
                <a href="{{ route('sitemap') }}">サイトマップ</a>
                @auth
                    <a href="{{ route('admin.dashboard') }}">管理画面</a>
                @else
                    <a href="{{ route('login') }}">管理者ログイン</a>
                @endauth
            </div>
            <p>&copy; {{ date('Y') }} {{ config('app.name', 'Asada Auto') }}. All rights reserved.</p>
        </div>
    </div>
</footer>

</body>
</html>
