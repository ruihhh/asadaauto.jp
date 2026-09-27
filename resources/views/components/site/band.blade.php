@props([])
{{--
    ご相談帯（フッターの直前に全ページ共通で出す。案B：黒〜赤の写真地に、上が赤い白カード）。layouts/site から1回だけ呼ぶ。
    ページ側で @section('contact_band', 'hide') を指定すると出さない（お問い合わせ・買取査定・完了ページ）。
    カード：電話（大きな番号・営業時間・本日の営業状況）／LINE（URL が設定されているときだけ）／フォーム／店舗のご案内（住所・駐車場・地図）。
    PCは横並び、スマホは1列。

    使い方: <x-site.band />（props なし）
    出力: <section class="l-band" aria-labelledby="band-title"><div class="l-container">
          <div class="l-band__head"><h2 class="l-band__title">ご相談・ご来店はこちら</h2><p class="l-band__lead">…</p></div>
          <ul class="l-band__cards"><li class="l-band__card">…</li>…</ul></div></section>
--}}
@php
    $tel = config('shop.tel');
    $lineUrl = config('shop.line_url');
@endphp
<section class="l-band" aria-labelledby="band-title">
    <div class="l-container">
        <div class="l-band__head">
            <h2 id="band-title" class="l-band__title">ご相談・ご来店はこちら</h2>
            <p class="l-band__lead">在庫確認・お見積もり・来店予約・買取査定。ご都合のよい方法でどうぞ。</p>
        </div>

        <ul class="l-band__cards">
            <li class="l-band__card">
                <span class="l-band__icon" aria-hidden="true"><x-site.icon name="phone" /></span>
                <h3 class="l-band__card-title">お電話でのご相談</h3>
                <a class="l-band__tel" href="{{ config('shop.tel_href') }}" aria-label="電話をかける {{ $tel }}">{{ $tel }}</a>
                <p class="l-band__text">営業時間 {{ \App\Support\BusinessHours::hoursLabel() }}<br>定休日 {{ config('shop.closed_label') }}</p>
                <x-site.open-status variant="badge" />
            </li>
            @if ($lineUrl)
                <li class="l-band__card">
                    <span class="l-band__icon l-band__icon--line" aria-hidden="true"><x-site.icon name="line" /></span>
                    <h3 class="l-band__card-title">LINEでのご相談</h3>
                    <p class="l-band__text">写真を送っての見積もりのご相談や、ご来店の日時の調整にお使いください。</p>
                    <div class="l-band__actions">
                        <a class="c-btn c-btn--line c-btn--block" href="{{ $lineUrl }}" target="_blank" rel="noopener">
                            <x-site.icon name="line" />LINEで相談<span class="u-visually-hidden">（新しいタブで開きます）</span>
                        </a>
                    </div>
                </li>
            @endif
            <li class="l-band__card">
                <span class="l-band__icon" aria-hidden="true"><x-site.icon name="mail" /></span>
                <h3 class="l-band__card-title"><span class="u-nowrap">お問い合わせフォーム</span><span class="u-nowrap">（24時間受付）</span></h3>
                <p class="l-band__text">在庫確認・来店予約・お見積もり・買取査定のお申し込みができます。担当者から電話またはメールでご連絡します。</p>
                <div class="l-band__actions">
                    <a class="c-btn c-btn--primary c-btn--block" href="{{ route('contact.index') }}">
                        <x-site.icon name="mail" />フォームで問い合わせる
                    </a>
                </div>
            </li>
            <li class="l-band__card">
                <span class="l-band__icon" aria-hidden="true"><x-site.icon name="map-pin" /></span>
                <h3 class="l-band__card-title">店舗のご案内</h3>
                <p class="l-band__addr"><x-site.address postal /></p>
                <p class="l-band__text">{{ config('shop.parking') }}</p>
                <div class="l-band__actions">
                    <a class="c-btn c-btn--secondary c-btn--block" href="{{ route('store') }}">
                        <x-site.icon name="store" />店舗案内・アクセスを見る
                    </a>
                    <a class="c-link l-band__sublink" href="{{ config('shop.directions_url') }}" target="_blank" rel="noopener"><span>地図アプリで道順を見る<span class="u-nowrap">（新しいタブで開きます）</span></span></a>
                </div>
            </li>
        </ul>
    </div>
</section>
