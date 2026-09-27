@props([])
{{--
    サイトヘッダー（layouts/site から1回だけ呼ぶ）。案B：上の黒い帯（PC）＋白いヘッダー。
    上の黒い帯（.l-topbar、960px 以上）：左に業態（1200px 以上）／営業状況と住所（960〜1199px）、右に［はじめての方へ］［当店のお約束］［店舗案内・アクセス］
        ［LINEで相談］（config('shop.line_url') があるときだけ）
    PC（960px以上）：ロゴマーク（赤い角丸に車）＋業態（赤・小）＋店名｜本日の営業状況のピル＋住所（1200px 以上）｜
        「お電話でのご相談」＋大きな電話番号（数字の書体）＋営業時間｜2段ボタン［お問い合わせは無料です／在庫確認・来店予約］
    スマホ：高さ66px（sticky にしない）。ロゴマーク＋業態＋店名｜［電話（赤）］［メニュー（黒）］（各58×54）
    買取査定のページ（buy.index）では主ボタンを［査定は無料です／無料査定を申し込む］（buy.index#appraisal-form）に切り替える
    （受付完了 buy.thanks では申し込んだ直後なので、通常の［在庫確認・来店予約］に戻す）。

    使い方: <x-site.header />（props なし）
    出力: <div class="l-topbar">…</div>
          <header class="l-header"><div class="l-container l-header__inner">
          a.l-header__brand（ロゴマーク＋店名＋業態。トップへのリンク）/ .l-header__status（PC）/ .l-header__contact（PC。a.c-btn2.l-header__cta）/ .l-header__sp（スマホ）</div></header>
    メニューボタンは $store.menu（x-site.scripts）で x-site.menu（#site-menu）を開閉する。
--}}
@php
    $tel = config('shop.tel');
    $lineUrl = config('shop.line_url');
    $isBuy = request()->routeIs('buy.index');
    $area = (string) config('shop.city').'の';
    // 幅の狭いスマホで業態が2行になるときは「・」の後ろでだけ折り返す（「車検整／備」のような途中の改行を防ぐ）。
    // 先頭の「尼崎市の」はスマホでは省く（l-header__tagline-area）
    $taglineParts = explode('・', (string) config('shop.tagline'));
    $taglineHtml = '';
    foreach ($taglineParts as $i => $part) {
        $suffix = $i === array_key_last($taglineParts) ? '' : '・';
        $taglineHtml .= $i === 0 && str_starts_with($part, $area)
            ? '<span class="u-nowrap"><span class="l-header__tagline-area">'.e($area).'</span>'.e(substr($part, strlen($area))).$suffix.'</span>'
            : '<span class="u-nowrap">'.e($part).$suffix.'</span>';
    }
    $topLines = $area.implode('・', (array) config('shop.business_lines', []));
@endphp
<div class="l-topbar">
    <div class="l-container l-topbar__inner">
        <p class="l-topbar__lines">{{ $topLines }}</p>
        <div class="l-topbar__status">
            <x-site.open-status variant="badge" />
            <span class="l-topbar__addr"><x-site.icon name="map-pin" :size="16" /><x-site.address /></span>
        </div>
        <ul class="l-topbar__links">
            <li><a class="l-topbar__link" href="{{ route('home') }}#flow"><x-site.icon name="wakaba" :size="18" />はじめての方へ</a></li>
            <li><a class="l-topbar__link" href="{{ route('home') }}#promise">当店のお約束</a></li>
            <li><a class="l-topbar__link" href="{{ route('store') }}">店舗案内・アクセス</a></li>
            @if ($lineUrl)
                <li>
                    <a class="l-topbar__link l-topbar__link--line" href="{{ $lineUrl }}" target="_blank" rel="noopener">
                        <x-site.icon name="line" :size="18" />LINEで相談<span class="u-visually-hidden">（新しいタブで開きます）</span>
                    </a>
                </li>
            @endif
        </ul>
    </div>
</div>
<header class="l-header">
    <div class="l-container l-header__inner">
        {{-- ロゴマーク・店名・業態をまとめて1つのリンクにする（スマホでも押せる高さ 44px 以上を確保する） --}}
        <a class="l-header__brand" href="{{ route('home') }}">
            <span class="l-header__mark" aria-hidden="true"><x-site.icon name="car" :size="36" /></span>
            <span class="l-header__text">
                <span class="l-header__name">{{ config('shop.name') }}</span>
                <span class="l-header__tagline">{!! $taglineHtml !!}</span>
            </span>
        </a>

        <div class="l-header__status">
            <x-site.open-status variant="badge" />
            <p class="l-header__address"><x-site.icon name="map-pin" :size="18" /><x-site.address /></p>
        </div>

        <div class="l-header__contact">
            <div class="l-header__tel">
                <p class="l-header__tel-label">お電話でのご相談</p>
                <a class="l-header__tel-number" href="{{ config('shop.tel_href') }}" aria-label="電話をかける {{ $tel }}"><x-site.icon name="phone" :size="26" />{{ $tel }}</a>
                <p class="l-header__hours">営業時間 {{ \App\Support\BusinessHours::hoursLabel() }}@if (filled(config('shop.closed_label')))｜定休日 {{ config('shop.closed_label') }}@endif</p>
            </div>
            <div class="l-header__buttons">
                @if ($isBuy)
                    <x-site.btn2 class="l-header__cta" :href="route('buy.index').'#appraisal-form'" small="査定は無料です" big="無料査定を申し込む" />
                @else
                    <x-site.btn2 class="l-header__cta" :href="route('contact.index', ['purpose' => 'visit'])" small="お問い合わせは無料です" big="在庫確認・来店予約" />
                @endif
            </div>
        </div>

        <div class="l-header__sp">
            <a class="l-header__sp-btn l-header__sp-btn--tel" href="{{ config('shop.tel_href') }}" aria-label="電話をかける {{ $tel }}">
                <x-site.icon name="phone" :size="22" />
                <span>電話</span>
            </a>
            <button type="button" class="l-header__sp-btn l-header__sp-btn--menu" aria-controls="site-menu" aria-expanded="false"
                    x-data :aria-expanded="$store.menu.open ? 'true' : 'false'" x-on:click="$store.menu.toggle($el)">
                <x-site.icon name="menu" :size="22" />
                <span>メニュー</span>
            </button>
        </div>
    </div>
</header>
