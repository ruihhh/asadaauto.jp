@props([])
{{--
    営業状況バー（スマホだけ。ヘッダーのすぐ下、灰の帯）。layouts/site から1回だけ呼ぶ。
    1行目：本日の営業状況のピル（x-site.open-status の badge）＋「店舗案内 ›」、2行目：住所（ピンのアイコン付き）。
    店舗案内ページ（route('store')）では、同じページへのリンクにならないよう「営業時間 ›」（#hours）にする。

    使い方: <x-site.openbar />（props なし）
    出力: <div class="l-openbar"><div class="l-container l-openbar__inner"><p class="l-openbar__status">x-site.open-status（badge）</p>
          <a class="l-openbar__link">店舗案内 ›</a><p class="l-openbar__addr">（ピン）住所</p></div></div>
--}}
@php
    $onStore = request()->routeIs('store');
@endphp
<div class="l-openbar">
    <div class="l-container l-openbar__inner">
        <p class="l-openbar__status">
            <x-site.open-status variant="badge" />
        </p>
        @if ($onStore)
            <a class="l-openbar__link" href="#hours">営業時間 ›</a>
        @else
            <a class="l-openbar__link" href="{{ route('store') }}">店舗案内 ›</a>
        @endif
        <p class="l-openbar__addr"><x-site.icon name="map-pin" :size="16" /><x-site.address /></p>
    </div>
</div>
