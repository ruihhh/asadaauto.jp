@extends('layouts.site')

@section('title', '無料買取査定｜尼崎・兵庫県の中古車買取')
@section('meta_description', '尼崎・兵庫の中古車買取査定ならアサダオートサポート。査定料0円・しつこい営業なし・契約後の減額なし。最短即日回答。3分で簡単お申し込み。')
@section('og_title', '無料買取査定 | ' . config('app.name'))
@section('og_description', '査定料0円・しつこい営業なし・最短即日回答の中古車買取。兵庫県尼崎市のアサダオートサポート。')
@section('canonical', route('buy.index'))

@section('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        {"@type":"ListItem","position":1,"name":"ホーム","item":"{{ url('/') }}"},
        {"@type":"ListItem","position":2,"name":"無料買取査定","item":"{{ route('buy.index') }}"}
    ]
}
</script>
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "Service",
    "name": "中古車買取査定サービス",
    "provider": {
        "@type": "AutoDealer",
        "name": "{{ config('app.name') }}",
        "url": "{{ url('/') }}"
    },
    "description": "中古車の無料買取査定サービス。査定料0円・しつこい営業なし・最短即日回答。",
    "areaServed": [
        {"@type":"City","name":"尼崎市"},
        {"@type":"City","name":"西宮市"},
        {"@type":"City","name":"伊丹市"},
        {"@type":"City","name":"宝塚市"},
        {"@type":"City","name":"神戸市"},
        {"@type":"City","name":"大阪市"},
        {"@type":"AdministrativeArea","name":"兵庫県"},
        {"@type":"AdministrativeArea","name":"大阪府"}
    ],
    "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "JPY",
        "description": "無料査定"
    }
}
</script>
@endsection

@section('content')

{{-- ============================================================
     ヒーロー（フォーム組み込み型・2カラム）
     ============================================================ --}}
<section class="buy-hero">
    <div class="container">
        <div class="buy-hero-inner">

            {{-- 左側：テキスト・信頼訴求 --}}
            <div class="buy-hero-text">
                <p class="hero-eyebrow">兵庫県尼崎市 | FREE APPRAISAL</p>
                <h1 class="buy-hero-title">尼崎の中古車買取<br><span>無料査定受付中</span></h1>
                <p class="buy-hero-sub">兵庫県・大阪府エリア対応。まずはお気軽にご相談ください</p>

                <div class="buy-hero-trust-grid">
                    <div class="buy-hero-trust-item">
                        <span class="buy-hero-trust-check">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="11" height="11"><polyline points="20 6 9 17 4 12"/></svg>
                        </span>
                        査定料・手数料<strong>0円</strong>
                    </div>
                    <div class="buy-hero-trust-item">
                        <span class="buy-hero-trust-check">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="11" height="11"><polyline points="20 6 9 17 4 12"/></svg>
                        </span>
                        しつこい営業<strong>一切なし</strong>
                    </div>
                    <div class="buy-hero-trust-item">
                        <span class="buy-hero-trust-check">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="11" height="11"><polyline points="20 6 9 17 4 12"/></svg>
                        </span>
                        契約後の<strong>減額なし</strong>
                    </div>
                    <div class="buy-hero-trust-item">
                        <span class="buy-hero-trust-check">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="11" height="11"><polyline points="20 6 9 17 4 12"/></svg>
                        </span>
                        キャンセル<strong>無料</strong>
                    </div>
                </div>

                <a class="buy-hero-phone" href="tel:0649608765">
                    <span class="buy-hero-phone-icon">
                        <svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1-9.4 0-17-7.6-17-17 0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1L6.6 10.8z"/></svg>
                    </span>
                    <span class="buy-hero-phone-body">
                        <span class="buy-hero-phone-label">電話でのご相談</span>
                        <span class="buy-hero-phone-num">06-4960-8765</span>
                        <span class="buy-hero-phone-hours">受付 11:00〜21:00（木曜・第3日曜定休）</span>
                    </span>
                </a>

                <div class="buy-hero-nums">
                    <div class="buy-hero-num-item">
                        <strong>500<em>台+</em></strong>
                        <span>累計買取実績</span>
                    </div>
                    <div class="buy-hero-num-div"></div>
                    <div class="buy-hero-num-item">
                        <strong>97<em>%</em></strong>
                        <span>査定満足度</span>
                    </div>
                    <div class="buy-hero-num-div"></div>
                    <div class="buy-hero-num-item">
                        <strong>最短<em>即日</em></strong>
                        <span>査定対応</span>
                    </div>
                </div>
            </div>

            {{-- 右側：ミニフォームカード（2ステップ） --}}
            <div class="buy-hero-form-wrap">
                <div class="buy-hero-form-card"
                     x-data="{
                         step: 1,
                         nextStep() {
                             const step1El = this.$refs.step1;
                             const inputs = [...step1El.querySelectorAll('input[required], select[required]')];
                             const firstInvalid = inputs.find(inp => !inp.checkValidity());
                             if (firstInvalid) { firstInvalid.reportValidity(); }
                             else { this.step = 2; window.scrollTo({top: this.$el.offsetTop - 80, behavior: 'smooth'}); }
                         }
                     }">

                    {{-- カードヘッダー --}}
                    <div class="buy-mini-header">
                        <p class="buy-mini-title">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h3l3 3v5h-3"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                            無料査定のご依頼
                        </p>
                        {{-- ステップバー --}}
                        <div class="buy-mini-steps">
                            <div class="buy-mini-step" :class="step >= 1 ? 'is-active' : ''">
                                <span class="buy-mini-step-dot" :class="step > 1 ? 'is-done' : ''">
                                    <template x-if="step > 1">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" width="11" height="11"><polyline points="20 6 9 17 4 12"/></svg>
                                    </template>
                                    <template x-if="step <= 1"><span>1</span></template>
                                </span>
                                <span class="buy-mini-step-label">お車の情報</span>
                            </div>
                            <div class="buy-mini-step-line" :class="step >= 2 ? 'is-done' : ''"></div>
                            <div class="buy-mini-step" :class="step >= 2 ? 'is-active' : ''">
                                <span class="buy-mini-step-dot">
                                    <span>2</span>
                                </span>
                                <span class="buy-mini-step-label">お客様情報</span>
                            </div>
                        </div>
                    </div>

                    @if($errors->any())
                    <div class="buy-mini-errors">
                        <p>入力内容をご確認ください</p>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('buy.send') }}" method="POST">
                        @csrf
                        <input type="hidden" name="condition" value="normal">

                        {{-- STEP 1: お車の情報 --}}
                        <div class="buy-mini-body" x-show="step === 1" x-ref="step1">
                            <div class="buy-mini-row2">
                                <div class="buy-mini-field">
                                    <label>メーカー <span class="required">*</span></label>
                                    <input type="text" name="make" required placeholder="例：トヨタ"
                                           value="{{ old('make') }}" list="mini-makes-list"
                                           class="@error('make') is-error @enderror">
                                    <datalist id="mini-makes-list">
                                        @foreach ($makes ?? [] as $make)
                                            <option value="{{ $make }}">
                                        @endforeach
                                        <option value="トヨタ">
                                        <option value="日産">
                                        <option value="ホンダ">
                                        <option value="スズキ">
                                        <option value="ダイハツ">
                                        <option value="マツダ">
                                        <option value="スバル">
                                        <option value="三菱">
                                        <option value="レクサス">
                                    </datalist>
                                </div>
                                <div class="buy-mini-field">
                                    <label>車名 <span class="required">*</span></label>
                                    <input type="text" name="model" required placeholder="例：プリウス"
                                           value="{{ old('model') }}"
                                           class="@error('model') is-error @enderror">
                                </div>
                            </div>
                            <div class="buy-mini-row2">
                                <div class="buy-mini-field">
                                    <label>年式 <span class="required">*</span></label>
                                    <select name="model_year" required class="@error('model_year') is-error @enderror">
                                        <option value="">選択</option>
                                        @for ($y = date('Y'); $y >= 1990; $y--)
                                            <option value="{{ $y }}" @selected(old('model_year') == $y)>{{ $y }}年</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="buy-mini-field">
                                    <label>走行距離(km) <span class="required">*</span></label>
                                    <input type="number" name="mileage" required placeholder="例：45000"
                                           min="0" value="{{ old('mileage') }}"
                                           class="@error('mileage') is-error @enderror">
                                </div>
                            </div>
                            <p class="buy-mini-note">おおよそで結構です。後から変更できます。</p>
                            <button type="button" class="buy-mini-next-btn" @click="nextStep()">
                                次へ → お客様情報の入力
                            </button>
                        </div>

                        {{-- STEP 2: お客様情報 --}}
                        <div class="buy-mini-body" x-show="step === 2" style="display:none;">
                            <button type="button" class="buy-mini-back-btn" @click="step = 1">
                                ← お車の情報を修正
                            </button>
                            <div class="buy-mini-field buy-mini-field--full">
                                <label>お名前 <span class="required">*</span></label>
                                <input type="text" name="name" required placeholder="山田 太郎"
                                       value="{{ old('name') }}"
                                       class="@error('name') is-error @enderror">
                            </div>
                            <div class="buy-mini-field buy-mini-field--full">
                                <label>電話番号 <span class="required">*</span></label>
                                <input type="tel" name="phone" required placeholder="090-0000-0000"
                                       value="{{ old('phone') }}"
                                       class="@error('phone') is-error @enderror">
                            </div>
                            <div class="buy-mini-field buy-mini-field--full">
                                <label>メールアドレス <span class="required">*</span></label>
                                <input type="email" name="email" required placeholder="example@mail.com"
                                       value="{{ old('email') }}"
                                       class="@error('email') is-error @enderror">
                            </div>
                            <button type="submit" class="buy-mini-submit-btn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M22 2L11 13"/><path d="M22 2L15 22 11 13 2 9l20-7z"/></svg>
                                無料査定を申し込む
                            </button>
                            <p class="buy-mini-submit-note">送信後、担当スタッフよりご連絡いたします</p>
                        </div>
                    </form>

                    <div class="buy-mini-footer">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                        入力約3分 ／ 完全無料 ／ しつこい勧誘なし
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

{{-- ============================================================
     緊急性ストリップ
     ============================================================ --}}
<div class="buy-urgency-strip">
    <div class="container">
        <div class="buy-urgency-inner">
            <span class="buy-urgency-icon">⚠</span>
            <span>クルマの価値は<strong>時間とともに下がります</strong>。早めの査定で少しでも高く売れる可能性が高まります。</span>
            <a href="#appraisal-form" class="buy-urgency-btn">今すぐ無料査定 →</a>
        </div>
    </div>
</div>

{{-- ============================================================
     3つの強み
     ============================================================ --}}
<section class="section section-white">
    <div class="container">
        <div class="buy-section-head">
            <p class="buy-section-eyebrow">OUR STRENGTHS</p>
            <h2 class="buy-section-title">{{ config('app.name') }}の<em>3つの強み</em></h2>
            <p class="buy-section-sub">他社と比べてみてください。私たちが選ばれる理由がわかります。</p>
        </div>
        <div class="buy-strengths">
            <div class="buy-strength-card">
                <div class="buy-strength-num">01</div>
                <div class="buy-strength-icon-wrap buy-strength-icon-wrap--gold">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="28" height="28"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                </div>
                <div class="buy-strength-body">
                    <h3>他社より高い査定額</h3>
                    <p>独自の販売ネットワークを活かし、他社より高い査定額をご提示。「他社で断られた」「もっと高く売りたい」という方もぜひご相談ください。愛車の価値を最大限に引き出します。</p>
                </div>
            </div>
            <div class="buy-strength-card">
                <div class="buy-strength-num">02</div>
                <div class="buy-strength-icon-wrap buy-strength-icon-wrap--blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="28" height="28"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div class="buy-strength-body">
                    <h3>最短即日のスピード対応</h3>
                    <p>ご依頼から最短即日でご連絡。出張査定にも対応しており、お客様のご都合に合わせてスピーディに対応します。忙しい方でも安心してご利用いただけます。</p>
                </div>
            </div>
            <div class="buy-strength-card">
                <div class="buy-strength-num">03</div>
                <div class="buy-strength-icon-wrap buy-strength-icon-wrap--green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="28" height="28"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87m-4-12a4 4 0 010 7.75"/></svg>
                </div>
                <div class="buy-strength-body">
                    <h3>安心・丁寧なサポート</h3>
                    <p>面倒な書類手続きもスタッフが丁寧にサポート。売却後のアフターフォローも万全。初めて売却する方でも安心してご相談いただける環境を整えています。</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     4つの安心保証
     ============================================================ --}}
<section class="section buy-guarantee-section">
    <div class="container">
        <div class="buy-section-head">
            <p class="buy-section-eyebrow">OUR PROMISE</p>
            <h2 class="buy-section-title">4つの<em>安心保証</em></h2>
            <p class="buy-section-sub">安心してお任せいただくための4つのお約束</p>
        </div>
        <div class="buy-guarantee-grid">
            <div class="buy-guarantee-card">
                <div class="buy-guarantee-icon buy-guarantee-icon--red">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="28" height="28"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <h3>査定後の<br>減額なし</h3>
                <p>一度ご提示した査定額を後から下げることは一切ありません。ご安心ください。</p>
            </div>
            <div class="buy-guarantee-card">
                <div class="buy-guarantee-icon buy-guarantee-icon--blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="28" height="28"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                <h3>キャンセル<br>無料</h3>
                <p>査定後に売却をお断りいただいても費用は一切かかりません。気軽にご相談ください。</p>
            </div>
            <div class="buy-guarantee-card">
                <div class="buy-guarantee-icon buy-guarantee-icon--green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="28" height="28"><path d="M18 8h1a4 4 0 010 8h-1"/><path d="M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8z"/><line x1="6" y1="1" x2="6" y2="4"/><line x1="10" y1="1" x2="10" y2="4"/><line x1="14" y1="1" x2="14" y2="4"/></svg>
                </div>
                <h3>しつこい<br>営業なし</h3>
                <p>査定後に売却をお断りいただいても、その後のしつこい勧誘は一切行いません。</p>
            </div>
            <div class="buy-guarantee-card">
                <div class="buy-guarantee-icon buy-guarantee-icon--gold">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="28" height="28"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                </div>
                <h3>個人情報の<br>厳重管理</h3>
                <p>お預かりした個人情報は第三者に提供せず、プライバシーポリシーに従い厳重に管理します。</p>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     どんな車でも買取
     ============================================================ --}}
<section class="section buy-any-section">
    <div class="container">
        <div class="buy-section-head">
            <p class="buy-section-eyebrow">ANY CAR WELCOME</p>
            <h2 class="buy-section-title">どんなお車でも<em>買取します</em></h2>
        </div>
        <div class="buy-any-grid">
            <div class="buy-any-item">
                <span class="buy-any-icon">🚗</span>
                <span>事故車・修復歴あり</span>
            </div>
            <div class="buy-any-item">
                <span class="buy-any-icon">📋</span>
                <span>車検切れ</span>
            </div>
            <div class="buy-any-item">
                <span class="buy-any-icon">🔢</span>
                <span>走行距離が多い</span>
            </div>
            <div class="buy-any-item">
                <span class="buy-any-icon">💳</span>
                <span>ローン残債あり</span>
            </div>
            <div class="buy-any-item">
                <span class="buy-any-icon">🔧</span>
                <span>不動車・故障車</span>
            </div>
            <div class="buy-any-item">
                <span class="buy-any-icon">📅</span>
                <span>旧年式・古い車</span>
            </div>
        </div>
        <p class="buy-any-note">※ 状態によっては買取できない場合もございます。まずはお気軽にご相談ください。</p>
    </div>
</section>

{{-- ============================================================
     売却の流れ
     ============================================================ --}}
<section class="section section-gray">
    <div class="container">
        <div class="buy-section-head">
            <p class="buy-section-eyebrow">HOW IT WORKS</p>
            <h2 class="buy-section-title">売却までの<em>5ステップ</em></h2>
        </div>
        <div class="buy-steps">
            <div class="buy-step">
                <div class="buy-step-circle">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="22" height="22"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </div>
                <div class="buy-step-body">
                    <span class="buy-step-num">STEP 1</span>
                    <h3 class="buy-step-title">査定依頼</h3>
                    <p class="buy-step-desc">車の情報と連絡先を入力。入力時間は約3分。無料なので、気軽にご依頼ください。</p>
                </div>
            </div>
            <div class="buy-step">
                <div class="buy-step-circle">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="22" height="22"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </div>
                <div class="buy-step-body">
                    <span class="buy-step-num">STEP 2</span>
                    <h3 class="buy-step-title">買取査定</h3>
                    <p class="buy-step-desc">査定スタッフがご連絡します。出張査定など実車確認が必要な場合もございます。</p>
                </div>
            </div>
            <div class="buy-step">
                <div class="buy-step-circle">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="22" height="22"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                </div>
                <div class="buy-step-body">
                    <span class="buy-step-num">STEP 3</span>
                    <h3 class="buy-step-title">査定価格を比較</h3>
                    <p class="buy-step-desc">提示された査定額をご確認いただき、納得できる金額が出たら売却先を決定します。</p>
                </div>
            </div>
            <div class="buy-step">
                <div class="buy-step-circle">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="22" height="22"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                </div>
                <div class="buy-step-body">
                    <span class="buy-step-num">STEP 4</span>
                    <h3 class="buy-step-title">売却手続き</h3>
                    <p class="buy-step-desc">面倒な売却手続きは担当スタッフが丁寧にサポートします。書類準備もご安心ください。</p>
                </div>
            </div>
            <div class="buy-step buy-step--last">
                <div class="buy-step-circle buy-step-circle--final">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="22" height="22"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <div class="buy-step-body">
                    <span class="buy-step-num">STEP 5</span>
                    <h3 class="buy-step-title">引き渡し・完了</h3>
                    <p class="buy-step-desc">必要書類を揃え、荷物を片付けて車を引き渡します。代金をお受け取りいただき完了です。</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     詳細査定フォーム
     ============================================================ --}}
<section class="section section-white" id="appraisal-form">
    <div class="container">
        <div class="buy-form-wrap">
            <div class="buy-section-head">
                <p class="buy-section-eyebrow">FREE ESTIMATE</p>
                <h2 class="buy-section-title">詳細情報で<em>より正確な査定</em>を</h2>
                <p class="buy-section-sub">車両状態や詳細情報をお知らせいただくと、より正確な査定額をご提示できます。</p>
            </div>

            {{-- 安心ポイント --}}
            <div class="buy-form-assurance">
                <div class="buy-form-assurance-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                    査定料無料
                </div>
                <div class="buy-form-assurance-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                    売却しなくてもOK
                </div>
                <div class="buy-form-assurance-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                    しつこい営業なし
                </div>
                <div class="buy-form-assurance-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
                    個人情報は厳重管理
                </div>
            </div>

            @if($errors->any())
            <div class="alert-box" role="alert">
                <p class="alert-box-title">入力内容にエラーがあります</p>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="buy-form-card">
                <form action="{{ route('buy.send') }}" method="POST">
                    @csrf

                    {{-- 車両情報 --}}
                    <div class="buy-form-section">
                        <div class="buy-form-section-title">
                            <span class="store-card-title-badge">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="15" height="15"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h3l3 3v5h-3"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                            </span>
                            お車の情報
                        </div>

                        <div class="form-grid form-group">
                            <div>
                                <label class="form-label" for="make">メーカー <span class="required">*</span></label>
                                <input class="form-input" id="make" type="text" name="make"
                                       value="{{ old('make') }}" required placeholder="例：トヨタ"
                                       list="makes-list">
                                <datalist id="makes-list">
                                    @foreach ($makes ?? [] as $make)
                                        <option value="{{ $make }}">
                                    @endforeach
                                    <option value="トヨタ">
                                    <option value="日産">
                                    <option value="ホンダ">
                                    <option value="スズキ">
                                    <option value="ダイハツ">
                                    <option value="マツダ">
                                    <option value="スバル">
                                    <option value="三菱">
                                    <option value="レクサス">
                                </datalist>
                            </div>
                            <div>
                                <label class="form-label" for="model">車名 <span class="required">*</span></label>
                                <input class="form-input" id="model" type="text" name="model"
                                       value="{{ old('model') }}" required placeholder="例：プリウス">
                            </div>
                        </div>

                        <div class="form-grid form-group">
                            <div>
                                <label class="form-label" for="grade">グレード</label>
                                <input class="form-input" id="grade" type="text" name="grade"
                                       value="{{ old('grade') }}" placeholder="例：Gグレード">
                            </div>
                            <div>
                                <label class="form-label" for="color">車体色</label>
                                <input class="form-input" id="color" type="text" name="color"
                                       value="{{ old('color') }}" placeholder="例：パールホワイト">
                            </div>
                        </div>

                        <div class="form-grid form-group">
                            <div>
                                <label class="form-label" for="model_year">年式 <span class="required">*</span></label>
                                <select class="form-input" id="model_year" name="model_year" required>
                                    <option value="">選択してください</option>
                                    @for ($y = date('Y'); $y >= 1990; $y--)
                                        <option value="{{ $y }}" @selected(old('model_year') == $y)>{{ $y }}年</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <label class="form-label" for="mileage">走行距離(km) <span class="required">*</span></label>
                                <input class="form-input" id="mileage" type="number" name="mileage"
                                       value="{{ old('mileage') }}" required placeholder="例：45000" min="0">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">車両状態 <span class="required">*</span></label>
                            <div class="buy-condition-btns">
                                <label class="buy-condition-label {{ old('condition') === 'good' ? 'selected' : '' }}">
                                    <input type="radio" name="condition" value="good" @checked(old('condition') === 'good')>
                                    <span class="buy-condition-icon">😊</span>
                                    <span class="buy-condition-text">良好<small>目立つ傷・凹みなし</small></span>
                                </label>
                                <label class="buy-condition-label {{ old('condition', 'normal') === 'normal' ? 'selected' : '' }}">
                                    <input type="radio" name="condition" value="normal" @checked(old('condition', 'normal') === 'normal')>
                                    <span class="buy-condition-icon">😐</span>
                                    <span class="buy-condition-text">普通<small>多少の傷・汚れあり</small></span>
                                </label>
                                <label class="buy-condition-label {{ old('condition') === 'damaged' ? 'selected' : '' }}">
                                    <input type="radio" name="condition" value="damaged" @checked(old('condition') === 'damaged')>
                                    <span class="buy-condition-icon">😟</span>
                                    <span class="buy-condition-text">傷・凹みあり<small>目立つ損傷がある</small></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- 申込者情報 --}}
                    <div class="buy-form-section">
                        <div class="buy-form-section-title">
                            <span class="store-card-title-badge">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="15" height="15"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </span>
                            お客様情報
                        </div>

                        <div class="form-grid form-group">
                            <div>
                                <label class="form-label" for="name">お名前 <span class="required">*</span></label>
                                <input class="form-input" id="name" type="text" name="name"
                                       value="{{ old('name') }}" required placeholder="山田 太郎">
                            </div>
                            <div>
                                <label class="form-label" for="phone">電話番号 <span class="required">*</span></label>
                                <input class="form-input" id="phone" type="tel" name="phone"
                                       value="{{ old('phone') }}" required placeholder="090-0000-0000">
                            </div>
                        </div>

                        <div class="form-grid form-group">
                            <div>
                                <label class="form-label" for="email">メールアドレス <span class="required">*</span></label>
                                <input class="form-input" id="email" type="email" name="email"
                                       value="{{ old('email') }}" required placeholder="example@mail.com">
                            </div>
                            <div>
                                <label class="form-label" for="zip">郵便番号</label>
                                <input class="form-input" id="zip" type="text" name="zip"
                                       value="{{ old('zip') }}" placeholder="000-0000">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="message">備考・ご要望</label>
                            <textarea class="form-input" id="message" name="message"
                                      placeholder="修復歴の有無、オプション装備、ご希望の査定日時など" style="height:110px;resize:vertical;">{{ old('message') }}</textarea>
                        </div>
                    </div>

                    <div class="buy-form-submit">
                        <button class="buy-submit-btn" type="submit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M22 2L11 13"/><path d="M22 2L15 22 11 13 2 9l20-7z"/></svg>
                            無料査定を申し込む
                        </button>
                        <p class="buy-form-note">送信後、担当スタッフよりご連絡いたします。しつこい勧誘は一切行いません。</p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     買取実績
     ============================================================ --}}
<section class="section section-gray">
    <div class="container">
        <div class="buy-section-head">
            <p class="buy-section-eyebrow">APPRAISAL RESULTS</p>
            <h2 class="buy-section-title">お客様の<em>買取実績</em></h2>
            <p class="buy-section-sub">実際にご利用いただいたお客様の査定実績です。</p>
        </div>
        <div class="buy-results-grid">
            @php
            $results = [
                ['car'=>'トヨタ アルファード','year'=>'2020年式','km'=>'2.8万km','price'=>'3,850,000','comment'=>'他社より50万円以上高い査定をしていただきました！','stars'=>5,'type'=>'ミニバン'],
                ['car'=>'ホンダ ヴェゼル','year'=>'2019年式','km'=>'4.1万km','price'=>'1,920,000','comment'=>'丁寧に対応していただき、スムーズに売却できました。','stars'=>5,'type'=>'SUV'],
                ['car'=>'日産 セレナ','year'=>'2018年式','km'=>'5.6万km','price'=>'1,580,000','comment'=>'思っていたより高く売れて大満足です！また利用したいです。','stars'=>5,'type'=>'ミニバン'],
                ['car'=>'スズキ ジムニー','year'=>'2021年式','km'=>'1.2万km','price'=>'2,650,000','comment'=>'希少車だけあって予想以上の査定額でした。','stars'=>5,'type'=>'SUV'],
                ['car'=>'トヨタ プリウス','year'=>'2017年式','km'=>'7.8万km','price'=>'980,000','comment'=>'出張査定をしてもらい、その場で契約できました。','stars'=>4,'type'=>'セダン'],
                ['car'=>'マツダ CX-5','year'=>'2020年式','km'=>'3.3万km','price'=>'2,200,000','comment'=>'書類の準備など全てサポートしてもらえて助かりました。','stars'=>5,'type'=>'SUV'],
            ];
            @endphp
            @foreach($results as $r)
            <div class="buy-result-card">
                <div class="buy-result-header">
                    <div>
                        <span class="buy-result-type">{{ $r['type'] }}</span>
                        <p class="buy-result-car">{{ $r['car'] }}</p>
                        <p class="buy-result-year">{{ $r['year'] }} / {{ $r['km'] }}</p>
                    </div>
                    <div class="buy-result-stars">
                        @for($i = 0; $i < 5; $i++)
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="{{ $i < $r['stars'] ? '#f59e0b' : '#e5e7eb' }}"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        @endfor
                    </div>
                </div>
                <div class="buy-result-price-row">
                    <span class="buy-result-label">査定額</span>
                    <span class="buy-result-price">{{ $r['price'] }}<small>円</small></span>
                </div>
                <p class="buy-result-comment">「{{ $r['comment'] }}」</p>
            </div>
            @endforeach
        </div>
        <p class="buy-results-note">※ 査定額は査定時の市場状況により異なります。上記は実績の一例です。</p>
        <div class="buy-results-cta">
            <a href="#appraisal-form" class="buy-results-cta-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                私も無料で査定してみる
            </a>
        </div>
    </div>
</section>

{{-- ============================================================
     よくあるご質問
     ============================================================ --}}
<section class="section section-white">
    <div class="container">
        <div class="buy-section-head">
            <p class="buy-section-eyebrow">FAQ</p>
            <h2 class="buy-section-title">よくある<em>ご質問</em></h2>
        </div>
        <div class="buy-faq-wrap">
            @php
            $faqs = [
                ['q'=>'査定は本当に無料ですか？', 'a'=>'はい、査定料・手数料は完全無料です。売却に至らない場合でも費用は一切かかりません。お気軽にお申し込みください。'],
                ['q'=>'査定後に断ることはできますか？', 'a'=>'もちろんできます。査定額をご確認いただいた後、売却するかどうかはお客様が自由に決めることができます。しつこい勧誘は一切行いません。'],
                ['q'=>'事故車・走行距離が多い車でも査定してもらえますか？', 'a'=>'はい、状態に関わらず査定いたします。走行距離が多い車や、傷・凹みがある車も買取可能な場合がございます。まずはお気軽にお申し込みください。'],
                ['q'=>'車検切れ・ローン残債がある車でも売れますか？', 'a'=>'車検切れの車でも買取は可能です。ローン残債がある場合は、一括返済後に売却となるケースが多いです。詳しくはお問い合わせください。'],
                ['q'=>'売却に必要な書類を教えてください。', 'a'=>'一般的に必要な書類は、車検証・自賠責保険証・リサイクル券・印鑑証明書（実印）・車庫証明などです。詳細はご成約時にスタッフが丁寧にご説明します。'],
                ['q'=>'出張査定はしてもらえますか？', 'a'=>'はい、ご自宅や職場への出張査定に対応しております。お客様のご都合のよい日時・場所をお知らせください。もちろん出張費用も無料です。'],
            ];
            @endphp
            <div class="buy-faq-list">
                @foreach($faqs as $i => $faq)
                <div class="buy-faq-item" x-data="{ open: false }">
                    <button class="buy-faq-q" @click="open = !open" :class="{ 'buy-faq-q-open': open }">
                        <span class="buy-faq-badge">Q</span>
                        <span>{{ $faq['q'] }}</span>
                        <svg class="buy-faq-arrow" :class="{ 'buy-faq-arrow-open': open }"
                             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div class="buy-faq-a" x-show="open" x-transition style="display:none;">
                        <span class="buy-faq-a-badge">A</span>
                        <p>{{ $faq['a'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     再CTA
     ============================================================ --}}
<div class="buy-bottom-cta">
    <div class="container">
        <div class="buy-bottom-cta-inner">
            <div class="buy-bottom-cta-text">
                <p class="buy-bottom-cta-eyebrow">まずはお気軽にご相談ください</p>
                <p class="buy-bottom-cta-title">無料査定を今すぐ申し込む</p>
                <p class="buy-bottom-cta-sub">査定料・手数料完全無料 ／ しつこい勧誘一切なし ／ 契約後の減額なし</p>
            </div>
            <div class="buy-bottom-cta-btns">
                <a href="#appraisal-form" class="buy-bottom-cta-main">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    無料で査定する
                </a>
                <a href="tel:0649608765" class="buy-bottom-cta-tel">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1-9.4 0-17-7.6-17-17 0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1L6.6 10.8z"/></svg>
                    06-4960-8765
                </a>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     スティッキーCTA（スマホ専用）
     ============================================================ --}}
<div class="buy-sticky-cta" id="buy-sticky-cta">
    <a href="#appraisal-form" class="buy-sticky-cta-form">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="17" height="17"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        無料査定を申し込む
    </a>
    <a href="tel:0649608765" class="buy-sticky-cta-phone">
        <svg viewBox="0 0 24 24" fill="currentColor" width="17" height="17"><path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1-9.4 0-17-7.6-17-17 0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1L6.6 10.8z"/></svg>
        電話する
    </a>
</div>

<script>
// 車両状態ラジオボタン
document.querySelectorAll('.buy-condition-label input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', () => {
        document.querySelectorAll('.buy-condition-label').forEach(l => l.classList.remove('selected'));
        if (radio.checked) radio.closest('.buy-condition-label').classList.add('selected');
    });
});

// スティッキーCTA: ヒーローを過ぎたら表示
(function() {
    const cta = document.getElementById('buy-sticky-cta');
    const hero = document.querySelector('.buy-hero');
    if (!cta || !hero) return;
    function check() {
        const heroBottom = hero.getBoundingClientRect().bottom;
        cta.classList.toggle('is-visible', heroBottom < 0);
    }
    window.addEventListener('scroll', check, { passive: true });
    check();
})();
</script>

@endsection
