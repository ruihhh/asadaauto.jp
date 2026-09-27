@php
    // 公開サイト共通レイアウト（デザイン刷新版・案B「車選び型」）
    //
    // 並び：スキップリンク → 上の黒い帯・ヘッダー（x-site.header）→ 赤いナビ（x-site.nav、PC・sticky）→ 営業状況バー（x-site.openbar、スマホ）→
    //       クイックリンク（x-site.sp-quick、スマホ）→ メニュー（x-site.menu）→ main → ご相談帯（x-site.band）→ フッター →
    //       右端の固定ボタン（x-site.side-fix、PC 1200px 以上）→ 比較トレイ → トースト → 固定下部バー（x-site.mbar、スマホ）→ 共通スクリプト
    //
    // ページ側のフック:
    //   @section('title' / 'meta_description' / 'meta_robots' / 'canonical' / 'og_type' / 'og_title' / 'og_description' / 'og_image')
    //   @section('structured_data') … 既存の書き方（<script type="application/ld+json">…</script> を直接書く）
    //   @push('structured_data')    … 部品（x-site.faq の jsonld など）からの追加
    //   @push('head')               … <head> の末尾に足すもの
    //   @section('body_class', 'p-home')
    //   @section('mbar', 'default|contact|buy|none')   … スマホ固定下部バーの種類
    //   @section('contact_band', 'hide')              … フッター直前のご相談帯を出さない
    //   @push('scripts')            … 共通ストア（x-site.scripts）の後に読み込むスクリプト
    $mbarVariant = trim($__env->yieldContent('mbar', 'default')) ?: 'default';
    $hideBand = trim($__env->yieldContent('contact_band')) === 'hide';
    $shopName = config('shop.name');
    $defaultDescription = '兵庫県尼崎市下坂部の中古車販売店'.$shopName.'。支払総額・修復歴・車検の期限をわかりやすく表示し、販売・買取・車検整備までご相談いただけます。';
    $defaultOgTitle = $shopName.' | 兵庫県尼崎市の中古車販売';

    // AutoDealer の構造化データ（全ページ共通）。営業時間は BusinessHours から作り、今後3回の第3日曜を休みとして載せる
    // priceRange（価格帯の目安）は根拠のある値がないため載せない（旧の円記号だけの値は削除。任意の項目で、なくてもエラーにならない）
    $schemaHours = \App\Support\BusinessHours::schemaOpeningHours();
    $dealerSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'AutoDealer',
        '@id' => url('/').'#organization',
        'name' => $shopName,
        'url' => url('/'),
        'image' => url('/images/store-hero-bg.png'),
        'description' => '兵庫県尼崎市下坂部の中古車販売店。支払総額・修復歴・車検の期限をわかりやすく表示し、販売・買取・車検整備までご相談いただけます。',
        'telephone' => config('shop.tel'),
        'email' => 'info@asadaauto.jp',
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => \Illuminate\Support\Str::after(config('shop.address'), config('shop.pref').config('shop.city')),
            'addressLocality' => config('shop.city'),
            'addressRegion' => config('shop.pref'),
            'postalCode' => config('shop.postal'),
            'addressCountry' => 'JP',
        ],
        'geo' => [
            '@type' => 'GeoCoordinates',
            'latitude' => config('shop.geo.lat'),
            'longitude' => config('shop.geo.lng'),
        ],
        'openingHoursSpecification' => $schemaHours['openingHoursSpecification'],
        'specialOpeningHoursSpecification' => $schemaHours['specialOpeningHoursSpecification'],
        'hasMap' => config('shop.map_url'),
        'keywords' => '中古車,尼崎,兵庫県,自動車販売,中古車販売,尼崎市中古車,兵庫県中古車',
        'areaServed' => [
            ['@type' => 'City', 'name' => '尼崎市', 'containedInPlace' => ['@type' => 'State', 'name' => '兵庫県']],
            ['@type' => 'City', 'name' => '西宮市'],
            ['@type' => 'City', 'name' => '伊丹市'],
            ['@type' => 'City', 'name' => '宝塚市'],
            ['@type' => 'City', 'name' => '川西市'],
            ['@type' => 'City', 'name' => '大阪市'],
            ['@type' => 'City', 'name' => '豊中市'],
        ],
        'sameAs' => [],
    ];
@endphp
<!DOCTYPE html>
<html lang="ja">
<head>
    @unless (app()->isLocal())
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-HHVN8S1CE9"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-HHVN8S1CE9');
    </script>
    @endunless
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>@hasSection('title')@yield('title') | {{ $shopName }}@else{{ $shopName }}｜{{ config('shop.tagline') }}@endif</title>
    <meta name="description" content="@yield('meta_description', $defaultDescription)">
    <meta name="robots" content="@yield('meta_robots', 'index, follow')">

    {{-- Canonical --}}
    <link rel="canonical" href="@yield('canonical', url()->current())">

    {{-- OGP --}}
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="{{ $shopName }}">
    <meta property="og:locale" content="ja_JP">
    <meta property="og:title" content="@yield('og_title', $defaultOgTitle)">
    <meta property="og:description" content="@yield('og_description', $defaultDescription)">
    <meta property="og:url" content="{{ url()->current() }}">
    @hasSection('og_image')
    <meta property="og:image" content="@yield('og_image')">
    @endif

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', $defaultOgTitle)">
    <meta name="twitter:description" content="@yield('og_description', $defaultDescription)">

    {{-- 書体（案B）：Noto Sans JP（本文 400/500・見出し 700/900）と Oswald（価格・台数・電話番号などの数字 500/600/700） --}}
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=noto-sans-jp:400,500,700,900|oswald:500,600,700&display=swap" rel="stylesheet">

    {{-- 公開サイトの見た目は site.css だけで作る（Tailwind の resources/css/app.css は管理画面・認証画面だけが読み込む）。app.js は Alpine 本体 --}}
    @vite(['resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/site.css') }}?v={{ filemtime(public_path('css/site.css')) }}">

    {{-- AutoDealer 基本構造化データ（全ページ共通） --}}
    <script type="application/ld+json">{!! json_encode($dealerSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_PRETTY_PRINT) !!}</script>

    {{-- ページ固有の構造化データ --}}
    @yield('structured_data')
    @stack('structured_data')
    @stack('head')
</head>
<body class="@yield('body_class'){{ $mbarVariant !== 'none' ? ' has-mbar' : '' }}">

<a class="l-skip" href="#main">本文へ移動</a>

{{-- PC：上の黒い帯＋白いヘッダー＋赤いナビ（sticky）。スマホ：ヘッダー＋営業状況バー＋アイコン4つのクイックリンク --}}
<x-site.header />
<x-site.nav />
<x-site.openbar />
<x-site.sp-quick />
<x-site.menu />

<main id="main" class="l-main" tabindex="-1">
    @yield('content')
</main>

@unless ($hideBand)
    <x-site.band />
@endunless

<x-site.footer />

{{-- PC（1200px 以上）の右端の縦並び固定ボタン --}}
<x-site.side-fix />

@unless (request()->routeIs('cars.compare'))
    <x-site.compare-tray />
@endunless

{{-- トースト（$store.toast）。読み上げ用の領域は常に置き、中身だけを出し入れする --}}
<div class="l-toast" role="status" aria-live="polite" aria-atomic="true"
     x-data
     x-on:mouseenter="$store.toast.pause()" x-on:mouseleave="$store.toast.resume()"
     x-on:focusin="$store.toast.pause()" x-on:focusout="$store.toast.resume()">
    <div class="l-toast__box" x-show="$store.toast.visible" x-cloak>
        <span x-text="$store.toast.message"></span>
        <button type="button" class="l-toast__action" x-show="$store.toast.actionLabel" x-text="$store.toast.actionLabel" x-on:click="$store.toast.runAction()"></button>
    </div>
</div>

<x-site.mbar :variant="$mbarVariant" />

<x-site.scripts />
@stack('scripts')

</body>
</html>
