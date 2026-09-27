@props([
    'name',
    'idPrefix' => '',
    'bag' => null,
])
{{--
    入力欄の直下に出すエラー文（c-field__error。警告アイコン付き）。エラーがないときは何も出さない。
    欄には aria-invalid と aria-describedby を付けて、このエラー文と結び付ける（id は「接頭辞＋name＋-error」）。

    使い方:
        <div class="c-field">
            <label class="c-field__label" for="email">メールアドレス<span class="c-badge-req">必須</span></label>
            <p class="c-field__hint" id="email-hint">例）taro@example.com</p>
            <input class="c-field__input" id="email" name="email" type="email" autocomplete="email" value="{{ old('email') }}" required
                   aria-describedby="email-hint @error('email') email-error @enderror" @error('email') aria-invalid="true" @enderror>
            <x-site.field-error name="email" />
        </div>
        <x-site.field-error name="name" id-prefix="buy-" />   … 欄の id が buy-name のとき（エラー文の id は buy-name-error）

    name:     フォームの name 属性（配列の「items.0」は id では「items_0」になる）
    idPrefix: 欄の id の接頭辞（x-site.form-errors の idPrefix と同じ値にする）
    bag:      MessageBag（省略時はビュー共有の $errors）

    出力: <p class="c-field__error" id="email-error"><svg class="c-icon c-icon--alert">…</svg>メールアドレスを入力してください。</p>
--}}
@php
    $bag ??= $errors ?? null;
    $message = $bag !== null ? $bag->first($name) : null;
    $errorId = $idPrefix.str_replace('.', '_', $name).'-error';
@endphp
@if (filled($message))
    <p {{ $attributes->merge(['class' => 'c-field__error', 'id' => $errorId]) }}><x-site.icon name="alert" :size="18" />{{ $message }}</p>
@endif
