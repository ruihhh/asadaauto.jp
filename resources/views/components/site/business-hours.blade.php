@props([
    'now' => null,
    'caption' => '営業時間・定休日',
    'notes' => true,
    'upcoming' => 1,
])
{{--
    営業時間表（月曜〜日曜の7行）。今日の行に「今日」、定休日は橙の太字で「定休日」。スマホでも7列のタイルにしない。

    使い方:
        <x-site.business-hours />
        <x-site.business-hours :notes="false" />   … 表の下の「次の第3日曜の定休日」「臨時休業」を出さない
        <x-site.business-hours :upcoming="3" />     … 次の第3日曜の定休日を3回分出す（「10月18日（日）、11月15日（日）、12月20日（日）」）

    now:     判定に使う日時（省略時は現在時刻。Asia/Tokyo で判定）
    caption: 表の caption（読み上げ用。画面には出さない）
    notes:   表の下に、次の第n曜日の定休日と config('shop.holidays') の臨時休業を出すか
    upcoming: 次の第n曜日の定休日を何回分出すか（既定 1）

    出力: <div class="c-hours"><table class="c-hours__table">…<tr class="c-hours__row is-today">…</table><div class="c-hours__notes">…</div></div>
--}}
@php
    $today = \Carbon\CarbonImmutable::instance($now ?? now())->setTimezone(\App\Support\BusinessHours::TIMEZONE);
    $rows = \App\Support\BusinessHours::week($today);
    $todayReason = \App\Support\BusinessHours::closedReason($today);
    $nthRules = (array) config('shop.closed_nth_weekdays', []);
    $weekdayShort = ['日', '月', '火', '水', '木', '金', '土'];
    // 今日が第3日曜のときは表の日曜の行に「本日は定休日です」と出るので、「次の第3日曜」は明日以降から探す
    $nextNths = $notes && $nthRules !== [] ? \App\Support\BusinessHours::upcomingNthClosed(max(1, (int) $upcoming), $today->addDay()) : [];
    $nthLabel = $nthRules !== [] ? '第'.(int) $nthRules[0][0].$weekdayShort[(int) $nthRules[0][1]].'曜' : null;
    $holidays = $notes ? \App\Support\BusinessHours::upcomingHolidays($today) : [];
@endphp
<div {{ $attributes->merge(['class' => 'c-hours']) }}>
    <table class="c-hours__table">
        <caption class="u-visually-hidden">{{ $caption }}</caption>
        <tbody>
            @foreach ($rows as $row)
                <tr class="c-hours__row{{ $row['today'] ? ' is-today' : '' }}" @if ($row['today']) aria-current="date" @endif>
                    <th scope="row" class="c-hours__day">
                        {{ $row['label'] }}@if ($row['today'])<span class="c-hours__today">今日</span>@endif
                    </th>
                    <td class="c-hours__time">
                        @if ($row['closed'])
                            <span class="c-hours__closed">定休日</span>
                        @else
                            {{-- 「（第3日曜は定休）」はひとかたまりにして、狭い画面でも「定／休）」のように語の途中で改行しない --}}
                            {{ $row['hours'] }}@if ($row['note'])<span class="u-nowrap">（{{ $row['note'] }}）</span>@endif
                            @if ($row['today_closed'])
                                <span class="c-hours__closed">本日は{{ $todayReason === 'holiday' ? '臨時休業' : '定休日' }}です</span>
                            @endif
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @if ($nextNths !== [] || $holidays !== [])
        <div class="c-hours__notes">
            @if ($nextNths !== [])
                <p><span class="c-hours__notes-label">次の{{ $nthLabel }}の定休日：</span>{{ collect($nextNths)->map(fn ($date) => \App\Support\BusinessHours::dateLabel($date))->implode('、') }}</p>
            @endif
            @foreach ($holidays as $holiday)
                <p><span class="c-hours__notes-label">臨時休業：</span>{{ $holiday['label'] }}@if ($holiday['reason'] !== '')（{{ $holiday['reason'] }}）@endif</p>
            @endforeach
        </div>
    @endif
</div>
