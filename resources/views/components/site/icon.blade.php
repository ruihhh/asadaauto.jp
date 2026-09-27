@props([
    'name',
    'size' => 20,
])
{{--
    線画の SVG アイコン（案B の線画。stroke 2px・丸い線端・viewBox 24×24・色は currentColor）。常に aria-hidden。意味は隣の文字で伝える。

    使い方:
        <x-site.icon name="phone" />
        <x-site.icon name="chevron-right" class="c-btn2__arrow" />   … class などの属性はそのまま付く（x-site.btn2 の右の矢印）

    name（UI）: phone, line, mail, map-pin, clock, calendar, cal-check, car, heart, heart-fill, compare, search, menu, close,
                check, alert, info, chevron-right, chevron-left, chevron-down, chevron-up, camera, zoom, parking, train, bus,
                shield, file, wrench, yen, calc, external, user, store, route, star, meter, frame, gear, fuel, drop,
                bag, family, mountain, leaf, tag, bankin, wakaba（初心者マーク。色付き）
    name（装備）: eq-brake, eq-360, eq-camera, eq-navi, eq-etc, eq-dashcam, eq-seat, eq-slide, eq-cruise, eq-key,
                  eq-3row, eq-aircon, eq-lane, eq-sunroof, eq-wheel, eq-check（装備名からは CarText::equipmentIcon() で選ぶ）
    size: 幅・高さ（px）。既定 20。c-btn の中では CSS で 20px にそろう
    知らない name は info を出す。
--}}
@php
    $paths = [
        // ---- 連絡・操作 ----
        'phone' => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/>',
        'line' => '<path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8v.5z"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.5 6.5 8.5 6.5 8.5-6.5"/>',
        'map-pin' => '<path d="M12 22s7-6.1 7-12a7 7 0 0 0-14 0c0 5.9 7 12 7 12z"/><circle cx="12" cy="10" r="2.5"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
        'cal-check' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4M9 15.5l2 2 4-4"/>',
        'car' => '<path d="M5 17H3.5a.5.5 0 0 1-.5-.5V12l2.3-5a2 2 0 0 1 1.8-1.2h9.8a2 2 0 0 1 1.8 1.2L21 12v4.5a.5.5 0 0 1-.5.5H19M9 17h6M3 12h18"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/>',
        'heart' => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21.2l7.8-7.8 1-1a5.5 5.5 0 0 0 0-7.8z"/>',
        'heart-fill' => '<path fill="currentColor" d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1.1L12 21.2l7.8-7.8 1-1a5.5 5.5 0 0 0 0-7.8z"/>',
        'compare' => '<rect x="3" y="4" width="7" height="16" rx="1.5"/><rect x="14" y="4" width="7" height="16" rx="1.5"/><path d="M6.5 8.5v1M17.5 8.5v1"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="M16.5 16.5 21 21"/>',
        'zoom' => '<circle cx="11" cy="11" r="7"/><path d="M16.5 16.5 21 21M11 8v6M8 11h6"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'close' => '<path d="M18 6 6 18M6 6l12 12"/>',
        'check' => '<path d="M20 6 9 17l-5-5"/>',
        'alert' => '<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/>',
        'info' => '<circle cx="12" cy="12" r="9.5"/><path d="M12 16.5v-5M12 8h.01"/>',
        'chevron-right' => '<path d="m9 6 6 6-6 6"/>',
        // 旧版と同じ座標（既存テストがこの形を確かめる）
        'chevron-left' => '<polyline points="15 18 9 12 15 6"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'chevron-up' => '<path d="m6 15 6-6 6 6"/>',
        'camera' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8.5 7 10 4.5h4L15.5 7"/><circle cx="12" cy="13.5" r="3.5"/>',
        'external' => '<path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><path d="M15 3h6v6M10 14 21 3"/>',
        'user' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        'star' => '<path fill="currentColor" stroke="none" d="m12 2.5 2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17.4l-5.9 3.1 1.2-6.5-4.8-4.6 6.6-.9z"/>',
        'wakaba' => '<path fill="#FFD60A" stroke="#FFFFFF" stroke-width="1" d="M3.5 2.5 12 7v14.5l-8.5-5z"/><path fill="#1B9A4B" stroke="#FFFFFF" stroke-width="1" d="M20.5 2.5 12 7v14.5l8.5-5z"/>',

        // ---- お店・案内 ----
        'store' => '<path d="M3.5 9 5 4h14l1.5 5M4.5 10.5V20h15v-9.5"/><path d="M3.5 9a2.8 2.8 0 0 0 5.7 0 2.8 2.8 0 0 0 5.6 0 2.8 2.8 0 0 0 5.7 0"/><path d="M10 20v-5h4v5"/>',
        'route' => '<circle cx="6" cy="19" r="2"/><circle cx="18" cy="5" r="2"/><path d="M8 19h8.5a3.5 3.5 0 0 0 0-7h-9a3.5 3.5 0 0 1 0-7H16"/>',
        'parking' => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 17V7h4a3 3 0 0 1 0 6H9"/>',
        'train' => '<rect x="5" y="3" width="14" height="14" rx="3"/><path d="M5 10h14M9 13.5h.01M15 13.5h.01M8 21l2-4M16 21l-2-4"/>',
        'bus' => '<rect x="4" y="3" width="16" height="15" rx="2"/><path d="M4 11h16M8 14.5h.01M16 14.5h.01M7 18v3M17 18v3"/>',
        'shield' => '<path d="M12 3 20 6v6c0 4.5-3.4 8.3-8 9-4.6-.7-8-4.5-8-9V6z"/><path d="m8.5 12 2.5 2.5 4.5-5"/>',
        'file' => '<path d="M14 3H6.5A1.5 1.5 0 0 0 5 4.5v15A1.5 1.5 0 0 0 6.5 21h11a1.5 1.5 0 0 0 1.5-1.5V8z"/><path d="M14 3v5h5M8.5 13h7M8.5 17h5"/>',
        'wrench' => '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.8-3.8a6 6 0 0 1-7.9 7.9l-6.9 6.9a2.1 2.1 0 0 1-3-3l6.9-6.9a6 6 0 0 1 7.9-7.9z"/>',
        'yen' => '<circle cx="12" cy="12" r="9.5"/><path d="m8 6.5 4 5.5 4-5.5M12 12v6M8.5 13h7M8.5 16h7"/>',
        'calc' => '<rect x="5" y="2.5" width="14" height="19" rx="2"/><rect x="8" y="5.5" width="8" height="4" rx="1"/><path d="M8.5 13h.01M12 13h.01M15.5 13h.01M8.5 16.5h.01M12 16.5h.01M15.5 16.5h.01" stroke-width="2.6"/>',
        'tag' => '<path d="M3 12V4a1 1 0 0 1 1-1h8l9 9-9 9z"/><circle cx="7.5" cy="7.5" r="1.5"/>',
        'bankin' => '<path d="M3 18v-3.5l1.8-4A1.8 1.8 0 0 1 6.5 9.5h7a1.8 1.8 0 0 1 1.7 1l1.8 4V18zM3 14.5h14"/><circle cx="6.5" cy="18" r="1.6"/><circle cx="13.5" cy="18" r="1.6"/><path d="M19.5 3v4M17.5 5h4M21 9.5v2.5M19.8 10.8h2.4"/>',

        // ---- 車の状態 ----
        'meter' => '<path d="M3.5 17.5a9 9 0 1 1 17 0"/><path d="m12 15.5 4-5"/><circle cx="12" cy="15.5" r="1.4"/><path d="m6.6 10.6.9.7M12 6.3v1.2M17.4 10.6l-.9.7"/>',
        'frame' => '<path d="M2.5 16.5v-3.5l1.8-4A1.8 1.8 0 0 1 6 8h5.5"/><path d="M2.5 16.5h8"/><circle cx="6" cy="17" r="1.8"/><circle cx="16.5" cy="11" r="4.5"/><path d="m19.8 14.3 2.7 2.7"/>',
        'gear' => '<circle cx="6" cy="5" r="1.7"/><circle cx="12" cy="5" r="1.7"/><circle cx="18" cy="5" r="1.7"/><circle cx="6" cy="19" r="1.7"/><circle cx="12" cy="19" r="1.7"/><path d="M6 6.7v10.6M12 6.7v10.6M18 6.7V12H6"/>',
        'fuel' => '<path d="M4.5 21V5a2 2 0 0 1 2-2h5.5a2 2 0 0 1 2 2v16M3 21h12.5M4.5 10H14"/><path d="M14 8h2a2 2 0 0 1 2 2v6a1.5 1.5 0 0 0 3 0V8.5l-3-3"/>',
        'drop' => '<path d="M12 3s6 6.5 6 11a6 6 0 0 1-12 0c0-4.5 6-11 6-11z"/>',

        // ---- 使い方・特集 ----
        'bag' => '<path d="M5.5 8h13l1 13h-15z"/><path d="M9 10V7a3 3 0 0 1 6 0v3"/>',
        'family' => '<circle cx="8" cy="5.5" r="2.5"/><circle cx="17" cy="9" r="2"/><path d="M3.5 21v-6a4.5 4.5 0 0 1 9 0v6M13.5 21v-4a3.5 3.5 0 0 1 7 0v4"/>',
        'mountain' => '<path d="M2 20 9 9l4 6 3-4 6 9z"/><circle cx="17.5" cy="5" r="2"/>',
        'leaf' => '<path d="M5 19c0-8.3 6-14 15-14 0 9-5.8 15-14 15z"/><path d="m5 19 7-7"/>',

        // ---- 装備 ----
        'eq-brake' => '<path d="M2.5 16.5V13l1.6-3.3A1.5 1.5 0 0 1 5.5 9h5a1.5 1.5 0 0 1 1.4.7L13.5 13v3.5zM2.5 13h11"/><circle cx="5.5" cy="17" r="1.5"/><circle cx="10.5" cy="17" r="1.5"/><path d="M16.5 9.5a4.5 4.5 0 0 1 0 6M19.5 7a8 8 0 0 1 0 11"/>',
        'eq-360' => '<rect x="9" y="7" width="6" height="10" rx="2"/><path d="M12 2.8A9.2 9.2 0 1 1 5.2 5.8"/><path d="M5 2.5V6h3.5"/>',
        'eq-camera' => '<rect x="2.5" y="4" width="19" height="13" rx="2"/><path d="m8 14 2-6h4l2 6M7 14h10M9 21h6M12 17v4"/>',
        'eq-navi' => '<rect x="2" y="4" width="20" height="13" rx="2"/><path d="M12 14.5s3-2.4 3-4.7a3 3 0 0 0-6 0c0 2.3 3 4.7 3 4.7zM8 21h8M12 17v4"/>',
        'eq-etc' => '<rect x="2.5" y="6" width="19" height="12" rx="2"/><text x="12" y="14.8" font-size="6.6" font-weight="900" text-anchor="middle" fill="currentColor" stroke="none" font-family="Arial,Helvetica,sans-serif">ETC</text>',
        'eq-dashcam' => '<rect x="2.5" y="7" width="13" height="10" rx="2"/><circle cx="9" cy="12" r="2.6"/><path d="m15.5 10.5 6-3v9l-6-3"/>',
        'eq-seat' => '<path d="M5 4.5c0-1 .8-1.7 1.8-1.7S8.6 3.5 8.6 4.5V13H15a2 2 0 0 1 2 2v2.5H8a3 3 0 0 1-3-3z"/><path d="M8 17.5V21M15 17.5V21"/><path d="M12.5 2.5c-.9 1 .9 2 0 3M16 2.5c-.9 1 .9 2 0 3M19.5 2.5c-.9 1 .9 2 0 3"/>',
        'eq-slide' => '<path d="M2.5 17V8a2 2 0 0 1 2-2h11l5 5.5V17z"/><path d="M9 6v11M11.5 11.5h4.5M14.3 9.5l2 2-2 2"/><circle cx="6.5" cy="18" r="1.7"/><circle cx="17" cy="18" r="1.7"/>',
        'eq-cruise' => '<path d="M3.5 17a8.5 8.5 0 1 1 17 0"/><path d="m12 15 3.5-4.5"/><circle cx="12" cy="15" r="1.4"/><path d="M8 20.5h8"/>',
        'eq-key' => '<rect x="7" y="2.5" width="10" height="15" rx="5"/><circle cx="12" cy="7.5" r="1.6"/><path d="M10 12h4M12 17.5V21.5"/>',
        'eq-3row' => '<path d="M3.5 18.5v-9a1.5 1.5 0 0 1 3 0v6.5h2M10 18.5v-9a1.5 1.5 0 0 1 3 0v6.5h2M16.5 18.5v-9a1.5 1.5 0 0 1 3 0v6.5h2"/>',
        'eq-aircon' => '<path d="M12 3v18M4.2 7.5l15.6 9M4.2 16.5l15.6-9"/><path d="m9.5 4.5 2.5 2 2.5-2M9.5 19.5l2.5-2 2.5 2"/>',
        'eq-lane' => '<path d="M5.5 21 9 3M18.5 21 15 3"/><path d="M12 4.5v2M12 10.5v2.5M12 17v2.5"/>',
        'eq-sunroof' => '<rect x="6" y="2.5" width="12" height="19" rx="4"/><rect x="8.5" y="8" width="7" height="6" rx="1"/>',
        'eq-wheel' => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3"/><path d="M12 3v6M12 15v6M3 12h6M15 12h6"/>',
        'eq-check' => '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
    ];
@endphp
<svg {{ $attributes->merge(['class' => 'c-icon c-icon--'.$name]) }} width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">{!! $paths[$name] ?? $paths['info'] !!}</svg>
