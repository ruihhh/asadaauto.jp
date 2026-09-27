@props([
    'variant' => 'badge',
    'now' => null,
    'status' => null,
    'onDark' => false,
])
{{--
    本日の営業状況。App\Support\BusinessHours::status()（Asia/Tokyo で判定）の結果を、色と文言の両方で表示する。

    使い方:
        <x-site.open-status />                    … badge（角丸の pill。PCヘッダー・カードの中）
        <x-site.open-status variant="line" />     … line（文字のみ。スマホの営業状況バー）
        <x-site.open-status :now="$someCarbon" /> … 見本・テスト用に時刻を指定
        <x-site.open-status variant="line" on-dark /> … 黒・写真の暗い面の上（文字を明るい緑・黄にする）

    variant: badge | line
    now:     判定に使う日時（省略時は現在時刻）
    status:  BusinessHours::status() の結果をそのまま渡すとき（省略可）
    onDark:  黒・写真の暗い面の上に置くとき（c-open-status--on-dark。既定の色は白地用）

    出力: <span class="c-open-status c-open-status--{variant} c-open-status--{ok|neutral|caution} [c-open-status--on-dark]" data-state="open|before|after|closed">
            <span class="c-open-status__main">●＋本日営業中（21:00まで）</span>
            <span class="c-open-status__sub">（次の営業 10月20日（火）11:00〜）</span> … 営業終了・定休日のときだけ
--}}
@php
    $status ??= \App\Support\BusinessHours::status($now);

    // 「本日は定休日です（次の営業 …）」は、状態の部分と次の営業の部分に分けて見せる（狭い所で折り返せるように）
    $main = $status['label'];
    $sub = null;
    if ($status['next_label'] !== null && str_contains($status['label'], '（次の営業')) {
        $main = \Illuminate\Support\Str::before($status['label'], '（次の営業');
        $sub = '（次の営業 '.$status['next_label'].'）';
    }
@endphp
<span {{ $attributes->merge(['class' => 'c-open-status c-open-status--'.$variant.' c-open-status--'.$status['tone'].($onDark ? ' c-open-status--on-dark' : '')]) }} data-state="{{ $status['state'] }}">
    <span class="c-open-status__main"><span class="c-open-status__dot" aria-hidden="true"></span>{{ $main }}</span>
    @if ($sub !== null)
        <span class="c-open-status__sub">{{ $sub }}</span>
    @endif
</span>
