@props([])
{{--
    スマホメニュー（全画面・黒地・アニメーションなし）。ヘッダーの［メニュー］ボタンから $store.menu で開閉する。
    右上の［閉じる］は、開いたときの［メニュー］ボタンと同じ位置に置く（パネルがスクロールしても動かない）。
    Esc で閉じる／開いている間は背面のスクロールを止める（html.is-menu-open）／フォーカスをパネル内に閉じ込める。

    使い方: <x-site.menu />（props なし。layouts/site から1回だけ呼ぶ）
    出力: <div id="site-menu" class="l-menu" role="dialog" aria-modal="true" aria-labelledby="site-menu-title">
          <div class="l-menu__head">…<button class="l-menu__close" data-menu-close>閉じる</button></div>
          <div class="l-menu__body">電話・LINE／営業状況・営業時間・住所／ナビ項目（.l-menu__link。黄のアイコン付き）／お気に入り・比較／個人情報の取り扱い</div></div>
--}}
@php
    $tel = config('shop.tel');
    $lineUrl = config('shop.line_url');
    $links = [
        ['label' => '在庫を探す', 'icon' => 'search', 'url' => route('cars.index')],
        ['label' => '新着の車', 'icon' => 'star', 'url' => route('cars.index', ['sort' => 'latest'])],
        ['label' => '買取査定', 'icon' => 'yen', 'url' => route('buy.index')],
        ['label' => 'ご購入の流れ・ローン', 'icon' => 'route', 'url' => route('home').'#flow'],
        ['label' => '店舗案内・アクセス', 'icon' => 'store', 'url' => route('store')],
        ['label' => 'お問い合わせ', 'icon' => 'mail', 'url' => route('contact.index')],
    ];
@endphp
<div id="site-menu" class="l-menu" role="dialog" aria-modal="true" aria-labelledby="site-menu-title"
     x-data x-show="$store.menu.open" x-cloak
     x-on:keydown.escape.window="$store.menu.open && $store.menu.close()"
     x-on:keydown.tab="$store.menu.trap($event, $el)">
    <div class="l-menu__head">
        <p id="site-menu-title" class="l-menu__title">メニュー</p>
        <button type="button" class="l-menu__close" data-menu-close x-on:click="$store.menu.close()">
            <x-site.icon name="close" :size="22" />
            <span>閉じる</span>
        </button>
    </div>
    <div class="l-menu__body">
        <div class="l-menu__contact">
            <a class="c-btn c-btn--primary c-btn--lg c-btn--block" href="{{ config('shop.tel_href') }}">
                <x-site.icon name="phone" />電話する <span class="l-menu__tel">{{ $tel }}</span>
            </a>
            @if ($lineUrl)
                <a class="c-btn c-btn--line c-btn--lg c-btn--block" href="{{ $lineUrl }}" target="_blank" rel="noopener">
                    <x-site.icon name="line" />LINEで相談<span class="u-visually-hidden">（新しいタブで開きます）</span>
                </a>
            @endif
            <a class="c-btn c-btn--yellow c-btn--block" href="{{ route('contact.index', ['purpose' => 'visit']) }}">
                <x-site.icon name="calendar" />在庫確認・来店予約
            </a>
        </div>

        <div class="l-menu__info">
            <x-site.open-status variant="badge" />
            <p class="l-menu__info-row"><span class="l-menu__info-label">営業時間</span><span>{{ \App\Support\BusinessHours::hoursLabel() }}</span></p>
            <p class="l-menu__info-row"><span class="l-menu__info-label">定休日</span><span>{{ config('shop.closed_label') }}</span></p>
            <p class="l-menu__info-row">
                <span class="l-menu__info-label">住所</span>
                <span><x-site.address /><br><a class="c-link" href="{{ config('shop.map_url') }}" target="_blank" rel="noopener">地図を見る（新しいタブで開きます）</a></span>
            </p>
        </div>

        <nav aria-label="サイト内のページ">
            <ul class="l-menu__list">
                @foreach ($links as $link)
                    <li>
                        <a class="l-menu__link" href="{{ $link['url'] }}" x-on:click="$store.menu.close(false)">
                            <x-site.icon :name="$link['icon']" />
                            {{ $link['label'] }}
                            <x-site.icon name="chevron-right" class="l-menu__chev" />
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <ul class="l-menu__list">
            <li>
                <a class="l-menu__link" href="{{ route('cars.favorites') }}" x-on:click="$store.menu.close(false)">
                    <x-site.icon name="heart" />
                    <span>お気に入り</span>
                    <span class="l-menu__link-count" x-text="'（' + $store.favorites.count + '件）'"></span>
                    <x-site.icon name="chevron-right" class="l-menu__chev" />
                </a>
            </li>
            <li>
                <a class="l-menu__link" href="{{ route('cars.compare') }}" :href="$store.compare.url" x-on:click="$store.menu.close(false)">
                    <x-site.icon name="compare" />
                    <span>車両比較</span>
                    <span class="l-menu__link-count" x-text="'（' + $store.compare.count + '台）'"></span>
                    <x-site.icon name="chevron-right" class="l-menu__chev" />
                </a>
            </li>
            <li>
                <a class="l-menu__link l-menu__small" href="{{ route('privacy') }}" x-on:click="$store.menu.close(false)">
                    <x-site.icon name="file" />
                    個人情報の取り扱い
                    <x-site.icon name="chevron-right" class="l-menu__chev" />
                </a>
            </li>
        </ul>
    </div>
</div>
