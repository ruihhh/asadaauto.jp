@props([
    'href' => null,
    'icon' => 'check',
    'tone' => 'red',
    'count' => null,
    'current' => false,
])
{{--
    チップ（案B：丸いアイコン＋文字＋台数の丸帯。「特集から探す」などの絞り込みリンク）。並べるときは ul.c-chips（スマホで2列のタイルにするなら c-chips--grid）。

    使い方:
        <ul class="c-chips c-chips--grid">
            <li><x-site.chip :href="route('cars.index', ['featured' => 1])" icon="star" tone="yellow" count="2台">店長おすすめ</x-site.chip></li>
            <li><x-site.chip href="…" icon="leaf" tone="green" count="2台">ハイブリッド</x-site.chip></li>
        </ul>

    href:    リンク先（なければ span で出す）
    icon:    x-site.icon の name
    tone:    アイコンの丸の色 red（既定）| yellow | green | black
    count:   右の丸帯の文字（「2台」など。実数だけ）
    current: いま選ばれている絞り込み（aria-current="page"）
    slot:    文字

    出力: <a class="c-chip" [aria-current="page"]><span class="c-chip__ic c-chip__ic--{tone}"><svg></span><span>文字</span><span class="c-chip__n">2台</span></a>
--}}
@php
    $tag = $href ? 'a' : 'span';
@endphp
<{{ $tag }} {{ $attributes->merge(['class' => 'c-chip']) }} @if ($href) href="{{ $href }}" @endif @if ($current) aria-current="page" @endif>
    <span class="c-chip__ic c-chip__ic--{{ $tone }}" aria-hidden="true"><x-site.icon :name="$icon" /></span>
    <span>{{ $slot }}</span>
    @if (filled($count))<span class="c-chip__n">{{ $count }}</span>@endif
</{{ $tag }}>
