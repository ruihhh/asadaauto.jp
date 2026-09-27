<?php

/*
|--------------------------------------------------------------------------
| 店舗の基本情報・表示用の設定値
|--------------------------------------------------------------------------
|
| 電話番号・住所・営業時間・定休日・LINE・古物商許可・保証などは、ビューに
| 直書きせずここだけで管理する。値が null の項目は、どのページでも画面に
| 出さない（オーナー確認待ちの項目は null のままにしておく）。
|
*/

$address = '兵庫県尼崎市下坂部4丁目5-1';

return [

    'name' => 'アサダオートサポート',
    'tagline' => '尼崎市の中古車販売・買取・車検整備',
    'business_lines' => ['中古車販売', '買取', '車検', '一般整備', '板金', '保険'],

    // 電話（表示用と tel: リンク用）
    'tel' => '06-4960-8765',
    'tel_href' => 'tel:0649608765',

    // 所在地
    'postal' => '661-0975',
    'address' => $address,
    'pref' => '兵庫県',
    'city' => '尼崎市',
    'area' => '尼崎市下坂部', // 「当店（尼崎市下坂部）に展示」などの短い地名
    'map_url' => 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($address),
    'directions_url' => 'https://www.google.com/maps/dir/?api=1&destination='.rawurlencode($address),
    'map_embed_url' => env('SHOP_MAP_EMBED_URL') ?: null,
    'geo' => ['lat' => 34.7167, 'lng' => 135.4167], // 概算値（実測値はオーナー確認待ち）

    // 営業時間・定休日（判定は App\Support\BusinessHours が Asia/Tokyo で行う）
    'hours' => ['open' => '11:00', 'close' => '21:00'],
    'closed_weekdays' => [4],          // 曜日番号（0=日〜6=土）。4=木曜
    'closed_nth_weekdays' => [[3, 0]], // [第n, 曜日番号]。[3, 0]=第3日曜
    'closed_label' => '木曜・第3日曜',
    'holidays' => [
        // 臨時休業。'Y-m-d' => '理由' の形で登録する（例：'2026-12-31' => '年末年始休業'）
    ],

    // LINE 公式アカウントの友だち追加 URL。未設定のあいだは LINE ボタンを出さない
    'line_url' => env('SHOP_LINE_URL') ?: null,

    // 古物商許可（未設定のあいだは表示しない。公開前に必須）
    'kobutsu' => [
        'authority' => '兵庫県公安委員会',
        'number' => env('SHOP_KOBUTSU_NO') ?: null,
        'holder' => env('SHOP_KOBUTSU_HOLDER') ?: null,
    ],

    // 運営者
    'operator' => [
        'company' => env('SHOP_OPERATOR') ?: null,
        'representative' => '朝田 繕行',
        'representative_kana' => null,
    ],

    'parking' => '無料駐車場あり',

    // 支払総額の前提条件（「支払総額は◯◯の価格です」の◯◯）
    'price_condition' => '兵庫県内で登録し、当店で店頭納車する場合',

    // 保証・定期点検整備（全車共通の表示文。例：'保証付き（3か月・3,000km）'）。null の間は「お問い合わせください」
    'warranty' => null,
    'maintenance' => null,

    // ローン。トップの「月々のお支払いの計算例」は sample_rate と max_months の両方が入ったときだけ出す
    //   sample_rate：計算例に使う実質年率を「％の数値」で書く（3.9％なら 3.9。0.039 のような割合では書かない）
    //   max_months： 最長の支払回数（例：84）。計算例の回数の選択肢は12回刻みでここまで
    //   note：       ローンの案内に添える一文（任意）
    //
    // お支払いシミュレーション（x-site.loan-sim）は「計算例」として次の例の値で表示する（2026-09-27 オーナー決定）。
    //   example_rate：   計算例の初期値の実質年率（％の数値）。利用者が画面で変えられる
    //   example_months： 選べる支払回数（例）
    //   default_months： 初期表示の支払回数（example_months のどれか）
    // オーナーが提携ローンの実際の条件を確定したら、この3つを差し替えるだけにする。
    'loan' => [
        'sample_rate' => null,
        'max_months' => null,
        'note' => null,
        'example_rate' => 3.9,
        'example_months' => [36, 48, 60, 72, 84],
        'default_months' => 60,
    ],

    // お問い合わせへの返信の目安
    'reply_note' => null,

    // 買取
    'buy' => [
        'payment_timing' => null,
        'no_reduction_after_contract' => false,
        'visit_area' => null,
    ],

    'established_year' => null,

];
