@props([
    'variant' => 'default',
])
{{--
    スマホ固定下部バー（959px以下の全ページ。高さ60px＋safe-area。案B：黒・濃い灰・赤の3色）。layouts/site から @yield('mbar', 'default') を渡して呼ぶ。
    電話の列の読み上げ名は「電話する 06-4960-8765」のように、見えている文字に番号を足したものにする。
    body には layouts/site が has-mbar を付け、フッターや注記がバーに隠れないよう下余白を足す。入力欄にフォーカス中は隠す（html.is-typing）。

    使い方（ページ側）:
        @section('mbar', 'contact')   … default | contact | buy | none

    variant:
        default … ［電話する（黒）］［LINEで相談（緑）または 在庫を探す（濃い灰）］［来店予約・問合せ（赤・少し広い）］
        contact … ［電話する（赤）］［LINEで相談 または 店舗案内（濃い灰）］
        buy     … ［電話で相談（黒）］［無料査定を申し込む（赤・#appraisal-form）］
        none    … 出さない

    出力: <nav class="l-mbar l-mbar--{variant}" aria-label="電話・お問い合わせ"><ul class="l-mbar__list">
          <li><a class="l-mbar__item l-mbar__item--{primary|line|plain|navy}">アイコン＋ラベル</a></li>…</ul></nav>
          （色の名前は旧名のまま：primary＝赤、navy＝黒、plain＝濃い灰、line＝LINE の緑）
--}}
@php
    $tel = config('shop.tel');
    $lineUrl = config('shop.line_url');
    // 電話の列の読み上げ名は「見えている文字＋番号」（見えている文字と読み上げ名を一致させる。WCAG 2.5.3）
    $telItem = ['label' => '電話する', 'icon' => 'phone', 'url' => config('shop.tel_href'), 'tone' => 'primary'];
    $lineItem = $lineUrl ? ['label' => 'LINEで相談', 'icon' => 'line', 'url' => $lineUrl, 'tone' => 'line', 'external' => true] : null;

    $items = match ($variant) {
        'contact' => [
            $telItem,
            $lineItem ?? ['label' => '店舗案内', 'icon' => 'store', 'url' => route('store'), 'tone' => 'plain'],
        ],
        'buy' => [
            ['label' => '電話で相談', 'tone' => 'navy'] + $telItem,
            ['label' => '無料査定を申し込む', 'icon' => 'yen', 'url' => route('buy.index').'#appraisal-form', 'tone' => 'primary'],
        ],
        'none' => [],
        default => [
            ['tone' => 'navy'] + $telItem,
            $lineItem ?? ['label' => '在庫を探す', 'icon' => 'search', 'url' => route('cars.index'), 'tone' => 'plain'],
            ['label' => '来店予約・問合せ', 'icon' => 'calendar', 'url' => route('contact.index', ['purpose' => 'visit']), 'tone' => 'primary'],
        ],
    };
@endphp
@if ($items !== [])
    <nav {{ $attributes->merge(['class' => 'l-mbar l-mbar--'.$variant]) }} aria-label="電話・お問い合わせ">
        <ul class="l-mbar__list">
            @foreach ($items as $item)
                <li>
                    <a class="l-mbar__item l-mbar__item--{{ $item['tone'] }}" href="{{ $item['url'] }}"
                       @if ($item['icon'] === 'phone') aria-label="{{ $item['label'] }} {{ $tel }}" @endif
                       @if (! empty($item['external'])) target="_blank" rel="noopener" @endif>
                        <x-site.icon :name="$item['icon']" :size="22" />
                        {{-- 幅の狭いスマホで2行になるときは「・」の後ろでだけ折り返す（「問合／せ」のような改行を防ぐ） --}}
                        <span>@foreach (explode('・', $item['label']) as $part)<span class="u-nowrap">{{ $part }}{{ $loop->last ? '' : '・' }}</span>@endforeach</span>
                        @if (! empty($item['external']))<span class="u-visually-hidden">（新しいタブで開きます）</span>@endif
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
@endif
