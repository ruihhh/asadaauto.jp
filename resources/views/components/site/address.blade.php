@props([
    'postal' => false,
])
{{--
    店舗の住所（config('shop.address')）。狭い幅で折り返すときも「兵庫県尼崎市」「下坂部4丁目5-1」の区切りでだけ改行する
    （「4丁目5-」「1」のような途中の改行を防ぐ）。文字の大きさ・色は置いた場所のものを引き継ぐ。

    使い方:
        <x-site.address />            … 兵庫県尼崎市下坂部4丁目5-1
        <x-site.address postal />     … 〒661-0975 兵庫県尼崎市下坂部4丁目5-1

    postal: 先頭に郵便番号（〒◯◯◯-◯◯◯◯）を付けるか

    出力: [<span class="u-nowrap">〒661-0975</span> ]<span class="u-nowrap">兵庫県尼崎市</span><span class="u-nowrap">下坂部4丁目5-1</span>
--}}
@php
    $address = (string) config('shop.address');
    $head = (string) config('shop.pref').(string) config('shop.city');
    $tail = $head !== '' && str_starts_with($address, $head) ? substr($address, strlen($head)) : null;
@endphp
@if ($postal && filled(config('shop.postal')))
    <span class="u-nowrap">〒{{ config('shop.postal') }}</span>
@endif
@if ($tail !== null)
    <span class="u-nowrap">{{ $head }}</span><span class="u-nowrap">{{ $tail }}</span>
@else
    {{ $address }}
@endif
