@props([
    'items' => [],
    'jsonld' => false,
])
{{--
    よくある質問（<details><summary>。JavaScript は使わない）。画面の文面と FAQPage の構造化データを同じ items から出す。

    使い方:
        <x-site.faq :items="[
            ['q' => '支払総額には何が含まれますか？', 'a' => '税金・自賠責保険料・登録などの手続き費用を含みます。'],
            …
        ]" jsonld />

    items:  [['q' => 質問, 'a' => 答え（プレーンテキスト。改行は <br> にする）], …]
    jsonld: true のとき FAQPage の JSON-LD を @push('structured_data') で出す（1ページに1回だけ使う）

    出力: <div class="c-faq"><details class="c-faq__item"><summary class="c-faq__q"><span class="c-faq__mark">Q</span>
          <span class="c-faq__qtext">…</span>▼</summary><div class="c-faq__a"><span class="c-faq__mark c-faq__mark--a">A</span>
          <div class="c-faq__atext">…</div></div></details>…</div>
--}}
@php
    $items = array_values(array_filter($items, fn ($item) => filled($item['q'] ?? null) && filled($item['a'] ?? null)));
    $faqSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(fn (array $item): array => [
            '@type' => 'Question',
            'name' => $item['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']],
        ], $items),
    ];
@endphp
<div {{ $attributes->merge(['class' => 'c-faq']) }}>
    @foreach ($items as $item)
        <details class="c-faq__item">
            <summary class="c-faq__q">
                <span class="c-faq__mark" aria-hidden="true">Q</span>
                <span class="c-faq__qtext">{{ $item['q'] }}</span>
                <x-site.icon name="chevron-down" class="c-faq__chev" />
            </summary>
            <div class="c-faq__a">
                <span class="c-faq__mark c-faq__mark--a" aria-hidden="true">A</span>
                <div class="c-faq__atext">{!! nl2br(e($item['a'])) !!}</div>
            </div>
        </details>
    @endforeach
</div>
@if ($jsonld && $items !== [])
    @push('structured_data')
        <script type="application/ld+json">{!! json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_PRETTY_PRINT) !!}</script>
    @endpush
@endif
