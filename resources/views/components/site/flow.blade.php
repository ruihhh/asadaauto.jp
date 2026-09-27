@props([
    'steps' => [],
    'headingLevel' => 3,
])
{{--
    ご購入の流れ（案B：道路（黄色の破線）の上に STEP の札とメダル型のイラスト）。PCは横4列、600〜959px は2列、スマホは縦（メダルの間を縦の道路でつなぐ）。
    最後のステップのメダルは黄の輪。ol で作り、STEP. の英字は読み上げない（番号は ol の順番で伝わる）。

    使い方:
        <x-site.flow :steps="[
            ['title' => '在庫を探す・問い合わせ', 'text' => '気になる車が見つかったら、電話かフォームで在庫確認を。見積もりは無料です。', 'illust' => 'f-search'],
            ['title' => '来店・見学・試乗', 'text' => '…', 'illust' => 'f-store'],
            ['title' => 'ご契約・書類', 'text' => '…', 'illust' => 'f-contract'],
            ['title' => '納車', 'text' => '…', 'illust' => 'f-key'],
        ]" />

    steps:        [['title' => 見出し, 'text' => 説明（任意）, 'illust' => x-site.illust の name（任意）], …]。
                  PC は4つで1行（既定）。3つ・5つのときは c-flow--3 / c-flow--5 が自動で付き、PC でその数で1行に並ぶ（6つ以上は4列で折り返す）
    headingLevel: 各ステップの見出しのレベル（2〜4）

    出力: <ol class="c-flow [c-flow--3|c-flow--5]"><li class="c-flow__step"><p class="c-flow__no"><span aria-hidden="true">STEP.</span><b>1</b></p>
          <div class="c-flow__medal"><svg class="c-illust"></div><h3 class="c-flow__title">…</h3><p class="c-flow__text">…</p></li>…</ol>
--}}
@php
    $tag = 'h'.min(max((int) $headingLevel, 2), 4);
    $count = count($steps);
    $countClass = in_array($count, [3, 5], true) ? ' c-flow--'.$count : '';
@endphp
<ol {{ $attributes->merge(['class' => 'c-flow'.$countClass]) }}>
    @foreach ($steps as $step)
        <li class="c-flow__step">
            <p class="c-flow__no"><span aria-hidden="true">STEP.</span><b>{{ $loop->iteration }}</b></p>
            <div class="c-flow__medal">
                <x-site.illust :name="$step['illust'] ?? 'f-search'" />
            </div>
            <{{ $tag }} class="c-flow__title">{{ $step['title'] ?? '' }}</{{ $tag }}>
            @if (filled($step['text'] ?? null))
                <p class="c-flow__text">{{ $step['text'] }}</p>
            @endif
        </li>
    @endforeach
</ol>
