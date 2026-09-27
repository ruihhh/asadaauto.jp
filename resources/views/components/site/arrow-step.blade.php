@props([
    'no' => null,
    'title' => null,
    'illust' => null,
    'go' => false,
    'split' => false,
    'headingId' => null,
])
{{--
    STEP の矢印パネル（案B：STEP.1 → 2 → 3 を矢印の形でつなぎ、最後に赤い結果パネル）。検索フォームなどで使う汎用の部品。
    div.c-arrow-steps（または form.c-arrow-steps）の中に並べる。1199px までは縦に積み（下向きの矢印。600px 以上は結果パネルが「件数＋ボタン」の横並び、960px 以上はボディタイプの選択が4列）、1200px 以上は横並び（右向きの矢印）。
    1200px 以上の横並びでは1枚目（検索のボディタイプの選択用）が広くなる。手順の図などで全部を同じ幅にするときは c-arrow-steps--even を付ける。
    中身（選ぶ欄・ボタン）は slot に書く。STEP. の英字は読み上げず、番号と見出しだけを読み上げる。

    使い方:
        <form class="c-arrow-steps" method="GET" action="{{ route('cars.index') }}">
            <x-site.arrow-step :no="1" title="ボディタイプを選ぶ" heading-id="q-step1">
                <div class="c-type-choices" role="radiogroup" aria-labelledby="q-step1">…x-site.type-choice…</div>
            </x-site.arrow-step>
            <x-site.arrow-step :no="2" title="予算を決める" illust="coins" split>
                <div><label class="c-arrow-step__label" for="q-price">支払総額の上限</label><select class="c-field__input c-field__input--select" id="q-price" name="price_max">…</select></div>
            </x-site.arrow-step>
            <x-site.arrow-step :no="3" title="走行距離" illust="meter" split>…</x-site.arrow-step>
            <x-site.arrow-step go>
                <p class="c-arrow-step__count-label">条件に合う車</p>
                <p class="c-arrow-step__count"><span x-text="hits">4</span><small>台</small></p>
                <button class="c-btn c-btn--yellow" type="submit"><x-site.icon name="search" />この条件で探す</button>
            </x-site.arrow-step>
        </form>

    no:        STEP の番号（任意）
    title:     パネルの見出し（任意。HtmlString で u-nowrap の span を複数渡してもよい：見出しの文字は1つの span にまとめて出す）
    illust:    見出しの下に置くイラスト（x-site.illust の name。coins / meter など）
    go:        最後の赤い結果パネル（件数・検索ボタン）
    split:     スマホでイラストを左・中身を右に置く
    headingId: 見出しの id（ラジオのまとまりの aria-labelledby に使う）

    出力: <div class="c-arrow-step [c-arrow-step--go] [c-arrow-step--split]"><p class="c-arrow-step__head" id>
          <span class="c-arrow-step__no"><span aria-hidden="true">STEP.</span><b>1</b></span><span class="c-arrow-step__title">見出し</span></p>
          <svg class="c-illust c-arrow-step__ill">（slot）</div>
--}}
<div {{ $attributes->merge(['class' => 'c-arrow-step'.($go ? ' c-arrow-step--go' : '').($split ? ' c-arrow-step--split' : '')]) }}>
    @if (filled($title))
        <p class="c-arrow-step__head" @if (filled($headingId)) id="{{ $headingId }}" @endif>
            @if ($no !== null)<span class="c-arrow-step__no"><span aria-hidden="true">STEP.</span><b>{{ $no }}</b></span>@endif
            <span class="c-arrow-step__title">{{ $title }}</span>
        </p>
    @endif
    @if (filled($illust))
        <x-site.illust :name="$illust" class="c-arrow-step__ill" />
    @endif
    {{ $slot }}
</div>
