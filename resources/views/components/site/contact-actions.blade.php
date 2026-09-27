@props([
    'stockNo' => null,
    'purpose' => null,
    'layout' => 'row',
    'formLabel' => null,
])
{{--
    連絡手段セット：電話（番号入りの primary）＋LINE（設定されているときだけ）＋フォーム（secondary）。
    電話ボタンの下に営業時間と本日の営業状況を出す。stockNo があるときは「お電話では在庫番号 ◯◯ とお伝えください」を添える。

    使い方:
        <x-site.contact-actions />
        <x-site.contact-actions :stock-no="$car->stock_no" purpose="estimate" layout="stack" />
        <x-site.contact-actions purpose="search" form-label="探してほしい車を伝える" />

    stockNo:   在庫番号（フォームの ?stock_no= に付ける）
    purpose:   ご用件（フォームの ?purpose= に付ける。stock / estimate / visit / loan / trade / search / favorites / other）
    layout:    row（600px以上で横並び）| stack（常に縦積み）
    formLabel: フォームボタンの文言（省略時は「フォームで問い合わせる」、stockNo があれば「この車の在庫確認・見積もり（無料）」）

    出力: <div class="c-contact-actions c-contact-actions--{layout}"><div class="c-contact-actions__item">…</div>…</div>
--}}
@php
    $tel = config('shop.tel');
    $lineUrl = config('shop.line_url');
    $query = array_filter(['stock_no' => $stockNo, 'purpose' => $purpose], fn ($value) => filled($value));
    $formLabel ??= filled($stockNo) ? 'この車の在庫確認・見積もり（無料）' : 'フォームで問い合わせる';
@endphp
<div {{ $attributes->merge(['class' => 'c-contact-actions c-contact-actions--'.$layout]) }}>
    <div class="c-contact-actions__item">
        <a class="c-btn c-btn--primary c-btn--lg c-btn--block" href="{{ config('shop.tel_href') }}">
            <x-site.icon name="phone" />電話する {{ $tel }}
        </a>
        <p class="c-contact-actions__meta">営業時間 {{ \App\Support\BusinessHours::summaryHtml() }}</p>
        <x-site.open-status variant="line" />
        @if (filled($stockNo))
            <p class="c-contact-actions__stock">お電話では在庫番号 <strong>{{ $stockNo }}</strong> とお伝えください</p>
        @endif
    </div>
    @if ($lineUrl)
        <div class="c-contact-actions__item">
            <a class="c-btn c-btn--line c-btn--lg c-btn--block" href="{{ $lineUrl }}" target="_blank" rel="noopener">
                <x-site.icon name="line" />LINEで相談<span class="u-visually-hidden">（新しいタブで開きます）</span>
            </a>
            <p class="c-contact-actions__meta">LINEの友だち追加画面が開きます</p>
        </div>
    @endif
    <div class="c-contact-actions__item">
        <a class="c-btn c-btn--secondary c-btn--lg c-btn--block" href="{{ route('contact.index', $query) }}">
            <x-site.icon name="mail" />{{ $formLabel }}
        </a>
        <p class="c-contact-actions__meta">お問い合わせフォーム<span class="u-nowrap">（24時間受付）</span></p>
    </div>
</div>
