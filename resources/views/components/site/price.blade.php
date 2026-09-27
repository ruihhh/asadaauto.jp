@props([
    'car',
    'size' => 'card',
    'note' => true,
])
{{--
    価格ブロック（案B：「支払総額／税込」の赤いラベル＋数字の書体の大きな赤い数字）。支払総額をいちばん大きく、
    その下に車両本体価格（と諸費用）、条件注記を価格のすぐ下に置く。

    使い方:
        <x-site.price :car="$car" />                              … カード用（note=true なら注記も出す）
        <x-site.price :car="$car" size="card" :note="false" />    … 車両カードの中（注記は一覧側でまとめて出す）
        <x-site.price :car="$car" size="detail" />                … 詳細ページ（赤い太枠の箱。内訳の積み上げバーと式・注記は箱の中）

    car:  App\Models\Car
    size: card | detail
    note: 条件注記（CarText::priceNote()＋「価格は◯年◯月◯日時点のものです」）を出すか

    出力:
        card   <div class="c-price c-price--card"><p class="c-price__total"><span class="c-price__label">支払総額<small class="c-price__tax">（税込）</small></span>
               <span class="c-price__value">335.0</span><span class="c-price__unit">万円</span></p>
               <p class="c-price__base">車両本体価格 300.0万円＋諸費用 35.0万円</p><p class="c-price__note">…</p></div>
               （「（」「）」は読み上げ用。見た目はラベルの2行目に「税込」）
        detail <div class="c-price c-price--detail"><p class="c-price__total"><span class="c-price__label c-price__heading">支払総額（税込）</span>
               <span class="c-price__value">335.0</span><span class="c-price__unit">万円</span></p><p class="c-price__yen">3,350,000円</p>
               x-site.price-breakdown（積み上げバー＋式。本体価格が未入力なら「車両本体価格はお問い合わせください」）<p class="c-price__note">…</p></div>
        応談   <p class="c-price__ask">価格はお問い合わせください（応談）</p>（price が未入力なだけのときは「（応談）」を付けない）
        本体価格が未入力：card は「車両本体価格はお問い合わせください」、detail も同じ文言（諸費用・バーは出さない）
--}}
@php
    use App\Support\CarText;

    $ask = $car->price_negotiable || $car->price === null;
    // 「（応談）」は応談の車だけに付ける（価格が未入力なだけの車には付けない）
    $askLabel = $car->price_negotiable ? '価格はお問い合わせください（応談）' : '価格はお問い合わせください';
    $parts = $ask ? null : CarText::priceParts((int) $car->price);
    $base = $car->base_price !== null ? (int) $car->base_price : null;
    $fees = CarText::fees($car);
    $asOf = CarText::priceAsOf($car);
@endphp
<div {{ $attributes->merge(['class' => 'c-price c-price--'.$size]) }}>
    @if ($ask)
        <p class="c-price__ask">{{ $askLabel }}</p>
    @elseif ($size === 'detail')
        <p class="c-price__total">
            <span class="c-price__label c-price__heading">支払総額（税込）</span><span class="c-price__value">{{ $parts['value'] }}</span><span class="c-price__unit">{{ $parts['unit'] }}</span>
        </p>
        @if ($parts['unit'] === '万円')
            <p class="c-price__yen">{{ CarText::yen((int) $car->price) }}</p>
        @endif
        <x-site.price-breakdown :car="$car" />
    @else
        <p class="c-price__total">
            <span class="c-price__label">支払総額<small class="c-price__tax"><span class="u-visually-hidden">（</span>税込<span class="u-visually-hidden">）</span></small></span><span class="c-price__value">{{ $parts['value'] }}</span><span class="c-price__unit">{{ $parts['unit'] }}</span>
        </p>
        @if ($base !== null && $fees !== null)
            {{-- 幅の狭いカードでは「＋」の後ろでだけ折り返す（「35.0／万円」のような途中の改行を防ぐ） --}}
            <p class="c-price__base"><span class="u-nowrap">車両本体価格 {{ CarText::price($base) }}</span>＋<span class="u-nowrap">諸費用 {{ CarText::price($fees) }}</span></p>
        @elseif ($base !== null)
            <p class="c-price__base">車両本体価格 {{ CarText::price($base) }}</p>
        @else
            <p class="c-price__base">車両本体価格はお問い合わせください</p>
        @endif
    @endif
    @if ($note && ! $ask)
        <p class="c-price__note">{{ CarText::priceNote() }}@if ($asOf !== null)<br>{{ $asOf }}@endif</p>
    @endif
</div>
