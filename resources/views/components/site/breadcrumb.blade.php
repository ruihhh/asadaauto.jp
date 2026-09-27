@props([
    'items' => [],
    'home' => true,
])
{{--
    パンくず（「ホーム › 中古車在庫一覧 › マツダ CX-5」）。x-site.page-header の中でも使う。
    h1 をページの別の場所に置くページ（車両詳細など）では、page-header の代わりにこれだけを置く。

    使い方:
        <x-site.breadcrumb :items="[['label' => '中古車在庫一覧', 'url' => route('cars.index')], ['label' => $name]]" />

    items: [['label' => .., 'url' => ..], …]。先頭の「ホーム」は自動で付く。url のない最後の項目が現在地（aria-current="page"）
    home:  false のとき「ホーム」を自動で付けない

    出力: <nav class="c-breadcrumb" aria-label="現在地"><ol class="c-breadcrumb__list"><li class="c-breadcrumb__item">
          <a class="c-breadcrumb__link">ホーム</a><span class="c-breadcrumb__sep">›</span></li>…
          <li class="c-breadcrumb__item"><span class="c-breadcrumb__current" aria-current="page">…</span></li></ol></nav>
--}}
@php
    $list = array_values($items);
    if ($home) {
        array_unshift($list, ['label' => 'ホーム', 'url' => route('home')]);
    }
    $lastIndex = count($list) - 1;
@endphp
<nav {{ $attributes->merge(['class' => 'c-breadcrumb']) }} aria-label="現在地">
    <ol class="c-breadcrumb__list">
        @foreach ($list as $i => $item)
            <li class="c-breadcrumb__item">
                @if (! empty($item['url']) && $i !== $lastIndex)
                    <a class="c-breadcrumb__link" href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                @else
                    <span class="c-breadcrumb__current" aria-current="page">{{ $item['label'] }}</span>
                @endif
                @if ($i !== $lastIndex)
                    <span class="c-breadcrumb__sep" aria-hidden="true">›</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
