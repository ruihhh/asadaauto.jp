@props([
    'paginator',
    'unit' => '件',
])
{{--
    ページ送り（c-pagination）。1ページに収まるときは何も出さない。クエリ文字列は paginator 側で withQueryString() しておく。

    使い方:
        <x-site.pagination :paginator="$cars" unit="台" />

    paginator: LengthAwarePaginator（paginate() の戻り値）。simplePaginate() のときは「前へ／次へ」だけになる
    unit:      件数の単位（「全12台中 1〜12台目」の「台」）

    出力: <nav class="c-pagination" aria-label="ページ送り"><ul class="c-pagination__list">
          <li><a class="c-pagination__link" rel="prev">前へ</a></li><li><span class="c-pagination__link is-current" aria-current="page">1</span></li>…
          </ul><p class="c-pagination__summary">全24台中 1〜12台目を表示</p></nav>
--}}
@php
    $lengthAware = $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
    $window = $lengthAware ? \Illuminate\Pagination\UrlWindow::make($paginator) : null;
    $elements = $window !== null ? array_filter([
        $window['first'],
        is_array($window['slider']) ? '…' : null,
        $window['slider'],
        is_array($window['last']) ? '…' : null,
        $window['last'],
    ]) : [];
@endphp
@if ($paginator->hasPages())
    <nav {{ $attributes->merge(['class' => 'c-pagination']) }} aria-label="ページ送り">
        <ul class="c-pagination__list">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="c-pagination__link is-disabled" aria-disabled="true">前へ</span>
                @else
                    <a class="c-pagination__link" href="{{ $paginator->previousPageUrl() }}" rel="prev">前へ</a>
                @endif
            </li>
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="c-pagination__gap" aria-hidden="true">{{ $element }}</span></li>
                @else
                    @foreach ($element as $page => $url)
                        <li>
                            @if ($page == $paginator->currentPage())
                                <span class="c-pagination__link is-current" aria-current="page"><span class="u-visually-hidden">ページ</span>{{ $page }}</span>
                            @else
                                <a class="c-pagination__link" href="{{ $url }}"><span class="u-visually-hidden">ページ</span>{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach
            <li>
                @if ($paginator->hasMorePages())
                    <a class="c-pagination__link" href="{{ $paginator->nextPageUrl() }}" rel="next">次へ</a>
                @else
                    <span class="c-pagination__link is-disabled" aria-disabled="true">次へ</span>
                @endif
            </li>
        </ul>
        @if ($lengthAware && $paginator->total() > 0)
            <p class="c-pagination__summary">全{{ $paginator->total() }}{{ $unit }}中 {{ $paginator->firstItem() }}〜{{ $paginator->lastItem() }}{{ $unit }}目を表示</p>
        @endif
    </nav>
@endif
