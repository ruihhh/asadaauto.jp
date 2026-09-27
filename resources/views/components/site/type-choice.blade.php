@props([
    'name',
    'value',
    'label',
    'illust' => null,
    'count' => null,
    'checked' => false,
    'type' => 'radio',
])
{{--
    ボディタイプの選択タイル（案B：彩色のイラスト＋名前＋台数。選ぶと赤の枠と右上に赤いチェック）。div.c-type-choices（2列）に並べる。
    input は見た目だけ隠す（キーボードで選べる）。

    使い方:
        <div class="c-type-choices" role="radiogroup" aria-labelledby="q-step1">
            @foreach ($bodyTypes as $type => $n)
                <x-site.type-choice name="body_type" :value="$type" :label="\App\Support\CarText::bodyType($type)"
                    :illust="\App\Support\CarText::bodyIllust($type)" :count="$n.'台'" :checked="request('body_type') === $type" />
            @endforeach
            <x-site.type-choice name="body_type" value="" label="すべて" illust="all" :count="$total.'台'" :checked="! request('body_type')" />
        </div>

    name / value: input の name・value
    label:   表示名
    illust:  x-site.illust の name（省略時は label から CarText::bodyIllust() で選ぶ）
    count:   台数の文字（「2台」など。実数だけ。任意）
    checked: 選ばれているか
    type:    radio（既定）| checkbox

    出力: <label class="c-type-choice"><input class="c-type-choice__input" type="radio"><svg class="c-illust c-type-choice__ill">
          <span class="c-type-choice__text"><span class="c-type-choice__name">SUV</span><span class="c-type-choice__count">2台</span></span></label>
--}}
<label {{ $attributes->merge(['class' => 'c-type-choice']) }}>
    <input class="c-type-choice__input" type="{{ $type === 'checkbox' ? 'checkbox' : 'radio' }}" name="{{ $name }}" value="{{ $value }}" @checked($checked)>
    <x-site.illust :name="$illust ?? \App\Support\CarText::bodyIllust($label)" class="c-type-choice__ill" />
    <span class="c-type-choice__text">
        <span class="c-type-choice__name">{{ $label }}</span>
        @if (filled($count))<span class="c-type-choice__count">{{ $count }}</span>@endif
    </span>
</label>
