@props([
    'num',
    'unit' => null,
    'top' => null,
    'bottom' => null,
    'href' => null,
    'label' => null,
    'size' => null,
    'variant' => 'red',
])
{{--
    丸いバッジ（案B：赤い丸に黄の大きな数字。「ただいま 4台 掲載中」）。黄の版は「査定 無料」などの約束に使う。
    数字は必ず実数（DB の件数など）を渡す。根拠のない数字・最上級表現は入れない。

    使い方:
        <x-site.round-badge top="ただいま" :num="$count" unit="台" bottom="掲載中" size="lg"
                            :href="route('cars.index')" :label="'ただいま'.$count.'台掲載中。掲載中の車を見る'" />
        <x-site.round-badge variant="yellow" top="査定" num="無料" />

    num:     大きな文字（数字は数字の書体。黄の版では日本語の短い言葉も可）
    unit:    数字の後ろの小さな文字（「台」など）
    top / bottom: 上下の小さな文字
    href:    リンクにするとき（label で読み上げ名を必ず付ける）
    label:   読み上げ名（href があるとき aria-label に使う）
    size:    null（110px）| lg（172px）
    variant: red（既定）| yellow

    出力: <a|span class="c-round-badge [c-round-badge--lg] [c-round-badge--yellow]">
          <span class="c-round-badge__top">ただいま</span><span class="c-round-badge__num">4<small>台</small></span><span class="c-round-badge__bottom">掲載中</span></a>
--}}
@php
    $classes = 'c-round-badge'.($size === 'lg' ? ' c-round-badge--lg' : '').($variant === 'yellow' ? ' c-round-badge--yellow' : '');
    $tag = $href ? 'a' : 'span';
@endphp
<{{ $tag }} {{ $attributes->merge(['class' => $classes]) }} @if ($href) href="{{ $href }}" @if (filled($label)) aria-label="{{ $label }}" @endif @endif>
    @if (filled($top))<span class="c-round-badge__top">{{ $top }}</span>@endif
    <span class="c-round-badge__num">{{ $num }}@if (filled($unit))<small>{{ $unit }}</small>@endif</span>
    @if (filled($bottom))<span class="c-round-badge__bottom">{{ $bottom }}</span>@endif
</{{ $tag }}>
