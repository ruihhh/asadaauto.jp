@props([])
{{--
    PC の右端に縦に並べる固定ボタン（1200px 以上。モックアップ案B と同じ）。layouts/site から1回だけ呼ぶ。
    本文に重ならないよう、site.css が main・ご相談帯・フッターの l-container の右の余白（--content-pr）を、画面の右の空きが足りない幅（1200〜1400px 前後）でだけ広げる。
    ［在庫を探す（黒）］［来店予約（赤）］［店舗案内（濃い灰）］。

    使い方: <x-site.side-fix />（props なし）
    出力: <nav class="l-sidefix" aria-label="すぐに使うメニュー"><a class="l-sidefix__link l-sidefix__link--{black|red|gray}">
          <svg class="c-icon"><span class="l-sidefix__label"><span>在庫を</span><span>探す</span></span></a>…</nav>
--}}
@php
    $items = [
        ['lines' => ['在庫を', '探す'], 'icon' => 'search', 'url' => route('cars.index'), 'tone' => 'black'],
        ['lines' => ['来店', '予約'], 'icon' => 'calendar', 'url' => route('contact.index', ['purpose' => 'visit']), 'tone' => 'red'],
        ['lines' => ['店舗', '案内'], 'icon' => 'store', 'url' => route('store'), 'tone' => 'gray'],
    ];
@endphp
<nav {{ $attributes->merge(['class' => 'l-sidefix']) }} aria-label="すぐに使うメニュー">
    @foreach ($items as $item)
        <a class="l-sidefix__link l-sidefix__link--{{ $item['tone'] }}" href="{{ $item['url'] }}">
            <x-site.icon :name="$item['icon']" :size="26" />
            <span class="l-sidefix__label">@foreach ($item['lines'] as $line)<span>{{ $line }}</span>@endforeach</span>
        </a>
    @endforeach
</nav>
