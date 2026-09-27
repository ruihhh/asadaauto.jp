@props([
    'href',
    'title',
    'sub' => null,
    'icon' => 'chevron-right',
    'tone' => 'red',
    'count' => null,
    'unit' => '台',
])
{{--
    アイコン付きのリンクカード（案B：丸いアイコン＋見出し・説明＋右に台数または矢印）。「使い方から選ぶ」「ローンのご相談」「車検・一般整備」など。
    カード全体が1つのリンク（中にボタンを入れない）。並べるときは l-grid（l-grid--2 / --3 / --4）。

    使い方:
        <x-site.link-card :href="route('cars.index', ['body_type' => '軽自動車'])" icon="bag" title="通勤・お買い物に" sub="小回りのきく軽自動車" :count="1" />
        <x-site.link-card href="…#loan" icon="calc" :title="new \Illuminate\Support\HtmlString('ローンの<b>ご相談</b>')" sub="月々の目安から一緒に考えます" />

    href:  リンク先
    title: 見出し（HtmlString で <b> を入れると赤く大きくなる）
    sub:   説明（任意）
    icon:  x-site.icon の name
    tone:  アイコンの丸の色 red（既定）| yellow | black | tint | green
    count: 右に出す台数（任意。実数だけ）。ないときは右に矢印を出す
    unit:  台数の単位

    出力: <a class="c-link-card"><span class="c-link-card__ic c-link-card__ic--{tone}"><svg></span>
          <span class="c-link-card__txt"><span class="c-link-card__title">…</span><span class="c-link-card__sub">…</span></span>
          <span class="c-link-card__n">1<small>台</small></span> または <svg class="c-link-card__chev"></a>
--}}
<a {{ $attributes->merge(['class' => 'c-link-card']) }} href="{{ $href }}">
    <span class="c-link-card__ic c-link-card__ic--{{ $tone }}" aria-hidden="true"><x-site.icon :name="$icon" /></span>
    <span class="c-link-card__txt">
        <span class="c-link-card__title">{{ $title }}</span>
        @if (filled($sub))<span class="c-link-card__sub">{{ $sub }}</span>@endif
    </span>
    @if ($count !== null)
        <span class="c-link-card__n">{{ $count }}<small>{{ $unit }}</small></span>
    @else
        <x-site.icon name="chevron-right" class="c-link-card__chev" />
    @endif
</a>
