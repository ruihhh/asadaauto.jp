@props([
    'steps' => [],
    'title' => null,
    'headingLevel' => 3,
])
{{--
    メダル型のイラストで見せる短い手順（案B：赤い輪のメダルにイラストと番号。最後のメダルは黄の輪）。
    お問い合わせの「送信後の流れ」や、完了ページ（x-site.thanks）の「このあとの流れ」に使う。3つを想定。
    スマホは縦に並べて赤い点線でつなぎ、600px 以上は横並び＋赤い矢印。灰の斜線地の枠の中に置く。
    道路の上に大きく並べる「ご購入の流れ」は x-site.flow。

    使い方:
        <x-site.medal-steps title="送信後の流れ" :heading-level="4" :steps="[
            ['title' => '担当者が内容を確認します', 'illust' => 'p-explain'],
            ['title' => '電話またはメールでご連絡します', 'text' => 'ご記入の連絡先にご連絡します。', 'illust' => 'mail-check'],
            ['title' => 'ご来店・お見積もりなどをご案内します', 'illust' => 'f-store'],
        ]" />

    steps:        [['title' => 見出し, 'text' => 説明（任意。HtmlString 可）, 'illust' => x-site.illust の name], …]
    title:        枠の見出し（任意。h3.c-subhead など）
    headingLevel: 見出しのレベル（2〜4。置き場所の見出しの階層に合わせる）

    出力: <div class="c-medal-steps"><h3 class="c-subhead c-medal-steps__title">…</h3><ol class="c-medal-steps__list" role="list">
          <li class="c-medal-steps__item"><span class="c-medal-steps__medal"><svg class="c-illust"><span class="c-medal-steps__num" aria-hidden="true">1</span></span>
          <span class="c-medal-steps__body"><span class="c-medal-steps__label">…</span><span class="c-medal-steps__text">…</span></span></li>…</ol></div>
--}}
@php
    $tag = 'h'.min(max((int) $headingLevel, 2), 4);
@endphp
<div {{ $attributes->merge(['class' => 'c-medal-steps']) }}>
    @if (filled($title))
        <{{ $tag }} class="c-subhead c-medal-steps__title">{{ $title }}</{{ $tag }}>
    @endif
    <ol class="c-medal-steps__list" role="list">
        @foreach ($steps as $step)
            <li class="c-medal-steps__item">
                <span class="c-medal-steps__medal"><x-site.illust :name="$step['illust'] ?? 'f-search'" /><span class="c-medal-steps__num" aria-hidden="true">{{ $loop->iteration }}</span></span>
                <span class="c-medal-steps__body">
                    <span class="c-medal-steps__label">{{ $step['title'] ?? '' }}</span>
                    @if (filled($step['text'] ?? null))
                        <span class="c-medal-steps__text">{{ $step['text'] }}</span>
                    @endif
                </span>
            </li>
        @endforeach
    </ol>
</div>
