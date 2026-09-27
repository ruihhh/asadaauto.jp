@props([
    'price' => 150,
    'cars' => [],
    'carsLabel' => '掲載中の車で計算：',
    'idPrefix' => 'loan',
    'min' => 30,
    'max' => 500,
])
{{--
    お支払いシミュレーション（計算例。案B：左に入力、右に黒い結果の面）。元利均等返済で月々の目安を計算する（Alpine）。
    金利・回数は config('shop.loan') の例の値（example_rate＝3.9％、example_months＝36/48/60/72/84回、default_months＝60回）を初期値にし、
    利用者が金利を入力で変えられる。結果のすぐ近くに「計算例です。実際の金利・回数・お支払い額は、ローン会社の審査により異なります。」を出す。
    区画の見出しはページ側で「お支払いシミュレーション（計算例）」とする（x-site.section-head など）。

    使い方:
        <section class="l-section l-section--soft" id="loan" aria-labelledby="loan-title">
            <div class="l-container">
                <x-site.section-head id="loan-title" :title="new \Illuminate\Support\HtmlString('お支払いシミュレーション<small>（計算例）</small>')" en="LOAN"
                    lead="支払総額・頭金・回数・金利を動かすと、月々の目安がわかります。" />
                <x-site.loan-sim :cars="$cars" />
            </div>
        </section>

    price:    支払総額の初期値（万円）
    cars:     「掲載中の車で計算」のボタンにする車（App\Models\Car の配列。価格が応談・未入力の車は出さない。最大6台）
    carsLabel: 車のボタンの前の文言（既定「掲載中の車で計算：」。車両詳細で1台だけ渡すときは「この車の支払総額に戻す：」など）
    idPrefix: 欄の id の接頭辞（1ページに2つ置くときは変える）
    min / max: 支払総額のスライダーの範囲（万円）

    出力: <div class="c-loan" x-data="loanSim({…})">
          <div class="c-loan__form">支払総額（range）・掲載中の車のボタン（aria-pressed）・頭金（range）・支払回数（fieldset のラジオ .c-seg）・金利（number。範囲外はエラー文）</div>
          <div class="c-loan__result"><p class="c-loan__ex">計算例</p><p class="c-loan__monthly" aria-live="polite">約27,557円</p>
          <p class="c-loan__cond">60回・実質年率3.9%・頭金0円・ボーナス払いなし</p><p class="c-loan__note">計算例です。…（月々の金額のすぐ下）</p>
          元金と利息の比率バー／支払総額−頭金＝借りる金額の式／お支払い期間の年数ブロック／dl.c-loan__legend（借りる金額・利息の目安・お支払い合計）／
          ［この条件でローンを相談する］</div></div>
--}}
@php
    use App\Support\CarText;

    $rate = (float) config('shop.loan.example_rate', 3.9);
    $monthsList = array_values(array_filter(array_map('intval', (array) config('shop.loan.example_months', [36, 48, 60, 72, 84])), fn (int $m): bool => $m > 0));
    if ($monthsList === []) {
        $monthsList = [60];
    }
    $defaultMonths = (int) config('shop.loan.default_months', 60);
    if (! in_array($defaultMonths, $monthsList, true)) {
        $defaultMonths = $monthsList[intdiv(count($monthsList), 2)];
    }
    $price = max((int) $min, min((int) $max, (int) $price));
    $p = $idPrefix;

    $carOptions = collect($cars)
        ->filter(fn ($car) => $car->price !== null && ! $car->price_negotiable)
        ->map(fn ($car) => [
            'label' => filled($car->model) ? $car->model : CarText::name($car),
            'value' => (int) round($car->price / 10000),
            'price' => CarText::priceMan((int) $car->price) ?? number_format((int) round($car->price / 10000)),
        ])
        ->filter(fn (array $option): bool => $option['value'] >= $min && $option['value'] <= $max)
        ->take(6)
        ->values()
        ->all();

    // JavaScript が動く前にも同じ数字を出すため、初期値の計算をここでもしておく（元利均等返済）
    $principal = $price * 10000;
    $r = $rate / 100 / 12;
    $monthly = $principal <= 0 ? 0 : ($r > 0 ? $principal * $r / (1 - (1 + $r) ** -$defaultMonths) : $principal / $defaultMonths);
    $interest = max($monthly * $defaultMonths - $principal, 0);
    $man = fn (float $yen): string => number_format(round($yen / 1000) / 10, 1);
    $yearsLabel = fn (int $months): string => $months % 12 === 0 ? ($months / 12).'年' : intdiv($months, 12).'年'.($months % 12).'か月';
    $rateLabel = rtrim(rtrim(number_format($rate, 2, '.', ''), '0'), '.');
    $sum = $principal + $interest ?: 1;

    $config = [
        'price' => $price,
        'months' => $defaultMonths,
        'rate' => $rate,
        'min' => (int) $min,
        'max' => (int) $max,
    ];
@endphp
<div {{ $attributes->merge(['class' => 'c-loan']) }} x-data="loanSim(@js($config))">
    <div class="c-loan__form">
        <div class="c-loan__field">
            <div class="c-loan__row">
                <label class="c-loan__label" for="{{ $p }}-price">支払総額</label>
                <output class="c-loan__output" for="{{ $p }}-price"><span x-text="price">{{ $price }}</span><small>万円</small></output>
            </div>
            <input class="c-range" type="range" id="{{ $p }}-price" min="{{ $min }}" max="{{ $max }}" step="1" value="{{ $price }}"
                   x-model.number="price" :aria-valuetext="price + '万円'" aria-describedby="{{ $p }}-price-scale"
                   x-effect="$el.style.setProperty('--p', fill(price, min, max))">
            <p class="c-loan__scale" id="{{ $p }}-price-scale"><span>{{ $min }}万円</span><span>{{ $max }}万円</span></p>
            @if ($carOptions !== [])
                <div class="c-loan__cars" role="group" aria-labelledby="{{ $p }}-cars-label">
                    <span id="{{ $p }}-cars-label">{{ $carsLabel }}</span>
                    @foreach ($carOptions as $option)
                        <button type="button" class="c-loan__car" aria-pressed="false" :aria-pressed="price === {{ $option['value'] }} ? 'true' : 'false'"
                                x-on:click="price = {{ $option['value'] }}">{{ $option['label'] }}<b>{{ $option['price'] }}</b><span class="u-visually-hidden">万円</span></button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="c-loan__field">
            <div class="c-loan__row">
                <label class="c-loan__label" for="{{ $p }}-down">頭金</label>
                <output class="c-loan__output" for="{{ $p }}-down"><span x-text="down">0</span><small>万円</small></output>
            </div>
            <input class="c-range" type="range" id="{{ $p }}-down" min="0" max="{{ $price }}" :max="price" step="1" value="0"
                   x-model.number="down" :aria-valuetext="down + '万円'" aria-describedby="{{ $p }}-down-scale"
                   x-effect="$el.style.setProperty('--p', fill(down, 0, price))">
            <p class="c-loan__scale" id="{{ $p }}-down-scale"><span>0円</span><span><span x-text="price">{{ $price }}</span>万円</span></p>
        </div>

        <fieldset class="c-loan__field c-loan__months">
            <legend class="c-loan__label">支払回数</legend>
            <div class="c-seg">
                @foreach ($monthsList as $months)
                    <label class="c-seg__item">
                        <input class="c-seg__input" type="radio" name="{{ $p }}-months" value="{{ $months }}" x-model.number="months" @checked($months === $defaultMonths)>
                        <span class="c-seg__box"><b>{{ $months }}</b>回</span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <div class="c-loan__field c-loan__rate">
            <label class="c-loan__label" for="{{ $p }}-rate">金利（実質年率）</label>
            <span class="c-loan__rate-input">
                <input id="{{ $p }}-rate" type="number" inputmode="decimal" min="0" max="20" step="0.1" value="{{ $rateLabel }}"
                       x-model="rateInput" aria-describedby="{{ $p }}-rate-help {{ $p }}-rate-err" :aria-invalid="rateError ? 'true' : 'false'">%
            </span>
            <p class="c-loan__help" id="{{ $p }}-rate-help"><mark>例：実質年率{{ $rateLabel }}%で計算</mark><span class="u-nowrap">数字を変えて</span><span class="u-nowrap">試せます。</span></p>
            <p class="c-loan__err" id="{{ $p }}-rate-err" x-show="rateError" x-cloak>0〜20の数字を入れてください（いまは実質年率{{ $rateLabel }}%で計算しています）</p>
        </div>
    </div>

    <div class="c-loan__result">
        <p class="c-loan__ex" aria-hidden="true">計算例</p>
        <p class="c-loan__res-label">月々のお支払い（計算例）</p>
        <p class="c-loan__monthly" aria-live="polite" aria-atomic="true"><small>約</small><span x-text="yen(monthly)">{{ number_format(round($monthly)) }}</span><small>円</small></p>
        <p class="c-loan__cond" x-text="cond">{{ $defaultMonths }}回・実質年率{{ $rateLabel }}%・頭金0円・ボーナス払いなし</p>
        {{-- 計算例の注記は、月々の金額と条件のすぐ下に置く（結果から離さない） --}}
        <p class="c-loan__note">計算例です。実際の金利・回数・お支払い額は、ローン会社の審査により異なります。</p>

        <span class="c-loan__bar" aria-hidden="true"><svg viewBox="0 0 1000 20" preserveAspectRatio="none" focusable="false"><rect class="c-loan__bar-p" x="0" y="0" height="20" width="{{ round($principal / $sum * 1000) }}" :width="barP"/><rect class="c-loan__bar-r" y="0" height="20" x="{{ round($principal / $sum * 1000) + 4 }}" :x="barP + 4" width="{{ max(round($interest / $sum * 1000) - 4, 0) }}" :width="barR"/></svg></span>

        <div class="c-loan__formula" aria-hidden="true">
            <div class="c-price-breakdown__term c-price-breakdown__term--total"><span>支払総額</span><b><span x-text="price">{{ $price }}</span><small>万円</small></b></div>
            <span class="c-price-breakdown__op">−</span>
            <div class="c-price-breakdown__term c-price-breakdown__term--fee"><span>頭金</span><b><span x-text="down">0</span><small>万円</small></b></div>
            <span class="c-price-breakdown__op">＝</span>
            <div class="c-price-breakdown__term c-price-breakdown__term--light"><span>借りる金額</span><b><span x-text="man(principal)">{{ $man($principal) }}</span><small>万円</small></b></div>
        </div>

        <div class="c-loan__years" aria-hidden="true">
            <p class="c-loan__years-label">お支払い期間 <b x-text="yearsLabel">{{ $yearsLabel($defaultMonths) }}</b>（<span x-text="months">{{ $defaultMonths }}</span>回）</p>
            <ol class="c-loan__years-bar">
                <template x-for="y in yearBlocks" :key="y">
                    <li><x-site.icon name="calendar" /><span x-text="y + '年'"></span></li>
                </template>
            </ol>
        </div>

        <dl class="c-loan__legend">
            <div><dt><span class="c-loan__key" aria-hidden="true"></span>借りる金額</dt><dd><span x-text="man(principal)">{{ $man($principal) }}</span><small>万円</small></dd></div>
            <div><dt><span class="c-loan__key c-loan__key--r" aria-hidden="true"></span>利息の目安</dt><dd><span x-text="man(interest)">{{ $man($interest) }}</span><small>万円</small></dd></div>
            <div><dt>お支払い合計（頭金込み）</dt><dd><span x-text="man(total)">{{ $man($monthly * $defaultMonths) }}</span><small>万円</small></dd></div>
        </dl>

        <a class="c-btn c-btn--primary c-btn--block" href="{{ route('contact.index', ['purpose' => 'loan']) }}">この条件でローンを相談する</a>
    </div>
</div>

@once
    @push('scripts')
        <script>
        // お支払いシミュレーション（x-site.loan-sim）の計算。元利均等返済：月々 = 借りる金額 × 月利 ÷ (1 − (1 + 月利)^−回数)
        document.addEventListener('alpine:init', () => {
            window.Alpine.data('loanSim', (config) => ({
                price: config.price,
                down: 0,
                months: config.months,
                rateInput: String(config.rate),
                exampleRate: config.rate,
                min: config.min,
                max: config.max,
                init() {
                    this.$watch('price', (value) => {
                        if (this.down > value) this.down = value;
                    });
                },
                get rateError() {
                    const value = String(this.rateInput).trim();
                    const rate = Number(value);
                    return value === '' || !Number.isFinite(rate) || rate < 0 || rate > 20;
                },
                get rate() {
                    return this.rateError ? this.exampleRate : Number(this.rateInput);
                },
                get principal() {
                    return Math.max(this.price - Math.min(this.down, this.price), 0) * 10000;
                },
                get monthly() {
                    const p = this.principal;
                    const n = this.months;
                    const r = this.rate / 100 / 12;
                    if (p <= 0 || n <= 0) return 0;
                    return r > 0 ? p * r / (1 - Math.pow(1 + r, -n)) : p / n;
                },
                get interest() {
                    return Math.max(this.monthly * this.months - this.principal, 0);
                },
                get total() {
                    return this.monthly * this.months + Math.min(this.down, this.price) * 10000;
                },
                get cond() {
                    const down = Math.min(this.down, this.price);
                    return this.months + '回・実質年率' + this.rate + '%・頭金' + (down > 0 ? down + '万円' : '0円') + '・ボーナス払いなし';
                },
                get yearsLabel() {
                    return this.months % 12 === 0 ? (this.months / 12) + '年' : Math.floor(this.months / 12) + '年' + (this.months % 12) + 'か月';
                },
                get yearBlocks() {
                    return Array.from({ length: Math.ceil(this.months / 12) }, (_, i) => i + 1);
                },
                get barP() {
                    const sum = this.principal + this.interest || 1;
                    return Math.round(this.principal / sum * 1000);
                },
                get barR() {
                    return Math.max(1000 - this.barP - 4, 0) * (this.interest > 0 ? 1 : 0);
                },
                fill(value, min, max) {
                    return max > min ? ((value - min) / (max - min) * 100) + '%' : '0%';
                },
                yen(value) {
                    return Math.round(value).toLocaleString('ja-JP');
                },
                man(value) {
                    return (Math.round(value / 1000) / 10).toFixed(1);
                },
            }));
        });
        </script>
    @endpush
@endonce
