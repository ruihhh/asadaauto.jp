@extends('layouts.site')

@section('title', '買取査定のお申し込みを受け付けました')
@section('meta_robots', 'noindex, nofollow')

@section('content')
<div class="container container-narrow" style="padding-top:60px;padding-bottom:60px;text-align:center;">
    <div class="success-icon" style="width:80px;height:80px;border-radius:50%;background:#fdf1f1;color:var(--red);display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-size:36px;">
        ✅
    </div>
    <h1 style="font-size:1.6rem;font-weight:900;margin:0 0 12px;">査定のお申し込みを受け付けました</h1>
    <p style="color:var(--muted);font-size:14px;margin:0 0 32px;line-height:1.8;">
        ご依頼いただきありがとうございます。<br>
        担当スタッフより折り返しご連絡いたします。しばらくお待ちください。<br>
        <span style="color:var(--red);font-weight:700;">※ 自動返信メールをご確認ください（届かない場合は迷惑メールフォルダをご確認ください）</span>
    </p>
    <div class="panel" style="text-align:left;margin-bottom:32px;">
        <h2 style="font-size:15px;font-weight:900;margin:0 0 14px;padding-bottom:10px;border-bottom:2px solid var(--red);">次のステップ</h2>
        <ol style="margin:0;padding-left:20px;line-height:2;font-size:13px;color:var(--muted);">
            <li>担当スタッフよりお電話またはメールにてご連絡します</li>
            <li>お車の状態を確認し、査定額をご提示します</li>
            <li>査定額にご納得いただけましたら売却手続きへ進みます</li>
        </ol>
    </div>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
        <a href="{{ route('home') }}" class="btn-secondary">トップページへ戻る</a>
        <a href="{{ route('cars.index') }}" class="btn-primary">在庫車両を見る &rsaquo;</a>
    </div>
</div>
@endsection
