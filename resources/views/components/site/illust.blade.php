@props([
    'name',
    'label' => null,
    'width' => null,
    'height' => null,
])
{{--
    彩色のイラスト（案B の画風：黒の輪郭線 2.5px・赤 #D7000F／黄 #FFD60A／白／窓の水色 #BFE0F2・足元に薄い影）。
    インラインの SVG を1つ出す（スプライトは使わない）。既定では飾り（aria-hidden）。意味を持たせるときは label を渡す（role="img"）。

    使い方:
        <x-site.illust name="suv" />                                   … ボディタイプ（120×72）
        <x-site.illust :name="\App\Support\CarText::bodyIllust($car->body_type)" />
        <x-site.illust name="f-key" />                                … ご購入の流れのメダル（96×96。x-site.flow・x-site.medal-steps の中）
        <x-site.illust name="p-total" label="支払総額の値札" />          … 読み上げさせるとき

    name:
        ボディタイプ（120×72）: kei 軽自動車 / compact コンパクトカー / minivan ミニバン / suv SUV / sedan セダン /
                                hatchback ハッチバック / wagon ステーションワゴン / sports スポーツ・クーペ / welfare 福祉車両 /
                                truck トラック / other その他 / all すべて（3台の組み合わせ）
        STEP（80×64）: coins（予算） / meter（走行距離）
        お約束（96×96）: p-total（支払総額） / p-frame（修復歴） / p-shaken（車検） / p-explain（保証と整備の説明）
        ご購入の流れ（96×96）: f-search（探す・問い合わせ） / f-store（来店・試乗） / f-contract（契約） / f-key（納車）
        そのほか（96×96）: buy（買取査定） / loan（ローン） / service（車検・整備） / empty（見つからない） / mail-check（受付完了）
    label:  読み上げ用の説明（渡したときだけ role="img" と aria-label を付ける）
    width / height: 表示サイズ（px）。省略時は viewBox の大きさ。多くの場合は置き場所の CSS で決める
    出力: <svg class="c-illust c-illust--{name}" viewBox="…" width="…" height="…" aria-hidden="true">…</svg>
    知らない name は other を出す。
--}}
@php
    $ink = '#1E1E1E';
    $red = '#D7000F';
    $redDark = '#A8000B';
    $yellow = '#FFD60A';
    $yellowDark = '#E0B400';
    $window = '#BFE0F2';
    $hub = '#C9C9C9';
    $green = '#1B9A4B';

    $shadow = fn (float $cx, float $rx): string => '<ellipse cx="'.$cx.'" cy="66" rx="'.$rx.'" ry="3.5" fill="#000" opacity=".12"/>';
    $wheel = fn (float $cx, float $r = 9.5): string => '<circle cx="'.$cx.'" cy="54" r="'.$r.'" fill="'.$ink.'"/><circle cx="'.$cx.'" cy="54" r="'.round($r * 0.43, 1).'" fill="'.$hub.'"/>';
    $windows = fn (string $inner, string $stroke = '#1E1E1E'): string => '<g fill="'.$window.'" stroke="'.$stroke.'" stroke-width="1.5" stroke-linejoin="round">'.$inner.'</g>';

    $defs = [];

    // ================= ボディタイプ（120×72・右向き） =================
    $defs['suv'] = ['vb' => '0 0 120 72', 'body' =>
        $shadow(61, 52)
        .'<rect x="42" y="10" width="46" height="3.5" rx="1.5" fill="'.$ink.'"/>'
        .'<path fill="'.$red.'" stroke="'.$ink.'" stroke-width="2.5" stroke-linejoin="round" d="M10 52V40q0-5 5-6l14-2 9-15q2-3 6-3h44q4 0 6 3l9 15 6 1q5 1 5 6v13q0 3-3 3H13q-3 0-3-3z"/>'
        .$windows('<path d="M41.5 19H62v12H34.5z"/><path d="M66 19h19.5q2 0 3 1.8L94 31H66z"/>')
        .'<path d="M64 20v26" stroke="#8E000A" stroke-width="1.5"/>'
        .'<rect x="8" y="46" width="108" height="8" rx="3" fill="'.$ink.'"/>'
        .'<rect x="106" y="36" width="7" height="4" rx="1" fill="'.$yellow.'"/>'
        .$wheel(32, 10).$wheel(90, 10)];

    $defs['minivan'] = ['vb' => '0 0 120 72', 'body' =>
        $shadow(60, 52)
        .'<path fill="#FFFFFF" stroke="#3A3A3A" stroke-width="2.5" stroke-linejoin="round" d="M8 52V20q0-8 8-8h70q5 0 8 4l14 17q5 1 5 6v13q0 3-3 3H11q-3 0-3-3z"/>'
        .$windows('<rect x="14" y="17" width="22" height="13" rx="2"/><rect x="40" y="17" width="22" height="13" rx="2"/><rect x="66" y="17" width="18" height="13" rx="2"/><path d="M88 17h2q2 0 3 1.5L103 30H88z"/>', '#3A3A3A')
        .'<path d="M38 38h26" stroke="#3A3A3A" stroke-width="2"/>'
        .'<rect x="6" y="46" width="109" height="8" rx="3" fill="#3A3A3A"/>'
        .'<rect x="107" y="37" width="6" height="4" rx="1" fill="'.$yellow.'"/>'
        .'<rect x="9" y="33" width="3" height="7" rx="1" fill="'.$red.'"/>'
        .$wheel(28).$wheel(92)];

    $defs['kei'] = ['vb' => '0 0 120 72', 'body' =>
        $shadow(60, 42)
        .'<path fill="'.$yellow.'" stroke="'.$ink.'" stroke-width="2.5" stroke-linejoin="round" d="M22 52V18q0-6 6-6h44q5 0 8 4l12 16q5 1 5 6v14q0 3-3 3H25q-3 0-3-3z"/>'
        .$windows('<rect x="28" y="17" width="18" height="13" rx="2"/><rect x="50" y="17" width="20" height="13" rx="2"/><path d="M74 17h1q2 0 3 1.5L87 30H74z"/>')
        .'<rect x="20" y="46" width="79" height="8" rx="3" fill="'.$ink.'"/>'
        .'<rect x="91" y="36" width="5" height="4" rx="1" fill="#FFFFFF"/>'
        .$wheel(38, 9).$wheel(82, 9)];

    $defs['compact'] = ['vb' => '0 0 120 72', 'body' =>
        $shadow(60, 46)
        .'<g transform="translate(4 0)">'
        .'<path fill="'.$green.'" stroke="'.$ink.'" stroke-width="2.5" stroke-linejoin="round" d="M14 52V26q0-6 5-10l4-3q3-2 7-2h34q4 0 6 3l12 16 10 2q5 1 5 6v14q0 3-3 3H17q-3 0-3-3z"/>'
        .$windows('<rect x="20" y="16" width="20" height="13" rx="2"/><path d="M44 16h20q2 0 3 1.5L76 29H44z"/>')
        .'<path d="M42 32v13" stroke="#0E5C2E" stroke-width="1.5"/>'
        .'<path d="M18 38h76" stroke="#FFFFFF" stroke-width="2.5" opacity=".85"/>'
        .'<rect x="12" y="46" width="88" height="8" rx="3" fill="'.$ink.'"/>'
        .'<rect x="90" y="37" width="6" height="4" rx="1" fill="'.$yellow.'"/>'
        .$wheel(32, 9).$wheel(80, 9)
        .'</g>'];

    $defs['sedan'] = ['vb' => '0 0 120 72', 'body' =>
        $shadow(60, 54)
        .'<path fill="#3A3A3A" stroke="'.$ink.'" stroke-width="2.5" stroke-linejoin="round" d="M8 52V42q0-4 4-5l12-2 12-16q3-4 8-4h26q5 0 8 4l12 15 16 3q5 1 5 6v9q0 3-3 3H11q-3 0-3-3z"/>'
        .$windows('<path d="M40 19q1-2 4-2h12v13H33z"/><path d="M60 17h9q3 0 5 2.5L84 30H60z"/>')
        .'<path d="M58 33v11" stroke="#6A6A6A" stroke-width="1.5"/>'
        .'<path d="M14 39h92" stroke="#8C8C8C" stroke-width="1.5"/>'
        .'<rect x="6" y="46" width="108" height="8" rx="3" fill="'.$ink.'"/>'
        .'<rect x="104" y="40" width="6" height="4" rx="1" fill="'.$yellow.'"/>'
        .'<rect x="9" y="40" width="3" height="6" rx="1" fill="'.$red.'"/>'
        .$wheel(30).$wheel(90)];

    $defs['hatchback'] = ['vb' => '0 0 120 72', 'body' =>
        $shadow(60, 48)
        .'<g transform="translate(2 0)">'
        .'<path fill="#F39800" stroke="'.$ink.'" stroke-width="2.5" stroke-linejoin="round" d="M12 52V38q0-4 4-5l6-1 12-16q3-4 8-4h26q4 0 7 4l11 14 12 3q5 1 5 6v13q0 3-3 3H15q-3 0-3-3z"/>'
        .$windows('<path d="M37 17q2-3 5-3h12v13H28z"/><path d="M58 14h9q3 0 5 2.5L81 27H58z"/>')
        .'<path d="M56 30v14" stroke="#A86500" stroke-width="1.5"/>'
        .'<rect x="10" y="46" width="96" height="8" rx="3" fill="'.$ink.'"/>'
        .'<rect x="96" y="38" width="6" height="4" rx="1" fill="#FFFFFF"/>'
        .$wheel(32).$wheel(84)
        .'</g>'];

    $defs['wagon'] = ['vb' => '0 0 120 72', 'body' =>
        $shadow(61, 54)
        .'<rect x="20" y="14.5" width="50" height="2.5" rx="1" fill="'.$ink.'"/>'
        .'<path fill="#C9CED3" stroke="'.$ink.'" stroke-width="2.5" stroke-linejoin="round" d="M8 52V28q0-6 6-8l6-2h56q5 0 8 4l11 14 14 3q5 1 5 6v7q0 3-3 3H11q-3 0-3-3z"/>'
        .$windows('<rect x="14" y="22" width="18" height="11" rx="2"/><rect x="36" y="22" width="20" height="11" rx="2"/><path d="M60 22h14q3 0 4.5 2L85 33H60z"/>')
        .'<path d="M58 36v9" stroke="#7E858C" stroke-width="1.5"/>'
        .'<rect x="6" y="46" width="110" height="8" rx="3" fill="'.$ink.'"/>'
        .'<rect x="107" y="41" width="6" height="4" rx="1" fill="'.$yellow.'"/>'
        .'<rect x="9" y="33" width="3" height="7" rx="1" fill="'.$red.'"/>'
        .$wheel(28).$wheel(92)];

    $defs['sports'] = ['vb' => '0 0 120 72', 'body' =>
        $shadow(61, 54)
        .'<path d="M7 34h14" stroke="'.$ink.'" stroke-width="3" stroke-linecap="round"/>'
        .'<path fill="#2A2A2A" stroke="'.$ink.'" stroke-width="2.5" stroke-linejoin="round" d="M6 52V44q0-4 4-5l14-3 14-12q4-3 9-3h18q5 0 9 3l14 11 22 4q6 1 6 7v5q0 3-3 3H9q-3 0-3-3z"/>'
        .$windows('<path d="M46 25q1-2 3-2h14q3 0 5 2l9 8H40z"/>')
        .'<path d="M60 23.5v9.5" stroke="#2A2A2A" stroke-width="2"/>'
        .'<path d="M10 43h100" stroke="'.$red.'" stroke-width="3.5"/>'
        .'<rect x="4" y="47" width="114" height="7" rx="3" fill="'.$ink.'"/>'
        .'<rect x="105" y="39.5" width="7" height="3" rx="1" fill="'.$yellow.'"/>'
        .$wheel(30).$wheel(92)];

    $defs['welfare'] = ['vb' => '0 0 120 72', 'body' =>
        $shadow(60, 52)
        .'<path fill="#FFFFFF" stroke="#3A3A3A" stroke-width="2.5" stroke-linejoin="round" d="M8 52V18q0-7 7-7h68q5 0 8 4l14 18q5 1 5 6v13q0 3-3 3H11q-3 0-3-3z"/>'
        .$windows('<rect x="14" y="16" width="22" height="13" rx="2"/><rect x="40" y="16" width="22" height="13" rx="2"/><rect x="66" y="16" width="16" height="13" rx="2"/><path d="M86 16h2q2 0 3 1.5L101 30H86z"/>', '#3A3A3A')
        .'<rect x="9" y="35" width="98" height="3" fill="'.$green.'"/>'
        .'<circle cx="26" cy="43" r="7.5" fill="#0B57B7" stroke="#FFFFFF" stroke-width="1.5"/>'
        .'<g fill="none" stroke="#FFFFFF" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="25.2" cy="38.6" r="1.1" fill="#FFFFFF" stroke="none"/><path d="M24.6 40.5v3.2h3.4l1.4 2.8"/><path d="M22.8 42.2a3.3 3.3 0 1 0 4.6 3.4"/></g>'
        .'<rect x="6" y="46" width="109" height="8" rx="3" fill="#3A3A3A"/>'
        .'<rect x="107" y="37" width="6" height="4" rx="1" fill="'.$yellow.'"/>'
        .$wheel(28).$wheel(92)];

    $defs['truck'] = ['vb' => '0 0 120 72', 'body' =>
        $shadow(60, 54)
        .'<rect x="8" y="29" width="62" height="19" rx="2" fill="#D9D9D9" stroke="'.$ink.'" stroke-width="2.5" stroke-linejoin="round"/>'
        .'<path d="M20 29v19M32 29v19M44 29v19M56 29v19" stroke="#9A9A9A" stroke-width="1.5"/>'
        .'<path fill="#FFFFFF" stroke="'.$ink.'" stroke-width="2.5" stroke-linejoin="round" d="M72 52V19q0-5 5-5h15q4 0 6 3l11 15q5 1 5 6v14q0 3-3 3H75q-3 0-3-3z"/>'
        .$windows('<path d="M78 19h13q2 0 3 1.5L103 31H78z"/>')
        .'<rect x="74" y="36" width="36" height="3" fill="'.$red.'"/>'
        .'<rect x="6" y="46" width="110" height="8" rx="3" fill="'.$ink.'"/>'
        .'<rect x="108" y="40" width="5" height="4" rx="1" fill="'.$yellow.'"/>'
        .$wheel(26).$wheel(92)];

    $defs['other'] = ['vb' => '0 0 120 72', 'body' =>
        $shadow(60, 48)
        .'<path fill="#E6E6E6" stroke="#555555" stroke-width="2.5" stroke-linejoin="round" d="M12 52V38q0-4 4-5l10-2 11-14q3-4 8-4h28q5 0 8 4l11 14 11 2q5 1 5 6v11q0 3-3 3H15q-3 0-3-3z"/>'
        .'<g fill="#FFFFFF" stroke="#555555" stroke-width="1.5" stroke-linejoin="round"><path d="M40 18q1-2 4-2h13v13H32z"/><path d="M61 16h11q3 0 5 2.5L86 29H61z"/></g>'
        .'<rect x="10" y="46" width="98" height="8" rx="3" fill="#555555"/>'
        .'<circle cx="32" cy="54" r="9.5" fill="#555555"/><circle cx="32" cy="54" r="4" fill="#E6E6E6"/>'
        .'<circle cx="86" cy="54" r="9.5" fill="#555555"/><circle cx="86" cy="54" r="4" fill="#E6E6E6"/>'];

    // 入れ子にする（別の定義を、指定した位置と大きさで描く）
    $use = function (string $name, float $x, float $y, float $w, float $h) use (&$defs): string {
        $def = $defs[$name];

        return '<svg x="'.$x.'" y="'.$y.'" width="'.$w.'" height="'.$h.'" viewBox="'.$def['vb'].'">'.$def['body'].'</svg>';
    };

    $defs['all'] = ['vb' => '0 0 120 72', 'body' =>
        $use('minivan', 0, 0, 64, 38).$use('suv', 56, 0, 64, 38).$use('kei', 22, 26, 76, 46)];

    // ================= STEP（80×64） =================
    $defs['coins'] = ['vb' => '0 0 80 64', 'body' =>
        '<g stroke="'.$ink.'" stroke-width="2" stroke-linejoin="round">'
        .'<path fill="'.$yellowDark.'" d="M6 52v5a16 5.5 0 0 0 32 0v-5"/><ellipse cx="22" cy="52" rx="16" ry="5.5" fill="'.$yellow.'"/>'
        .'<path fill="'.$yellowDark.'" d="M6 45v5a16 5.5 0 0 0 32 0v-5"/><ellipse cx="22" cy="45" rx="16" ry="5.5" fill="'.$yellow.'"/>'
        .'<path fill="'.$yellowDark.'" d="M6 38v5a16 5.5 0 0 0 32 0v-5"/><ellipse cx="22" cy="38" rx="16" ry="5.5" fill="'.$yellow.'"/>'
        .'<circle cx="55" cy="25" r="17" fill="'.$yellow.'"/></g>'
        .'<circle cx="55" cy="25" r="12.5" fill="none" stroke="'.$yellowDark.'" stroke-width="2"/>'
        .'<path d="m49 17 6 8 6-8M55 25v9M50 27h10M50 31h10" fill="none" stroke="'.$ink.'" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>'
        .'<path d="M30 8v7M26.5 11.5h7M73 44v5M70.5 46.5h5" stroke="'.$red.'" stroke-width="2.4" stroke-linecap="round"/>'];

    $defs['meter'] = ['vb' => '0 0 80 64', 'body' =>
        '<rect x="8" y="6" width="64" height="54" rx="12" fill="#FFFFFF" stroke="'.$ink.'" stroke-width="2"/>'
        .'<path d="M18 44A22 22 0 0 1 29 24.95" fill="none" stroke="'.$green.'" stroke-width="7"/>'
        .'<path d="M29 24.95A22 22 0 0 1 51 24.95" fill="none" stroke="'.$yellow.'" stroke-width="7"/>'
        .'<path d="M51 24.95A22 22 0 0 1 62 44" fill="none" stroke="'.$red.'" stroke-width="7"/>'
        .'<path d="M40 44 30 31" stroke="'.$ink.'" stroke-width="3.2" stroke-linecap="round"/>'
        .'<circle cx="40" cy="44" r="4.2" fill="'.$ink.'"/>'
        .'<text x="40" y="57" font-size="9" font-weight="700" text-anchor="middle" fill="'.$ink.'" font-family="Oswald,Arial,sans-serif">km</text>'];

    // ================= お約束（96×96） =================
    $defs['p-total'] = ['vb' => '0 0 96 96', 'body' =>
        '<g transform="rotate(-12 48 48)">'
        .'<path d="M14 30h44l18 18-18 18H14a4 4 0 0 1-4-4V34a4 4 0 0 1 4-4z" fill="'.$red.'" stroke="'.$ink.'" stroke-width="2.5" stroke-linejoin="round"/>'
        .'<circle cx="61" cy="48" r="4" fill="#FFFFFF" stroke="'.$ink.'" stroke-width="2"/>'
        .'<text x="33" y="45" font-size="12.5" font-weight="900" fill="#FFFFFF" text-anchor="middle">支払</text>'
        .'<text x="33" y="60" font-size="12.5" font-weight="900" fill="#FFFFFF" text-anchor="middle">総額</text>'
        .'</g>'
        .'<circle cx="74" cy="72" r="13" fill="'.$yellow.'" stroke="'.$ink.'" stroke-width="2.5"/>'
        .'<path d="m68 72 4.5 4.5L80 68" fill="none" stroke="'.$ink.'" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>'];

    $defs['p-frame'] = ['vb' => '0 0 96 96', 'body' =>
        '<path d="M10 62V52q0-4 4-5l9-2 9-13q2-3 6-3h22q3 0 5 3l9 13 6 1q4 1 4 5v11z" fill="#FFFFFF" stroke="'.$ink.'" stroke-width="2.5" stroke-linejoin="round"/>'
        .'<path d="M18 56h58M34 36v20M60 36v20M34 36h26" fill="none" stroke="'.$red.'" stroke-width="3" stroke-dasharray="5 3"/>'
        .'<circle cx="26" cy="64" r="7.5" fill="'.$ink.'"/><circle cx="70" cy="64" r="7.5" fill="'.$ink.'"/>'
        .'<circle cx="64" cy="33" r="13" fill="#FFFFFF" fill-opacity=".8" stroke="'.$ink.'" stroke-width="3"/>'
        .'<path d="m73.5 42.5 10 10" stroke="'.$ink.'" stroke-width="6" stroke-linecap="round"/>'];

    $defs['p-shaken'] = ['vb' => '0 0 96 96', 'body' =>
        '<rect x="16" y="22" width="64" height="58" rx="7" fill="#FFFFFF" stroke="'.$ink.'" stroke-width="2.5"/>'
        .'<path d="M16 29a7 7 0 0 1 7-7h50a7 7 0 0 1 7 7v10H16z" fill="'.$red.'" stroke="'.$ink.'" stroke-width="2.5"/>'
        .'<text x="48" y="35.5" font-size="12" font-weight="900" fill="#FFFFFF" text-anchor="middle">車検</text>'
        .'<path d="M32 15v13M64 15v13" stroke="'.$ink.'" stroke-width="4" stroke-linecap="round"/>'
        .'<g fill="#D9D9D9"><rect x="24" y="46" width="9" height="7" rx="1.5"/><rect x="37" y="46" width="9" height="7" rx="1.5"/><rect x="50" y="46" width="9" height="7" rx="1.5"/><rect x="63" y="46" width="9" height="7" rx="1.5"/><rect x="24" y="58" width="9" height="7" rx="1.5"/><rect x="37" y="58" width="9" height="7" rx="1.5"/><rect x="24" y="69" width="9" height="7" rx="1.5"/></g>'
        .'<circle cx="62" cy="65" r="13" fill="'.$yellow.'" stroke="'.$ink.'" stroke-width="2.5"/>'
        .'<path d="m55.5 65 4.5 4.5 8.5-8.5" fill="none" stroke="'.$ink.'" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>'];

    $defs['p-explain'] = ['vb' => '0 0 96 96', 'body' =>
        '<rect x="12" y="18" width="46" height="62" rx="5" fill="#FFFFFF" stroke="'.$ink.'" stroke-width="2.5"/>'
        .'<rect x="24" y="12" width="22" height="11" rx="3" fill="'.$yellow.'" stroke="'.$ink.'" stroke-width="2.5"/>'
        .'<path d="m20 36 3 3 5-6M20 50l3 3 5-6M20 64l3 3 5-6" fill="none" stroke="'.$red.'" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>'
        .'<path d="M34 37h16M34 51h16M34 65h12" stroke="'.$ink.'" stroke-width="3" stroke-linecap="round"/>'
        .'<path d="M58 44h26a5 5 0 0 1 5 5v14a5 5 0 0 1-5 5H72l-8 7v-7h-6a5 5 0 0 1-5-5V49a5 5 0 0 1 5-5z" fill="'.$red.'" stroke="'.$ink.'" stroke-width="2.5" stroke-linejoin="round"/>'
        .'<circle cx="63" cy="56" r="2.6" fill="#FFFFFF"/><circle cx="71" cy="56" r="2.6" fill="#FFFFFF"/><circle cx="79" cy="56" r="2.6" fill="#FFFFFF"/>'];

    // ================= ご購入の流れ（96×96） =================
    $defs['f-search'] = ['vb' => '0 0 96 96', 'body' =>
        '<rect x="20" y="8" width="40" height="74" rx="7" fill="'.$ink.'"/>'
        .'<rect x="24" y="16" width="32" height="56" rx="2" fill="#E8F4FB"/>'
        .$use('suv', 25, 30, 30, 18)
        .'<rect x="28" y="52" width="24" height="6" rx="2" fill="'.$red.'"/>'
        .'<rect x="28" y="61" width="16" height="4" rx="2" fill="#BDBDBD"/>'
        .'<circle cx="63" cy="58" r="14" fill="#FFFFFF" fill-opacity=".75" stroke="'.$red.'" stroke-width="4.5"/>'
        .'<path d="m73 68 11 11" stroke="'.$red.'" stroke-width="7" stroke-linecap="round"/>'];

    $defs['f-store'] = ['vb' => '0 0 96 96', 'body' =>
        '<rect x="16" y="36" width="64" height="44" fill="#FFFFFF" stroke="'.$ink.'" stroke-width="2.5"/>'
        .'<path d="M11 36 18 22h60l7 14z" fill="'.$red.'" stroke="'.$ink.'" stroke-width="2.5" stroke-linejoin="round"/>'
        .'<path d="M34 23 31 35M48 23v12M62 23l3 12" stroke="#FFFFFF" stroke-width="5"/>'
        .'<rect x="22" y="43" width="24" height="14" rx="1.5" fill="'.$window.'" stroke="'.$ink.'" stroke-width="2"/>'
        .'<rect x="56" y="44" width="17" height="36" fill="#3A3A3A"/>'
        .$use('kei', 2, 50, 62, 37)
        .'<path d="M8 84h80" stroke="'.$ink.'" stroke-width="2.5" stroke-linecap="round"/>'];

    $defs['f-contract'] = ['vb' => '0 0 96 96', 'body' =>
        '<path d="M16 10h36l16 16v60H16z" fill="#FFFFFF" stroke="'.$ink.'" stroke-width="2.5" stroke-linejoin="round"/>'
        .'<path d="M52 10v16h16" fill="#E6E6E6" stroke="'.$ink.'" stroke-width="2.5" stroke-linejoin="round"/>'
        .'<path d="M25 38h34M25 48h34M25 58h22" stroke="#9A9A9A" stroke-width="3" stroke-linecap="round"/>'
        .'<circle cx="52" cy="74" r="9" fill="#FFFFFF" stroke="'.$red.'" stroke-width="3"/>'
        .'<text x="52" y="78.5" font-size="11" font-weight="900" fill="'.$red.'" text-anchor="middle">印</text>'
        .'<g transform="rotate(35 78 52)"><rect x="73" y="24" width="10" height="40" rx="2" fill="'.$yellow.'" stroke="'.$ink.'" stroke-width="2.5"/><path d="M73 64h10l-5 10z" fill="'.$ink.'"/></g>'];

    $defs['f-key'] = ['vb' => '0 0 96 96', 'body' =>
        '<circle cx="32" cy="44" r="18" fill="'.$yellow.'" stroke="'.$ink.'" stroke-width="2.5"/>'
        .'<circle cx="32" cy="44" r="6" fill="#FFFFFF" stroke="'.$ink.'" stroke-width="2.5"/>'
        .'<path d="m45 57 30 25M64 73l-6 7M72 80l-6 7" stroke="'.$ink.'" stroke-width="6" stroke-linecap="round"/>'
        .'<path d="M62 24c-8-10-20-8-16 2 3 6 10 2 16-2zM62 24c8-10 20-8 16 2-3 6-10 2-16-2z" fill="'.$red.'" stroke="'.$ink.'" stroke-width="2" stroke-linejoin="round"/>'
        .'<path d="m60 27-6 13M64 27l6 13" stroke="'.$red.'" stroke-width="4" stroke-linecap="round"/>'
        .'<circle cx="62" cy="24.5" r="3.6" fill="'.$redDark.'" stroke="'.$ink.'" stroke-width="1.5"/>'
        .'<path d="M12 12v8M8 16h8M84 42v6M81 45h6" stroke="'.$red.'" stroke-width="2.6" stroke-linecap="round"/>'];

    // ================= そのほか（96×96） =================
    $defs['buy'] = ['vb' => '0 0 96 96', 'body' =>
        $use('suv', 2, 42, 78, 47)
        .'<g transform="rotate(14 66 30)">'
        .'<path d="M50 16h24l13 14-13 14H50a3 3 0 0 1-3-3V19a3 3 0 0 1 3-3z" fill="'.$yellow.'" stroke="'.$ink.'" stroke-width="2.5" stroke-linejoin="round"/>'
        .'<circle cx="76" cy="30" r="3" fill="#FFFFFF" stroke="'.$ink.'" stroke-width="2"/>'
        .'<path d="m54 22 6 7 6-7M60 29v9M55.5 31h9M55.5 35h9" fill="none" stroke="'.$ink.'" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>'
        .'</g>'
        .'<path d="M18 14v8M14 18h8" stroke="'.$red.'" stroke-width="2.6" stroke-linecap="round"/>'];

    $defs['loan'] = ['vb' => '0 0 96 96', 'body' =>
        '<rect x="12" y="10" width="44" height="66" rx="7" fill="'.$ink.'"/>'
        .'<rect x="18" y="17" width="32" height="14" rx="2" fill="'.$window.'"/>'
        .'<text x="46" y="28.5" font-size="10" font-weight="700" text-anchor="end" fill="'.$ink.'" font-family="Oswald,Arial,sans-serif">60</text>'
        .'<g fill="#FFFFFF"><circle cx="23" cy="41" r="4"/><circle cx="34" cy="41" r="4"/><circle cx="23" cy="52" r="4"/><circle cx="34" cy="52" r="4"/><circle cx="23" cy="63" r="4"/><circle cx="34" cy="63" r="4"/></g>'
        .'<rect x="41" y="37" width="9" height="30" rx="4" fill="'.$red.'"/>'
        .'<g stroke="'.$ink.'" stroke-width="2" stroke-linejoin="round">'
        .'<path fill="'.$yellowDark.'" d="M52 76v5a15 5 0 0 0 30 0v-5"/><ellipse cx="67" cy="76" rx="15" ry="5" fill="'.$yellow.'"/>'
        .'<path fill="'.$yellowDark.'" d="M52 69v5a15 5 0 0 0 30 0v-5"/><ellipse cx="67" cy="69" rx="15" ry="5" fill="'.$yellow.'"/>'
        .'<path fill="'.$yellowDark.'" d="M52 62v5a15 5 0 0 0 30 0v-5"/><ellipse cx="67" cy="62" rx="15" ry="5" fill="'.$yellow.'"/>'
        .'</g>'
        .'<path d="M80 22v8M76 26h8" stroke="'.$red.'" stroke-width="2.6" stroke-linecap="round"/>'];

    $defs['service'] = ['vb' => '0 0 96 96', 'body' =>
        $use('sedan', 4, 44, 76, 46)
        .'<circle cx="62" cy="30" r="22" fill="'.$yellow.'" stroke="'.$ink.'" stroke-width="2.5"/>'
        .'<path d="M66.5 20.5a1.5 1.5 0 0 0 0 2l2.4 2.4a1.5 1.5 0 0 0 2 0l5.2-5.2a9 9 0 0 1-11.9 11.9L53.6 42a3.2 3.2 0 0 1-4.5-4.5l10.4-10.4a9 9 0 0 1 11.9-11.9z" fill="#FFFFFF" stroke="'.$ink.'" stroke-width="2.5" stroke-linejoin="round"/>'];

    $defs['empty'] = ['vb' => '0 0 96 96', 'body' =>
        '<path d="M8 84h80" stroke="#D9D9D9" stroke-width="3" stroke-linecap="round" stroke-dasharray="8 6"/>'
        .'<circle cx="42" cy="42" r="27" fill="#FFFFFF" stroke="'.$red.'" stroke-width="6"/>'
        .$use('other', 21, 29, 42, 25)
        .'<path d="m62 62 18 18" stroke="'.$red.'" stroke-width="9" stroke-linecap="round"/>'
        .'<circle cx="74" cy="18" r="9" fill="'.$yellow.'" stroke="'.$ink.'" stroke-width="2"/>'
        .'<path d="M71 15.5a3 3 0 1 1 4 2.8c-.8.4-1 .9-1 1.7M74 23h.01" fill="none" stroke="'.$ink.'" stroke-width="2.2" stroke-linecap="round"/>'];

    $defs['mail-check'] = ['vb' => '0 0 96 96', 'body' =>
        '<rect x="10" y="24" width="64" height="46" rx="5" fill="#FFFFFF" stroke="'.$ink.'" stroke-width="2.5"/>'
        .'<path d="m12 27 30 22 30-22" fill="none" stroke="'.$red.'" stroke-width="3.5" stroke-linejoin="round"/>'
        .'<path d="M16 62h20" stroke="#D9D9D9" stroke-width="3" stroke-linecap="round"/>'
        .'<circle cx="70" cy="66" r="16" fill="'.$green.'" stroke="'.$ink.'" stroke-width="2.5"/>'
        .'<path d="m62 66 5.5 5.5L78.5 60" fill="none" stroke="#FFFFFF" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>'
        .'<path d="M84 22v8M80 26h8M12 12v6M9 15h6" stroke="'.$red.'" stroke-width="2.6" stroke-linecap="round"/>'];

    $key = isset($defs[$name]) ? $name : 'other';
    $def = $defs[$key];
    [, , $vbW, $vbH] = array_map('floatval', explode(' ', $def['vb']));
    $w = $width ?? $vbW;
    $h = $height ?? ($width !== null ? round($width * $vbH / $vbW, 1) : $vbH);
@endphp
<svg {{ $attributes->merge(['class' => 'c-illust c-illust--'.$key]) }} viewBox="{{ $def['vb'] }}" width="{{ $w }}" height="{{ $h }}" @if (filled($label)) role="img" aria-label="{{ $label }}" @else aria-hidden="true" @endif focusable="false">{!! $def['body'] !!}</svg>
