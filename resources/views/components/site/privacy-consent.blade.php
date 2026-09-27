@props([
    'id' => 'privacy-consent',
])
{{--
    個人情報の取り扱いへの同意。送信ボタンの直前に置く。name を持たない required のチェックボックス（サーバーには送らない）。
    ・チェックの押せる範囲（ラベル）とリンクを分ける（文中のリンクとチェックの押し間違いを防ぎ、リンクの押せる高さを 44px にする）。
    ・リンクは入力中の内容を失わないよう新しいタブで開く。
    ・入力エラーで戻ったとき（$errors がある＝同意して送信した後）は、チェックを入れた状態で表示する
      （name がないため old() では戻せない。required なので、チェックなしではブラウザが送信を止める）。

    使い方:
        <x-site.privacy-consent />
        <x-site.privacy-consent id="buy-privacy-consent" />   … 1ページに2つ置くときは id を変える

    出力: <div class="c-consent"><label class="c-consent__label" for="privacy-consent">
          <input type="checkbox" class="c-consent__input" required><span class="c-consent__text">個人情報の取り扱いに同意します</span>
          <span class="c-badge-req">必須</span></label>
          <p class="c-consent__link"><a class="c-link c-link--block">個人情報の取り扱いを読む（新しいタブで開きます）</a></p></div>
--}}
@php
    $bag = $errors ?? null;
    $checked = $bag !== null && $bag->any();
@endphp
<div {{ $attributes->merge(['class' => 'c-consent']) }}>
    <label class="c-consent__label" for="{{ $id }}">
        <input type="checkbox" id="{{ $id }}" class="c-consent__input" required @checked($checked)>
        <span class="c-consent__text">個人情報の取り扱いに同意します</span>
        <span class="c-badge-req">必須</span>
    </label>
    <p class="c-consent__link">
        <a class="c-link c-link--block" href="{{ route('privacy') }}" target="_blank" rel="noopener"><span>個人情報の取り扱いを読む<span class="u-nowrap">（新しいタブで開きます）</span></span></a>
        <span class="c-consent__note">入力中の内容は消えません。</span>
    </p>
</div>
