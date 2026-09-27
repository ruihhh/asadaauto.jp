@props([
    'car',
    'actions' => true,
    'inquiry' => true,
    'headingLevel' => 3,
    'loading' => 'lazy',
    'compact' => false,
    'favRemove' => false,
])
{{--
    車両カード（案B）。写真（左上に店長おすすめのリボン・右上に状態タグ・左下に在庫番号・右下にボディタイプ）→ 車名 → グレード →
    価格（「支払総額／税込」の赤いラベル＋大きな数字）→ 状態表（年式・走行距離・車検・修復歴の2×2＋ミッション・燃料）→
    保証・整備（設定があるときだけ）→ 操作ボタン（［在庫確認・見積もり（無料）］赤／［お気に入り］［比較に追加］）。
    カード全体を <a> で包まない（写真と車名だけがリンク）。並べるときは l-grid l-grid--3（スマホ1列・600px以上2列・960px以上3列）。

    使い方:
        <ul class="l-grid l-grid--3" role="list">
            @foreach ($cars as $car)
                <li><x-site.car-card :car="$car" /></li>
            @endforeach
        </ul>
        <x-site.car-card :car="$car" :actions="false" />        … 関連車両など（ボタンなし）
        <x-site.car-card :car="$car" :inquiry="false" />        … お気に入り・比較のボタンだけ
        <x-site.car-card :car="$car" :heading-level="2" />
        <x-site.car-card :car="$car" compact />                  … トップの「いま掲載中の車」（写真を 16:10 に・お気に入り／比較のボタンなし）
        <x-site.car-card :car="$car" fav-remove />               … お気に入りページ（登録済みのボタンを［お気に入りから外す］と表示）

    car:          App\Models\Car（写真の枚数を出すため images を eager load しておくとよい）
    actions:      操作ボタン（在庫確認・お気に入り・比較）を出すか
    inquiry:      ［在庫確認・見積もり（無料）］を出すか（actions=true のとき）
    headingLevel: 車名の見出しレベル（2〜4）
    loading:      写真の loading 属性（lazy | eager）。一覧の1行目など、最初の画面に入るカードは eager にする
    compact:      トップ用の短い版。写真を 16:10 にし、［お気に入り］［比較に追加］を出さない（［在庫確認・見積もり（無料）］は出す）。
                  車名・価格・状態表・在庫番号はほかのページと同じ
    favRemove:    お気に入りページ用。登録済みのボタンを「お気に入り済み」ではなく「お気に入りから外す」と表示する
                  （押すと外れる操作のボタンにするため aria-pressed は付けない）

    出力: <article class="c-car-card [c-car-card--sold|--reserved] [c-car-card--compact]"><div class="c-car-card__photo"><a class="c-car-card__media">写真（なければボディタイプのイラスト＋写真準備中）</a>
          <p class="c-ribbon c-car-card__ribbon">店長おすすめ</p><p class="c-car-card__tags">状態タグ（c-tag--caution/sold/new）・写真 ◯枚</p>
          <p class="c-car-card__stock">在庫番号 MZ4187</p><p class="c-car-card__type">SUV</p></div>
          <div class="c-car-card__body"><h3 class="c-car-card__title"><a class="c-car-card__title-link"><span class="c-car-card__title-text">トヨタ プリウス</span></a></h3>
          （車名のリンクは見た目の位置を変えずに押せる高さを 44px 以上にしてある。写真のリンクは読み上げ・キーボードの対象外）
          <p class="c-car-card__grade"> x-site.price x-site.car-facts
          <p class="c-car-card__status">（売約済・商談中のときだけ）
          <div class="c-car-card__actions">［在庫確認・見積もり（無料）］<div class="c-car-card__toggles">［お気に入り］［比較に追加］</div></div></div></article>
    status：sold（売約済）は［在庫確認・見積もり］と［比較に追加］を出さず「この車は売約済みです。」を出す。
            reserved（商談中）はボタンを残し「ただいま商談中です。状況はお問い合わせください。」を添える。
    お気に入り・比較の状態は $store.favorites / $store.compare（x-site.scripts）で管理する。
--}}
@php
    use App\Support\CarText;

    $name = CarText::name($car);
    $url = route('cars.show', $car);
    $level = min(max((int) $headingLevel, 2), 4);

    $images = collect();
    if (filled($car->image_path)) {
        $images->push($car->image_path);
    }
    if ($car->relationLoaded('images') || $images->isEmpty()) {
        foreach ($car->images as $image) {
            $images->push($image->path);
        }
    }
    $photo = $images->first();
    $tags = CarText::tags($car);
    // 店長おすすめは写真の左上のリボン、ほかの状態（商談中・売約済・新着）は写真の右上のタグにする
    $pick = collect($tags)->contains(fn (array $tag): bool => $tag['variant'] === 'pick');
    $stateTags = array_values(array_filter($tags, fn (array $tag): bool => $tag['variant'] !== 'pick'));
    $bodyType = filled($car->body_type) ? CarText::bodyType($car->body_type) : null;
    $warranty = CarText::warranty();
    $maintenance = CarText::maintenance();

    // 売約済：在庫確認のボタンと比較は出さない（お気に入りから外せるよう、お気に入りのボタンだけ残す）
    // 商談中：ボタンはそのまま出し、状況を文字で添える
    $sold = $car->status === 'sold';
    $reserved = $car->status === 'reserved';
    $stateClass = $sold ? ' c-car-card--sold' : ($reserved ? ' c-car-card--reserved' : '');
    if ($compact) {
        $stateClass .= ' c-car-card--compact';
    }
    $showInquiry = $inquiry && ! $sold;
    $showToggles = ! $compact;
    $favOnLabel = $favRemove ? 'お気に入りから外す' : 'お気に入り済み';
    $favOffLabel = $favRemove ? 'お気に入りに戻す' : 'お気に入り';
@endphp
<article {{ $attributes->merge(['class' => 'c-car-card'.$stateClass]) }}>
    {{-- 写真（リンクは読み上げ・キーボードの対象外）。写真の上の文字（リボン・状態・在庫番号・ボディタイプ）はリンクの外に置いて読み上げる --}}
    <div class="c-car-card__photo">
        <a class="c-car-card__media" href="{{ $url }}" tabindex="-1" aria-hidden="true">
            @if ($photo)
                <img class="c-car-card__img" src="{{ asset('images/'.$photo) }}" alt="{{ $name }}の写真" width="640" height="480" loading="{{ $loading === 'eager' ? 'eager' : 'lazy' }}" decoding="async">
            @else
                <span class="c-car-card__noimg">
                    <x-site.illust :name="\App\Support\CarText::bodyIllust($car->body_type)" />
                    写真準備中
                </span>
            @endif
        </a>
        @if ($pick)
            <p class="c-ribbon c-car-card__ribbon"><x-site.icon name="star" />店長おすすめ</p>
        @endif
        @if ($stateTags !== [] || $images->count() >= 2)
            <p class="c-car-card__tags">
                @foreach ($stateTags as $tag)
                    <span class="c-tag c-tag--{{ $tag['variant'] }}">{{ $tag['label'] }}</span>
                @endforeach
                @if ($images->count() >= 2)
                    <span class="c-car-card__count"><x-site.icon name="camera" :size="14" />写真 {{ $images->count() }}枚</span>
                @endif
            </p>
        @endif
        @if (filled($car->stock_no))
            <p class="c-car-card__stock">在庫番号 {{ $car->stock_no }}</p>
        @endif
        @if ($bodyType !== null)
            <p class="c-car-card__type">{{ $bodyType }}</p>
        @endif
    </div>
    <div class="c-car-card__body">
        <div class="c-car-card__head">
            <h{{ $level }} class="c-car-card__title"><a class="c-car-card__title-link" href="{{ $url }}"><span class="c-car-card__title-text">{{ $name }}</span></a></h{{ $level }}>
            @if (filled($car->grade))
                <p class="c-car-card__grade">{{ $car->grade }}</p>
            @endif
        </div>
        <x-site.price :car="$car" size="card" :note="false" />
        <x-site.car-facts :car="$car" variant="card" />
        @if ($warranty !== null || $maintenance !== null)
            <p class="c-car-card__assure">
                @if ($warranty !== null)保証：{{ $warranty }}@endif
                @if ($warranty !== null && $maintenance !== null)／@endif
                @if ($maintenance !== null)定期点検整備：{{ $maintenance }}@endif
            </p>
        @endif
        @if ($sold)
            <p class="c-car-card__status">この車は売約済みです。</p>
        @elseif ($reserved)
            <p class="c-car-card__status c-car-card__status--reserved">ただいま商談中です。状況はお問い合わせください。</p>
        @endif
        @if ($actions && ($showInquiry || $showToggles))
            <div class="c-car-card__actions">
                @if ($showInquiry)
                    <a class="c-btn c-btn--primary c-btn--block" href="{{ route('contact.index', ['stock_no' => $car->stock_no, 'purpose' => 'stock']) }}">在庫確認・見積もり（無料）</a>
                @endif
                @if ($showToggles)
                    <div class="c-car-card__toggles{{ $favRemove ? ' c-car-card__toggles--wide' : '' }}" x-data="{ id: @js((int) $car->getKey()), name: @js($name) }">
                        @if ($favRemove)
                            <button type="button" class="c-car-card__toggle" x-on:click="$store.favorites.toggle(id)">
                                <x-site.icon name="heart" :size="18" x-show="! $store.favorites.has(id)" x-cloak />
                                <x-site.icon name="heart-fill" :size="18" x-show="$store.favorites.has(id)" />
                                <span x-text="$store.favorites.has(id) ? '{{ $favOnLabel }}' : '{{ $favOffLabel }}'">{{ $favOnLabel }}</span>
                            </button>
                        @else
                            <button type="button" class="c-car-card__toggle" aria-pressed="false" :aria-pressed="$store.favorites.has(id) ? 'true' : 'false'" x-on:click="$store.favorites.toggle(id)">
                                <x-site.icon name="heart" :size="18" x-show="! $store.favorites.has(id)" />
                                <x-site.icon name="heart-fill" :size="18" x-show="$store.favorites.has(id)" x-cloak />
                                <span x-text="$store.favorites.has(id) ? '{{ $favOnLabel }}' : '{{ $favOffLabel }}'">{{ $favOffLabel }}</span>
                            </button>
                        @endif
                        @unless ($sold)
                            <button type="button" class="c-car-card__toggle" aria-pressed="false" :aria-pressed="$store.compare.has(id) ? 'true' : 'false'" x-on:click="$store.compare.toggle(id, name)">
                                <x-site.icon name="compare" :size="18" x-show="! $store.compare.has(id)" />
                                <x-site.icon name="check" :size="18" x-show="$store.compare.has(id)" x-cloak />
                                <span x-text="$store.compare.has(id) ? '比較中' : '比較に追加'">比較に追加</span>
                            </button>
                        @endunless
                    </div>
                @endif
            </div>
        @endif
    </div>
</article>
