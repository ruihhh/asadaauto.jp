@props([
    'title',
    'icon' => 'search',
    'headingLevel' => 2,
    'tel' => true,
    'illust' => null,
])
{{--
    空状態（検索結果0件・お気に入り0件・比較0件・関連車両なし。案B：点線の枠・淡い赤の丸のアイコン）。説明文では必ず次にできることを示す。

    使い方:
        <x-site.empty-state title="条件に合う車が見つかりませんでした">
            条件を減らすか、探してほしい車をお伝えください。入荷したらご連絡します。
            <x-slot:actions>
                <a class="c-btn c-btn--primary" href="{{ route('cars.index') }}">条件をすべて解除する</a>
                <a class="c-btn c-btn--secondary" href="{{ route('contact.index', ['purpose' => 'search']) }}">探してほしい車を伝える</a>
            </x-slot:actions>
        </x-site.empty-state>

    title:        見出し（18px/700）。文節で折り返す（site.css 03-22）。改行の位置を決めたいときは HtmlString で u-nowrap の span を渡してよい
    icon:         x-site.icon の name（淡い赤の丸の中に 40px で出す）
    illust:       x-site.illust の name（渡すとアイコンの代わりにイラストを出す。例：empty）
    headingLevel: 見出しレベル（2〜4）
    tel:          電話番号と営業時間の行を出すか
    slot:         説明文 / actions（名前付きスロット）：次の行動のボタン（最大3つ）

    出力: <div class="c-empty"><span class="c-empty__badge"><svg class="c-icon c-empty__icon"></span>（または <svg class="c-illust c-empty__art">）
          <h2 class="c-empty__title"><div class="c-empty__text">
          <div class="c-empty__actions"><p class="c-empty__tel"></div>
--}}
@php
    $level = min(max((int) $headingLevel, 2), 4);
@endphp
<div {{ $attributes->merge(['class' => 'c-empty']) }}>
    @if (filled($illust))
        <x-site.illust :name="$illust" class="c-empty__art" />
    @else
        <span class="c-empty__badge" aria-hidden="true"><x-site.icon :name="$icon" :size="40" class="c-empty__icon" /></span>
    @endif
    <h{{ $level }} class="c-empty__title">{{ $title }}</h{{ $level }}>
    @if ($slot->isNotEmpty())
        <div class="c-empty__text">{{ $slot }}</div>
    @endif
    @isset($actions)
        <div class="c-empty__actions">{{ $actions }}</div>
    @endisset
    @if ($tel)
        <p class="c-empty__tel">
            お電話でもご相談いただけます
            <a class="c-empty__tel-link" href="{{ config('shop.tel_href') }}" aria-label="電話をかける {{ config('shop.tel') }}">{{ config('shop.tel') }}</a><br>
            営業時間 {{ \App\Support\BusinessHours::summaryHtml() }}
        </p>
    @endif
</div>
