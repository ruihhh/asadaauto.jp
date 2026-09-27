@props([])
{{--
    スマホのクイックリンク（アイコン4つ。959px 以下だけ）。layouts/site が営業状況バーの下に1回だけ出す。
    ［在庫を探す］［買取査定］［ローン］［店舗案内］。いま表示中のページの項目には is-active と aria-current="page" を付ける。
    ［ローン］はトップのお支払いシミュレーション（x-site.loan-sim）の区画（id="loan"）へ。

    使い方: <x-site.sp-quick />（props なし）
    出力: <nav class="l-spquick" aria-label="よく使うページ"><ul class="l-spquick__list">
          <li><a class="l-spquick__link [is-active]">（アイコン）在庫を探す</a></li>…</ul></nav>
--}}
@php
    $items = [
        ['label' => '在庫を探す', 'icon' => 'search', 'url' => route('cars.index'), 'active' => request()->routeIs('cars.index', 'cars.show')],
        ['label' => '買取査定', 'icon' => 'yen', 'url' => route('buy.index'), 'active' => request()->routeIs('buy.*')],
        ['label' => 'ローン', 'icon' => 'calc', 'url' => route('home').'#loan', 'active' => false],
        ['label' => '店舗案内', 'icon' => 'store', 'url' => route('store'), 'active' => request()->routeIs('store')],
    ];
@endphp
<nav {{ $attributes->merge(['class' => 'l-spquick']) }} aria-label="よく使うページ">
    <ul class="l-spquick__list">
        @foreach ($items as $item)
            <li>
                <a class="l-spquick__link{{ $item['active'] ? ' is-active' : '' }}" href="{{ $item['url'] }}" @if ($item['active']) aria-current="page" @endif>
                    <x-site.icon :name="$item['icon']" :size="26" />{{ $item['label'] }}
                </a>
            </li>
        @endforeach
    </ul>
</nav>
