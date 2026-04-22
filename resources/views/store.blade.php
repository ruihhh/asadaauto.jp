@extends('layouts.site')

@section('title', '店舗情報・アクセス｜尼崎市下坂部の中古車販売店')
@section('meta_description', '兵庫県尼崎市下坂部4丁目5-1。電話06-4960-8765。営業時間11:00〜21:00（木曜・第3日曜定休）。無料駐車場完備・全国陸送対応のアサダオートサポート。')
@section('og_title', '店舗情報・アクセス | ' . config('app.name'))
@section('og_description', '兵庫県尼崎市下坂部の中古車販売店。第三者検査・総額表示・整備履歴公開。試乗・査定は予約不要。')
@section('canonical', route('store'))

@section('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "AutoDealer",
    "@id": "{{ url('/') }}#organization",
    "name": "{{ config('app.name') }}",
    "url": "{{ url('/') }}",
    "telephone": "06-4960-8765",
    "address": {
        "@type": "PostalAddress",
        "streetAddress": "下坂部4丁目5-1",
        "addressLocality": "尼崎市",
        "addressRegion": "兵庫県",
        "postalCode": "661-0975",
        "addressCountry": "JP"
    },
    "geo": {
        "@type": "GeoCoordinates",
        "latitude": 34.7167,
        "longitude": 135.4167
    },
    "hasMap": "https://maps.google.com/?q=兵庫県尼崎市下坂部4丁目5-1",
    "keywords": "中古車,尼崎,兵庫県,尼崎市中古車,兵庫県中古車,中古車販売",
    "areaServed": [
        {"@type":"City","name":"尼崎市"},
        {"@type":"City","name":"西宮市"},
        {"@type":"City","name":"伊丹市"},
        {"@type":"City","name":"宝塚市"},
        {"@type":"City","name":"川西市"},
        {"@type":"City","name":"神戸市"},
        {"@type":"City","name":"大阪市"},
        {"@type":"City","name":"豊中市"},
        {"@type":"AdministrativeArea","name":"兵庫県"},
        {"@type":"AdministrativeArea","name":"大阪府"}
    ],
    "openingHoursSpecification": [
        {
            "@type": "OpeningHoursSpecification",
            "dayOfWeek": ["Monday","Tuesday","Wednesday","Friday","Saturday","Sunday"],
            "opens": "11:00",
            "closes": "21:00"
        }
    ],
    "amenityFeature": [
        {"@type":"LocationFeatureSpecification","name":"無料駐車場","value":true},
        {"@type":"LocationFeatureSpecification","name":"試乗可能","value":true},
        {"@type":"LocationFeatureSpecification","name":"全国陸送納車","value":true},
        {"@type":"LocationFeatureSpecification","name":"ローン対応","value":true},
        {"@type":"LocationFeatureSpecification","name":"下取り・買取","value":true}
    ]
}
</script>
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        {
            "@type": "Question",
            "name": "試乗はできますか？",
            "acceptedAnswer": {"@type":"Answer","text":"はい、在庫車両のほとんどで試乗可能です。お気軽にスタッフまでお申し付けください。運転免許証をお持ちいただければすぐにご対応します。"}
        },
        {
            "@type": "Question",
            "name": "県外への納車はできますか？",
            "acceptedAnswer": {"@type":"Answer","text":"全国どこでも納車対応しております。陸送費は距離によって異なりますが、詳しくはお問い合わせください。"}
        },
        {
            "@type": "Question",
            "name": "ローンの審査は難しいですか？",
            "acceptedAnswer": {"@type":"Answer","text":"複数の提携ローン会社と提携しており、他社で断られた方でもご相談いただける場合があります。まずはお気軽にご相談ください。"}
        },
        {
            "@type": "Question",
            "name": "車の下取りはできますか？",
            "acceptedAnswer": {"@type":"Answer","text":"はい、メーカー・年式・走行距離を問わず、どのようなお車でも査定いたします。他社の見積もりがある場合はぜひご提示ください。"}
        },
        {
            "@type": "Question",
            "name": "購入後のメンテナンスはお願いできますか？",
            "acceptedAnswer": {"@type":"Answer","text":"もちろんです。自社整備工場にて車検・点検・修理・板金塗装まで幅広く承っております。購入後も長くお付き合いいただけます。"}
        },
        {
            "@type": "Question",
            "name": "土日・祝日も営業していますか？",
            "acceptedAnswer": {"@type":"Answer","text":"土曜日・日曜日・祝日も営業しております（11:00〜21:00）。木曜日および第3日曜日が定休日となっております。"}
        }
    ]
}
</script>
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        {"@type":"ListItem","position":1,"name":"ホーム","item":"{{ url('/') }}"},
        {"@type":"ListItem","position":2,"name":"店舗情報・アクセス","item":"{{ route('store') }}"}
    ]
}
</script>
@endsection

@section('content')

{{-- ============================================================
     ページヒーロー
     ============================================================ --}}
<div class="store-hero">
    <div class="container">
        <nav class="breadcrumb">
            <span><a href="{{ route('home') }}">ホーム</a></span>
            <span>店舗情報・アクセス</span>
        </nav>
        <div class="store-hero-content">
            <p class="hero-eyebrow">STORE INFO</p>
            <h1 class="store-hero-title">店舗情報・アクセス</h1>
            <p class="store-hero-sub">{{ config('app.name') }}へのアクセス方法・営業時間のご案内</p>
            <div class="store-hero-stats">
                <div class="store-hero-stat">
                    <strong>地域密着</strong><span>尼崎市下坂部</span>
                </div>
                <div class="store-hero-stat-divider"></div>
                <div class="store-hero-stat">
                    <strong>常時 100台+</strong><span>在庫車両</span>
                </div>
                <div class="store-hero-stat-divider"></div>
                <div class="store-hero-stat">
                    <strong>全国対応</strong><span>陸送納車</span>
                </div>
                <div class="store-hero-stat-divider"></div>
                <div class="store-hero-stat">
                    <strong>無料</strong><span>駐車場完備</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     クイック情報バー
     ============================================================ --}}
<div class="store-quick-bar">
    <div class="container">
        <div class="store-quick-inner">
            <div class="store-quick-item">
                <span class="store-quick-icon">📞</span>
                <div>
                    <p class="store-quick-label">お電話でのお問い合わせ</p>
                    <a href="tel:06-4960-8765" class="store-quick-tel">06-4960-8765</a>
                </div>
            </div>
            <div class="store-quick-divider"></div>
            <div class="store-quick-item">
                <span class="store-quick-icon">🕐</span>
                <div>
                    <p class="store-quick-label">営業時間</p>
                    <p class="store-quick-val">月〜水・金〜日 11:00〜21:00 <span class="store-holiday-badge">木曜・第3日曜定休</span></p>
                </div>
            </div>
            <div class="store-quick-divider"></div>
            <div class="store-quick-item">
                <span class="store-quick-icon">📍</span>
                <div>
                    <p class="store-quick-label">所在地</p>
                    <p class="store-quick-val">〒661-0975 兵庫県尼崎市下坂部4丁目5-1</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     メインコンテンツ
     ============================================================ --}}
<div class="container store-main-wrap">

    {{-- ① 店舗概要 + 営業時間 --}}
    <div class="store-section-grid">

        {{-- 店舗基本情報 --}}
        <section class="store-card">
            <h2 class="store-card-title">
                <span class="store-card-title-badge">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="15" height="15"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </span>
                店舗基本情報
            </h2>
            <table class="store-info-table">
                <tr>
                    <th>店舗名</th>
                    <td><strong>{{ config('app.name') }}</strong></td>
                </tr>
                <tr>
                    <th>住所</th>
                    <td>
                        〒661-0975<br>
                        兵庫県尼崎市下坂部4丁目5-1<br>
                        <a href="#map" class="store-map-link">地図で確認する ↓</a>
                    </td>
                </tr>
                <tr>
                    <th>電話番号</th>
                    <td>
                        <a href="tel:06-4960-8765" class="store-tel-link">06-4960-8765</a>
                        <span class="store-info-note">（木曜・第3日曜日を除く 11:00〜21:00）</span>
                    </td>
                </tr>
                <tr>
                    <th>メール</th>
                    <td><a href="{{ route('contact.index') }}" class="store-link">お問い合わせフォームへ →</a></td>
                </tr>
                <tr>
                    <th>古物商許可</th>
                    <td>兵庫県公安委員会許可</td>
                </tr>
                <tr>
                    <th>駐車場</th>
                    <td>あり（無料）</td>
                </tr>
            </table>
        </section>

        {{-- 営業時間 --}}
        <section class="store-card">
            <h2 class="store-card-title">
                <span class="store-card-title-badge">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="15" height="15"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </span>
                営業時間
            </h2>
            <div class="store-hours-calendar">
                @php
                    $todayDow = \Carbon\Carbon::now()->dayOfWeek; // 0=Sun,1=Mon...
                    $days = [
                        ['label'=>'月','dow'=>1,'hours'=>'11:00〜21:00','open'=>true],
                        ['label'=>'火','dow'=>2,'hours'=>'11:00〜21:00','open'=>true],
                        ['label'=>'水','dow'=>3,'hours'=>'11:00〜21:00','open'=>true],
                        ['label'=>'木','dow'=>4,'hours'=>'定休日','open'=>false],
                        ['label'=>'金','dow'=>5,'hours'=>'11:00〜21:00','open'=>true],
                        ['label'=>'土','dow'=>6,'hours'=>'11:00〜21:00','open'=>true],
                        ['label'=>'日','dow'=>0,'hours'=>'11:00〜21:00','open'=>true],
                    ];
                @endphp
                <div class="hours-week">
                    @foreach($days as $day)
                        <div class="hours-day {{ !$day['open'] ? 'hours-day-closed' : '' }} {{ $todayDow === $day['dow'] ? 'hours-day-today' : '' }}">
                            <span class="hours-day-label">{{ $day['label'] }}</span>
                            @if($todayDow === $day['dow'])
                                <span class="hours-today-badge">今日</span>
                            @endif
                            <span class="hours-time {{ !$day['open'] ? 'hours-time-closed' : '' }}">{{ $day['hours'] }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="store-hours-note">
                    <div class="store-hours-note-item">
                        <span>📌</span><span>祝日・年末年始・GW等は営業時間が変わる場合があります</span>
                    </div>
                    <div class="store-hours-note-item">
                        <span>📌</span><span>試乗・商談は予約なしでもご来店いただけます</span>
                    </div>
                    <div class="store-hours-note-item">
                        <span>📌</span><span>お電話・フォームにて時間外のご相談も受け付けます</span>
                    </div>
                </div>
            </div>
        </section>
    </div>

    {{-- ② アクセスマップ --}}
    <section class="store-card" id="map">
        <h2 class="store-card-title">
            <span class="store-card-title-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="15" height="15"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </span>
            アクセスマップ
        </h2>
        <div class="store-map-wrap">
            <iframe
                src="https://maps.google.com/maps?q=兵庫県尼崎市下坂部4丁目5-1&output=embed&z=16&hl=ja"
                width="100%" height="420"
                style="border:0;display:block;"
                allowfullscreen loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
        <div class="store-map-actions">
            <a href="https://maps.google.com/?q=兵庫県尼崎市下坂部4丁目5-1" target="_blank" rel="noopener noreferrer" class="store-map-btn store-map-btn-google">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                Google マップで開く
            </a>
            <a href="https://maps.apple.com/?q=兵庫県尼崎市下坂部4丁目5-1" target="_blank" rel="noopener noreferrer" class="store-map-btn store-map-btn-apple">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/></svg>
                Apple マップで開く
            </a>
        </div>
    </section>

    {{-- ③ アクセス方法 --}}
    <section class="store-card">
        <h2 class="store-card-title">
            <span class="store-card-title-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="15" height="15"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
            </span>
            アクセス方法
        </h2>
        <div class="store-access-grid">
            <div class="store-access-item">
                <div class="store-access-icon store-access-car">🚗</div>
                <div class="store-access-body">
                    <h3 class="store-access-title">お車でお越しの方</h3>
                    <p class="store-access-text">
                        阪神高速11号池田線「尼崎東」出口より車で約5分。<br>
                        国道2号線から下坂部方面へお越しください。<br>
                        <strong>駐車場：無料完備</strong>
                    </p>
                </div>
            </div>
            <div class="store-access-item">
                <div class="store-access-icon store-access-train">🚉</div>
                <div class="store-access-body">
                    <h3 class="store-access-title">電車でお越しの方</h3>
                    <p class="store-access-text">
                        JR東西線「尼崎」駅よりタクシーで約10分<br>
                        阪神本線「尼崎」駅よりタクシーで約12分<br>
                        <strong>送迎サービス：事前にご連絡いただければ駅までお迎えに参ります</strong>
                    </p>
                </div>
            </div>
            <div class="store-access-item">
                <div class="store-access-icon store-access-bus">🚌</div>
                <div class="store-access-body">
                    <h3 class="store-access-title">バスでお越しの方</h3>
                    <p class="store-access-text">
                        阪神バス「下坂部」停留所より徒歩約3分<br>
                        阪神「尼崎」駅北口より阪神バスご利用ください
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ④ スタッフ紹介 --}}
    <section class="store-card">
        <h2 class="store-card-title">
            <span class="store-card-title-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="15" height="15"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87m-4-12a4 4 0 010 7.75"/></svg>
            </span>
            スタッフ紹介
        </h2>
        <div class="store-staff-grid">
            <div class="store-staff-card store-staff-card--manager">
                <div class="store-staff-avatar store-staff-avatar--manager"><span class="store-staff-avatar-icon">👨‍💼</span></div>
                <div class="store-staff-info">
                    <p class="store-staff-role">店長</p>
                    <p class="store-staff-name">麻田 太郎</p>
                    <p class="store-staff-message">「お客様一人ひとりに合った最高の一台をご提案します。気になることは何でもお気軽にご相談ください！」</p>
                    <div class="store-staff-tags">
                        <span class="store-staff-tag">国産車専門</span>
                        <span class="store-staff-tag">査定歴15年</span>
                        <span class="store-staff-tag">ファミリーカー得意</span>
                    </div>
                </div>
            </div>
            <div class="store-staff-card store-staff-card--sub">
                <div class="store-staff-avatar store-staff-avatar--sub"><span class="store-staff-avatar-icon">👩‍💼</span></div>
                <div class="store-staff-info">
                    <p class="store-staff-role">副店長</p>
                    <p class="store-staff-name">山田 花子</p>
                    <p class="store-staff-message">「女性スタッフとして、はじめて車を購入される方も安心してご相談いただける環境づくりを大切にしています。」</p>
                    <div class="store-staff-tags">
                        <span class="store-staff-tag">軽自動車専門</span>
                        <span class="store-staff-tag">ローン相談</span>
                        <span class="store-staff-tag">初めての方歓迎</span>
                    </div>
                </div>
            </div>
            <div class="store-staff-card store-staff-card--mechanic">
                <div class="store-staff-avatar store-staff-avatar--mechanic"><span class="store-staff-avatar-icon">🧑‍🔧</span></div>
                <div class="store-staff-info">
                    <p class="store-staff-role">整備士</p>
                    <p class="store-staff-name">佐藤 健一</p>
                    <p class="store-staff-message">「全車両を隅々まで点検し、安心してお乗りいただける状態でご納車します。整備のことなら何でも聞いてください。」</p>
                    <div class="store-staff-tags">
                        <span class="store-staff-tag">二級整備士</span>
                        <span class="store-staff-tag">整備歴20年</span>
                        <span class="store-staff-tag">車検・修理</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ⑤ 選ばれる理由 --}}
    <section class="store-card store-card-light">
        <h2 class="store-card-title">
            <span class="store-card-title-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="15" height="15"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
            </span>
            {{ config('app.name') }}が選ばれる理由
        </h2>
        <div class="store-appeal-grid">
            <div class="store-appeal-item">
                <div class="store-appeal-num">01</div>
                <div class="store-appeal-icon">🔍</div>
                <h3 class="store-appeal-title">第三者機関による車両検査</h3>
                <p class="store-appeal-body">全在庫車両を第三者検査機関による厳格な検査を実施。修復歴・走行距離・内外装の状態を透明に公開しています。</p>
            </div>
            <div class="store-appeal-item">
                <div class="store-appeal-num">02</div>
                <div class="store-appeal-icon">💰</div>
                <h3 class="store-appeal-title">総額表示で安心のお買い物</h3>
                <p class="store-appeal-body">諸費用・税金・保険を含んだ「総額表示」を徹底。後から追加費用が発生しない、明瞭会計でご安心いただけます。</p>
            </div>
            <div class="store-appeal-item">
                <div class="store-appeal-num">03</div>
                <div class="store-appeal-icon">🛡️</div>
                <h3 class="store-appeal-title">充実の保証制度</h3>
                <p class="store-appeal-body">納車後3ヶ月・走行5,000kmの無料保証が標準付帯。有料延長保証（最大3年）もご用意しています。</p>
            </div>
            <div class="store-appeal-item">
                <div class="store-appeal-num">04</div>
                <div class="store-appeal-icon">🏦</div>
                <h3 class="store-appeal-title">豊富なローン・支払い方法</h3>
                <p class="store-appeal-body">複数の提携ローン会社で低金利プランをご用意。頭金0円・最長84回払いにも対応。審査は最短即日回答。</p>
            </div>
            <div class="store-appeal-item">
                <div class="store-appeal-num">05</div>
                <div class="store-appeal-icon">🔧</div>
                <h3 class="store-appeal-title">自社整備工場完備</h3>
                <p class="store-appeal-body">自社工場で整備・車検・板金まで一括対応。購入後のアフターフォローも安心してお任せください。</p>
            </div>
            <div class="store-appeal-item">
                <div class="store-appeal-num">06</div>
                <div class="store-appeal-icon">🚙</div>
                <h3 class="store-appeal-title">下取り・強化買取</h3>
                <p class="store-appeal-body">乗り換え時の下取りから単純買取まで、他社より高く買い取れる理由をご説明します。まずはご相談を。</p>
            </div>
        </div>
    </section>

    {{-- ⑥ よくある質問 --}}
    <section class="store-card">
        <h2 class="store-card-title">
            <span class="store-card-title-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="15" height="15"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </span>
            よくあるご質問
        </h2>
        <div class="store-faq-list">
            @php
            $faqs = [
                ['q'=>'試乗はできますか？', 'a'=>'はい、在庫車両のほとんどで試乗可能です。お気軽にスタッフまでお申し付けください。運転免許証をお持ちいただければすぐにご対応します。'],
                ['q'=>'県外への納車はできますか？', 'a'=>'全国どこでも納車対応しております。陸送費は距離によって異なりますが、詳しくはお問い合わせください。'],
                ['q'=>'ローンの審査は難しいですか？', 'a'=>'複数の提携ローン会社と提携しており、他社で断られた方でもご相談いただける場合があります。まずはお気軽にご相談ください。'],
                ['q'=>'車の下取りはできますか？', 'a'=>'はい、メーカー・年式・走行距離を問わず、どのようなお車でも査定いたします。他社の見積もりがある場合はぜひご提示ください。'],
                ['q'=>'購入後のメンテナンスはお願いできますか？', 'a'=>'もちろんです。自社整備工場にて車検・点検・修理・板金塗装まで幅広く承っております。購入後も長くお付き合いいただけます。'],
                ['q'=>'土日・祝日も営業していますか？', 'a'=>'土曜日・日曜日・祝日も営業しております（11:00〜21:00）。木曜日および第3日曜日が定休日となっております。'],
            ];
            @endphp
            @foreach($faqs as $i => $faq)
            <div class="store-faq-item" x-data="{ open: false }">
                <button class="store-faq-q" @click="open = !open" :class="{ 'store-faq-q-open': open }">
                    <span class="store-faq-icon">Q</span>
                    <span>{{ $faq['q'] }}</span>
                    <svg class="store-faq-arrow" :class="{ 'store-faq-arrow-open': open }"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="18" height="18">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div class="store-faq-a" x-show="open" x-transition style="display:none;">
                    <span class="store-faq-a-icon">A</span>
                    <p>{{ $faq['a'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    {{-- ⑦ お問い合わせCTA --}}
    <section class="store-cta">
        <div class="store-cta-inner">
            <div class="store-cta-text">
                <h2 class="store-cta-title">気になることはお気軽にご相談ください</h2>
                <p class="store-cta-sub">電話・フォームどちらでもご対応しております。お見積もり・在庫確認・試乗予約もお気軽に。</p>
            </div>
            <div class="store-cta-btns">
                <a href="tel:06-4960-8765" class="store-cta-tel-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20">
                        <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 10.8 19.79 19.79 0 01.1 2.18 2 2 0 012.1 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
                    </svg>
                    <span>
                        06-4960-8765
                        <small>受付時間 11:00〜21:00（木曜・第3日曜除く）</small>
                    </span>
                </a>
                <a href="{{ route('contact.index') }}" class="btn-primary" style="gap:8px;">
                    メールでお問い合わせ
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
            </div>
        </div>
    </section>

</div>{{-- /container --}}

@endsection
