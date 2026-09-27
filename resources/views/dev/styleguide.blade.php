@extends('layouts.site')

{{--
    共通部品の見本（開発用。APP_ENV=local のときだけ /_styleguide に登録される）。
    見た目の正本はモックアップ案B「車選び型：写真ヒーローと斜め帯」。公開サイトは site.css だけで表示する（Tailwind は読み込まない）。
--}}

@section('title', '部品の見本（開発用）')
@section('meta_robots', 'noindex, nofollow')
@section('body_class', 'p-dev')

@php
    use App\Models\Car;
    use App\Support\BusinessHours;
    use App\Support\CarText;
    use Carbon\CarbonImmutable;
    use Illuminate\Support\HtmlString;

    $cars = Car::query()->publicInventory()->with('images')->orderByDesc('featured')->orderByDesc('published_at')->get();
    $car = $cars->first();

    if ($car !== null) {
        // 写真なし・商談中・店長おすすめの見本
        $noPhoto = $car->replicate();
        $noPhoto->image_path = null;
        $noPhoto->status = 'reserved';
        $noPhoto->featured = true;
        $noPhoto->body_type = '軽自動車';
        $noPhoto->setRelation('images', collect());

        // 修復歴あり・車検2年付きの見本
        $repairCar = $car->replicate();
        $repairCar->accident_count = 1;
        $repairCar->inspection_type = '2年付';
        $repairCar->featured = false;
        $repairCar->setRelation('images', $car->images);

        // 応談の見本
        $askCar = $car->replicate();
        $askCar->price_negotiable = true;

        // 売約済・車両本体価格が未入力の見本
        $soldCar = $car->replicate();
        $soldCar->status = 'sold';
        $soldCar->featured = false;
        $soldCar->base_price = null;
        $soldCar->setRelation('images', $car->images);

        // 車両本体価格が未入力・車検の期限切れの見本
        $noBaseCar = $car->replicate();
        $noBaseCar->featured = false;
        $noBaseCar->base_price = null;
        $noBaseCar->inspection_type = null;
        $noBaseCar->inspection_expiry = CarbonImmutable::now(BusinessHours::TIMEZONE)->subMonths(2);
        $noBaseCar->setRelation('images', $car->images);

        // 車検の期限ありの見本
        $expiryCar = $car->replicate();
        $expiryCar->inspection_type = null;
        $expiryCar->inspection_expiry = CarbonImmutable::now(BusinessHours::TIMEZONE)->addMonths(18);
    }

    $tz = BusinessHours::TIMEZONE;
    $statusSamples = [
        '営業中（2026-10-20 火 14:00）' => CarbonImmutable::create(2026, 10, 20, 14, 0, 0, $tz),
        '開店前（2026-10-20 火 10:00）' => CarbonImmutable::create(2026, 10, 20, 10, 0, 0, $tz),
        '営業終了（2026-10-19 月 21:30）' => CarbonImmutable::create(2026, 10, 19, 21, 30, 0, $tz),
        '定休日・木曜（2026-10-01 12:00）' => CarbonImmutable::create(2026, 10, 1, 12, 0, 0, $tz),
        '定休日・第3日曜（2026-10-18 12:00）' => CarbonImmutable::create(2026, 10, 18, 12, 0, 0, $tz),
    ];

    $swatches = [
        'red' => '--c-red #D7000F（主色・支払総額）',
        'red-dark' => '--c-red-dark #A8000B（押下・影）',
        'red-tint' => '--c-red-tint #FFF0F0',
        'black' => '--c-black #141414（面・帯。旧 --c-navy）',
        'gray' => '--c-gray #3A3A3A',
        'text' => '--c-text #1E1E1E（本文）',
        'text-sub' => '--c-text-sub #555555（補足）',
        'yellow' => '--c-yellow #FFD60A（差し色・黒文字専用）',
        'yellow-tint' => '--c-yellow-tint #FFF7D1',
        'bg-soft' => '--c-bg-soft #F3F3F3',
        'border' => '--c-border #D9D9D9',
        'link' => '--c-link #0B57B7（文中のリンク）',
        'line' => '--c-line-btn #008236（LINE）',
        'ok' => '--c-ok #0B7A3E（修復歴なし・営業中）',
        'check' => '--c-check #8A5A00（要確認・定休日）',
        'caution' => '--c-caution #B34700（修復歴あり・商談中）',
    ];

    $icons = ['phone', 'line', 'mail', 'map-pin', 'clock', 'calendar', 'cal-check', 'car', 'heart', 'heart-fill', 'compare', 'search', 'zoom', 'menu', 'close',
        'check', 'alert', 'info', 'chevron-right', 'chevron-left', 'chevron-down', 'chevron-up', 'camera', 'external', 'user', 'star', 'wakaba',
        'store', 'route', 'parking', 'train', 'bus', 'shield', 'file', 'wrench', 'yen', 'calc', 'tag', 'bankin',
        'meter', 'frame', 'gear', 'fuel', 'drop', 'bag', 'family', 'mountain', 'leaf'];
    $equipIcons = ['eq-brake', 'eq-360', 'eq-camera', 'eq-navi', 'eq-etc', 'eq-dashcam', 'eq-seat', 'eq-slide', 'eq-cruise', 'eq-key',
        'eq-3row', 'eq-aircon', 'eq-lane', 'eq-sunroof', 'eq-wheel', 'eq-check'];

    $bodyIllusts = [
        'kei' => '軽自動車', 'compact' => 'コンパクトカー', 'minivan' => 'ミニバン', 'suv' => 'SUV', 'sedan' => 'セダン', 'hatchback' => 'ハッチバック',
        'wagon' => 'ステーションワゴン', 'sports' => 'スポーツ・クーペ', 'welfare' => '福祉車両', 'truck' => 'トラック', 'other' => 'その他', 'all' => 'すべて',
    ];
    $roundIllusts = [
        'coins' => 'coins（予算）', 'meter' => 'meter（走行距離）',
        'p-total' => 'p-total（支払総額）', 'p-frame' => 'p-frame（修復歴）', 'p-shaken' => 'p-shaken（車検）', 'p-explain' => 'p-explain（保証と整備）',
        'f-search' => 'f-search（探す）', 'f-store' => 'f-store（来店）', 'f-contract' => 'f-contract（契約）', 'f-key' => 'f-key（納車）',
        'buy' => 'buy（買取査定）', 'loan' => 'loan（ローン）', 'service' => 'service（車検・整備）', 'empty' => 'empty（見つからない）', 'mail-check' => 'mail-check（受付完了）',
    ];

    $bodyCounts = $cars->groupBy(fn ($c) => (string) $c->body_type)->map->count()->sortDesc();

    $faqItems = [
        ['q' => '支払総額には何が含まれますか？', 'a' => "税金・自賠責保険料・登録などの手続き費用を含みます。\n県外での登録、ご自宅への納車、ご希望のオプションの費用は含みません。"],
        ['q' => '修復歴とは何ですか？長い質問が2行になったときも、アイコンと文字が重ならずに折り返されることを確認するための見本です。', 'a' => '車の骨格（フレーム）部分を修理・交換した履歴のことです。'],
    ];

    $flowSteps = [
        ['title' => '在庫を探す・問い合わせ', 'text' => '気になる車が見つかったら、電話かフォームで在庫確認を。見積もりは無料です。', 'illust' => 'f-search'],
        ['title' => '来店・見学・試乗', 'text' => '実際の車を見て、座って、確かめてください。試乗のご希望もお伝えください。', 'illust' => 'f-store'],
        ['title' => 'ご契約・書類', 'text' => 'お支払い方法（現金・ローン）を決めて、必要な書類をご案内します。', 'illust' => 'f-contract'],
        ['title' => '納車', 'text' => '準備が整ったら納車です。ご自宅への納車をご希望の場合はご相談ください（別途費用）。', 'illust' => 'f-key'],
    ];

    $promiseRepairTitle = new HtmlString('<span class="u-nowrap">修復歴を</span><span class="u-nowrap">「あり・なし」で表示</span>');

    $demoErrors = new \Illuminate\Support\MessageBag([
        'name' => ['お名前を入力してください。'],
        'email' => ['メールアドレスの形式が正しくありません（例：taro@example.com）。'],
    ]);

    $demoPaginator = new \Illuminate\Pagination\LengthAwarePaginator(range(1, 12), 60, 12, 2, ['path' => url('/_styleguide')]);
@endphp

@section('content')
<x-site.page-header title="部品の見本（開発用）" en="STYLE GUIDE" lead="共通部品（components/site と site.css の §00〜§05）の見た目を確認するページです。見た目の正本はモックアップ案B「車選び型」です。" />

{{-- ================= 写真ヒーロー・ヒーロー下のパネル ================= --}}
<section aria-labelledby="sg-hero" class="p-dev-hero">
    <div class="l-container">
        <p class="p-dev-label" id="sg-hero">c-hero（写真＋暗いグラデーション・斜め帯のキャッチ・丸いバッジ・営業状況）＋ c-deck（ヒーローの下端に重ねる白いパネル）。実際のトップではキャッチを h1 にする</p>
    </div>
    <div class="c-hero">
        <picture>
            <source type="image/webp" srcset="{{ asset('images/store-hero-bg.png.webp') }}">
            <img class="c-hero__img" src="{{ asset('images/store-hero-bg.png') }}" alt="アサダオートサポートの店舗外観。看板の前に展示車が並んでいる" width="1584" height="672">
        </picture>
        <div class="l-container c-hero__inner">
            <p class="c-hero__tag"><x-site.icon name="map-pin" />兵庫県尼崎市下坂部の中古車販売店</p>
            <p class="c-hero__catch">
                <span class="c-slant c-slant--black">納得して選べる、</span>
                <span class="c-slant c-slant--red">尼崎の中古車。</span>
            </p>
            <p class="c-slant c-slant--white c-hero__sub"><em>支払総額</em>・<em>修復歴</em>・<em>車検の期限</em>まで、見てわかる。</p>
            <x-site.round-badge class="c-hero__badge" top="ただいま" :num="$cars->count()" unit="台" bottom="掲載中" :href="route('cars.index')" :label="'ただいま'.$cars->count().'台掲載中。掲載中の車を見る'" />
            <p class="c-hero__pill"><x-site.open-status />{{ BusinessHours::hoursLabel() }}｜{{ config('shop.closed_label') }}定休</p>
            <p class="c-hero__caption">写真：店舗外観（この看板が目印です）</p>
        </div>
    </div>
    <div class="l-container">
        <div class="c-deck">
            <div class="c-deck__actions">
                <x-site.btn2 :href="route('cars.index')" size="xl" block bubble="全車 支払総額で表示" small="写真・支払総額つきで掲載中" :big="'掲載中の車を見る（'.$cars->count().'台）'" />
                <x-site.btn2 :href="config('shop.tel_href')" variant="black" size="xl" block num icon="phone" :small="'電話で相談する（'.BusinessHours::hoursLabel().'）'" :big="config('shop.tel')" />
            </div>
            <div class="c-deck__aside">
                <p class="c-deck__label">当店のお約束</p>
                <ul class="c-deck__badges">
                    <li><span class="c-deck__badge-ic"><x-site.icon name="tag" /></span><span class="c-deck__badge-t">支払総額で<br>表示</span></li>
                    <li><span class="c-deck__badge-ic"><x-site.icon name="frame" /></span><span class="c-deck__badge-t">修復歴を<br>明示</span></li>
                    <li><span class="c-deck__badge-ic"><x-site.icon name="cal-check" /></span><span class="c-deck__badge-t">車検の<br>期限を表示</span></li>
                    <li><span class="c-deck__badge-ic c-deck__badge-ic--yellow"><x-site.icon name="yen" /></span><span class="c-deck__badge-t">買取査定<br>無料</span></li>
                </ul>
            </div>
        </div>
    </div>
</section>

{{-- ================= 色・文字 ================= --}}
<section class="l-section" aria-labelledby="sg-colors">
    <div class="l-container">
        <x-site.section-head id="sg-colors" title="色と文字" en="TOKENS" lead="赤 × 黒 × 白に、差し色の黄。黄は黒文字専用。状態色は文字と一緒に使います。" />
        <div class="p-dev-swatches">
            @foreach ($swatches as $key => $label)
                <div class="p-dev-swatch p-dev-swatch--{{ $key }}">
                    <span class="p-dev-swatch__chip"></span>
                    <span class="p-dev-swatch__name">{{ $label }}</span>
                </div>
            @endforeach
        </div>

        <div class="p-dev-type">
            <p class="p-dev-label">x-site.section-head（h2：赤い縦線＋日本語＋英字の飾り。右に件数・「◯◯を見る」）</p>
            <x-site.section-head title="いま掲載中の車" en="STOCK" :level="3">
                <p class="c-count-pill">全<b>{{ $cars->count() }}</b>台</p>
                <a class="c-more" href="{{ route('cars.index') }}">在庫一覧を見る<x-site.icon name="chevron-right" /></a>
            </x-site.section-head>
            <x-site.section-head :title="new HtmlString('お支払いシミュレーション<small>（計算例）</small>')" en="LOAN" :level="3" lead="支払総額・頭金・回数・金利を動かすと、月々の目安がわかります。" />
            <p class="p-dev-label">c-section-title--center（完了ページなど）／c-subhead（h3）</p>
            <p class="c-section-title c-section-title--center">お気軽にご相談ください</p>
            <p class="c-subhead">使い方から選ぶ</p>
            <p class="p-dev-label">本文 16px・行間1.75 ／ 数字の書体（Oswald。u-num）</p>
            <p>支払総額・修復歴・車検など、車選びに大切な情報をわかりやすく表示しています。販売・買取・ローン・車検整備まで、お気軽にご相談ください。1行はおおよそ全角40字までにします。</p>
            <p class="u-num p-dev-text-md">335.0 ／ 06-4960-8765 ／ 1.1万km ／ STEP.1</p>
            <p class="p-dev-text-md p-dev-text-black">リード 18px／900：納得して選べる、尼崎の中古車。</p>
            <p class="p-dev-text-sm">補足 14px：価格は2026年4月26日時点のものです。</p>
            <p class="p-dev-text-xs">最小 13px：タグ・注記だけに使う（これ未満は禁止）</p>
        </div>
    </div>
</section>

{{-- ================= 斜め帯・黒の斜線地の見出し ================= --}}
<section class="l-section l-section--soft" aria-labelledby="sg-slant">
    <div class="l-container">
        <x-site.section-head id="sg-slant" title="斜め帯のキャッチ・丸いバッジ" en="CATCH" lead="c-slant（--black / --red / --white / --yellow）を c-slant-stack で積む。ヒーロー・ページの見出し帯に使います。" />
        <div class="p-dev-slants">
            <span class="c-slant c-slant--black">納得して選べる、</span>
            <span class="c-slant c-slant--red">尼崎の中古車。</span>
            <span class="c-slant c-slant--white"><em>支払総額</em>・<em>修復歴</em>・<em>車検の期限</em>まで、見てわかる。</span>
            <span class="c-slant c-slant--yellow">愛車の買取査定</span>
        </div>
        <div class="p-dev-row">
            <p class="p-dev-label">x-site.round-badge（赤：実数の件数／黄：約束）・c-count-pill</p>
            <div class="p-dev-badges">
                <x-site.round-badge top="ただいま" :num="$cars->count()" unit="台" bottom="掲載中" size="lg" :href="route('cars.index')" :label="'ただいま'.$cars->count().'台掲載中。掲載中の車を見る'" />
                <x-site.round-badge top="ただいま" :num="$cars->count()" unit="台" bottom="掲載中" />
                <x-site.round-badge variant="yellow" top="査定" num="無料" />
                <x-site.round-badge variant="yellow" top="査定だけ" num="でもOK" />
                <p class="c-count-pill">全<b>{{ $cars->count() }}</b>台</p>
            </div>
        </div>
    </div>
</section>

<section class="l-section l-section--dark" aria-labelledby="sg-promise">
    <div class="l-container">
        <x-site.band-title id="sg-promise" title="当店の4つのお約束" en="OUR PROMISE" lead="x-site.band-title（黒の斜線地 l-section--dark の上の斜めの赤帯）＋ x-site.promise を ol.c-promises に並べる。" />
        <ol class="c-promises">
            <x-site.promise :no="1" illust="p-total" title="支払総額で表示">
                表示価格は、税金・自賠責保険料・登録費用込みの支払総額です。
                <x-slot:visual>
                    <div class="c-promise__box"><span class="c-tag c-tag--body">車両本体</span>＋<span class="c-tag c-tag--fee">諸費用</span>＝<span class="c-tag c-tag--total">支払総額</span></div>
                    <p class="c-promise__note">※県外での登録・ご自宅への納車・ご希望のオプションは別途です。</p>
                </x-slot:visual>
            </x-site.promise>
            <x-site.promise :no="2" illust="p-frame" :title="$promiseRepairTitle">
                すべての車に、修復歴の有無をはっきり表示します。
                <x-slot:visual>
                    <div class="c-promise__box"><span class="c-tag c-tag--ok">修復歴 なし</span><span class="c-tag c-tag--caution">修復歴 あり</span></div>
                    <p class="c-promise__note">{{ CarText::REPAIR_DEFINITION }}</p>
                </x-slot:visual>
            </x-site.promise>
            <x-site.promise :no="3" illust="p-shaken" title="車検の期限を表示">
                車検がいつまで残っているかを表示します。確認中の車は「要確認」と表示しています。
                <x-slot:visual>
                    <div class="c-promise__box"><span class="c-promise__cap">表示の例</span><span class="c-tag c-tag--outline">車検 ○年○月まで</span><span class="c-tag c-tag--check">車検 要確認</span></div>
                </x-slot:visual>
            </x-site.promise>
            <x-site.promise :no="4" illust="p-explain" title="保証と整備の内容をご説明">
                保証の有無や内容、納車前の整備について、ご契約の前にスタッフがご説明します。
                <x-slot:visual>
                    <ul class="c-promise__checks">
                        <li class="c-promise__check"><x-site.icon name="check" />保証の有無と内容</li>
                        <li class="c-promise__check"><x-site.icon name="check" />納車前の整備の内容</li>
                    </ul>
                </x-slot:visual>
            </x-site.promise>
        </ol>
    </div>
</section>

{{-- ================= ボタン・リンク ================= --}}
<section class="l-section" aria-labelledby="sg-buttons">
    <div class="l-container">
        <x-site.section-head id="sg-buttons" title="ボタン・リンク" en="BUTTON" />
        <div class="p-dev-row">
            <p class="p-dev-label">x-site.btn2（2段ボタン。red / black / yellow・xl・吹き出し）</p>
            <div class="l-cluster">
                <x-site.btn2 :href="route('contact.index', ['purpose' => 'visit'])" small="お問い合わせは無料です" big="在庫確認・来店予約" />
                <x-site.btn2 :href="route('contact.index', ['purpose' => 'loan'])" variant="black" small="月々の目安を計算できます" big="ローンのご相談" />
                <x-site.btn2 :href="route('buy.index')" variant="yellow" small="査定は無料です" big="買取査定を申し込む" />
            </div>
            <div class="l-grid l-grid--2 u-mt-6">
                <x-site.btn2 :href="route('cars.index')" size="xl" block bubble="全車 支払総額で表示" small="写真・支払総額つきで掲載中" :big="'掲載中の車を見る（'.$cars->count().'台）'" />
                <x-site.btn2 :href="config('shop.tel_href')" size="xl" block variant="black" small="電話で相談する（11:00〜21:00）" :big="config('shop.tel')" icon="phone" num :aria-label="'電話をかける '.config('shop.tel')" />
            </div>
        </div>
        <div class="p-dev-row">
            <p class="p-dev-label">c-btn（primary 赤 / secondary 白・黒枠 / yellow / navy＝黒 / line / ghost）</p>
            <div class="l-cluster">
                <a class="c-btn c-btn--primary" href="#sg-buttons">在庫確認・見積もり（無料）</a>
                <a class="c-btn c-btn--secondary" href="#sg-buttons"><x-site.icon name="map-pin" />地図・アクセスを見る</a>
                <a class="c-btn c-btn--yellow" href="#sg-buttons"><x-site.icon name="calendar" />見学・試乗の予約</a>
                <a class="c-btn c-btn--navy" href="#sg-buttons">店舗案内を見る</a>
                <a class="c-btn c-btn--line" href="#sg-buttons"><x-site.icon name="line" />LINEで相談</a>
                <button type="button" class="c-btn c-btn--ghost" aria-pressed="false"><x-site.icon name="heart" />お気に入り</button>
                <button type="button" class="c-btn c-btn--ghost" aria-pressed="true"><x-site.icon name="heart-fill" />お気に入り済み</button>
            </div>
        </div>
        <div class="p-dev-row">
            <p class="p-dev-label">大きさ（--lg 56px / 標準 48px / --sm 44px）・無効（理由を近くに文字で出す）・--block</p>
            <div class="l-cluster">
                <a class="c-btn c-btn--primary c-btn--lg" href="{{ config('shop.tel_href') }}"><x-site.icon name="phone" />電話する {{ config('shop.tel') }}</a>
                <a class="c-btn c-btn--secondary c-btn--sm" href="#sg-buttons">詳しく見る</a>
                <button type="button" class="c-btn c-btn--primary" disabled>送信しています…</button>
            </div>
            <p class="p-dev-text-sm u-mt-2">送信中のため、もう一度押すことはできません。</p>
        </div>
        <div class="p-dev-row">
            <p class="p-dev-label">c-more（単独のリンク：赤・太字・矢印）／c-link（文中のリンク：青・下線）</p>
            <p><a class="c-more" href="#sg-buttons">在庫一覧を見る<x-site.icon name="chevron-right" /></a></p>
            <p>くわしくは<a class="c-link" href="{{ route('privacy') }}">個人情報の取り扱い</a>をご覧ください。<a class="c-link c-link--block" href="#sg-buttons">支払総額に含まれる費用を見る ›</a></p>
        </div>
    </div>
</section>

{{-- ================= タグ・チップ・タブ・カード ================= --}}
<section class="l-section l-section--soft" aria-labelledby="sg-tags">
    <div class="l-container">
        <x-site.section-head id="sg-tags" title="タグ・チップ・タブ・カード" en="PARTS" />
        <div class="p-dev-row">
            <p class="p-dev-label">c-tag（色だけでなく必ず文字を入れる）</p>
            <div class="c-tags">
                <span class="c-tag c-tag--ok">修復歴なし</span>
                <span class="c-tag c-tag--caution">商談中</span>
                <span class="c-tag c-tag--check">車検 要確認</span>
                <span class="c-tag c-tag--neutral">AT（CVT）</span>
                <span class="c-tag c-tag--outline">車検 2027年3月まで</span>
                <span class="c-tag c-tag--new">新着</span>
                <span class="c-tag c-tag--sold">売約済</span>
                <span class="c-tag c-tag--pick">店長おすすめ</span>
                <span class="c-tag c-tag--red">SUV</span>
                <span class="c-tag c-tag--body">車両本体</span>
                <span class="c-tag c-tag--fee">諸費用</span>
                <span class="c-tag c-tag--total">支払総額</span>
            </div>
        </div>
        <div class="p-dev-row">
            <p class="p-dev-label">x-site.chip（ul.c-chips c-chips--grid：スマホは2列のタイル）</p>
            <ul class="c-chips c-chips--grid">
                <li><x-site.chip :href="route('cars.index')" icon="star" tone="yellow" count="2台">店長おすすめ</x-site.chip></li>
                <li><x-site.chip :href="route('cars.index')" icon="leaf" tone="green" count="2台">ハイブリッド</x-site.chip></li>
                <li><x-site.chip :href="route('cars.index')" icon="fuel" tone="black" count="1台">ディーゼル</x-site.chip></li>
                <li><x-site.chip :href="route('cars.index')" icon="meter" count="3台" current>走行3万km以下</x-site.chip></li>
                <li><x-site.chip :href="route('cars.index')" icon="yen" tone="black" count="1台">支払総額<wbr>150万円以下</x-site.chip></li>
            </ul>
        </div>
        <div class="p-dev-row" x-data="{ tab: 'all' }">
            <p class="p-dev-label">c-tabs（role="tablist"。選ばれたタブは赤）</p>
            <div class="c-tabs" role="tablist" aria-label="並べ替え">
                <button type="button" class="c-tabs__tab" role="tab" :aria-selected="tab === 'all' ? 'true' : 'false'" aria-selected="true" x-on:click="tab = 'all'">おすすめ順</button>
                <button type="button" class="c-tabs__tab" role="tab" :aria-selected="tab === 'new' ? 'true' : 'false'" aria-selected="false" x-on:click="tab = 'new'">新着順</button>
                <button type="button" class="c-tabs__tab" role="tab" :aria-selected="tab === 'price' ? 'true' : 'false'" aria-selected="false" x-on:click="tab = 'price'">支払総額の安い順</button>
            </div>
        </div>
        <div class="p-dev-row">
            <p class="p-dev-label">x-site.link-card を ul.c-link-cards に並べる（スマホ2列のタイル・600px 以上は横長・1200px 以上は4列。右に台数）</p>
            <ul class="c-link-cards" role="list">
                <li><x-site.link-card :href="route('cars.index')" icon="bag" title="通勤・お買い物に" sub="小回りのきく軽自動車" :count="1" /></li>
                <li><x-site.link-card :href="route('cars.index')" icon="family" tone="yellow" title="子育て・家族に" sub="広く使えるミニバン" :count="1" /></li>
                <li><x-site.link-card :href="route('cars.index')" icon="mountain" tone="black" title="休日・レジャーに" sub="荷物を積みやすいSUV" :count="2" /></li>
                <li><x-site.link-card :href="route('cars.index')" icon="yen" tone="tint" title="予算を抑えたい" sub="支払総額150万円以下" :count="1" /></li>
            </ul>
            <p class="p-dev-label">ul.c-link-cards.c-link-cards--3（スマホ1列・960px 以上は3列。右は矢印）</p>
            <ul class="c-link-cards c-link-cards--3" role="list">
                <li><x-site.link-card :href="route('contact.index', ['purpose' => 'loan'])" icon="calc" :title="new HtmlString('ローンの<b>ご相談</b>')" sub="月々の目安から一緒に考えます" /></li>
                <li><x-site.link-card :href="route('contact.index')" icon="wrench" :title="new HtmlString('<b>車検</b>・一般整備')" sub="車検・点検・修理のご相談" /></li>
                <li><x-site.link-card :href="route('contact.index')" icon="bankin" :title="new HtmlString('<b>板金</b>・保険')" sub="キズ・へこみの修理、保険のご相談" /></li>
            </ul>
        </div>
        <div class="p-dev-row">
            <p class="p-dev-label">c-card / --accent（上が赤）/ --soft / --link</p>
            <div class="l-grid l-grid--3">
                <div class="c-card"><p class="c-card__title">c-card</p><p>白地・枠・角丸12px。</p></div>
                <div class="c-card c-card--accent"><p class="c-card__title">c-card--accent</p><p>ご相談帯・お約束の形。</p></div>
                <a class="c-card c-card--link" href="#sg-tags"><p class="c-card__title">c-card--link</p><p>カード全体がリンクのとき（中にボタンを入れない）。</p></a>
            </div>
        </div>
    </div>
</section>

{{-- ================= 車両カード・価格・状態表・装備 ================= --}}
<section class="l-section l-section--soft" aria-labelledby="sg-cars">
    <div class="l-container">
        <x-site.section-head id="sg-cars" title="車両カード" en="CAR CARD" />
        @if ($car === null)
            <div class="c-alert c-alert--warn">
                <x-site.icon name="alert" />
                <div class="c-alert__body"><p>公開中の車両がないため、見本を出せません。</p></div>
            </div>
        @else
            <div class="c-alert">
                <x-site.icon name="info" />
                <div class="c-alert__body"><p>価格はすべて<b>税込の支払総額</b>です。{{ CarText::priceNote() }}</p></div>
            </div>
            <div class="p-dev-row">
                <p class="p-dev-label">x-site.car-card（公開中の車。l-grid l-grid--4：1200px 以上で4列）</p>
                <ul class="l-grid l-grid--4" role="list">
                    @foreach ($cars as $c)
                        <li><x-site.car-card :car="$c" :loading="$loop->index < 4 ? 'eager' : 'lazy'" /></li>
                    @endforeach
                </ul>
            </div>
            <div class="p-dev-row">
                <p class="p-dev-label">写真なし（ボディタイプのイラスト）・商談中／修復歴あり・車検2年付き（ボタンなし）／売約済・本体価格なし／本体価格なし・車検切れ</p>
                <ul class="l-grid l-grid--4" role="list">
                    <li><x-site.car-card :car="$noPhoto" /></li>
                    <li><x-site.car-card :car="$repairCar" :actions="false" /></li>
                    <li><x-site.car-card :car="$soldCar" /></li>
                    <li><x-site.car-card :car="$noBaseCar" /></li>
                </ul>
            </div>
            <div class="p-dev-row">
                <p class="p-dev-label">compact（トップ用：写真 16:10・お気に入り／比較のボタンなし）／fav-remove（お気に入りページ用）</p>
                <ul class="l-grid l-grid--4" role="list">
                    <li><x-site.car-card :car="$car" compact /></li>
                    <li><x-site.car-card :car="$car" fav-remove /></li>
                </ul>
            </div>
        @endif
    </div>
</section>

@if ($car !== null)
<section class="l-section" aria-labelledby="sg-price">
    <div class="l-container">
        <x-site.section-head id="sg-price" title="価格・内訳・主な仕様・装備" en="DETAIL" />
        <div class="l-split">
            <div class="l-stack l-stack--lg">
                <div>
                    <p class="p-dev-label">x-site.price size=detail（積み上げバー＋式は x-site.price-breakdown）</p>
                    <x-site.price :car="$car" size="detail" />
                </div>
                <div>
                    <p class="p-dev-label">size=detail（車両本体価格が未入力：バーは出さない）</p>
                    <x-site.price :car="$noBaseCar" size="detail" />
                </div>
            </div>
            <div class="l-stack l-stack--lg">
                <div>
                    <p class="p-dev-label">x-site.price size=card（note=true）</p>
                    <x-site.price :car="$car" />
                </div>
                <div>
                    <p class="p-dev-label">応談</p>
                    <x-site.price :car="$askCar" />
                </div>
                <div>
                    <p class="p-dev-label">x-site.price-breakdown（単独。bar=false）</p>
                    <x-site.price-breakdown :car="$car" :bar="false" />
                </div>
                <div>
                    <p class="p-dev-label">x-site.car-facts variant=card（修復歴あり・車検2年付き）</p>
                    <x-site.car-facts :car="$repairCar" />
                </div>
            </div>
        </div>
        <div class="p-dev-row">
            <p class="c-subhead">主な仕様</p>
            <p class="p-dev-label">x-site.car-facts variant=detail（車検の期限ありの見本）</p>
            <x-site.car-facts :car="$expiryCar" variant="detail" />
        </div>
        <div class="p-dev-row">
            <p class="c-subhead">主な装備</p>
            <p class="p-dev-label">x-site.equip-list（CarText::equipmentHighlights。装備がないときは見本の6件）</p>
            <x-site.equip-list :items="CarText::equipmentHighlights($car) ?: ['衝突被害軽減ブレーキ', '全周囲カメラ', 'メモリーナビ', 'ETC', 'ドライブレコーダー', 'シートヒーター']" />
        </div>
    </div>
</section>
@endif

{{-- ================= 検索（STEP の矢印パネル） ================= --}}
<section class="l-section" aria-labelledby="sg-search">
    <div class="l-container">
        <x-site.section-head id="sg-search" title="条件から在庫を探す" en="SEARCH" lead="x-site.arrow-step を c-arrow-steps に並べる。ボディタイプは x-site.type-choice（c-type-choices）。" />
        <form class="c-arrow-steps" action="{{ route('cars.index') }}" method="GET" onsubmit="return false">
            <x-site.arrow-step :no="1" title="ボディタイプを選ぶ" heading-id="sg-step1">
                <div class="c-type-choices" role="radiogroup" aria-labelledby="sg-step1">
                    @foreach ($bodyCounts->take(3) as $type => $n)
                        <x-site.type-choice name="sg_body_type" :value="$type" :label="CarText::bodyType($type)" :count="$n.'台'" />
                    @endforeach
                    <x-site.type-choice name="sg_body_type" value="" label="すべて" illust="all" :count="$cars->count().'台'" checked />
                </div>
            </x-site.arrow-step>
            <x-site.arrow-step :no="2" title="予算を決める" illust="coins" split>
                <div>
                    <label class="c-arrow-step__label" for="sg-price-max">支払総額の上限</label>
                    <select class="c-field__input c-field__input--select" id="sg-price-max">
                        <option value="">上限なし</option>
                        <option>150万円以下</option>
                        <option>200万円以下</option>
                        <option>300万円以下</option>
                    </select>
                </div>
            </x-site.arrow-step>
            <x-site.arrow-step :no="3" title="走行距離" illust="meter" split>
                <div>
                    <label class="c-arrow-step__label" for="sg-km-max">走行距離の上限</label>
                    <select class="c-field__input c-field__input--select" id="sg-km-max">
                        <option value="">上限なし</option>
                        <option>1万km以下</option>
                        <option>3万km以下</option>
                        <option>5万km以下</option>
                    </select>
                </div>
            </x-site.arrow-step>
            <x-site.arrow-step go>
                <p class="c-arrow-step__count-label">条件に合う車</p>
                <p class="c-arrow-step__count">{{ $cars->count() }}<small>台</small></p>
                <button class="c-btn c-btn--yellow" type="submit"><x-site.icon name="search" />この条件で探す</button>
            </x-site.arrow-step>
        </form>
    </div>
</section>

{{-- ================= ご購入の流れ ================= --}}
<section class="l-section" aria-labelledby="sg-flow">
    <div class="l-container">
        <x-site.section-head id="sg-flow" title="ご購入の流れ" en="FLOW" lead="x-site.flow（道路の上のメダル型イラスト。PC 4列・スマホは縦の道路）。" />
        <x-site.flow :steps="$flowSteps" />
        <div class="c-cta-row">
            <x-site.btn2 :href="route('contact.index', ['purpose' => 'visit'])" small="見学・試乗のご希望はこちら" big="来店予約をする" />
            <x-site.btn2 href="#sg-loan" variant="black" small="月々の目安を計算できます" big="ローンのご相談" />
        </div>
    </div>
</section>

{{-- ================= お支払いシミュレーション ================= --}}
<section class="l-section l-section--soft" id="sg-loan" aria-labelledby="sg-loan-title">
    <div class="l-container">
        <x-site.section-head id="sg-loan-title" :title="new HtmlString('お支払いシミュレーション<small>（計算例）</small>')" en="LOAN" lead="支払総額・頭金・回数・金利を動かすと、月々の目安がわかります。" />
        <x-site.loan-sim :cars="$cars" />
    </div>
</section>

{{-- ================= 買取の案内の帯 ================= --}}
<section class="l-section l-section--soft" aria-labelledby="sg-promo">
    <div class="l-container">
        <p class="p-dev-label">c-promo（黒い写真地に斜めの赤い面と黄の線・黄の丸バッジ）＋ ul.c-link-cards.c-link-cards--3</p>
        <div class="c-promo">
            <div class="c-promo__media"><picture><source type="image/webp" srcset="{{ asset('images/buy-hero-bg.jpg.webp') }}"><img src="{{ asset('images/buy-hero-bg.jpg') }}" alt="" width="1400" height="560" loading="lazy"></picture></div>
            <div class="c-promo__body">
                <p class="c-slant c-slant--yellow">愛車の買取査定</p>
                <h2 class="c-promo__title" id="sg-promo">クルマを売るなら、<br><em>まずは無料査定</em>から。</h2>
                <p class="c-promo__lead">年式や走行距離などをお聞きして査定します。査定だけのご依頼も歓迎です。</p>
                <div class="c-promo__actions">
                    <x-site.btn2 :href="route('buy.index').'#appraisal-form'" variant="yellow" small="査定は無料です" big="買取査定を申し込む" />
                    <a class="c-promo__tel" href="{{ config('shop.tel_href') }}" aria-label="電話をかける {{ config('shop.tel') }}"><small>お電話でも受付中（{{ BusinessHours::hoursLabel() }}）</small><b><x-site.icon name="phone" />{{ config('shop.tel') }}</b></a>
                </div>
            </div>
            <ul class="c-promo__badges">
                <li><x-site.round-badge variant="yellow" top="査定" num="無料" /></li>
                <li><x-site.round-badge variant="yellow" top="査定だけ" num="でもOK" /></li>
                <li><x-site.round-badge variant="yellow" top="お気軽に" num="ご相談" /></li>
            </ul>
        </div>
    </div>
</section>

{{-- ================= FAQ・手順・営業時間 ================= --}}
<section class="l-section" aria-labelledby="sg-faq">
    <div class="l-container">
        <x-site.section-head id="sg-faq" title="よくある質問・手順・営業時間" en="INFO" />
        <div class="l-split">
            <div>
                <p class="p-dev-label">x-site.faq（details / summary）</p>
                <x-site.faq :items="$faqItems" />
                <p class="p-dev-label u-mt-6">c-steps（番号は赤い丸。--vertical で常に縦）</p>
                <ol class="c-steps c-steps--vertical" role="list">
                    <li class="c-steps__item"><span class="c-steps__num">1</span><div class="c-steps__body"><p class="c-steps__title">担当者が内容を確認します</p></div></li>
                    <li class="c-steps__item"><span class="c-steps__num">2</span><div class="c-steps__body"><p class="c-steps__title">電話またはメールでご連絡します</p><p class="c-steps__text">定休日をはさむ場合は、翌営業日以降のご連絡になります。</p></div></li>
                </ol>
            </div>
            <div>
                <p class="p-dev-label">x-site.business-hours（今日の行は黄の面＋赤い「今日」）</p>
                <x-site.business-hours />
                <p class="p-dev-label u-mt-6">x-site.open-status（いまの状態・状態ごとの見本）</p>
                <div class="p-dev-states">
                    <x-site.open-status />
                    @foreach ($statusSamples as $label => $at)
                        <x-site.open-status :now="$at" />
                    @endforeach
                    <x-site.open-status variant="line" />
                </div>
            </div>
        </div>
        <div class="p-dev-row">
            <p class="p-dev-label">c-steps（PC は横並び。c-steps--5 は5列）</p>
            <ol class="c-steps c-steps--5" role="list">
                @foreach (['お申し込み', '査定の日時の調整', '査定', 'ご契約', 'お引き渡し・ご入金'] as $stepTitle)
                    <li class="c-steps__item"><span class="c-steps__num">{{ $loop->iteration }}</span><div class="c-steps__body"><p class="c-steps__title">{{ $stepTitle }}</p></div></li>
                @endforeach
            </ol>
        </div>
    </div>
</section>

{{-- ================= 空状態・お知らせ・連絡手段 ================= --}}
<section class="l-section l-section--soft" aria-labelledby="sg-empty">
    <div class="l-container">
        <x-site.section-head id="sg-empty" title="空状態・お知らせ・連絡手段" en="NOTICE" />
        <div class="l-grid l-grid--2">
            <x-site.empty-state title="条件に合う車が見つかりませんでした" :heading-level="3" illust="empty">
                条件を減らすか、探してほしい車をお伝えください。入荷したらご連絡します。
                <x-slot:actions>
                    <a class="c-btn c-btn--primary" href="{{ route('cars.index') }}">条件をすべて解除する</a>
                    <a class="c-btn c-btn--secondary" href="{{ route('contact.index', ['purpose' => 'search']) }}">探してほしい車を伝える</a>
                </x-slot:actions>
            </x-site.empty-state>
            <x-site.empty-state title="お気に入りはまだありません" icon="heart" :heading-level="3" :tel="false">
                車両カードの［お気に入り］を押すと、この端末のブラウザに保存されます。
                <x-slot:actions>
                    <a class="c-btn c-btn--primary" href="{{ route('cars.index') }}">在庫一覧を見る</a>
                </x-slot:actions>
            </x-site.empty-state>
        </div>
        <div class="l-stack u-mt-6">
            <div class="c-alert">
                <x-site.icon name="info" />
                <div class="c-alert__body"><p class="c-alert__title">c-alert（既定＝お知らせ・価格の注記）</p><p>価格はすべて<b>税込の支払総額</b>です。</p></div>
            </div>
            <div class="c-alert c-alert--warn">
                <x-site.icon name="alert" />
                <div class="c-alert__body"><p class="c-alert__title">c-alert--warn</p><p>この車は千葉県船橋市で保管中です。現車確認はご予約ください。</p></div>
            </div>
            <div class="c-alert c-alert--error">
                <x-site.icon name="alert" />
                <div class="c-alert__body"><p class="c-alert__title">c-alert--error</p><p>送信できませんでした。時間をおいてもう一度お試しください。</p></div>
            </div>
        </div>
        <div class="p-dev-row">
            <p class="p-dev-label">x-site.contact-actions（layout=row）</p>
            <x-site.contact-actions :stock-no="$car?->stock_no" purpose="estimate" />
        </div>
    </div>
</section>

{{-- ================= フォーム部品 ================= --}}
<section class="l-section" aria-labelledby="sg-form">
    <div class="l-container l-container--narrow">
        <x-site.section-head id="sg-form" title="フォーム部品" en="FORM" lead="入力欄は高さ52px・文字17px。エラーは欄の直下と先頭の一覧に出します。" />
        <form class="c-form" action="#" onsubmit="return false">
            <x-site.form-errors :bag="$demoErrors" id-prefix="sg-" :focus="false" id="sg-form-errors" />

            <div class="c-field">
                <label class="c-field__label" for="sg-name">お名前 <span class="c-badge-req">必須</span></label>
                <p class="c-field__hint" id="sg-name-hint">例）山田 太郎</p>
                <input class="c-field__input" id="sg-name" type="text" autocomplete="name" aria-invalid="true" aria-describedby="sg-name-hint sg-name-error">
                <x-site.field-error name="name" id-prefix="sg-" :bag="$demoErrors" />
            </div>

            <div class="c-field">
                <label class="c-field__label" for="sg-tel">お電話番号 <span class="c-badge-req c-badge-req--optional">任意</span></label>
                <p class="c-field__hint" id="sg-tel-hint">例）090-1234-5678（日中につながりやすい番号）</p>
                <input class="c-field__input" id="sg-tel" type="tel" autocomplete="tel" inputmode="tel" aria-describedby="sg-tel-hint">
            </div>

            <div class="c-field">
                <label class="c-field__label" for="sg-email">メールアドレス <span class="c-badge-req">必須</span></label>
                <p class="c-field__hint" id="sg-email-hint">例）taro@example.com</p>
                <input class="c-field__input" id="sg-email" type="email" autocomplete="email" inputmode="email" aria-invalid="true" aria-describedby="sg-email-hint sg-email-error">
                <x-site.field-error name="email" id-prefix="sg-" :bag="$demoErrors" />
            </div>

            <div class="c-field">
                <label class="c-field__label" for="sg-year">年式 <span class="c-badge-req">必須</span></label>
                <p class="c-field__hint" id="sg-year-hint">車検証の「初度登録年月」に書かれています</p>
                <select class="c-field__input c-field__input--select" id="sg-year" aria-describedby="sg-year-hint">
                    <option value="">選んでください</option>
                    <option value="2023">2023年（令和5年）</option>
                    <option value="2019">2019年（平成31年/令和元年）</option>
                </select>
            </div>

            <div class="c-field">
                <label class="c-field__label" for="sg-mileage">走行距離 <span class="c-badge-req">必須</span></label>
                <p class="c-field__hint" id="sg-mileage-hint">おおよそで大丈夫です（例：45000）</p>
                <div class="c-field__with-unit">
                    <input class="c-field__input" id="sg-mileage" type="number" inputmode="numeric" aria-describedby="sg-mileage-hint">
                    <span class="c-field__unit">km</span>
                </div>
            </div>

            <fieldset class="c-field">
                <legend class="c-field__label c-field__legend">ご用件 <span class="c-badge-req c-badge-req--optional">任意</span></legend>
                <div class="c-choice-group c-choice-group--2">
                    @foreach (['在庫の確認', '見積もり', '見学・試乗の予約', 'ローンの相談', '今の車の下取り・買取', 'その他'] as $i => $label)
                        <label class="c-choice">
                            <input class="c-choice__input" type="radio" name="sg-purpose" value="{{ $i }}" @checked($i === 2)>
                            <span class="c-choice__box">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <fieldset class="c-field">
                <legend class="c-field__label c-field__legend">お車の状態 <span class="c-badge-req">必須</span></legend>
                <div class="c-choice-group c-choice-group--3">
                    <label class="c-choice"><input class="c-choice__input" type="radio" name="sg-condition" value="good"><span class="c-choice__box"><span>良い<span class="c-choice__hint">目立つ傷やへこみはない</span></span></span></label>
                    <label class="c-choice"><input class="c-choice__input" type="radio" name="sg-condition" value="normal" checked><span class="c-choice__box"><span>ふつう<span class="c-choice__hint">小さな傷やへこみがある</span></span></span></label>
                    <label class="c-choice"><input class="c-choice__input" type="radio" name="sg-condition" value="damaged"><span class="c-choice__box"><span>傷みあり<span class="c-choice__hint">大きな傷・へこみや故障がある</span></span></span></label>
                </div>
            </fieldset>

            <div class="c-choice-group">
                <label class="c-choice c-choice--check"><input class="c-choice__input" type="checkbox" checked><span class="c-choice__box">ローンの相談もしたい（チェックのタイル）</span></label>
            </div>

            <div class="c-field">
                <label class="c-field__label" for="sg-message">お問い合わせ内容 <span class="c-badge-req">必須</span></label>
                <textarea class="c-field__input c-field__input--textarea" id="sg-message"></textarea>
            </div>

            <x-site.privacy-consent id="sg-privacy-consent" />

            <x-site.btn2 type="submit" block small="送信後、担当者からご連絡します" big="この内容で送信する" />
            <button type="submit" class="c-btn c-btn--primary c-btn--lg c-btn--block">この内容で送信する（c-btn）</button>
        </form>
    </div>
</section>

{{-- ================= 表・ページ送り ================= --}}
<section class="l-section l-section--soft" aria-labelledby="sg-table">
    <div class="l-container">
        <x-site.section-head id="sg-table" title="表・ページ送り" en="TABLE" />
        <div class="c-table-wrap">
            <table class="c-table">
                <caption class="c-table__caption">ご売却に必要な書類（c-table）</caption>
                <thead>
                    <tr><th scope="col" class="c-table__th c-table__th--col">書類</th><th scope="col" class="c-table__th c-table__th--col">普通車</th><th scope="col" class="c-table__th c-table__th--col">軽自動車</th></tr>
                </thead>
                <tbody>
                    <tr><th scope="row" class="c-table__th">車検証</th><td class="c-table__td">必要</td><td class="c-table__td">必要</td></tr>
                    <tr><th scope="row" class="c-table__th">印鑑</th><td class="c-table__td">実印（印鑑登録証明書）</td><td class="c-table__td">認印</td></tr>
                </tbody>
            </table>
        </div>
        <div class="p-dev-row">
            <p class="p-dev-label">x-site.pagination（60台・2ページ目の見本）</p>
            <x-site.pagination :paginator="$demoPaginator" unit="台" />
        </div>
    </div>
</section>

{{-- ================= アイコン・イラスト ================= --}}
<section class="l-section" aria-labelledby="sg-icons">
    <div class="l-container">
        <x-site.section-head id="sg-icons" title="アイコン・イラスト" en="ICON" lead="x-site.icon（線画・currentColor）と x-site.illust（彩色のイラスト）。すべて aria-hidden。" />
        <p class="p-dev-label">x-site.icon（UI）</p>
        <ul class="p-dev-icons" role="list">
            @foreach ($icons as $icon)
                <li class="p-dev-icon"><x-site.icon :name="$icon" :size="24" />{{ $icon }}</li>
            @endforeach
        </ul>
        <p class="p-dev-label u-mt-6">x-site.icon（装備。名前は CarText::equipmentIcon() で選ぶ）</p>
        <ul class="p-dev-icons" role="list">
            @foreach ($equipIcons as $icon)
                <li class="p-dev-icon"><x-site.icon :name="$icon" :size="24" />{{ $icon }}</li>
            @endforeach
        </ul>
        <p class="p-dev-label u-mt-6">x-site.illust（ボディタイプ。CarText::bodyIllust() で DB の値から選ぶ）</p>
        <ul class="p-dev-illusts" role="list">
            @foreach ($bodyIllusts as $name => $label)
                <li class="p-dev-illust"><x-site.illust :name="$name" />{{ $name }}／{{ $label }}</li>
            @endforeach
        </ul>
        <p class="p-dev-label u-mt-6">x-site.illust（STEP・お約束・ご購入の流れ・そのほか）</p>
        <ul class="p-dev-illusts" role="list">
            @foreach ($roundIllusts as $name => $label)
                <li class="p-dev-illust p-dev-illust--round"><x-site.illust :name="$name" />{{ $label }}</li>
            @endforeach
        </ul>
    </div>
</section>

{{-- ================= トースト・固定バー ================= --}}
<section class="l-section l-section--soft" aria-labelledby="sg-chrome">
    <div class="l-container">
        <x-site.section-head id="sg-chrome" title="トースト・スマホの固定バー" en="CHROME" />
        <div class="l-cluster" x-data>
            <button type="button" class="c-btn c-btn--navy" x-on:click="$store.toast.show('見本のトーストです（4秒で消えます）')">トーストを出す</button>
            <button type="button" class="c-btn c-btn--secondary" x-on:click="$store.toast.show('お気に入りから外しました', { label: '元に戻す', handler: () => $store.toast.show('元に戻しました') })">［元に戻す］付きで出す</button>
        </div>
        <div class="p-dev-row">
            <p class="p-dev-label">x-site.sp-quick（スマホのクイックリンク。見本のため常に表示）</p>
            <div class="p-dev-demo-quick"><x-site.sp-quick /></div>
        </div>
        @foreach (['default', 'contact', 'buy'] as $variant)
            <div class="p-dev-row">
                <p class="p-dev-label">x-site.mbar variant={{ $variant }}（見本のため固定せずに表示）</p>
                <div class="p-dev-demo-mbar">
                    <x-site.mbar :variant="$variant" />
                </div>
            </div>
        @endforeach
        <p class="p-dev-note u-mt-6">完了ページ（x-site.thanks）は下に表示します。実際のページでは h1 になります（この見本ページでは h1 が2つになります）。</p>
    </div>
</section>

<x-site.thanks title="お問い合わせを受け付けました" :steps="['担当者が内容を確認します', '電話またはメールでご連絡します', ['title' => 'ご来店・お見積もりなどをご案内します', 'text' => '定休日（'.config('shop.closed_label').'）をはさむ場合は、翌営業日以降のご連絡になります。']]">
    お問い合わせありがとうございます。担当者から電話またはメールでご連絡します。
    <x-slot:actions>
        <a class="c-btn c-btn--secondary" href="{{ route('cars.index') }}">在庫一覧を見る</a>
        <a class="c-btn c-btn--secondary" href="{{ route('store') }}">店舗案内・アクセスを見る</a>
        <a class="c-btn c-btn--secondary" href="{{ route('home') }}">トップページへ戻る</a>
    </x-slot:actions>
</x-site.thanks>
@endsection
