@extends('layouts.site')

{{-- 法務確認前の仮文面。オーナー（必要なら専門家）の確認後に差し替える。事業者名・窓口は config/shop.php の値を使う --}}

@section('title', '個人情報の取り扱い')
@section('meta_description', config('shop.name').'の個人情報の取り扱いについて。取得する情報、利用目的、第三者への提供、アクセス解析（Google アナリティクス）、開示・訂正・削除のご依頼窓口をご案内します。')
@section('canonical', route('privacy'))
@section('body_class', 'p-privacy')

@php
    $operator = (array) config('shop.operator', []);
    $gaPolicyUrl = 'https://policies.google.com/technologies/partner-sites?hl=ja';
@endphp

@section('content')
<x-site.page-header narrow
    title="個人情報の取り扱い"
    lead="{{ config('shop.name') }}が、お問い合わせ・お見積もり・買取査定などでお預かりする個人情報の取り扱いについてご説明します。" />

<div class="l-section">
    <div class="l-container l-container--narrow">
        <div class="p-privacy-body">

            <section class="p-privacy-section" aria-labelledby="privacy-operator">
                <h2 id="privacy-operator" class="c-section-title">事業者</h2>
                <div class="c-table-wrap">
                    <table class="c-table">
                        <tbody>
                            <tr>
                                <th scope="row" class="c-table__th">事業者名</th>
                                <td class="c-table__td">{{ config('shop.name') }}</td>
                            </tr>
                            @if (filled($operator['company'] ?? null))
                                <tr>
                                    <th scope="row" class="c-table__th">運営会社</th>
                                    <td class="c-table__td">{{ $operator['company'] }}</td>
                                </tr>
                            @endif
                            @if (filled($operator['representative'] ?? null))
                                <tr>
                                    <th scope="row" class="c-table__th">代表者</th>
                                    <td class="c-table__td">{{ $operator['representative'] }}</td>
                                </tr>
                            @endif
                            <tr>
                                <th scope="row" class="c-table__th">所在地</th>
                                <td class="c-table__td"><x-site.address postal /></td>
                            </tr>
                            <tr>
                                <th scope="row" class="c-table__th">電話</th>
                                <td class="c-table__td"><a class="c-link" href="{{ config('shop.tel_href') }}" aria-label="電話をかける {{ config('shop.tel') }}">{{ config('shop.tel') }}</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="p-privacy-section" aria-labelledby="privacy-collect">
                <h2 id="privacy-collect" class="c-section-title">取得する情報</h2>
                <p class="p-privacy-text">お問い合わせフォーム・買取査定フォームにご入力いただいた情報と、お電話やご来店の際にお伺いした次の情報を取得します。</p>
                <ul class="p-privacy-list">
                    <li class="p-privacy-list__item">お名前</li>
                    <li class="p-privacy-list__item">電話番号</li>
                    <li class="p-privacy-list__item">メールアドレス</li>
                    <li class="p-privacy-list__item">郵便番号</li>
                    <li class="p-privacy-list__item">お車の情報（メーカー・車種・グレード・年式・走行距離・ボディカラー・状態など）</li>
                    <li class="p-privacy-list__item">お問い合わせ内容</li>
                </ul>
            </section>

            <section class="p-privacy-section" aria-labelledby="privacy-purpose">
                <h2 id="privacy-purpose" class="c-section-title">利用目的</h2>
                <p class="p-privacy-text">取得した個人情報は、次の目的だけに利用します。</p>
                <ul class="p-privacy-list">
                    <li class="p-privacy-list__item">お問い合わせ・お見積もり・買取査定へのご回答と、そのためのご連絡</li>
                    <li class="p-privacy-list__item">ご契約、名義変更・登録などの手続き</li>
                </ul>
            </section>

            <section class="p-privacy-section" aria-labelledby="privacy-third-party">
                <h2 id="privacy-third-party" class="c-section-title">第三者への提供</h2>
                <p class="p-privacy-text">法令に基づく場合を除き、ご本人の同意なく個人情報を第三者に提供することはありません。</p>
            </section>

            <section class="p-privacy-section" aria-labelledby="privacy-analytics">
                <h2 id="privacy-analytics" class="c-section-title">アクセス解析ツールについて</h2>
                <p class="p-privacy-text">当サイトでは、サイトの利用状況を把握して改善に役立てるため、Google LLC のアクセス解析ツール「Google アナリティクス 4」を利用しています。</p>
                <p class="p-privacy-text">Google アナリティクスは Cookie（クッキー）を使って、閲覧したページ、閲覧した日時、お使いの端末やブラウザの種類などのアクセス情報を収集します。これらの情報には、お名前・電話番号・メールアドレスなど、個人を特定する情報は含まれません。</p>
                <p class="p-privacy-text">収集された情報は、Google のポリシーに基づいて管理されます。詳しくは、<a class="c-link" href="{{ $gaPolicyUrl }}" target="_blank" rel="noopener">Google のサービスを使用するサイトやアプリから収集した情報の Google による使用（新しいタブで開きます）</a>をご覧ください。</p>
                <p class="p-privacy-text">Cookie による情報の収集を望まない場合は、お使いのブラウザの設定で Cookie を無効にできます。設定の方法は、ブラウザのヘルプをご覧ください。</p>
            </section>

            <section class="p-privacy-section" aria-labelledby="privacy-storage">
                <h2 id="privacy-storage" class="c-section-title">お気に入り・車両比較の保存について</h2>
                <p class="p-privacy-text">「お気に入り」「車両比較」で選んだ車は、お使いの端末のブラウザ（ローカルストレージ）に保存します。当店には送信されません。ブラウザの閲覧データを消去すると、保存した内容も消えます。</p>
            </section>

            <section class="p-privacy-section" aria-labelledby="privacy-security">
                <h2 id="privacy-security" class="c-section-title">安全管理</h2>
                <p class="p-privacy-text">お預かりした個人情報は、漏えい・紛失・改ざんを防ぐため、取り扱う者を限って適切に管理します。利用目的を果たして不要になった情報は、適切な方法で削除します。</p>
            </section>

            <section class="p-privacy-section" aria-labelledby="privacy-request">
                <h2 id="privacy-request" class="c-section-title">開示・訂正・削除のご依頼</h2>
                <p class="p-privacy-text">ご本人から、個人情報の開示・訂正・利用停止・削除のご依頼があったときは、ご本人であることを確認したうえで、速やかに対応します。次の窓口までご連絡ください。</p>
                <div class="c-card c-card--soft u-mt-4">
                    <p class="c-card__title">{{ config('shop.name') }} 個人情報のお問い合わせ窓口</p>
                    <p>電話 <a class="c-link" href="{{ config('shop.tel_href') }}" aria-label="電話をかける {{ config('shop.tel') }}">{{ config('shop.tel') }}</a></p>
                    <p>営業時間 {{ \App\Support\BusinessHours::hoursLabel() }}<br>定休日 {{ config('shop.closed_label') }}</p>
                    <p><x-site.address postal /></p>
                </div>
            </section>

            <section class="p-privacy-section" aria-labelledby="privacy-revision">
                <h2 id="privacy-revision" class="c-section-title">改定</h2>
                <p class="p-privacy-text">法令の変更などに応じて、この内容を改定することがあります。改定したときは、このページでお知らせします。</p>
            </section>

            <p class="p-privacy-dates">2026年9月27日 制定</p>

        </div>
    </div>
</div>
@endsection
