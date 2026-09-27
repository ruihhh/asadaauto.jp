<?php

namespace App\Support;

use App\Models\Car;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\HtmlString;

/**
 * 公開画面の車両情報の表記（万円・万km・和暦・車検・修復歴・ミッション・燃料・ボディタイプ・装備・展示場所・新着）。
 *
 * 表記は「業界ルール＋用語ルール」に合わせて、ここで1か所にまとめる。ビューはこれを呼ぶだけにする。
 */
class CarText
{
    /** 値がない項目の表示 */
    public const UNKNOWN = 'お問い合わせください';

    /** 修復歴の定義（主語なし）。「修復歴」という見出し・項目名のすぐ隣に置くとき（用語の説明の表など）に使う */
    public const REPAIR_DEFINITION_SHORT = '車の骨格（フレーム）部分を修理・交換した履歴のことです。';

    /** 修復歴の定義（詳細ページで「修復歴」に添える一文） */
    public const REPAIR_DEFINITION = '修復歴とは、'.self::REPAIR_DEFINITION_SHORT;

    /** 掲載から何日以内を「新着」とするか */
    public const NEW_DAYS = 7;

    // 元号（新しい順）。start はその元号が始まった日
    private const ERAS = [
        ['name' => '令和', 'abbr' => 'R', 'start' => '2019-05-01'],
        ['name' => '平成', 'abbr' => 'H', 'start' => '1989-01-08'],
        ['name' => '昭和', 'abbr' => 'S', 'start' => '1926-12-25'],
        ['name' => '大正', 'abbr' => 'T', 'start' => '1912-07-30'],
    ];

    private const TRANSMISSIONS = [
        'AT' => 'AT',
        'CVT' => 'AT（CVT）',
        'MT' => 'MT',
        'AMT' => 'AT（AMT）',
        'DCT' => 'AT（DCT）',
    ];

    private const FUELS = [
        'HV' => 'ハイブリッド',
        'PHV' => 'プラグインハイブリッド（PHEV）',
        'PHEV' => 'プラグインハイブリッド（PHEV）',
        'プラグインハイブリッド' => 'プラグインハイブリッド（PHEV）',
        'EV' => '電気（EV）',
        '電気' => '電気（EV）',
        'LPG' => 'LPG（LPガス）',
    ];

    // 表示名だけ変える（検索パラメータの値は DB の値のまま）
    private const BODY_TYPES = [
        'コンパクト' => 'コンパクトカー',
        'ワゴン' => 'ステーションワゴン',
        'SUV・クロスオーバー' => 'SUV',
        'SUV・四駆' => 'SUV',
        'HB' => 'ハッチバック',
    ];

    private const EQUIPMENT_LABELS = [
        'Wエアコン' => 'Wエアコン（前後独立エアコン）',
        '1500W電源' => 'AC100V電源（1500W）',
        'ESC（横滑り防止）' => 'ESC（横滑り防止装置）',
        'ESC' => 'ESC（横滑り防止装置）',
        'エアバッグ（サイド/カーテン）' => 'エアバッグ（サイド・カーテン）',
    ];

    private const EQUIPMENT_CATEGORIES = [
        'インテリア' => '内装',
        'エクステリア' => '外装',
    ];

    // breakable()：カタカナが続く所で、この語の前を折り返してよい位置にする（ボディカラー・装備によく出る語）
    private const BREAK_BEFORE_WORDS = [
        // ボディカラー
        'メタリック', 'パール', 'マイカ', 'クリスタル', 'プレミアム', 'ブラック', 'ホワイト', 'シルバー', 'グレー', 'グレイ',
        'レッド', 'ブルー', 'グリーン', 'イエロー', 'オレンジ', 'ブラウン', 'ベージュ', 'ゴールド', 'カーキ', 'ガンメタ',
        // 装備
        'レコーダー', 'ヒーター', 'カメラ', 'ナビ', 'ブレーキ', 'クルーズ', 'コントロール', 'スライド', 'ドア', 'ホイール',
        'ライト', 'ランプ', 'フォグ', 'モニター', 'センサー', 'キー', 'アシスト', 'シート', 'エアコン', 'ウインドウ',
        'バック', 'ルーフ', 'レール', 'エアロ', 'ハイビーム', 'ハイブリッド', 'ストップ', 'プレイヤー', 'フラット',
    ];

    // 「主な装備」の優先順
    private const HIGHLIGHT_ORDER = [
        '衝突被害軽減ブレーキ', '全周囲カメラ', 'バックカメラ', 'メモリーナビ', 'ETC2.0', 'ETC',
        'ドライブレコーダー', '両側電動スライドドア', 'アダプティブクルーズコントロール',
        'シートヒーター', '3列シート', 'スマートキー',
    ];

    /**
     * 車名。「メーカー 車種」（半角スペース区切り。既存テストがこの表記を探す）。
     */
    public static function name(Car $car): string
    {
        return trim($car->make.' '.$car->model);
    }

    /* ---------------------------------------------------------------
     | 価格
     * --------------------------------------------------------------- */

    /**
     * 1,000円単位なら「335.0万円」、それ以外は「3,351,200円」。null は「お問い合わせください」。
     */
    public static function price(?int $yen): string
    {
        if ($yen === null) {
            return self::UNKNOWN;
        }

        $parts = self::priceParts($yen);

        return $parts['value'].$parts['unit'];
    }

    /**
     * 価格ブロック用に数字と単位を分けたもの。
     *
     * @return array{value: string, unit: '万円'|'円'} 例：['value' => '335.0', 'unit' => '万円']
     */
    public static function priceParts(int $yen): array
    {
        $man = self::priceMan($yen);

        return $man !== null
            ? ['value' => $man, 'unit' => '万円']
            : ['value' => number_format($yen), 'unit' => '円'];
    }

    /**
     * 万円の数字だけ（「335.0」）。1,000円単位でない金額は万円にできないので null。
     */
    public static function priceMan(?int $yen): ?string
    {
        if ($yen === null || $yen % 1000 !== 0) {
            return null;
        }

        $tenths = intdiv($yen, 1000); // 0.1万円単位

        return ($tenths < 0 ? '-' : '').number_format(intdiv(abs($tenths), 10)).'.'.(abs($tenths) % 10);
    }

    /**
     * 円表記（「3,350,000円」）。
     */
    public static function yen(?int $yen): string
    {
        return $yen === null ? self::UNKNOWN : number_format($yen).'円';
    }

    /**
     * 諸費用（支払総額 − 車両本体価格）。車両本体価格がないとき・計算が合わないときは null。
     */
    public static function fees(Car $car): ?int
    {
        if ($car->base_price === null || $car->price === null) {
            return null;
        }

        $fees = (int) $car->price - (int) $car->base_price;

        return $fees >= 0 ? $fees : null;
    }

    /**
     * 価格の条件注記（価格のすぐ下に置く文）。
     */
    public static function priceNote(): string
    {
        return '支払総額は'.config('shop.price_condition').'の価格です。'
            .'税金・自賠責保険料・登録などの手続き費用を含みます。'
            .'県外での登録、ご自宅への納車、ご希望のオプションの費用は含みません。';
    }

    /**
     * 「価格は2026年4月26日時点のものです」（車両の更新日）。更新日がなければ null。
     */
    public static function priceAsOf(Car $car): ?string
    {
        $date = self::date($car->updated_at);

        return $date !== null ? "価格は{$date}時点のものです" : null;
    }

    /* ---------------------------------------------------------------
     | 走行距離・年式・日付
     * --------------------------------------------------------------- */

    /**
     * 千km未満を四捨五入して「1.1万km」。1,000km未満は「1,000km未満」。
     */
    public static function mileage(?int $km): string
    {
        if ($km === null) {
            return self::UNKNOWN;
        }

        if ($km < 1000) {
            return '1,000km未満';
        }

        $thousands = intdiv($km + 500, 1000); // 千km単位に四捨五入

        return number_format(intdiv($thousands, 10)).'.'.($thousands % 10).'万km';
    }

    /**
     * 年式。カードは「2023（R5）年式」、$long のときは「2023年（令和5年）」。
     * 改元の年は両方を書く（「2019（H31/R1）年式」「1989（S64/H1）年式」）。
     */
    public static function year(?int $year, bool $long = false): string
    {
        if (! $year) {
            return self::UNKNOWN;
        }

        $eras = self::erasInYear($year);

        if ($eras === []) {
            return $long ? "{$year}年" : "{$year}年式";
        }

        if ($long) {
            $parts = array_map(fn (array $e): string => $e['name'].($e['n'] === 1 ? '元' : $e['n']).'年', $eras);

            return "{$year}年（".implode('/', $parts).'）';
        }

        $parts = array_map(fn (array $e): string => $e['abbr'].$e['n'], $eras);

        return "{$year}（".implode('/', $parts).'）年式';
    }

    /**
     * カードの年式（year() の「2023（R5）年式」）を、西暦の数字とそれ以降に分けたもの。
     * 車両カードの年式の枠で、西暦を大きな数字の書体で・残りを小さく出すため（つなげて読むと year() と同じ文字になる）。
     * 2023 → ['value' => '2023', 'rest' => '（R5）年式']、2019 → ['value' => '2019', 'rest' => '（H31/R1）年式']、
     * 未入力 → ['value' => 'お問い合わせください', 'rest' => null]
     *
     * @return array{value: string, rest: string|null}
     */
    public static function yearParts(?int $year): array
    {
        $text = self::year($year);

        if ($year && preg_match('/^(\d+)(.+)$/u', $text, $m)) {
            return ['value' => $m[1], 'rest' => $m[2]];
        }

        return ['value' => $text, 'rest' => null];
    }

    /**
     * 走行距離を「数字」と「単位」に分けたもの（数字だけを大きな数字用の書体で出すため）。
     * 11200 → ['value' => '1.1', 'unit' => '万km']、999 → ['value' => '1,000', 'unit' => 'km未満']、null → null
     *
     * @return array{value: string, unit: string}|null
     */
    public static function mileageParts(?int $km): ?array
    {
        if ($km === null) {
            return null;
        }

        if (preg_match('/^([\d,.]+)(.+)$/u', self::mileage($km), $m)) {
            return ['value' => $m[1], 'unit' => $m[2]];
        }

        return null;
    }

    /**
     * 和暦。$unit は 'year'（令和9年）/ 'month'（令和9年3月）/ 'day'（令和9年3月1日）。1年目は「元年」。
     * 日付そのもの（タイムゾーン変換なし）で判定する。
     */
    public static function wareki(CarbonInterface $date, string $unit = 'month'): string
    {
        $ymd = $date->format('Y-m-d');

        foreach (self::ERAS as $era) {
            if ($ymd >= $era['start']) {
                $n = (int) $date->format('Y') - (int) substr($era['start'], 0, 4) + 1;
                $year = $era['name'].($n === 1 ? '元' : $n).'年';

                return match ($unit) {
                    'year' => $year,
                    'day' => $year.$date->format('n月j日'),
                    default => $year.$date->format('n月'),
                };
            }
        }

        return match ($unit) {
            'year' => $date->format('Y年'),
            'day' => $date->format('Y年n月j日'),
            default => $date->format('Y年n月'),
        };
    }

    /**
     * 日時を東京の日付にして「2026年9月27日」。published_at・updated_at などのタイムスタンプ用。
     */
    public static function date(?CarbonInterface $dateTime): ?string
    {
        return $dateTime !== null
            ? CarbonImmutable::instance($dateTime)->setTimezone(BusinessHours::TIMEZONE)->format('Y年n月j日')
            : null;
    }

    /* ---------------------------------------------------------------
     | 車検・修復歴
     * --------------------------------------------------------------- */

    /**
     * 車検の表記。
     *
     *  state      label                                        short                   tone
     *  expiry     2027年3月まで（令和9年3月）                  2027年3月まで           ok
     *  included   車検2年付き（ご納車時に新しく取得）          2年付き（納車時に取得） ok
     *  remaining  車検の残りあり（期限は要確認）               残りあり                neutral   … 区分「あり」で期限が未入力
     *  expired    車検なし（2026年8月で期限切れ）              期限切れ                caution   … 期限の月が過ぎている
     *  none       車検なし（車検の取得はお問い合わせください） なし                    caution
     *  unknown    要確認                                       要確認                  neutral   … 未入力
     *
     * @return array{state: 'expiry'|'included'|'remaining'|'expired'|'none'|'unknown', label: string, short: string, tone: 'ok'|'neutral'|'caution'}
     */
    public static function inspection(Car $car, ?CarbonInterface $now = null): array
    {
        $type = trim((string) $car->inspection_type);
        $expiry = $car->inspection_expiry;

        if (preg_match('/^(\d+)年付き?$/u', $type, $m)) {
            return [
                'state' => 'included',
                'label' => "車検{$m[1]}年付き（ご納車時に新しく取得）",
                'short' => "{$m[1]}年付き（納車時に取得）",
                'tone' => 'ok',
            ];
        }

        if ($type === 'なし') {
            return [
                'state' => 'none',
                'label' => '車検なし（車検の取得はお問い合わせください）',
                'short' => 'なし',
                'tone' => 'caution',
            ];
        }

        if ($expiry instanceof CarbonInterface && ($type === '' || $type === 'あり')) {
            $month = $expiry->format('Y年n月');
            $today = $now !== null
                ? CarbonImmutable::instance($now)->setTimezone(BusinessHours::TIMEZONE)
                : CarbonImmutable::now(BusinessHours::TIMEZONE);

            if ($expiry->format('Y-m') < $today->format('Y-m')) {
                return [
                    'state' => 'expired',
                    'label' => "車検なし（{$month}で期限切れ）",
                    'short' => '期限切れ',
                    'tone' => 'caution',
                ];
            }

            return [
                'state' => 'expiry',
                'label' => "{$month}まで（".self::wareki($expiry).'）',
                'short' => "{$month}まで",
                'tone' => 'ok',
            ];
        }

        if ($type === 'あり') {
            return [
                'state' => 'remaining',
                'label' => '車検の残りあり（期限は要確認）',
                'short' => '残りあり',
                'tone' => 'neutral',
            ];
        }

        return [
            'state' => 'unknown',
            'label' => '要確認',
            'short' => '要確認',
            'tone' => 'neutral',
        ];
    }

    /**
     * 修復歴。「あり」のときは note（部位はお問い合わせください）を添える。
     *
     * @return array{has: ?bool, label: string, text: string, note: ?string, tone: 'ok'|'caution'|'neutral'}
     */
    public static function repair(Car $car): array
    {
        if ($car->accident_count === null) {
            return ['has' => null, 'label' => self::UNKNOWN, 'text' => '修復歴はお問い合わせください', 'note' => null, 'tone' => 'neutral'];
        }

        if ((int) $car->accident_count > 0) {
            return ['has' => true, 'label' => 'あり', 'text' => '修復歴あり', 'note' => '部位はお問い合わせください', 'tone' => 'caution'];
        }

        return ['has' => false, 'label' => 'なし', 'text' => '修復歴なし', 'note' => null, 'tone' => 'ok'];
    }

    /* ---------------------------------------------------------------
     | ミッション・燃料・ボディタイプ
     * --------------------------------------------------------------- */

    /**
     * ミッション。CVT は「AT（CVT）」。未入力は「お問い合わせください」。
     */
    public static function transmission(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return self::UNKNOWN;
        }

        $key = strtoupper(mb_convert_kana($value, 'as'));

        return self::TRANSMISSIONS[$key] ?? $value;
    }

    /**
     * 燃料。未入力は「お問い合わせください」。
     */
    public static function fuel(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return self::UNKNOWN;
        }

        return self::FUELS[strtoupper(mb_convert_kana($value, 'as'))] ?? self::FUELS[$value] ?? $value;
    }

    /**
     * ボディタイプの表示名（コンパクト→コンパクトカー、ワゴン→ステーションワゴン）。未入力は「お問い合わせください」。
     */
    public static function bodyType(?string $value): string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return self::UNKNOWN;
        }

        return self::BODY_TYPES[$value] ?? $value;
    }

    /**
     * ボディタイプのイラスト名（x-site.illust の name）。DB の値・表示名のどちらを渡してもよい。
     * kei / compact / minivan / suv / sedan / hatchback / wagon / sports / welfare / truck / other
     */
    public static function bodyIllust(?string $value): string
    {
        $value = trim((string) $value);

        return match (true) {
            $value === '' => 'other',
            str_contains($value, '軽') => 'kei',
            str_contains($value, 'コンパクト') => 'compact',
            str_contains($value, 'ミニバン'), str_contains($value, 'ワンボックス') => 'minivan',
            str_contains($value, 'SUV'), str_contains($value, 'クロカン'), str_contains($value, 'クロスオーバー') => 'suv',
            str_contains($value, 'セダン') => 'sedan',
            $value === 'HB', str_contains($value, 'ハッチバック') => 'hatchback',
            str_contains($value, 'ワゴン') => 'wagon',
            str_contains($value, 'クーペ'), str_contains($value, 'スポーツ'), str_contains($value, 'オープン') => 'sports',
            str_contains($value, '福祉') => 'welfare',
            str_contains($value, 'トラック') => 'truck',
            default => 'other',
        };
    }

    /* ---------------------------------------------------------------
     | 装備
     * --------------------------------------------------------------- */

    /**
     * 装備のアイコン名（x-site.icon の name）。装備名・表示名のどちらを渡してもよい。対応するものがなければ eq-check。
     */
    public static function equipmentIcon(string $name): string
    {
        $rules = [
            '衝突被害軽減' => 'eq-brake',
            'ブレーキ' => 'eq-brake',
            '全周囲カメラ' => 'eq-360',
            'バックカメラ' => 'eq-camera',
            'ナビ' => 'eq-navi',
            'ETC' => 'eq-etc',
            'ドライブレコーダー' => 'eq-dashcam',
            'シートヒーター' => 'eq-seat',
            'シートエアコン' => 'eq-seat',
            'スライドドア' => 'eq-slide',
            'クルーズ' => 'eq-cruise',
            'スマートキー' => 'eq-key',
            '3列シート' => 'eq-3row',
            'エアコン' => 'eq-aircon',
            'レーンキープ' => 'eq-lane',
            'サンルーフ' => 'eq-sunroof',
            'アルミホイール' => 'eq-wheel',
        ];

        foreach ($rules as $needle => $icon) {
            if (str_contains($name, $needle)) {
                return $icon;
            }
        }

        return 'eq-check';
    }

    /**
     * 装備名の表示名（説明を添える）。
     */
    public static function equipmentLabel(string $name): string
    {
        return self::EQUIPMENT_LABELS[$name] ?? $name;
    }

    /**
     * 装備のカテゴリ名（インテリア→内装、エクステリア→外装）。
     */
    public static function equipmentCategory(string $category): string
    {
        return self::EQUIPMENT_CATEGORIES[$category] ?? $category;
    }

    /**
     * 主な装備（優先順に最大 $limit 件、表示名）。
     * ETC2.0 があれば ETC、全周囲カメラがあればバックカメラは重ねて出さない。
     *
     * @return list<string>
     */
    public static function equipmentHighlights(Car $car, int $limit = 6): array
    {
        $equipped = self::equipment($car);
        $result = [];

        foreach (self::HIGHLIGHT_ORDER as $item) {
            if (count($result) >= $limit) {
                break;
            }
            if (! in_array($item, $equipped, true)) {
                continue;
            }
            if ($item === 'ETC' && in_array('ETC2.0', $equipped, true)) {
                continue;
            }
            if ($item === 'バックカメラ' && in_array('全周囲カメラ', $equipped, true)) {
                continue;
            }
            $result[] = self::equipmentLabel($item);
        }

        return $result;
    }

    /**
     * 装備している項目だけを、カテゴリ別（表示名）にまとめたもの。どのカテゴリにもない項目は「その他」。
     *
     * @return array<string, list<string>> 例：['安全装備' => ['ABS', …], '内装' => [...]]
     */
    public static function equipmentByCategory(Car $car): array
    {
        $equipped = self::equipment($car);
        $groups = [];
        $known = [];

        foreach (Car::EQUIPMENT_CATEGORIES as $category => $items) {
            $known = array_merge($known, $items);
            $hits = array_values(array_filter($items, fn (string $item): bool => in_array($item, $equipped, true)));
            if ($hits !== []) {
                $groups[self::equipmentCategory($category)] = array_map([self::class, 'equipmentLabel'], $hits);
            }
        }

        $others = array_values(array_diff($equipped, $known));
        if ($others !== []) {
            $groups['その他'] = array_map([self::class, 'equipmentLabel'], $others);
        }

        return $groups;
    }

    /* ---------------------------------------------------------------
     | 折り返し位置
     * --------------------------------------------------------------- */

    /**
     * 装備名・ボディカラー・展示場所などを、折り返してよい位置に <wbr> を入れた HTML にする（文字はエスケープ済み）。
     * スマホの狭い枠で「衝突被害軽減ブ｜レーキ」「ポリメタルグレ｜ー」のように語の途中で改行しないよう、
     * 表示側の CSS の word-break: keep-all（入りきらないときだけ overflow-wrap: anywhere）と組み合わせて使う。
     * 区切る位置：漢字とカタカナの境目（衝突被害軽減｜ブレーキ）・かっこの前・「・」「、」のあと・都道府県名のあと（千葉県｜船橋市）・
     * 「で保管中」「ご予約」の前後・カタカナが続く所のよくある語の前（ドライブ｜レコーダー、ポリメタル｜グレー、デニム｜ブルー｜メタリック）。
     */
    public static function breakable(?string $text): HtmlString
    {
        $words = implode('|', self::BREAK_BEFORE_WORDS);
        $pattern = '/(?<=\p{Han})(?=\p{Katakana})'
            .'|(?<=\p{Katakana}|ー)(?=\p{Han})'
            .'|(?<=.)(?=（)'
            .'|(?<=[・、])(?=.)'
            .'|(?<=^東京都|^北海道|^京都府|^大阪府|^..県|^...県)(?=\p{Han})'
            .'|(?=で保管中)|(?<=は)(?=ご予約)|(?<=ご予約)(?=ください)'
            .'|(?<=\p{Katakana}|ー)(?='.$words.')/u';

        return new HtmlString((string) preg_replace($pattern, '<wbr>', e((string) $text)));
    }

    /* ---------------------------------------------------------------
     | 展示場所・新着・タグ
     * --------------------------------------------------------------- */

    /**
     * 店舗に展示している車か（場所が空、または県名・市名・「当店」「店頭」を含む）。
     */
    public static function atShop(Car $car): bool
    {
        $location = trim((string) $car->location);
        if ($location === '') {
            return true;
        }

        foreach ([config('shop.pref'), config('shop.city'), '当店', '店頭'] as $needle) {
            if (is_string($needle) && $needle !== '' && str_contains($location, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 展示場所。「当店（尼崎市下坂部）に展示」または「◯◯で保管中（現車確認はご予約ください）」。
     */
    public static function location(Car $car): string
    {
        return self::atShop($car)
            ? '当店（'.config('shop.area').'）に展示'
            : trim((string) $car->location).'で保管中（現車確認はご予約ください）';
    }

    /**
     * 「新着」とみなす掲載日時の下限（東京の暦日で NEW_DAYS 日前の 0:00）。一覧の絞り込みにも使える。
     */
    public static function newSince(?CarbonInterface $now = null): CarbonImmutable
    {
        $now = $now !== null
            ? CarbonImmutable::instance($now)->setTimezone(BusinessHours::TIMEZONE)
            : CarbonImmutable::now(BusinessHours::TIMEZONE);

        return $now->startOfDay()->subDays(self::NEW_DAYS);
    }

    /**
     * 掲載から7日以内か（Asia/Tokyo の暦日で判定）。掲載日が未設定なら登録日で判定する。
     */
    public static function isNew(Car $car, ?CarbonInterface $now = null): bool
    {
        $published = $car->published_at ?? $car->created_at;
        if (! $published instanceof CarbonInterface) {
            return false;
        }

        $now = $now !== null ? CarbonImmutable::instance($now) : CarbonImmutable::now(BusinessHours::TIMEZONE);
        $published = CarbonImmutable::instance($published);

        return $published->lte($now) && $published->gte(self::newSince($now));
    }

    /**
     * 写真の左上に出す状態タグ（商談中・売約済・新着・店長おすすめの順）。variant は .c-tag--{variant}。
     *
     * @return list<array{label: string, variant: 'caution'|'sold'|'new'|'pick'}>
     */
    public static function tags(Car $car, ?CarbonInterface $now = null): array
    {
        $tags = [];

        if ($car->status === 'reserved') {
            $tags[] = ['label' => '商談中', 'variant' => 'caution'];
        } elseif ($car->status === 'sold') {
            $tags[] = ['label' => '売約済', 'variant' => 'sold'];
        }

        if (self::isNew($car, $now)) {
            $tags[] = ['label' => '新着', 'variant' => 'new'];
        }

        if ($car->featured) {
            $tags[] = ['label' => '店長おすすめ', 'variant' => 'pick'];
        }

        return $tags;
    }

    /* ---------------------------------------------------------------
     | 保証・定期点検整備（全車共通の config 値）
     * --------------------------------------------------------------- */

    /**
     * 保証の表示文（config('shop.warranty')）。未設定なら null（表示側で「お問い合わせください」にする）。
     */
    public static function warranty(): ?string
    {
        $value = config('shop.warranty');

        return is_string($value) && trim($value) !== '' ? $value : null;
    }

    /**
     * 定期点検整備の表示文（config('shop.maintenance')）。未設定なら null（表示側で「お問い合わせください」にする）。
     */
    public static function maintenance(): ?string
    {
        $value = config('shop.maintenance');

        return is_string($value) && trim($value) !== '' ? $value : null;
    }

    /**
     * @return list<string>
     */
    private static function equipment(Car $car): array
    {
        $raw = $car->equipment;
        if (! is_array($raw)) {
            return [];
        }

        $items = [];
        array_walk_recursive($raw, function ($value) use (&$items): void {
            if (is_string($value) && trim($value) !== '') {
                $items[] = trim($value);
            }
        });

        return array_values(array_unique($items));
    }

    /**
     * その年にかかる元号（古い順）。
     *
     * @return list<array{name: string, abbr: string, n: int}>
     */
    private static function erasInYear(int $year): array
    {
        $result = [];
        $newerStart = null;
        $yearStart = sprintf('%04d-01-01', $year);

        foreach (self::ERAS as $era) {
            $startYear = (int) substr($era['start'], 0, 4);
            $coversYear = $startYear <= $year && ($newerStart === null || $newerStart > $yearStart);

            if ($coversYear) {
                $result[] = ['name' => $era['name'], 'abbr' => $era['abbr'], 'n' => $year - $startYear + 1];
            }

            $newerStart = $era['start'];
        }

        return array_reverse($result);
    }
}
