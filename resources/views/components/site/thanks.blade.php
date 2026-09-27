@props([
    'title',
    'steps' => [],
    'tel' => true,
])
{{--
    完了ページ（お問い合わせ・買取査定の受付完了で共通。案B：灰の斜線地に、上が赤い白カード）。中央寄せのカードに、受付完了のイラスト → h1 → リード →
    「このあとの流れ」→ お急ぎの方は電話 → 次の行動のボタン の順で並べる。ページ側では @section('contact_band', 'hide') を指定する。

    使い方:
        <x-site.thanks title="お問い合わせを受け付けました" :steps="['担当者が内容を確認します', '電話またはメールでご連絡します', …]">
            お問い合わせありがとうございます。…
            <x-slot:actions>
                <a class="c-btn c-btn--secondary" href="{{ route('cars.index') }}">在庫一覧を見る</a>
            </x-slot:actions>
        </x-site.thanks>

    title: h1 の文言（ページで唯一の h1）。改行の位置を決めたいときは HtmlString で u-nowrap の span を渡してよい
    steps: このあとの流れ。文字列の配列、または [['title' => .., 'text' => .., 'illust' => ..], …]。
           すべてに illust（x-site.illust の name）があれば、メダル型のイラストの図（x-site.medal-steps）で出す（お問い合わせ・買取査定の完了ページ）。
           illust がなければ番号付きの手順（c-steps）
    tel:   「お急ぎの方はお電話で」（斜めの赤帯）＋営業状況＋電話番号の2段ボタン＋営業時間を出すか
    slot:  リード文 / actions（名前付きスロット）：次の行動のボタン

    出力: <section class="c-thanks"><div class="l-container"><div class="c-thanks__card">
          <div class="c-thanks__icon"><h1 class="c-thanks__title"><div class="c-thanks__lead">
          <div class="c-thanks__section">（illust あり）<div class="c-medal-steps c-thanks__medals"><h2 class="c-subhead c-medal-steps__title">このあとの流れ</h2>…</div>
                                         （illust なし）<h2 class="c-subhead">このあとの流れ</h2><ol class="c-steps c-steps--vertical">…</ol></div>
          <section class="c-thanks__tel"><h2 class="c-thanks__tel-title"><span class="c-slant c-slant--red">お急ぎの方はお電話で</span></h2>
          x-site.open-status・x-site.btn2（黒・xl・電話番号）<p class="c-thanks__hours">…</p></section><div class="c-thanks__actions">…</div></div></div></section>
--}}
@php
    // すべての手順にイラストがあるときは、メダル型の図で出す
    $medal = $steps !== [] && collect($steps)->every(fn ($step): bool => is_array($step) && filled($step['illust'] ?? null));
    $shopTel = config('shop.tel');
@endphp
<section {{ $attributes->merge(['class' => 'c-thanks']) }} aria-labelledby="thanks-title">
    <div class="l-container">
        <div class="c-thanks__card">
            <div class="c-thanks__icon">
                <x-site.illust name="mail-check" />
            </div>
            <h1 id="thanks-title" class="c-thanks__title">{{ $title }}</h1>
            @if ($slot->isNotEmpty())
                <div class="c-thanks__lead">{{ $slot }}</div>
            @endif
            @if ($medal)
                <div class="c-thanks__section">
                    <x-site.medal-steps class="c-thanks__medals" title="このあとの流れ" :heading-level="2" :steps="$steps" />
                </div>
            @elseif ($steps !== [])
                <div class="c-thanks__section">
                    <h2 class="c-subhead">このあとの流れ</h2>
                    <ol class="c-steps c-steps--vertical" role="list">
                        @foreach ($steps as $step)
                            @php
                                $stepTitle = is_array($step) ? ($step['title'] ?? '') : $step;
                                $stepText = is_array($step) ? ($step['text'] ?? null) : null;
                            @endphp
                            <li class="c-steps__item">
                                <span class="c-steps__num">{{ $loop->iteration }}</span>
                                <div class="c-steps__body">
                                    <p class="c-steps__title">{{ $stepTitle }}</p>
                                    @if (filled($stepText))
                                        <p class="c-steps__text">{{ $stepText }}</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>
            @endif
            @if ($tel)
                {{-- お急ぎの方はお電話で（灰の面に、斜めの赤帯の見出し → 営業状況 → 大きな電話番号の2段ボタン → 営業時間）。
                     電話番号は数字の書体の1つのかたまりで、360px でも途中で折り返さない --}}
                <section class="c-thanks__tel" aria-labelledby="thanks-tel-title">
                    <h2 id="thanks-tel-title" class="c-thanks__tel-title"><span class="c-slant c-slant--red">お急ぎの方はお電話で</span></h2>
                    <x-site.open-status />
                    <x-site.btn2 class="c-thanks__tel-btn" :href="config('shop.tel_href')" variant="black" size="xl" block num icon="phone"
                        :small="'電話で相談する（'.\App\Support\BusinessHours::hoursLabel().'）'" :big="$shopTel" :aria-label="'電話をかける '.$shopTel" />
                    <p class="c-thanks__hours"><x-site.icon name="clock" :size="18" /><span>営業時間 {{ \App\Support\BusinessHours::summaryHtml() }}</span></p>
                </section>
            @endif
            @isset($actions)
                <div class="c-thanks__actions">{{ $actions }}</div>
            @endisset
        </div>
    </div>
</section>
