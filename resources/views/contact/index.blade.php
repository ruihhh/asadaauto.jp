@extends('layouts.site')

@section('title', 'お問い合わせ｜尼崎・兵庫の中古車')
@section('meta_description', '中古車の在庫確認・見積もり・試乗予約・査定など、お気軽にお問い合わせください。電話06-4960-8765でも受付中（11:00〜21:00）。')
@section('canonical', route('contact.index'))

@section('structured_data')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        {"@type":"ListItem","position":1,"name":"ホーム","item":"{{ url('/') }}"},
        {"@type":"ListItem","position":2,"name":"お問い合わせ","item":"{{ route('contact.index') }}"}
    ]
}
</script>
@endsection

@section('content')
<main class="container container-narrow" style="padding-top: 36px; padding-bottom: 36px;">
    <h2 class="page-title"><span class="page-title-underline">お問い合わせ</span></h2>

    <p style="color: var(--muted); margin-bottom: 24px;">
        車両の状態、お見積り、現車確認のご予約など、お気軽にお問い合わせください。<br>
        <span style="color: #e53935; font-weight: 700;">*</span> は必須項目です。
    </p>

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

    <div class="card card-lg">
        <form action="{{ route('contact.send') }}" method="POST">
            @csrf

            @if(isset($car))
                <div class="car-info-box">
                    <p class="label">お問い合わせ対象車両</p>
                    <p class="name">{{ $car->make }} {{ $car->model }}</p>
                    <p class="stock">在庫番号: {{ $car->stock_no }}</p>
                    <input type="hidden" name="stock_no" value="{{ $car->stock_no }}">
                </div>
            @else
                <div class="form-group">
                    <label class="form-label" for="stock_no">
                        在庫番号 <span class="hint">(車種をご存知の場合はご記入ください)</span>
                    </label>
                    <input class="form-input" id="stock_no" type="text" name="stock_no" value="{{ old('stock_no', $stock_no) }}" placeholder="例: AA-123">
                </div>
            @endif

            <div class="form-grid form-group">
                <div>
                    <label class="form-label" for="name">
                        お名前 <span class="required">*</span>
                    </label>
                    <input class="form-input" id="name" type="text" name="name" value="{{ old('name') }}" required placeholder="山田 太郎">
                </div>

                <div>
                    <label class="form-label" for="phone">
                        電話番号
                    </label>
                    <input class="form-input" id="phone" type="tel" name="phone" value="{{ old('phone') }}" placeholder="090-0000-0000">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">
                    メールアドレス <span class="required">*</span>
                </label>
                <input class="form-input" id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="example@asadaauto.jp">
            </div>

            <div class="form-group" style="margin-bottom: 28px;">
                <label class="form-label" for="message">
                    お問い合わせ内容 <span class="required">*</span>
                </label>
                <textarea class="form-input" id="message" name="message" required placeholder="ご質問やご要望をご記入ください。" style="height: 160px; resize: vertical;">{{ old('message') }}</textarea>
            </div>

            <button class="btn-primary" type="submit" style="width: 100%;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewbox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
                お問い合わせ内容を送信する
            </button>
        </form>
    </div>
</main>
@endsection
