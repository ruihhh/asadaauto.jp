@props([
    'title',
    'lead' => null,
    'breadcrumbs' => [],
    'home' => true,
    'narrow' => false,
    'flush' => false,
    'en' => null,
])
{{--
    下層ページの先頭に置く「パンくず＋h1＋リード」の帯。1ページに h1 は1つだけ（この帯の h1）。
    案B：灰の斜線地に、黒い斜め帯の h1（右端に赤い帯・左下に赤い線）。en を渡すと h1 の上に小さな英字の飾り（読み上げない）。

    使い方:
        <x-site.page-header title="店舗案内・アクセス" lead="兵庫県尼崎市下坂部の中古車販売店です。" />
            → パンくず「ホーム › 店舗案内・アクセス」
        <x-site.page-header :title="$name" :breadcrumbs="[
            ['label' => '中古車在庫一覧', 'url' => route('cars.index')],
            ['label' => $name],
        ]" />
        <x-site.page-header title="…" narrow />   … 本文が l-container--narrow（760px）のページ（フォーム・文章）。見出しの左端を本文とそろえる
        <x-site.page-header title="…" flush />    … 2カラムの列の中に置く（帯の背景・上の余白・内側の左右の余白なし）
        <x-site.page-header title="…">お知らせなどの追加の中身（任意）</x-site.page-header>
        <x-site.page-header title="中古車在庫一覧" en="STOCK LIST" />   … 英字の飾りを付ける

    title:       h1 の文言。改行の位置を決めたいときは HtmlString で u-nowrap の span を渡してよい
                 （例：new HtmlString('<span class="u-nowrap">尼崎の中古車買取・</span><span class="u-nowrap">無料査定</span>')）
    lead:        リード文（任意。2行程度まで）。title と同じく HtmlString も渡せる
    breadcrumbs: [['label' => .., 'url' => ..], …]。先頭の「ホーム」は自動で付く。url のない最後の項目が現在地（aria-current="page"）。
                 空なら「ホーム › {title}」になる（title が HtmlString のときはタグを除いた文字）
    home:        false のとき「ホーム」を自動で付けない
    narrow:      内側の幅を l-container--narrow（760px）にする
    flush:       2カラムの列の中に置く形（c-page-header--flush）
    en:          h1 の上に置く英字の飾り（任意。aria-hidden で読み上げない。意味は日本語の h1 で伝える）

    パンくずだけが必要なページ（h1 を別の場所に置く車両詳細など）は x-site.breadcrumb を使う。

    出力: <div class="c-page-header [c-page-header--flush]"><div class="l-container [l-container--narrow]"><nav class="c-breadcrumb" aria-label="現在地">…</nav>
          [<span class="c-page-header__en" aria-hidden="true">…</span>]<h1 class="c-page-title">…</h1><p class="c-page-lead">…</p></div></div>
--}}
@php
    $items = $breadcrumbs !== [] ? $breadcrumbs : [['label' => $title instanceof \Illuminate\Contracts\Support\Htmlable ? strip_tags($title->toHtml()) : $title]];
@endphp
<div {{ $attributes->merge(['class' => 'c-page-header'.($flush ? ' c-page-header--flush' : '')]) }}>
    <div class="l-container{{ $narrow ? ' l-container--narrow' : '' }}">
        <x-site.breadcrumb :items="$items" :home="$home" />
        @if (filled($en))
            <span class="c-page-header__en" aria-hidden="true" lang="en">{{ $en }}</span>
        @endif
        <h1 class="c-page-title">{{ $title }}</h1>
        @if (filled($lead))
            <p class="c-page-lead">{{ $lead }}</p>
        @endif
        @if ($slot->isNotEmpty())
            <div class="c-page-header__extra">{{ $slot }}</div>
        @endif
    </div>
</div>
