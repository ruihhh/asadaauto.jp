@props([
    'bag' => null,
    'idPrefix' => '',
    'title' => '入力内容を確認してください',
    'focus' => true,
    'order' => [],
])
{{--
    エラー一覧。フォームの先頭に置く（role="alert"）。各エラーから該当の欄へページ内リンクで飛べる。
    エラーがないときは何も出さない。

    使い方:
        <x-site.form-errors />                          … 共有の $errors を使う。リンク先は #name / #email など（欄の id＝name 属性）
        <x-site.form-errors id-prefix="buy-" />         … リンク先を #buy-name のようにする
        <x-site.form-errors :bag="$someMessageBag" />
        <x-site.form-errors :order="['stock_no', 'message', 'name', 'phone', 'email']" />   … 画面の欄の並びの順に出す

    bag:      MessageBag（省略時はビュー共有の $errors）
    idPrefix: 欄の id の接頭辞（欄の id は「接頭辞＋name 属性」にしておく）
    title:    見出しの文言（件数は自動で付く）
    focus:    表示したときにこの一覧へフォーカスを移すか（Alpine）
    order:    一覧に出す順（欄の name 属性の配列。画面の欄の並びに合わせる）。並びにない欄は後ろに元の順で出す。省略時は bag の順（検証ルールの順）

    出力: <div class="c-alert c-alert--error c-form-errors" id="form-errors" role="alert" tabindex="-1">
          <p class="c-alert__title">入力内容を確認してください<span class="u-nowrap">（2件）</span></p><ul class="c-form-errors__list">
          <li><a class="c-form-errors__link" href="#email">…</a></li></ul></div>
--}}
@php
    $bag ??= $errors ?? null;
    $messages = $bag !== null && $bag->any() ? $bag->messages() : [];
    // 画面の欄の並び（order）の順に並べ替える。並びにない欄は後ろに元の順で残す
    if ($order !== [] && $messages !== []) {
        $sorted = [];
        foreach ($order as $field) {
            if (isset($messages[$field])) {
                $sorted[$field] = $messages[$field];
            }
        }
        $messages = $sorted + $messages;
    }
@endphp
@if ($messages !== [])
    <div {{ $attributes->merge(['class' => 'c-alert c-alert--error c-form-errors', 'id' => 'form-errors']) }} role="alert" tabindex="-1" @if ($focus) x-data x-init="$nextTick(() => $el.focus())" @endif>
        <x-site.icon name="alert" />
        <div class="c-alert__body">
            <p class="c-alert__title">{{ $title }}<span class="u-nowrap">（{{ count($messages) }}件）</span></p>
            <ul class="c-form-errors__list">
                @foreach ($messages as $field => $fieldMessages)
                    <li><a class="c-form-errors__link" href="#{{ $idPrefix }}{{ str_replace('.', '_', $field) }}">{{ $fieldMessages[0] }}</a></li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
