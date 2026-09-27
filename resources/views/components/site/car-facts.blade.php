@props([
    'car',
    'variant' => 'card',
    'except' => [],
])
{{--
    車両の状態表（項目名：値。案B：アイコン付き）。値はすべて App\Support\CarText で作り、値がない項目は「お問い合わせください」と出す。

    使い方:
        <x-site.car-facts :car="$car" />                     … card：年式・走行距離・車検・修復歴の2×2の枠＋ミッション・燃料の小さな枠
        <x-site.car-facts :car="$car" variant="detail" />    … detail：詳細ページの「主な仕様」（アイコンの丸つきのタイル。スマホ2列・600px以上3列・960px以上4列）
        <x-site.car-facts :car="$car" variant="detail" :except="['stock_no']" />

    car:     App\Models\Car
    variant: card | detail
    except:  出さない項目のキー（year, mileage, inspection, repair, record, transmission, fuel, body_type, color, location, published, stock_no）

    card の値：年式「2023」（数字の書体）＋小さく「（R5）年式」／走行距離「1.1」（数字の書体）＋「万km」／車検「要確認」（琥珀）・「2027年3月まで」（緑）／
               修復歴「✓ なし」（緑）・「あり」（橙＋部位はお問い合わせください）／ミッション・燃料は小さな枠（項目名は読み上げ用）
    detail の行：年式（和暦）・走行距離・修復歴（＋定義。2枠分）・車検・点検記録簿（ありの場合だけ）・ミッション・燃料・ボディタイプ・
                 ボディカラー・展示場所（2枠分）・掲載日・在庫番号

    出力: <dl class="c-spec c-spec--{variant}"><div class="c-spec__row c-spec__row--{key} [c-spec__row--mini|--wide]">
          <dt class="c-spec__label">（card：<svg class="c-icon c-spec__icon">／detail：<span class="c-spec__icon [c-spec__icon--ok|--check|--caution]"><svg></span>）年式</dt>
          <dd class="c-spec__value [c-spec__value--ok|--check|--caution]">…<span class="c-spec__note">…</span></dd></div>…</dl>
--}}
@php
    use App\Support\CarText;

    $detail = $variant === 'detail';
    $repair = CarText::repair($car);
    $inspection = CarText::inspection($car);
    // 車検：期限あり・付き＝緑、要確認・残りあり＝琥珀、なし・期限切れ＝橙
    $inspectionTone = match (true) {
        $inspection['tone'] === 'ok' => 'ok',
        $inspection['tone'] === 'caution' => 'caution',
        default => 'check',
    };
    $yearParts = CarText::yearParts($car->model_year !== null ? (int) $car->model_year : null);
    $mileageParts = CarText::mileageParts($car->mileage !== null ? (int) $car->mileage : null);
    $mileageHtml = $mileageParts !== null
        ? new \Illuminate\Support\HtmlString('<span class="c-spec__num">'.e($mileageParts['value']).'</span><span class="c-spec__unit">'.e($mileageParts['unit']).'</span>')
        : CarText::UNKNOWN;

    $rows = [];
    if ($detail) {
        $rows['year'] = ['label' => '年式', 'icon' => 'calendar', 'value' => CarText::year($car->model_year !== null ? (int) $car->model_year : null, true)];
    } else {
        // 「2023（R5）年式」の西暦だけを大きな数字にする（つなげて読むと CarText::year() と同じ文字）
        $rows['year'] = ['label' => '年式', 'icon' => 'calendar', 'value' => $yearParts['rest'] !== null
            ? new \Illuminate\Support\HtmlString('<span class="c-spec__num">'.e($yearParts['value']).'</span><span class="c-spec__sub">'.e($yearParts['rest']).'</span>')
            : $yearParts['value']];
    }
    $rows['mileage'] = ['label' => '走行距離', 'icon' => 'meter', 'value' => $mileageHtml];

    if ($detail) {
        $repairNotes = array_values(array_filter([$repair['note'], CarText::REPAIR_DEFINITION]));
        $rows['repair'] = ['label' => '修復歴', 'icon' => 'frame', 'value' => $repair['label'], 'tone' => $repair['tone'], 'notes' => $repairNotes, 'wide' => true];
        $rows['inspection'] = ['label' => '車検', 'icon' => 'cal-check', 'value' => $inspection['label'], 'tone' => $inspectionTone];
        if ($car->has_service_record) {
            $rows['record'] = ['label' => '点検記録簿', 'icon' => 'file', 'value' => 'あり'];
        }
    } else {
        $rows['inspection'] = ['label' => '車検', 'icon' => 'cal-check', 'value' => $inspection['short'], 'tone' => $inspectionTone];
        $rows['repair'] = ['label' => '修復歴', 'icon' => 'frame', 'value' => $repair['label'], 'tone' => $repair['tone'], 'notes' => array_filter([$repair['note']]), 'mark' => $repair['tone'] === 'ok'];
    }

    $rows['transmission'] = ['label' => 'ミッション', 'icon' => 'gear', 'value' => CarText::transmission($car->transmission), 'mini' => ! $detail];
    $rows['fuel'] = ['label' => '燃料', 'icon' => 'fuel', 'value' => CarText::fuel($car->fuel_type), 'mini' => ! $detail];

    if ($detail) {
        $rows['body_type'] = ['label' => 'ボディタイプ', 'icon' => 'car', 'value' => CarText::bodyType($car->body_type)];
        $rows['color'] = ['label' => 'ボディカラー', 'icon' => 'drop', 'value' => filled($car->color) ? $car->color : CarText::UNKNOWN];
        $rows['location'] = ['label' => '展示場所', 'icon' => 'map-pin', 'value' => CarText::location($car), 'tone' => CarText::atShop($car) ? null : 'caution', 'wide' => true];
        $rows['published'] = ['label' => '掲載日', 'icon' => 'clock', 'value' => CarText::date($car->published_at ?? $car->created_at) ?? CarText::UNKNOWN];
        $rows['stock_no'] = ['label' => '在庫番号', 'icon' => 'tag', 'value' => filled($car->stock_no) ? $car->stock_no : CarText::UNKNOWN];
    }

    $rows = array_diff_key($rows, array_flip((array) $except));

    $isLong = fn ($value): bool => is_string($value) && mb_strlen($value) > 7;
@endphp
<dl {{ $attributes->merge(['class' => 'c-spec c-spec--'.$variant]) }}>
    @foreach ($rows as $key => $row)
        @php
            $tone = $row['tone'] ?? null;
            $rowClass = 'c-spec__row c-spec__row--'.$key.(! empty($row['mini']) ? ' c-spec__row--mini' : '').(! empty($row['wide']) ? ' c-spec__row--wide' : '');
            $valueClass = 'c-spec__value'.(in_array($tone, ['ok', 'check', 'caution'], true) ? ' c-spec__value--'.$tone : '')
                .(! $detail && empty($row['mini']) && $isLong($row['value']) ? ' c-spec__value--long' : '');
        @endphp
        <div class="{{ $rowClass }}">
            @if ($detail)
                <dt class="c-spec__label"><span class="c-spec__icon{{ in_array($tone, ['ok', 'check', 'caution'], true) ? ' c-spec__icon--'.$tone : '' }}" aria-hidden="true"><x-site.icon :name="$row['icon']" /></span>{{ $row['label'] }}</dt>
            @elseif (! empty($row['mini']))
                <dt class="c-spec__label"><x-site.icon :name="$row['icon']" class="c-spec__icon" /><span class="u-visually-hidden">{{ $row['label'] }}</span></dt>
            @else
                <dt class="c-spec__label"><x-site.icon :name="$row['icon']" class="c-spec__icon" />{{ $row['label'] }}</dt>
            @endif
            <dd class="{{ $valueClass }}">@if (! empty($row['mark']))<x-site.icon name="check" class="c-spec__mark" />@endif{{ $row['value'] }}@if (filled($row['sub'] ?? null))<span class="c-spec__sub">{{ $row['sub'] }}</span>@endif
                @foreach ($row['notes'] ?? [] as $noteText)
                    <span class="c-spec__note">{{ $noteText }}</span>
                @endforeach
            </dd>
        </div>
    @endforeach
</dl>
