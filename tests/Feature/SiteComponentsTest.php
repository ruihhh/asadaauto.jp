<?php

namespace Tests\Feature;

use App\Models\Car;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\MessageBag;
use Tests\TestCase;

/**
 * 共通部品（resources/views/components/site）の状態ごとの出し分け。
 * 車両は DB に保存せず、make() したもので描画だけを確かめる（フッターの集計があるページの確認のために DB は用意する）。
 */
class SiteComponentsTest extends TestCase
{
    use RefreshDatabase;

    private function car(array $attributes = []): Car
    {
        $car = Car::factory()->make(array_merge([
            'make' => 'マツダ',
            'model' => 'CX-5',
            'stock_no' => 'MZ4187',
            'slug' => 'mazda-cx-5-mz4187',
            'price' => 3350000,
            'base_price' => 3000000,
            'price_negotiable' => false,
            'featured' => false,
            'status' => 'available',
            'image_path' => null,
            'published_at' => now()->subDays(30),
        ], $attributes));
        $car->id = 4;
        $car->setRelation('images', collect());

        return $car;
    }

    public function test_car_card_for_available_car_has_inquiry_and_toggles(): void
    {
        $html = Blade::render('<x-site.car-card :car="$car" />', ['car' => $this->car()]);

        $this->assertStringContainsString('マツダ CX-5', $html);
        $this->assertStringContainsString('在庫確認・見積もり（無料）', $html);
        $this->assertStringContainsString('比較に追加', $html);
        $this->assertStringContainsString('写真準備中', $html);
        $this->assertStringNotContainsString('c-car-card__status', $html);
    }

    public function test_car_card_for_sold_car_hides_inquiry_and_compare(): void
    {
        $html = Blade::render('<x-site.car-card :car="$car" />', ['car' => $this->car(['status' => 'sold'])]);

        $this->assertStringContainsString('c-car-card--sold', $html);
        $this->assertStringContainsString('売約済', $html);
        $this->assertStringContainsString('この車は売約済みです。', $html);
        $this->assertStringNotContainsString('在庫確認・見積もり（無料）', $html);
        $this->assertStringNotContainsString('比較に追加', $html);
        $this->assertStringContainsString('お気に入り', $html);
    }

    public function test_car_card_for_reserved_car_keeps_inquiry_with_notice(): void
    {
        $html = Blade::render('<x-site.car-card :car="$car" />', ['car' => $this->car(['status' => 'reserved'])]);

        $this->assertStringContainsString('商談中', $html);
        $this->assertStringContainsString('ただいま商談中です。', $html);
        $this->assertStringContainsString('在庫確認・見積もり（無料）', $html);
    }

    public function test_price_block_variations(): void
    {
        $render = fn (Car $car, string $size = 'card') => Blade::render('<x-site.price :car="$car" :size="$size" />', compact('car', 'size'));

        $html = $render($this->car());
        $this->assertStringContainsString('335.0', $html);
        $this->assertStringContainsString('車両本体価格 300.0万円', $html);
        $this->assertStringContainsString('兵庫県内で登録し、当店で店頭納車する場合', $html);

        $html = $render($this->car(), 'detail');
        $this->assertStringContainsString('支払総額（税込）', $html);
        $this->assertStringContainsString('3,350,000円', $html);
        $this->assertStringContainsString('車両本体価格 300.0万円＋諸費用 35.0万円', $html);

        $html = $render($this->car(['base_price' => null]), 'detail');
        $this->assertStringContainsString('車両本体価格はお問い合わせください', $html);
        $this->assertStringNotContainsString('諸費用', $html);

        $this->assertStringContainsString('価格はお問い合わせください（応談）', $render($this->car(['price_negotiable' => true])));

        $html = $render($this->car(['price' => null]));
        $this->assertStringContainsString('価格はお問い合わせください', $html);
        $this->assertStringNotContainsString('応談', $html);
    }

    public function test_car_facts_show_unknown_inspection_as_needs_check(): void
    {
        $html = Blade::render('<x-site.car-facts :car="$car" />', ['car' => $this->car(['inspection_type' => null, 'inspection_expiry' => null])]);

        $this->assertStringContainsString('要確認', $html);
        $this->assertStringNotContainsString('事故歴', $html);
    }

    public function test_field_error_is_linked_by_id_and_hidden_without_error(): void
    {
        $bag = new MessageBag(['email' => ['メールアドレスを入力してください。']]);

        $html = Blade::render('<x-site.field-error name="email" :bag="$bag" />', ['bag' => $bag]);
        $this->assertStringContainsString('id="email-error"', $html);
        $this->assertStringContainsString('class="c-field__error"', $html);
        $this->assertStringContainsString('メールアドレスを入力してください。', $html);

        $html = Blade::render('<x-site.field-error name="name" id-prefix="buy-" :bag="$bag" />', ['bag' => $bag]);
        $this->assertSame('', trim($html));
    }

    public function test_business_hours_on_third_sunday_points_to_next_month(): void
    {
        $now = CarbonImmutable::parse('2026-10-18 12:00', 'Asia/Tokyo');
        $html = Blade::render('<x-site.business-hours :now="$now" />', ['now' => $now]);

        $this->assertStringContainsString('本日は定休日です', $html);
        $this->assertStringContainsString('11月15日（日）', $html);
        $this->assertStringNotContainsString('：10月18日（日）', $html);
    }

    public function test_open_status_states(): void
    {
        $label = fn (string $at) => trim(preg_replace('/\s+/u', ' ', strip_tags(
            Blade::render('<x-site.open-status :now="$now" />', ['now' => CarbonImmutable::parse($at, 'Asia/Tokyo')])
        )));

        $this->assertStringStartsWith('本日は定休日です', $label('2026-10-01 12:00'));
        $this->assertStringStartsWith('本日は定休日です', $label('2026-10-18 12:00'));
        $this->assertSame('本日 11:00から営業', $label('2026-10-19 10:00'));
        $this->assertStringStartsWith('本日の営業は終了しました', $label('2026-10-19 21:30'));
        $this->assertSame('本日営業中（21:00まで）', $label('2026-10-19 12:00'));
    }

    public function test_header_tagline_wraps_only_after_middle_dots(): void
    {
        $this->get(route('privacy'))
            ->assertOk()
            ->assertSee('<a class="l-header__brand" href="'.route('home').'">', false)
            ->assertSee('<span class="u-nowrap">買取・</span><span class="u-nowrap">車検整備</span>', false);
    }

    public function test_car_card_title_link_wraps_name_for_larger_tap_area(): void
    {
        $html = Blade::render('<x-site.car-card :car="$car" />', ['car' => $this->car()]);

        // 車名のリンクは押せる高さを 44px 以上にするため、行数の制限は内側の span に付ける
        $this->assertStringContainsString('<a class="c-car-card__title-link" href="'.route('cars.show', 'mazda-cx-5-mz4187').'"><span class="c-car-card__title-text">マツダ CX-5</span></a>', $html);
    }

    public function test_car_card_compact_keeps_inquiry_and_hides_toggles(): void
    {
        $html = Blade::render('<x-site.car-card :car="$car" compact />', ['car' => $this->car()]);

        $this->assertStringContainsString('c-car-card--compact', $html);
        $this->assertStringContainsString('在庫確認・見積もり（無料）', $html);
        $this->assertStringContainsString('在庫番号 MZ4187', $html);
        $this->assertStringNotContainsString('c-car-card__toggles', $html);
        $this->assertStringNotContainsString('比較に追加', $html);

        // 売約済みの compact は操作の欄そのものを出さない（空の枠を残さない）
        $sold = Blade::render('<x-site.car-card :car="$car" compact />', ['car' => $this->car(['status' => 'sold'])]);
        $this->assertStringNotContainsString('c-car-card__actions', $sold);
    }

    public function test_car_card_fav_remove_shows_remove_label_without_toggle_state(): void
    {
        $html = Blade::render('<x-site.car-card :car="$car" fav-remove />', ['car' => $this->car()]);

        $this->assertStringContainsString('>お気に入りから外す</span>', $html);
        $this->assertStringNotContainsString('お気に入り済み', $html);
        // お気に入りのボタンだけ aria-pressed を付けない（比較のボタンは切り替えのまま）
        $this->assertSame(1, substr_count($html, 'aria-pressed="false"'));

        $default = Blade::render('<x-site.car-card :car="$car" />', ['car' => $this->car()]);
        $this->assertStringContainsString('お気に入り済み', $default);
        $this->assertSame(2, substr_count($default, 'aria-pressed="false"'));
    }

    public function test_breadcrumb_and_page_header_variants(): void
    {
        $crumb = Blade::render('<x-site.breadcrumb :items="[[\'label\' => \'中古車在庫一覧\', \'url\' => \'/cars\'], [\'label\' => \'マツダ CX-5\']]" />');
        $this->assertStringContainsString('<nav class="c-breadcrumb" aria-label="現在地">', $crumb);
        $this->assertStringContainsString('<a class="c-breadcrumb__link" href="'.route('home').'">ホーム</a>', $crumb);
        $this->assertStringContainsString('<a class="c-breadcrumb__link" href="/cars">中古車在庫一覧</a>', $crumb);
        $this->assertStringContainsString('<span class="c-breadcrumb__current" aria-current="page">マツダ CX-5</span>', $crumb);
        $this->assertStringNotContainsString('<h1', $crumb);

        $narrow = Blade::render('<x-site.page-header title="お問い合わせ" narrow />');
        $this->assertStringContainsString('<div class="l-container l-container--narrow">', $narrow);
        $this->assertStringContainsString('<h1 class="c-page-title">お問い合わせ</h1>', $narrow);

        $flush = Blade::render('<x-site.page-header title="買取査定" flush />');
        $this->assertStringContainsString('class="c-page-header c-page-header--flush"', $flush);

        // title に HtmlString を渡すと改行の位置を決められる（パンくずはタグを除いた文字）
        $html = Blade::render('<x-site.page-header :title="$title" />', ['title' => new \Illuminate\Support\HtmlString('<span class="u-nowrap">尼崎の中古車買取・</span><span class="u-nowrap">無料査定</span>')]);
        $this->assertStringContainsString('<h1 class="c-page-title"><span class="u-nowrap">尼崎の中古車買取・</span><span class="u-nowrap">無料査定</span></h1>', $html);
        $this->assertStringContainsString('aria-current="page">尼崎の中古車買取・無料査定</span>', $html);
    }

    public function test_hours_notes_do_not_break_inside_words(): void
    {
        $hours = Blade::render('<x-site.business-hours :now="$now" />', ['now' => CarbonImmutable::parse('2026-10-19 12:00', 'Asia/Tokyo')]);
        $this->assertStringContainsString('11:00〜21:00<span class="u-nowrap">（第3日曜は定休）</span>', $hours);

        $thanks = Blade::render('<x-site.thanks title="受け付けました" />');
        $this->assertStringContainsString('営業時間 11:00〜21:00<span class="u-nowrap">（木曜・第3日曜定休）</span>', $thanks);

        $empty = Blade::render('<x-site.empty-state title="見つかりませんでした" />');
        $this->assertStringContainsString('<span class="u-nowrap">（木曜・第3日曜定休）</span>', $empty);
    }

    public function test_form_errors_keeps_count_together(): void
    {
        $html = Blade::render('<x-site.form-errors :bag="$bag" :focus="false" />', ['bag' => new MessageBag(['name' => ['お名前を入力してください。'], 'email' => ['メールアドレスを入力してください。']])]);

        $this->assertStringContainsString('入力内容を確認してください<span class="u-nowrap">（2件）</span>', $html);
        $this->assertStringContainsString('href="#name"', $html);
    }

    public function test_form_errors_follow_the_order_of_fields_on_screen(): void
    {
        // 検証ルールの順（email → phone）ではなく、画面の欄の並び（phone → email）で出す。並びにない欄は後ろ
        $bag = new MessageBag(['email' => ['メールアドレスを入力してください。'], 'zip' => ['郵便番号を確認してください。'], 'phone' => ['お電話番号を入力してください。']]);
        $html = Blade::render('<x-site.form-errors :bag="$bag" :focus="false" :order="[\'name\', \'phone\', \'email\']" />', ['bag' => $bag]);

        $this->assertStringContainsString('（3件）', $html);
        $this->assertLessThan(strpos($html, 'href="#email"'), strpos($html, 'href="#phone"'));
        $this->assertLessThan(strpos($html, 'href="#zip"'), strpos($html, 'href="#email"'));
    }

    public function test_component_options_added_for_page_requests(): void
    {
        // 営業状況の黒地用
        $dark = Blade::render('<x-site.open-status variant="line" on-dark :now="$now" />', ['now' => CarbonImmutable::parse('2026-10-19 12:00', 'Asia/Tokyo')]);
        $this->assertStringContainsString('c-open-status c-open-status--line c-open-status--ok c-open-status--on-dark', $dark);

        // 営業時間表：次の第3日曜の定休日を複数回分
        $hours = Blade::render('<x-site.business-hours :now="$now" :upcoming="3" />', ['now' => CarbonImmutable::parse('2026-09-28 12:00', 'Asia/Tokyo')]);
        $this->assertStringContainsString('10月18日（日）、11月15日（日）、12月20日（日）', $hours);

        // 完了ページ：電話番号は2段ボタンの大きな数字（途中で折り返さない）。イラスト付きの手順はメダル型の図、なければ番号の手順
        $thanks = Blade::render('<x-site.thanks title="受け付けました" />');
        $this->assertStringContainsString('<h2 id="thanks-tel-title" class="c-thanks__tel-title"><span class="c-slant c-slant--red">お急ぎの方はお電話で</span></h2>', $thanks);
        $this->assertMatchesRegularExpression('/c-btn2__big c-btn2__big--num">.*06-4960-8765<\/span>/s', $thanks);
        $this->assertStringContainsString('aria-label="電話をかける 06-4960-8765"', $thanks);
        $medal = Blade::render('<x-site.thanks title="受け付けました" :steps="$steps" :tel="false" />', ['steps' => [['title' => '確認します', 'illust' => 'p-explain'], ['title' => 'ご連絡します', 'illust' => 'mail-check']]]);
        $this->assertStringContainsString('<div class="c-medal-steps c-thanks__medals">', $medal);
        $this->assertStringContainsString('<h2 class="c-subhead c-medal-steps__title">このあとの流れ</h2>', $medal);
        $this->assertStringNotContainsString('c-steps', $medal);
        $this->assertStringNotContainsString('c-thanks__tel', $medal);
        $plain = Blade::render('<x-site.thanks title="受け付けました" :steps="[\'確認します\']" :tel="false" />');
        $this->assertStringContainsString('<ol class="c-steps c-steps--vertical" role="list">', $plain);

        // 流れは5つのとき c-flow--5（PC で5つを1行に）
        $steps = array_map(fn (int $i): array => ['title' => '手順'.$i, 'illust' => 'f-search'], range(1, 5));
        $this->assertStringContainsString('<ol class="c-flow c-flow--5">', Blade::render('<x-site.flow :steps="$steps" />', ['steps' => $steps]));

        // 矢印パネルの見出しの文字は1つの span にまとめる（u-nowrap の span を複数渡しても縦に並ばない）
        $step = Blade::render('<x-site.arrow-step :no="1" :title="$title">中身</x-site.arrow-step>', ['title' => new \Illuminate\Support\HtmlString('<span class="u-nowrap">在庫一覧で</span><span class="u-nowrap">車を探す</span>')]);
        $this->assertStringContainsString('<span class="c-arrow-step__title"><span class="u-nowrap">在庫一覧で</span><span class="u-nowrap">車を探す</span></span>', $step);

        // シミュレーター：車のボタンの前の文言を変えられる
        $loan = Blade::render('<x-site.loan-sim :cars="$cars" cars-label="この車の支払総額に戻す：" />', ['cars' => [$this->car()]]);
        $this->assertStringContainsString('>この車の支払総額に戻す：</span>', $loan);
        $this->assertStringNotContainsString('掲載中の車で計算', $loan);

        // お気に入りページのカード：外すボタンの並びは文言の幅に合わせる
        $card = Blade::render('<x-site.car-card :car="$car" fav-remove />', ['car' => $this->car()]);
        $this->assertStringContainsString('c-car-card__toggles c-car-card__toggles--wide', $card);
    }

    public function test_privacy_consent_separates_link_and_restores_check_after_errors(): void
    {
        // $errors はレイアウトと同じく、すべてのビューに共有されたものを読む
        view()->share('errors', new MessageBag);
        $html = Blade::render('<x-site.privacy-consent />');

        // リンクはチェックのラベルの外に置く（押し間違いを防ぐ）。チェックには name を付けない
        $label = substr($html, strpos($html, '<label'), strpos($html, '</label>') - strpos($html, '<label'));
        $this->assertStringNotContainsString('<a ', $label);
        $this->assertStringContainsString('個人情報の取り扱いに同意します', $label);
        $this->assertStringContainsString('<a class="c-link c-link--block" href="'.route('privacy').'" target="_blank" rel="noopener">', $html);
        $this->assertStringNotContainsString('name=', $label);
        $this->assertStringNotContainsString('checked', $html);

        // 入力エラーで戻ったとき（同意して送信した後）は、チェックを入れた状態で出す
        view()->share('errors', new MessageBag(['name' => ['お名前を入力してください。']]));
        $withErrors = Blade::render('<x-site.privacy-consent />');
        $this->assertMatchesRegularExpression('/<input type="checkbox" id="privacy-consent" class="c-consent__input" required\s+checked/', $withErrors);
    }

    public function test_mbar_phone_label_matches_visible_text(): void
    {
        config(['shop.tel' => '06-4960-8765']);

        $default = Blade::render('<x-site.mbar />');
        $this->assertStringContainsString('aria-label="電話する 06-4960-8765"', $default);
        $this->assertStringNotContainsString('電話をかける', $default);

        $buy = Blade::render('<x-site.mbar variant="buy" />');
        $this->assertStringContainsString('aria-label="電話で相談 06-4960-8765"', $buy);
    }

    public function test_chevron_left_icon_exists(): void
    {
        $html = Blade::render('<x-site.icon name="chevron-left" />');

        $this->assertStringContainsString('c-icon--chevron-left', $html);
        $this->assertStringContainsString('<polyline points="15 18 9 12 15 6"/>', $html);
    }

    public function test_car_card_shows_stock_no_body_type_and_pick_ribbon_outside_the_hidden_photo_link(): void
    {
        $html = Blade::render('<x-site.car-card :car="$car" />', ['car' => $this->car(['featured' => true, 'body_type' => 'SUV'])]);

        // 写真のリンクは読み上げの対象外なので、写真の上の文字（在庫番号・ボディタイプ・店長おすすめ）はリンクの外に置く
        $link = substr($html, strpos($html, '<a class="c-car-card__media"'), strpos($html, '</a>', strpos($html, '<a class="c-car-card__media"')) - strpos($html, '<a class="c-car-card__media"'));
        $this->assertStringContainsString('aria-hidden="true"', $link);
        $this->assertStringNotContainsString('在庫番号', $link);
        $this->assertStringContainsString('<p class="c-car-card__stock">在庫番号 MZ4187</p>', $html);
        $this->assertStringContainsString('<p class="c-car-card__type">SUV</p>', $html);
        $this->assertMatchesRegularExpression('/<p class="c-ribbon c-car-card__ribbon">.*店長おすすめ<\/p>/s', $html);

        // 支払総額は赤いラベル＋大きな数字。読み上げでは「支払総額（税込）」と読める
        $this->assertStringContainsString('<span class="c-price__value">335.0</span>', $html);
        $this->assertStringContainsString('支払総額（税込）', strip_tags($html));
        // 主な操作は赤の［在庫確認・見積もり（無料）］
        $this->assertStringContainsString('class="c-btn c-btn--primary c-btn--block" href="'.e(route('contact.index', ['stock_no' => 'MZ4187', 'purpose' => 'stock'])).'"', $html);
    }

    public function test_car_card_without_photo_shows_body_type_illustration(): void
    {
        $html = Blade::render('<x-site.car-card :car="$car" />', ['car' => $this->car(['body_type' => '軽自動車'])]);

        $this->assertStringContainsString('c-illust c-illust--kei', $html);
        $this->assertStringContainsString('写真準備中', $html);
    }

    public function test_car_facts_card_keeps_year_and_mileage_text_with_icons(): void
    {
        $html = Blade::render('<x-site.car-facts :car="$car" />', ['car' => $this->car(['model_year' => 2023, 'mileage' => 11200, 'accident_count' => 0])]);
        $text = preg_replace('/\s+/u', '', strip_tags($html));

        $this->assertStringContainsString('年式2023（R5）年式', $text);
        $this->assertStringContainsString('走行距離1.1万km', $text);
        $this->assertStringContainsString('修復歴なし', $text);
        $this->assertStringContainsString('c-spec__value c-spec__value--ok', $html);
        $this->assertStringContainsString('c-icon--calendar c-spec__icon', $html);
        // ミッション・燃料は小さな枠。項目名は読み上げ用に残す
        $this->assertStringContainsString('c-spec__row c-spec__row--transmission c-spec__row--mini', $html);
        $this->assertStringContainsString('<span class="u-visually-hidden">ミッション</span>', $html);
    }

    public function test_price_breakdown_shows_bar_and_formula_only_when_base_price_is_known(): void
    {
        $html = Blade::render('<x-site.price-breakdown :car="$car" />', ['car' => $this->car()]);
        $this->assertStringContainsString('車両本体価格 300.0万円＋諸費用 35.0万円＝支払総額 335.0万円', $html);
        $this->assertStringContainsString('class="c-price-breakdown__bar" aria-hidden="true"', $html);
        $this->assertStringContainsString('class="c-price-breakdown__formula" aria-hidden="true"', $html);
        // バーの幅は 300 : 35 の割合（viewBox 0〜1000）
        $this->assertMatchesRegularExpression('/c-price-breakdown__seg--body" x="0" y="0" width="892\.\d+"/', $html);

        $noBase = Blade::render('<x-site.price-breakdown :car="$car" />', ['car' => $this->car(['base_price' => null])]);
        $this->assertStringContainsString('車両本体価格はお問い合わせください', $noBase);
        $this->assertStringNotContainsString('c-price-breakdown__bar', $noBase);
        $this->assertStringNotContainsString('諸費用', $noBase);

        $this->assertSame('', trim(Blade::render('<x-site.price-breakdown :car="$car" />', ['car' => $this->car(['price_negotiable' => true])])));

        // 詳細の価格ブロックは内訳（バー＋式）を中に持つ
        $detail = Blade::render('<x-site.price :car="$car" size="detail" />', ['car' => $this->car()]);
        $this->assertStringContainsString('c-price-breakdown', $detail);
    }

    public function test_loan_sim_uses_example_values_from_config_and_states_it_is_an_example(): void
    {
        config(['shop.loan.example_rate' => 3.9, 'shop.loan.example_months' => [36, 48, 60, 72, 84], 'shop.loan.default_months' => 60]);
        $html = Blade::render('<x-site.loan-sim />');

        $this->assertStringContainsString('例：実質年率3.9%で計算', $html);
        $this->assertStringContainsString('計算例です。実際の金利・回数・お支払い額は、ローン会社の審査により異なります。', $html);
        $this->assertStringContainsString('<label class="c-loan__label" for="loan-price">支払総額</label>', $html);
        $this->assertStringContainsString('<label class="c-loan__label" for="loan-down">頭金</label>', $html);
        $this->assertStringContainsString('<label class="c-loan__label" for="loan-rate">金利（実質年率）</label>', $html);
        $this->assertStringContainsString('<legend class="c-loan__label">支払回数</legend>', $html);
        $this->assertSame(5, substr_count($html, 'name="loan-months"'));
        $this->assertMatchesRegularExpression('/value="60" x-model.number="months"\s+checked/', $html);
        // 初期表示（JavaScript が動く前）も元利均等の計算結果を出す：150万円・60回・3.9% → 約27,557円
        $this->assertStringContainsString('27,557', $html);
        $this->assertStringContainsString('60回・実質年率3.9%・頭金0円・ボーナス払いなし', $html);
        // 「計算例」の注記は結果（月々の金額と条件）のすぐ下に置く（比率バー・式・ボタンより前）
        $this->assertMatchesRegularExpression('/<p class="c-loan__cond"[^>]*>[^<]*<\/p>\s*(\{\{--.*?--\}\}\s*)?<p class="c-loan__note">計算例です。/su', $html);
        $this->assertLessThan(strpos($html, 'class="c-loan__bar"'), strpos($html, 'class="c-loan__note"'));
        $this->assertStringContainsString('aria-describedby="loan-rate-help loan-rate-err"', $html);
        $this->assertStringContainsString('href="'.e(route('contact.index', ['purpose' => 'loan'])).'"', $html);

        // 掲載中の車で計算するボタン（価格が応談の車は出さない）
        $cars = collect([$this->car(), $this->car(['model' => 'セレナ', 'price_negotiable' => true])]);
        $withCars = Blade::render('<x-site.loan-sim :cars="$cars" id-prefix="q" />', ['cars' => $cars]);
        $this->assertStringContainsString('x-on:click="price = 335">CX-5<b>335.0</b>', $withCars);
        $this->assertStringNotContainsString('セレナ<b>', $withCars);
        $this->assertStringContainsString('for="q-price"', $withCars);
    }

    public function test_illust_renders_every_body_type_and_is_hidden_from_screen_readers(): void
    {
        foreach (['kei', 'compact', 'minivan', 'suv', 'sedan', 'hatchback', 'wagon', 'sports', 'welfare', 'truck', 'other', 'all',
            'coins', 'meter', 'p-total', 'p-frame', 'p-shaken', 'p-explain', 'f-search', 'f-store', 'f-contract', 'f-key',
            'buy', 'loan', 'service', 'empty', 'mail-check'] as $name) {
            $html = Blade::render('<x-site.illust :name="$name" />', ['name' => $name]);
            $this->assertStringContainsString('class="c-illust c-illust--'.$name.'"', $html, $name);
            $this->assertStringContainsString('aria-hidden="true"', $html, $name);
        }

        $labelled = Blade::render('<x-site.illust name="suv" label="SUV のイラスト" />');
        $this->assertStringContainsString('role="img" aria-label="SUV のイラスト"', $labelled);
        $this->assertStringContainsString('c-illust--other', Blade::render('<x-site.illust name="unknown" />'));
    }

    public function test_heading_parts_keep_english_as_decoration_only(): void
    {
        $head = Blade::render('<x-site.section-head id="stock-title" title="いま掲載中の車" en="STOCK" lead="説明" />');
        $this->assertStringContainsString('<h2 id="stock-title" class="c-section-title"><span>いま掲載中の車</span><span class="c-section-title__en" aria-hidden="true" lang="en">STOCK</span></h2>', $head);
        $this->assertStringContainsString('<p class="c-section-lead">説明</p>', $head);

        $band = Blade::render('<x-site.band-title id="promise-title" title="当店の4つのお約束" en="OUR PROMISE" />');
        $this->assertStringContainsString('<span class="c-band-title__en" aria-hidden="true" lang="en">OUR PROMISE</span><span class="c-slant c-slant--red">当店の4つのお約束</span>', $band);

        $page = Blade::render('<x-site.page-header title="中古車在庫一覧" en="STOCK LIST" />');
        $this->assertStringContainsString('<span class="c-page-header__en" aria-hidden="true" lang="en">STOCK LIST</span>', $page);
        $this->assertStringContainsString('<h1 class="c-page-title">中古車在庫一覧</h1>', $page);

        // STEP. の英字は読み上げず、番号と見出しだけを読み上げる
        $flow = Blade::render('<x-site.flow :steps="[[\'title\' => \'納車\', \'illust\' => \'f-key\']]" />');
        $this->assertStringContainsString('<p class="c-flow__no"><span aria-hidden="true">STEP.</span><b>1</b></p>', $flow);
        $this->assertStringContainsString('<h3 class="c-flow__title">納車</h3>', $flow);
        $step = Blade::render('<x-site.arrow-step :no="2" title="予算を決める" illust="coins">中身</x-site.arrow-step>');
        $this->assertStringContainsString('<span class="c-arrow-step__no"><span aria-hidden="true">STEP.</span><b>2</b></span>', $step);
        $this->assertStringContainsString('c-illust--coins c-arrow-step__ill', $step);
    }

    public function test_btn2_round_badge_chip_and_link_card(): void
    {
        $btn = Blade::render('<x-site.btn2 href="/contact" small="お問い合わせは無料です" big="在庫確認・来店予約" bubble="全車 支払総額で表示" />');
        $this->assertStringContainsString('<a class="c-btn2 c-btn2--red c-btn2--has-bubble" href="/contact">', $btn);
        $this->assertStringContainsString('<span class="c-btn2__small">お問い合わせは無料です</span>', $btn);
        $this->assertStringContainsString('<span class="c-btn2__big">在庫確認・来店予約</span>', $btn);
        $this->assertStringContainsString('c-icon--chevron-right c-btn2__arrow', $btn);
        $this->assertStringContainsString('<button class="c-btn2 c-btn2--black" type="submit">', Blade::render('<x-site.btn2 type="submit" variant="black" big="送信する" />'));

        $badge = Blade::render('<x-site.round-badge top="ただいま" :num="4" unit="台" bottom="掲載中" href="/cars" label="ただいま4台掲載中。掲載中の車を見る" />');
        $this->assertStringContainsString('aria-label="ただいま4台掲載中。掲載中の車を見る"', $badge);
        $this->assertStringContainsString('<span class="c-round-badge__num">4<small>台</small></span>', $badge);

        $chip = Blade::render('<x-site.chip href="/cars" icon="star" tone="yellow" count="2台" current>店長おすすめ</x-site.chip>');
        $this->assertStringContainsString('aria-current="page"', $chip);
        $this->assertStringContainsString('<span class="c-chip__n">2台</span>', $chip);

        $card = Blade::render('<x-site.link-card href="/cars" icon="bag" title="通勤・お買い物に" sub="小回りのきく軽自動車" :count="1" />');
        $this->assertStringContainsString('<span class="c-link-card__n">1<small>台</small></span>', $card);
        $this->assertStringNotContainsString('c-link-card__chev', $card);
    }

    public function test_promise_equip_list_and_type_choice(): void
    {
        $promise = Blade::render('<x-site.promise :no="1" illust="p-total" title="支払総額で表示">本文<x-slot:visual><p>図</p></x-slot:visual></x-site.promise>');
        $this->assertStringContainsString('<span class="c-promise__no" aria-hidden="true">01</span>', $promise);
        $this->assertStringContainsString('<div class="c-promise__visual"><p>図</p></div>', $promise);

        $equips = Blade::render('<x-site.equip-list :items="[\'衝突被害軽減ブレーキ\', \'ETC\']" />');
        $this->assertStringContainsString('c-icon--eq-brake', $equips);
        $this->assertStringContainsString('<span class="c-equip__t">ETC</span>', $equips);
        // 装備名は語の区切りで折り返す（スマホの3列で「衝突被害軽減ブ／レーキ」のように割らない）
        $this->assertStringContainsString('<span class="c-equip__t">衝突被害軽減<wbr>ブレーキ</span>', $equips);
        $this->assertSame('', trim(Blade::render('<x-site.equip-list :items="[]" />')));

        $choice = Blade::render('<x-site.type-choice name="body_type" value="軽自動車" label="軽自動車" count="1台" checked />');
        $this->assertStringContainsString('<input class="c-type-choice__input" type="radio" name="body_type" value="軽自動車"', $choice);
        $this->assertStringContainsString('checked', $choice);
        $this->assertStringContainsString('c-illust--kei c-type-choice__ill', $choice);
    }
}
