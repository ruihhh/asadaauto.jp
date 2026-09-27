@props([
    'items' => [],
])
{{--
    装備のアイコン一覧（案B：黒い丸に黄のアイコン＋装備名）。スマホ3列・600px以上4列・960px以上6列。
    アイコンは装備名から CarText::equipmentIcon() で選ぶ（対応するものがなければチェックの丸）。
    装備名は CarText::breakable() で折り返してよい位置に <wbr> を入れ、スマホの3列でも「衝突被害軽減｜ブレーキ」「ドライブ｜レコーダー」のように語の区切りで改行する。

    使い方:
        <x-site.equip-list :items="\App\Support\CarText::equipmentHighlights($car)" />

    items: 装備の表示名の配列（CarText::equipmentHighlights() など）

    出力: <ul class="c-equips"><li class="c-equip"><span class="c-equip__ic"><svg class="c-icon c-icon--eq-brake"></span>
          <span class="c-equip__t">衝突被害軽減<wbr>ブレーキ</span></li>…</ul>（items が空なら何も出さない）
--}}
@if ($items !== [])
    <ul {{ $attributes->merge(['class' => 'c-equips']) }}>
        @foreach ($items as $item)
            <li class="c-equip">
                <span class="c-equip__ic" aria-hidden="true"><x-site.icon :name="\App\Support\CarText::equipmentIcon($item)" /></span>
                <span class="c-equip__t">{{ \App\Support\CarText::breakable($item) }}</span>
            </li>
        @endforeach
    </ul>
@endif
