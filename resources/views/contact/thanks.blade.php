@extends('layouts.site')

@section('title', '送信完了')
@section('meta_robots', 'noindex, nofollow')

@section('content')
<main class="container container-narrow text-center" style="padding-top: 64px; padding-bottom: 64px;">
    <div class="card card-lg">
        <div class="success-icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewbox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h2 class="page-title" style="margin-bottom: 18px;">お問い合わせ内容を<br>送信しました</h2>

        <p style="color: var(--muted); font-size: 1.05rem; line-height: 1.8; margin-bottom: 36px;">
            お問い合わせありがとうございます。<br>
            内容を確認の上、担当者より折り返しご連絡させていただきます。<br>
            今しばらくお待ちください。
        </p>

        <a href="{{ route('home') }}" class="btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewbox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            トップページへ戻る
        </a>
    </div>
</main>
@endsection
