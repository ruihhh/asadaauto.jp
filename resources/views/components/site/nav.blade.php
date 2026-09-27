@props([])
{{--
    グローバルナビ（PCだけ。赤・高さ58px・sticky・アイコン付き）。layouts/site から1回だけ呼ぶ。
    項目：在庫を探す／買取査定／ご購入の流れ・ローン／店舗案内・アクセス／お問い合わせ（アイコンは 1200px 以上で出す）。
    右端：お気に入り・比較（黄色の件数バッジ。0件も「0」と出す）。1279px 以下はアイコン＋件数だけ（文字は読み上げ用）。
    120px 以上スクロールしたら電話番号を追加表示する（throttle は最後のスクロールを捨てて先頭でも is-scrolled が残ることがあるため使わない。値が変わったときだけ再描画される）。アクティブの項目は濃い赤＋下に黄の線、aria-current="page"。

    使い方: <x-site.nav />（props なし）
    出力: <nav class="l-nav [is-scrolled]" aria-label="メインメニュー"><div class="l-container l-nav__inner">
          <ul class="l-nav__list"><li><a class="l-nav__link [is-active]" aria-current="page"><svg class="c-icon l-nav__icon">…</a></li>…</ul>
          <ul class="l-nav__tools"><li><a class="l-nav__tool">（ハート）<span class="l-nav__tool-label">お気に入り</span> <span class="l-nav__count">2</span></a></li>…
          <li class="l-nav__tel"><a class="l-nav__tool l-nav__tel-link">06-4960-8765</a></li></ul></div></nav>
--}}
@php
    $items = [
        ['label' => '在庫を探す', 'icon' => 'search', 'url' => route('cars.index'), 'active' => request()->routeIs('cars.index', 'cars.show')],
        ['label' => '買取査定', 'icon' => 'yen', 'url' => route('buy.index'), 'active' => request()->routeIs('buy.*')],
        ['label' => 'ご購入の流れ・ローン', 'icon' => 'route', 'url' => route('home').'#flow', 'active' => false],
        ['label' => '店舗案内・アクセス', 'icon' => 'store', 'url' => route('store'), 'active' => request()->routeIs('store')],
        ['label' => 'お問い合わせ', 'icon' => 'mail', 'url' => route('contact.index'), 'active' => request()->routeIs('contact.*')],
    ];
    $favActive = request()->routeIs('cars.favorites');
    $compareActive = request()->routeIs('cars.compare');
@endphp
<nav class="l-nav" aria-label="メインメニュー"
     x-data="{ scrolled: false }"
     x-init="scrolled = window.scrollY > 120"
     x-on:scroll.window.passive="scrolled = window.scrollY > 120"
     :class="{ 'is-scrolled': scrolled }">
    <div class="l-container l-nav__inner">
        <ul class="l-nav__list">
            @foreach ($items as $item)
                <li>
                    <a class="l-nav__link{{ $item['active'] ? ' is-active' : '' }}" href="{{ $item['url'] }}" @if ($item['active']) aria-current="page" @endif><x-site.icon :name="$item['icon']" class="l-nav__icon" />{{ $item['label'] }}</a>
                </li>
            @endforeach
        </ul>
        <ul class="l-nav__tools">
            <li>
                <a class="l-nav__tool{{ $favActive ? ' is-active' : '' }}" href="{{ route('cars.favorites') }}" @if ($favActive) aria-current="page" @endif>
                    <x-site.icon name="heart" />
                    <span class="l-nav__tool-label">お気に入り</span>
                    <span class="l-nav__count"><span x-text="$store.favorites.count">0</span><span class="u-visually-hidden">件</span></span>
                </a>
            </li>
            <li>
                <a class="l-nav__tool{{ $compareActive ? ' is-active' : '' }}" href="{{ route('cars.compare') }}" :href="$store.compare.url" @if ($compareActive) aria-current="page" @endif>
                    <x-site.icon name="compare" />
                    <span class="l-nav__tool-label">比較</span>
                    <span class="l-nav__count"><span x-text="$store.compare.count">0</span><span class="u-visually-hidden">台</span></span>
                </a>
            </li>
            <li class="l-nav__tel" x-show="scrolled" x-cloak>
                <a class="l-nav__tool l-nav__tel-link" href="{{ config('shop.tel_href') }}" aria-label="電話をかける {{ config('shop.tel') }}">
                    <x-site.icon name="phone" :size="20" />{{ config('shop.tel') }}
                </a>
            </li>
        </ul>
    </div>
</nav>
