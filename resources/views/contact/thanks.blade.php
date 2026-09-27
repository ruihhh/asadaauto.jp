@extends('layouts.site')

@php
    // お問い合わせの送信完了（/contact/thanks）。共通の完了ページ（x-site.thanks：受付完了のイラスト → h1 → リード →
    // このあとの流れ（イラスト付きの steps を渡すと、お問い合わせページの「送信後の流れ」と同じメダル型の図 x-site.medal-steps）→
    // お急ぎの方はお電話で（斜めの赤帯＋営業状況＋大きな電話番号）→ 次の行動）で組む。買取査定の完了ページも同じ部品。
    // 自動返信メールは実装されていないので触れない。
    $replyNote = config('shop.reply_note');
    $closedLabel = config('shop.closed_label');
    // 次の行動の［在庫一覧を見る］に添える台数（公開中の車の実数。0台なら台数を出さない）
    $totalPublic = \App\Models\Car::publicInventory()->count();
    $afterSteps = [
        ['title' => '担当者が内容を確認します', 'illust' => 'p-explain'],
        ['title' => '電話またはメールでご連絡します', 'text' => 'ご記入の連絡先にご連絡します。', 'illust' => 'mail-check'],
        ['title' => 'ご来店・お見積もりなどをご案内します', 'text' => '見学・試乗の日時などをご相談ください。', 'illust' => 'f-store'],
    ];
@endphp

@section('title', 'お問い合わせを受け付けました')
@section('meta_robots', 'noindex, nofollow')
@section('body_class', 'p-contact')
@section('mbar', 'contact')
@section('contact_band', 'hide')

@section('content')
<x-site.thanks class="p-contact-thanks" title="お問い合わせを受け付けました" :steps="$afterSteps">
    <p class="p-contact-thanks__lead">お問い合わせありがとうございます。担当者から電話またはメールでご連絡します。</p>
    <p class="p-contact-thanks__lead">定休日（{{ $closedLabel }}）をはさむ場合は、翌営業日以降のご連絡になります。</p>
    @if (filled($replyNote))
        <p class="p-contact-thanks__lead">{{ $replyNote }}</p>
    @endif
    <x-slot:actions>
        <x-site.btn2 :href="route('cars.index')" small="写真・支払総額つきで掲載中"
            :big="$totalPublic > 0 ? '在庫一覧を見る（'.$totalPublic.'台）' : '在庫一覧を見る'" />
        <x-site.btn2 :href="route('store')" variant="black" small="地図・営業時間・駐車場" big="店舗案内・アクセスを見る" />
        <a class="c-btn c-btn--secondary c-btn--lg" href="{{ route('home') }}">トップページへ戻る</a>
    </x-slot:actions>
</x-site.thanks>
@endsection
