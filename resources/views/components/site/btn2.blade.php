@props([
    'href' => null,
    'big',
    'small' => null,
    'variant' => 'red',
    'size' => null,
    'bubble' => null,
    'icon' => null,
    'num' => false,
    'block' => false,
    'type' => 'button',
])
{{--
    2段ボタン（案B：上に小さな説明・下に大きな文字、右に丸い矢印。上に吹き出しバッジを付けられる）。
    そのページのいちばん大事な行動（来店予約・在庫確認・無料査定・電話）に使う。普通のボタンは c-btn。

    使い方:
        <x-site.btn2 :href="route('contact.index', ['purpose' => 'visit'])" small="お問い合わせは無料です" big="在庫確認・来店予約" />
        <x-site.btn2 href="…" variant="black" small="電話で相談する（11:00〜21:00）" :big="config('shop.tel')" icon="phone" num
                     aria-label="電話をかける 06-4960-8765" />
        <x-site.btn2 href="…" size="xl" bubble="全車 支払総額で表示" small="写真・支払総額つきで掲載中" big="掲載中の車を見る（4台）" block />
        <x-site.btn2 type="submit" big="この内容で送信する" />      … href がないときは <button>

    href:    リンク先（なければ button）
    big:     大きな文字（行動を表す動詞で終える）
    small:   上の小さな説明（任意）
    variant: red（既定）| black | yellow
    size:    null | xl（高さ78px・文字20px）
    bubble:  上に出す吹き出しバッジの文言（任意）
    icon:    大きな文字の左に置くアイコン名（x-site.icon）
    num:     大きな文字を数字の書体（Oswald）にする（電話番号など）
    block:   横幅いっぱいにする
    type:    href がないときの button の type

    出力: <a class="c-btn2 c-btn2--{variant} [c-btn2--xl] [c-btn2--block] [c-btn2--has-bubble]">
          [<span class="c-btn2__bubble">] [<span class="c-btn2__small">] <span class="c-btn2__big [c-btn2__big--num]">…</span>
          <svg class="c-icon c-btn2__arrow"></a>
--}}
@php
    $classes = 'c-btn2 c-btn2--'.$variant
        .($size === 'xl' ? ' c-btn2--xl' : '')
        .($block ? ' c-btn2--block' : '')
        .(filled($bubble) ? ' c-btn2--has-bubble' : '');
@endphp
@if ($href)
<a {{ $attributes->merge(['class' => $classes]) }} href="{{ $href }}">
@else
<button {{ $attributes->merge(['class' => $classes, 'type' => $type]) }}>
@endif
    @if (filled($bubble))<span class="c-btn2__bubble">{{ $bubble }}</span>@endif
    @if (filled($small))<span class="c-btn2__small">{{ $small }}</span>@endif
    <span class="c-btn2__big{{ $num ? ' c-btn2__big--num' : '' }}">@if ($icon)<x-site.icon :name="$icon" />@endif{{ $big }}</span>
    <x-site.icon name="chevron-right" class="c-btn2__arrow" />
@if ($href)
</a>
@else
</button>
@endif
