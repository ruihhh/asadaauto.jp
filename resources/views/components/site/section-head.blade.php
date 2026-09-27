@props([
    'title',
    'en' => null,
    'lead' => null,
    'id' => null,
    'level' => 2,
    'center' => false,
])
{{--
    セクション見出し（案B：赤い縦線＋日本語の見出し＋小さな英字の飾り）。右側に件数・「◯◯を見る」を並べられる（slot）。

    使い方:
        <x-site.section-head id="stock-title" title="いま掲載中の車" en="STOCK">
            <p class="c-count-pill">全<b>4</b>台</p>
            <a class="c-more" href="{{ route('cars.index') }}">在庫一覧を見る<x-site.icon name="chevron-right" /></a>
        </x-site.section-head>
        <x-site.section-head title="条件から在庫を探す" en="SEARCH" lead="3つを選ぶだけ。…" />
        <x-site.section-head :title="new \Illuminate\Support\HtmlString('お支払いシミュレーション<small>（計算例）</small>')" en="LOAN" />

    title:  見出しの文言（HtmlString で <small> などを入れてよい）
    en:     英字の飾り（任意。aria-hidden で読み上げない。意味は日本語の見出しで伝える）
    lead:   見出しの下の説明（任意）
    id:     見出しの id（section の aria-labelledby に使う）
    level:  見出しのレベル（2〜4）
    center: 中央寄せ（縦線の代わりに下に赤い線）
    slot:   見出しの右に置くもの（c-count-pill・c-more など）

    出力: <div class="c-section-head [c-section-head--center]"><div class="c-section-head__row">
          <h2 class="c-section-title [c-section-title--center]">見出し<span class="c-section-title__en" aria-hidden="true">STOCK</span></h2>（slot）</div>
          <p class="c-section-lead">…</p></div>
--}}
@php
    $tag = 'h'.min(max((int) $level, 2), 4);
@endphp
<div {{ $attributes->merge(['class' => 'c-section-head'.($center ? ' c-section-head--center' : '')]) }}>
    <div class="c-section-head__row">
        <{{ $tag }}{!! filled($id) ? ' id="'.e($id).'"' : '' !!} class="c-section-title{{ $center ? ' c-section-title--center' : '' }}"><span>{{ $title }}</span>@if (filled($en))<span class="c-section-title__en" aria-hidden="true" lang="en">{{ $en }}</span>@endif</{{ $tag }}>
        {{ $slot }}
    </div>
    @if (filled($lead))
        <p class="c-section-lead">{{ $lead }}</p>
    @endif
</div>
