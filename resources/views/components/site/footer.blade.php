@props([])
{{--
    フッター（案B：黒）。layouts/site から1回だけ呼ぶ。
    上段：店舗情報（ロゴマーク・業態・店名・事業内容・住所・TEL・営業時間・定休日・Googleマップ）
    リンク列：クルマを探す／ボディタイプから探す（在庫がある種類だけ）／ご案内。PCは3列、スマホは2列
    下段：古物商許可（config に番号があるときだけ）・運営者（設定があるときだけ）・©

    使い方: <x-site.footer />（props なし）
    $footerBodyTypes（array<string, int>。DB のボディタイプの値 => 台数、多い順）は AppServiceProvider の View composer が渡す。
    出力: <footer class="l-footer"><div class="l-container l-footer__main"><div class="l-footer__shop"><div class="l-footer__brand">…</div>…</div>
          <nav class="l-footer__nav" aria-label="フッターメニュー">…</nav></div><div class="l-footer__bottom">…</div></footer>
--}}
@php
    use App\Support\CarText;

    $tel = config('shop.tel');
    $bodyTypes = $footerBodyTypes ?? [];
    $kobutsu = (array) config('shop.kobutsu', []);
    $company = config('shop.operator.company');
    $groups = [
        ['id' => 'footer-cars', 'title' => 'クルマを探す', 'links' => [
            ['label' => '在庫一覧', 'url' => route('cars.index')],
            ['label' => '新着の車', 'url' => route('cars.index', ['sort' => 'latest'])],
            ['label' => 'お気に入り', 'url' => route('cars.favorites')],
            ['label' => '車両比較', 'url' => route('cars.compare')],
        ]],
        ['id' => 'footer-body-types', 'title' => 'ボディタイプから探す', 'links' => collect($bodyTypes)
            ->map(fn (int $count, string $type): array => [
                'label' => CarText::bodyType($type),
                'count' => $count,
                'url' => route('cars.index', ['body_type' => $type]),
            ])->values()->all()],
        ['id' => 'footer-guide', 'title' => 'ご案内', 'links' => [
            ['label' => '買取査定', 'url' => route('buy.index')],
            ['label' => 'ご購入の流れ・ローン', 'url' => route('home').'#flow'],
            ['label' => '店舗案内・アクセス', 'url' => route('store')],
            ['label' => 'お問い合わせ', 'url' => route('contact.index')],
            ['label' => '個人情報の取り扱い', 'url' => route('privacy')],
        ]],
    ];
@endphp
<footer class="l-footer">
    <div class="l-container l-footer__main">
        <div class="l-footer__shop">
            <div class="l-footer__brand">
                <span class="l-header__mark" aria-hidden="true"><x-site.icon name="car" :size="32" /></span>
                <div>
                    <p class="l-footer__sub">{{ config('shop.tagline') }}</p>
                    <p class="l-footer__name">{{ config('shop.name') }}</p>
                </div>
            </div>
            <p>{{ implode('・', (array) config('shop.business_lines', [])) }}</p>
            <address>〒{{ config('shop.postal') }}<br><x-site.address /></address>
            <p><a class="l-footer__tel" href="{{ config('shop.tel_href') }}" aria-label="電話をかける {{ $tel }}"><x-site.icon name="phone" />TEL {{ $tel }}</a></p>
            <p>営業時間 {{ \App\Support\BusinessHours::hoursLabel() }}<br>定休日 {{ config('shop.closed_label') }}</p>
            <p><a class="l-footer__link l-footer__link--inline" href="{{ config('shop.map_url') }}" target="_blank" rel="noopener">Googleマップで見る（新しいタブで開きます）</a></p>
        </div>

        <nav class="l-footer__nav" aria-label="フッターメニュー">
            @foreach ($groups as $group)
                @if ($group['links'] !== [])
                    <div>
                        <p id="{{ $group['id'] }}" class="l-footer__heading">{{ $group['title'] }}</p>
                        <ul class="l-footer__links" aria-labelledby="{{ $group['id'] }}">
                            @foreach ($group['links'] as $link)
                                <li>
                                    <a class="l-footer__link" href="{{ $link['url'] }}">
                                        <x-site.icon name="chevron-right" :size="16" />{{ $link['label'] }}@isset($link['count'])<span class="l-footer__count">（{{ $link['count'] }}台）</span>@endisset
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endforeach
        </nav>
    </div>

    <div class="l-footer__bottom">
        <div class="l-container l-footer__bottom-inner">
            @if (filled($kobutsu['number'] ?? null))
                <p>古物商許可 {{ $kobutsu['authority'] ?? '' }} 第{{ $kobutsu['number'] }}号@if (filled($kobutsu['holder'] ?? null))（{{ $kobutsu['holder'] }}）@endif</p>
            @endif
            @if (filled($company))
                <p>運営：{{ $company }}</p>
            @endif
            <p class="l-footer__copy">&copy; {{ now(\App\Support\BusinessHours::TIMEZONE)->year }} {{ config('shop.name') }}</p>
        </div>
    </div>
</footer>
