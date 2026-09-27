@props([
    'no',
    'title',
    'illust' => null,
    'headingLevel' => 3,
])
{{--
    お約束のカード1枚（案B：上に赤い帯の白いカード・左上に大きな番号・丸の中のイラスト）。ol.c-promises（黒の斜線地の上。PC 4列）に並べる。
    番号は飾り（ol の順番で伝わるので読み上げない）。本文は slot、下に置く小さな図は visual スロット。

    使い方:
        <ol class="c-promises">
            <x-site.promise :no="1" illust="p-total" title="支払総額で表示">
                表示価格は、税金・自賠責保険料・登録費用込みの支払総額です。
                <x-slot:visual>
                    <div class="c-promise__box"><span class="c-tag c-tag--body">車両本体</span>＋<span class="c-tag c-tag--fee">諸費用</span>＝<span class="c-tag c-tag--total">支払総額</span></div>
                    <p class="c-promise__note">※県外での登録・ご自宅への納車・ご希望のオプションは別途です。</p>
                </x-slot:visual>
            </x-site.promise>
            …
        </ol>

    no:     番号（1〜）。「01」の形で出す
    title:  見出し（HtmlString で改行位置を決めてよい）
    illust: x-site.illust の name（p-total / p-frame / p-shaken / p-explain など）
    headingLevel: 見出しのレベル（2〜4）

    出力: <li class="c-promise"><span class="c-promise__no" aria-hidden="true">01</span><svg class="c-illust c-promise__ill">
          <h3 class="c-promise__title">…</h3><div class="c-promise__text">…</div><div class="c-promise__visual">…</div></li>
--}}
@php
    $tag = 'h'.min(max((int) $headingLevel, 2), 4);
@endphp
<li {{ $attributes->merge(['class' => 'c-promise']) }}>
    <span class="c-promise__no" aria-hidden="true">{{ sprintf('%02d', (int) $no) }}</span>
    @if (filled($illust))
        <x-site.illust :name="$illust" class="c-promise__ill" />
    @endif
    <{{ $tag }} class="c-promise__title">{{ $title }}</{{ $tag }}>
    <div class="c-promise__text">{{ $slot }}</div>
    @isset($visual)
        <div class="c-promise__visual">{{ $visual }}</div>
    @endisset
</li>
