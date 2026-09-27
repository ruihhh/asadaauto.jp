@props([
    'car',
    'bar' => true,
])
{{--
    価格の内訳（案B：車両本体価格（濃い灰）と諸費用（黄）の積み上げバー＋「300.0万円＋35.0万円＝335.0万円」の式）。
    x-site.price の size=detail の中で使う。単独で置いてもよい（お問い合わせの対象車など）。
    値はすべて確定額（諸費用＝支払総額−車両本体価格。「約」は付けない）。

    使い方:
        <x-site.price-breakdown :car="$car" />
        <x-site.price-breakdown :car="$car" :bar="false" />   … 式だけ

    car: App\Models\Car
    bar: 積み上げバーを出すか

    出し分け:
        ・価格が応談・未入力                     … 何も出さない
        ・車両本体価格が未入力                   … <p class="c-price-breakdown__ask">車両本体価格はお問い合わせください</p>（バー・式は出さない）
        ・諸費用が出せない（本体価格＞支払総額など）… <p class="c-price-breakdown__ask">車両本体価格 300.0万円</p>
        ・それ以外                               … バー＋式。読み上げには「車両本体価格 300.0万円＋諸費用 35.0万円＝支払総額 335.0万円」の1文を渡す
          （バーと式は aria-hidden）

    出力: <div class="c-price-breakdown"><p class="u-visually-hidden">車両本体価格 …＝支払総額 …</p>
          <span class="c-price-breakdown__bar" aria-hidden="true"><svg viewBox="0 0 1000 18" preserveAspectRatio="none">
          <rect class="c-price-breakdown__seg c-price-breakdown__seg--body"><rect class="… --fee"></svg></span>
          <div class="c-price-breakdown__formula" aria-hidden="true"><div class="c-price-breakdown__term c-price-breakdown__term--body"><span>車両本体価格</span><b>300.0<small>万円</small></b></div>
          <span class="c-price-breakdown__op">＋</span> … ＝ <div class="… --total">…</div></div></div>
--}}
@php
    use App\Support\CarText;

    $ask = $car->price_negotiable || $car->price === null;
    $total = $ask ? null : (int) $car->price;
    $base = $car->base_price !== null ? (int) $car->base_price : null;
    $fees = $ask ? null : CarText::fees($car);

    $term = function (?int $yen): array {
        $parts = CarText::priceParts((int) $yen);

        return ['value' => $parts['value'], 'unit' => $parts['unit']];
    };

    // バーの幅（viewBox 0〜1000。あいだに 6 のすき間）
    $gap = 6;
    $bodyW = $total ? round(($base ?? 0) / $total * 1000, 1) : 0;
    $feeW = $total ? max(round(1000 - $bodyW, 1), 0) : 0;
@endphp
@if (! $ask)
    @if ($base === null)
        <p {{ $attributes->merge(['class' => 'c-price-breakdown__ask']) }}>車両本体価格はお問い合わせください</p>
    @elseif ($fees === null)
        <p {{ $attributes->merge(['class' => 'c-price-breakdown__ask']) }}>車両本体価格 {{ CarText::price($base) }}</p>
    @else
        @php
            $b = $term($base);
            $f = $term($fees);
            $t = $term($total);
        @endphp
        <div {{ $attributes->merge(['class' => 'c-price-breakdown']) }}>
            <p class="u-visually-hidden">車両本体価格 {{ CarText::price($base) }}＋諸費用 {{ CarText::price($fees) }}＝支払総額 {{ CarText::price($total) }}</p>
            @if ($bar)
                <span class="c-price-breakdown__bar" aria-hidden="true"><svg viewBox="0 0 1000 18" preserveAspectRatio="none" focusable="false"><rect class="c-price-breakdown__seg c-price-breakdown__seg--body" x="0" y="0" width="{{ max($bodyW - $gap / 2, 0) }}" height="18"/><rect class="c-price-breakdown__seg c-price-breakdown__seg--fee" x="{{ $bodyW + $gap / 2 }}" y="0" width="{{ max($feeW - $gap / 2, 0) }}" height="18"/></svg></span>
            @endif
            <div class="c-price-breakdown__formula" aria-hidden="true">
                <div class="c-price-breakdown__term c-price-breakdown__term--body"><span>車両本体価格</span><b>{{ $b['value'] }}<small>{{ $b['unit'] }}</small></b></div>
                <span class="c-price-breakdown__op">＋</span>
                <div class="c-price-breakdown__term c-price-breakdown__term--fee"><span>諸費用</span><b>{{ $f['value'] }}<small>{{ $f['unit'] }}</small></b></div>
                <span class="c-price-breakdown__op">＝</span>
                <div class="c-price-breakdown__term c-price-breakdown__term--total"><span>支払総額</span><b>{{ $t['value'] }}<small>{{ $t['unit'] }}</small></b></div>
            </div>
        </div>
    @endif
@endif
