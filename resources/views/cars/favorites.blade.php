@extends('layouts.site')

@php
    use App\Support\BusinessHours;
    use App\Support\CarText;
    use Illuminate\Support\HtmlString;

    // URL の ids（＝この端末に保存した順）。コントローラは順番を保たないので、ここで登録した順に並べ直す
    $favIds = collect(explode(',', (string) request()->query('ids', '')))
        ->map(fn ($value) => (int) $value)
        ->filter(fn (int $id) => $id > 0)
        ->unique()
        ->values();
    $favCars = $favIds->map(fn (int $id) => $cars->firstWhere('id', $id))->filter()->values();

    // 登録していても表示できない台数（売約済み・掲載終了）
    $unavailable = max(0, $favIds->count() - $favCars->count());

    // まとめて問い合わせ：在庫番号をカンマ区切りで最大10件
    $stockNos = $favCars->pluck('stock_no')->filter()->take(10)->values();
    $bulkUrl = route('contact.index', ['purpose' => 'favorites', 'list' => $stockNos->implode(',')]);

    // 空のときの「登録のしかた」の最後に出す、いま掲載中の台数（実数）
    $publicCount = \App\Models\Car::publicInventory()->count();

    // 登録のしかた（STEP 図）の見出し。狭い枠でも語の途中で折り返さないよう、区切りを u-nowrap で決める
    $howtoTitles = [
        1 => new HtmlString('<span class="u-nowrap">在庫一覧で</span><span class="u-nowrap">車を探す</span>'),
        2 => new HtmlString('<span class="u-nowrap">［お気に入り］</span><span class="u-nowrap">を押す</span>'),
        3 => new HtmlString('<span class="u-nowrap">このページに</span><span class="u-nowrap">まとまる</span>'),
    ];

    // 見出し帯の登録台数（外したら Alpine で数え直す）
    $countHtml = new HtmlString('<span x-text="count">'.$favCars->count().'</span>');

    $pageData = [
        'url' => route('cars.favorites'),
        'formUrl' => route('contact.index'),
        'cars' => $favCars->map(fn ($car) => ['id' => (int) $car->id, 'stock' => (string) $car->stock_no])->all(),
    ];

    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'ホーム', 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'お気に入り', 'item' => route('cars.favorites')],
        ],
    ];
@endphp

@section('title', 'お気に入り')
@section('meta_description', 'お気に入りに登録した中古車の一覧です。登録はこの端末のブラウザに保存され、まとめて在庫確認・見積もりのお問い合わせができます。')
@section('meta_robots', 'noindex, follow')
@section('canonical', route('cars.favorites'))
@section('body_class', 'p-fav')

{{-- このページの「まとめて問い合わせる」に電話・フォームがあるので、車があるときはフッター直前のご相談帯を重ねて出さない --}}
@if ($favCars->isNotEmpty())
    @section('contact_band', 'hide')
@endif

@push('structured_data')
    <script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
<div x-data="favoritesPage(@js($pageData))">
    <x-site.page-header class="p-fav-header" title="お気に入り" en="FAVORITES"
        lead="気になる車を登録しておける一覧です。登録はこの端末のブラウザに保存されるため、ほかのスマホやパソコンには表示されません。">
        @if ($favCars->isNotEmpty())
            {{-- 登録台数を大きく（スマホ・タブレットは黒い帯、PC は見出し帯の右に丸いバッジ） --}}
            <div class="p-fav-count" x-show="! redirecting && count > 0">
                <p class="p-fav-count__bar u-hide-pc">
                    <span class="p-fav-count__ic" aria-hidden="true"><x-site.icon name="heart-fill" /></span>
                    <span class="p-fav-count__label">お気に入りに登録中</span>
                    <span class="p-fav-count__num">{{ $countHtml }}<small>台</small></span>
                </p>
                <x-site.round-badge class="p-fav-count__badge u-hide-sp" size="lg" top="お気に入り" :num="$countHtml" unit="台" bottom="登録中" />
            </div>
        @endif
    </x-site.page-header>

    <div class="l-section p-fav-main">
        <div class="l-container">
            {{-- ストアの登録と URL の ids が違うときは、登録した順の URL に移る（そのあいだだけ出す） --}}
            <p class="p-fav-loading" role="status" x-show="redirecting" x-cloak>お気に入りを読み込んでいます…</p>

            @if ($unavailable > 0)
                <div class="c-alert p-fav-notice" x-show="! redirecting">
                    <x-site.icon name="info" />
                    <div class="c-alert__body">
                        <p class="c-alert__title">{{ $unavailable }}台は<span class="u-nowrap">売約済み・掲載終了のため</span><span class="u-nowrap">表示できません</span></p>
                        <p>探している車をお知らせいただければ、入荷したときにご連絡します。</p>
                        <p><a class="c-btn c-btn--secondary c-btn--sm" href="{{ route('contact.index', ['purpose' => 'search']) }}">探してほしい車を伝える</a></p>
                    </div>
                </div>
            @endif

            @if ($favCars->isNotEmpty())
                <section aria-labelledby="fav-list-title" x-show="! redirecting && count > 0">
                    <x-site.section-head id="fav-list-title" title="お気に入りの車" en="MY CARS" lead="登録した順に並んでいます。">
                        <p class="c-count-pill">登録<b x-text="count">{{ $favCars->count() }}</b>台</p>
                        <a class="c-more" href="#fav-contact">まとめて問い合わせる<x-site.icon name="chevron-right" /></a>
                    </x-site.section-head>

                    <div class="c-alert">
                        <x-site.icon name="info" />
                        <p>価格はすべて<b>税込の支払総額</b>です。{{ CarText::priceNote() }}</p>
                    </div>

                    <ul class="l-grid l-grid--3 p-fav-grid" role="list" aria-labelledby="fav-list-title">
                        @foreach ($favCars as $car)
                            <li x-show="$store.favorites.has({{ (int) $car->id }})">
                                <x-site.car-card :car="$car" fav-remove :loading="$loop->index < 3 ? 'eager' : 'lazy'" />
                            </li>
                        @endforeach
                    </ul>

                    {{-- 使い方のヒント（アイコン＋短い説明） --}}
                    <h3 class="c-subhead p-fav-tips__title">お気に入りの使い方</h3>
                    <ul class="p-fav-tips" role="list">
                        <li class="p-fav-tip">
                            <span class="p-fav-tip__ic" aria-hidden="true"><x-site.icon name="heart-fill" /></span>
                            <p class="p-fav-tip__body"><b class="p-fav-tip__title">外すとき</b>［お気に入りから外す］を押すと、一覧から外れます。画面の下に出る［元に戻す］で戻せます。</p>
                        </li>
                        <li class="p-fav-tip">
                            <span class="p-fav-tip__ic p-fav-tip__ic--black" aria-hidden="true"><x-site.icon name="compare" /></span>
                            <p class="p-fav-tip__body"><b class="p-fav-tip__title">並べて比べる</b>［比較に追加］で3台まで選ぶと、表で並べて比べられます。</p>
                        </li>
                        <li class="p-fav-tip">
                            <span class="p-fav-tip__ic p-fav-tip__ic--yellow" aria-hidden="true"><x-site.icon name="phone" /></span>
                            <p class="p-fav-tip__body"><b class="p-fav-tip__title">まとめて問い合わせ</b>このページの下から、登録した車の在庫確認・見積もりを1回でご依頼いただけます。</p>
                        </li>
                    </ul>
                </section>
            @endif

            {{-- 0件のとき（読み込み前は見せない）。3台の車の絵の上に、赤い丸の大きなハートと小さなハートを重ねる（飾り） --}}
            <div class="p-fav-empty" x-show="! redirecting && count === 0" x-cloak>
                <span class="p-fav-empty__heart" aria-hidden="true"><x-site.icon name="heart-fill" /></span>
                <span class="p-fav-empty__spark" aria-hidden="true"><x-site.icon name="heart-fill" /></span>
                <span class="p-fav-empty__spark p-fav-empty__spark--right" aria-hidden="true"><x-site.icon name="heart" /></span>
                <x-site.empty-state illust="all" :title="$unavailable > 0 ? 'いま表示できる車はありません' : 'お気に入りの車はまだありません'">
                    在庫一覧や車の詳しいページで<span class="u-nowrap">［お気に入り］</span>を押すと、ここに登録されます。あとでまとめて見比べたり、問い合わせたりできます。
                    <x-slot:actions>
                        <a class="c-btn c-btn--primary" href="{{ route('cars.index') }}"><x-site.icon name="car" />在庫一覧を見る</a>
                        <a class="c-btn c-btn--secondary" href="{{ route('cars.index', ['sort' => 'latest']) }}">新着の中古車を見る</a>
                    </x-slot:actions>
                </x-site.empty-state>
            </div>
        </div>
    </div>

    @if ($favCars->isNotEmpty())
        {{-- まとめて問い合わせ（黒い写真地に斜めの赤い面。フォームは在庫番号入りで開く） --}}
        <section class="l-section l-section--soft" id="fav-contact" aria-labelledby="fav-contact-title" x-show="! redirecting && count > 0">
            <div class="l-container">
                <div class="c-promo p-fav-promo">
                    <div class="c-promo__media">
                        <picture>
                            <source type="image/webp" srcset="{{ asset('images/store-cta-bg.jpg.webp') }}">
                            <img src="{{ asset('images/store-cta-bg.jpg') }}" alt="" width="1400" height="400" loading="lazy">
                        </picture>
                    </div>
                    <div class="c-promo__body">
                        <p class="c-slant c-slant--yellow">在庫確認・見積もりは無料</p>
                        <h2 class="c-promo__title" id="fav-contact-title">お気に入りの車について<br><em>まとめて問い合わせる</em></h2>
                        <p class="c-promo__lead">在庫の確認や見積もり（無料）を、まとめて1回でご依頼いただけます。</p>

                        <div class="p-fav-stock">
                            <p class="p-fav-stock__label"><x-site.icon name="tag" />お電話では、この在庫番号をお伝えください</p>
                            <ul class="p-fav-stock__list" role="list">
                                @foreach ($favCars as $car)
                                    @if (filled($car->stock_no))
                                        <li class="p-fav-stock__no" x-show="$store.favorites.has({{ (int) $car->id }})">{{ $car->stock_no }}</li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>

                        <div class="c-promo__actions">
                            <x-site.btn2 :href="$bulkUrl" x-bind:href="formUrl" variant="yellow" small="在庫番号が入ったフォームが開きます" big="フォームで問い合わせる" />
                            <a class="c-promo__tel" href="{{ config('shop.tel_href') }}" aria-label="電話をかける {{ config('shop.tel') }}">
                                <small>電話で相談する（{{ BusinessHours::hoursLabel() }}）</small>
                                <b><x-site.icon name="phone" />{{ config('shop.tel') }}</b>
                            </a>
                            @if (config('shop.line_url'))
                                <a class="c-btn c-btn--line" href="{{ config('shop.line_url') }}" target="_blank" rel="noopener">
                                    <x-site.icon name="line" />LINEで相談<span class="u-visually-hidden">（新しいタブで開きます）</span>
                                </a>
                            @endif
                        </div>
                        <div class="p-fav-promo__meta">
                            <p><x-site.open-status variant="line" on-dark /></p>
                            <p class="p-fav-promo__note">営業時間 {{ BusinessHours::summaryHtml() }}。フォームは24時間受付です。</p>
                            <p class="p-fav-promo__note" x-show="count > 10" x-cloak>フォームには、登録した順に10台までの在庫番号が入ります。</p>
                        </div>
                    </div>
                    <ul class="c-promo__badges" role="list">
                        <li><x-site.round-badge variant="yellow" top="在庫確認" num="無料" /></li>
                        <li><x-site.round-badge variant="yellow" top="見積もり" num="無料" /></li>
                        <li><x-site.round-badge variant="yellow" top="フォームは" num="24時間" bottom="受付" /></li>
                    </ul>
                </div>
            </div>
        </section>
    @endif

    {{-- 0件のとき：登録のしかた（STEP.1 → 2 → 3 の矢印の図） --}}
    <section class="l-section l-section--soft" aria-labelledby="fav-howto-title" x-show="! redirecting && count === 0" x-cloak>
        <div class="l-container">
            <x-site.section-head id="fav-howto-title" title="お気に入りの登録のしかた" en="HOW TO" lead="3つの手順で、気になる車をこのページにまとめておけます。" />
            <div class="c-arrow-steps c-arrow-steps--even p-fav-howto">
                <x-site.arrow-step :no="1" :title="$howtoTitles[1]" illust="f-search">
                    <p class="p-fav-howto__text">在庫一覧や、車の詳しいページを開きます。</p>
                </x-site.arrow-step>
                <x-site.arrow-step :no="2" :title="$howtoTitles[2]">
                    <div class="p-fav-demo" aria-hidden="true">
                        <span class="p-fav-demo__btn"><x-site.icon name="heart" />お気に入り</span>
                        <x-site.icon name="chevron-down" class="p-fav-demo__arrow" />
                        <span class="p-fav-demo__btn p-fav-demo__btn--on"><x-site.icon name="heart-fill" />お気に入り済み</span>
                    </div>
                    <p class="p-fav-howto__text">車ごとにあるボタンです。押すとハートが赤くなります。</p>
                </x-site.arrow-step>
                <x-site.arrow-step :no="3" :title="$howtoTitles[3]">
                    <div class="p-fav-demo" aria-hidden="true">
                        <span class="p-fav-demo__nav"><x-site.icon name="heart" />お気に入り<b class="p-fav-demo__count">1</b></span>
                    </div>
                    <p class="p-fav-howto__text">画面の上の［お気に入り］（スマホは［メニュー］の中）から、いつでも開けます。</p>
                </x-site.arrow-step>
                <x-site.arrow-step go>
                    @if ($publicCount > 0)
                        <p class="c-arrow-step__count-label">掲載中の車</p>
                        <p class="c-arrow-step__count">{{ $publicCount }}<small>台</small></p>
                    @endif
                    <a class="c-btn c-btn--yellow" href="{{ route('cars.index') }}"><x-site.icon name="search" />在庫一覧を見る</a>
                </x-site.arrow-step>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    // お気に入りページ：登録の読み込み（URL との同期）と、外した車の表示・まとめて問い合わせのリンクを管理する
    Alpine.data('favoritesPage', (config) => ({
        redirecting: false,
        init() {
            const want = this.$store.favorites.ids.join(',');
            const have = new URLSearchParams(window.location.search).get('ids') || '';
            if (want !== have) {
                this.redirecting = true;
                window.location.replace(config.url + (want ? '?ids=' + want : ''));
            }
        },
        // いま表示している車（外した車は除く。並びは登録した順）
        get shown() {
            return config.cars.filter((car) => this.$store.favorites.has(car.id));
        },
        get count() {
            return this.shown.length;
        },
        get stockNos() {
            return this.shown.map((car) => car.stock).filter((stock) => stock !== '').slice(0, 10);
        },
        get formUrl() {
            const params = new URLSearchParams({ purpose: 'favorites', list: this.stockNos.join(',') });
            return config.formUrl + '?' + params.toString();
        },
    }));
});
</script>
@endpush
