@props([
    'title',
    'en' => null,
    'id' => null,
    'level' => 2,
    'lead' => null,
    'onLight' => false,
])
{{--
    斜めの赤帯の見出し（案B：黒の斜線地 l-section--dark の上に置く。上に黄の英字の飾り）。お約束などの目立たせたい区画に使う。

    使い方:
        <section class="l-section l-section--dark" id="promise" aria-labelledby="promise-title">
            <div class="l-container">
                <x-site.band-title id="promise-title" title="当店の4つのお約束" en="OUR PROMISE" lead="はじめての方にも、比べやすく・わかりやすく表示します。" />
                …
            </div>
        </section>

    title:   見出しの文言
    en:      英字の飾り（任意。aria-hidden で読み上げない）
    id:      見出しの id
    level:   見出しのレベル（2〜4）
    lead:    見出しの下の説明（任意。黒地の上の白い文字）
    onLight: 白・灰の地に置くとき（英字の飾りを赤にする）

    出力: <h2 class="c-band-title"><span class="c-band-title__en" aria-hidden="true">OUR PROMISE</span>
          <span class="c-slant c-slant--red">当店の4つのお約束</span></h2><p class="c-band-title-lead">…</p>
--}}
@php
    $tag = 'h'.min(max((int) $level, 2), 4);
@endphp
<{{ $tag }} {{ $attributes->merge(['class' => 'c-band-title'.($onLight ? ' c-band-title--on-light' : '')] + (filled($id) ? ['id' => $id] : [])) }}>@if (filled($en))<span class="c-band-title__en" aria-hidden="true" lang="en">{{ $en }}</span>@endif<span class="c-slant c-slant--red">{{ $title }}</span></{{ $tag }}>
@if (filled($lead))
    <p class="c-band-title-lead">{{ $lead }}</p>
@endif
