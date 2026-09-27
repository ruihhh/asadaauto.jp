@extends('layouts.site')

@section('title', '査定のお申し込みを受け付けました')
@section('meta_robots', 'noindex, nofollow')
@section('body_class', 'p-buy')
@section('contact_band', 'hide')

@php
    // 買取査定の受付完了（/buy/thanks）。共通の完了ページ（x-site.thanks：受付完了のイラスト → h1 → リード → このあとの流れ → 電話 → 次の行動）で組む。
    // 自動返信メールは実装されていないので触れない。「このあとの流れ」はイラスト付きで渡し、お問い合わせの完了ページと同じメダル型の図にする
    // h1 は語の単位でだけ折り返す（狭いスマホで「受け付／けました」のような改行を防ぐ）
    $thanksTitle = new \Illuminate\Support\HtmlString('<span class="u-nowrap">査定のお申し込みを</span><span class="u-nowrap">受け付けました</span>');
    // 次の行動の［在庫の車を見る］に添える台数（公開中の車の実数。0台なら台数を出さない）
    $totalPublic = \App\Models\Car::publicInventory()->count();
    $steps = [
        ['title' => '内容を確認します', 'text' => '担当者がお申し込みの内容を確認します。', 'illust' => 'p-explain'],
        // 「おおよ／その」のように語の途中で折り返さない
        ['title' => 'ご連絡します', 'text' => new \Illuminate\Support\HtmlString('電話またはメールで、<span class="u-nowrap">おおよその査定額を</span>お伝えします。'), 'illust' => 'mail-check'],
        ['title' => 'お車を見て査定します', 'text' => '日時をご相談のうえ、ご来店か出張査定で。査定だけでも大丈夫です。', 'illust' => 'buy'],
    ];
@endphp

@section('content')
<x-site.thanks class="p-buy-thanks" :title="$thanksTitle" :steps="$steps">
    <p class="p-buy-thanks__lead">お申し込みありがとうございます。担当者から電話またはメールでご連絡します。</p>
    <p class="p-buy-thanks__lead">定休日（{{ config('shop.closed_label') }}）をはさむ場合は、翌営業日以降のご連絡になります。</p>
    <div class="p-buy-memo p-buy-thanks__prep">
        <span class="p-buy-memo__ic"><x-site.icon name="file" :size="28" /></span>
        <div class="p-buy-memo__body">
            <p class="p-buy-memo__title"><span class="u-nowrap">車検証を</span><span class="u-nowrap">お手元にご用意ください</span></p>
            <p class="p-buy-memo__text">ご連絡のときに、年式などを車検証で確認することがあります。</p>
        </div>
    </div>
    <x-slot:actions>
        <x-site.btn2 :href="route('cars.index')" small="買い替えのご相談もどうぞ"
            :big="$totalPublic > 0 ? '在庫の車を見る（'.$totalPublic.'台）' : '在庫の車を見る'" />
        <x-site.btn2 :href="route('store')" variant="black" small="お店での査定もできます" big="店舗案内・アクセスを見る" />
        <a class="c-btn c-btn--secondary c-btn--lg" href="{{ route('home') }}">トップページへ戻る</a>
    </x-slot:actions>
</x-site.thanks>
@endsection
