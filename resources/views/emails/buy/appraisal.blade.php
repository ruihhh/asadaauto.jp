<!DOCTYPE html>
<html lang="ja">
<head><meta charset="utf-8"><title>買取査定依頼</title></head>
<body style="font-family: sans-serif; font-size: 14px; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">

<h2 style="background: #D4140F; color: #fff; padding: 16px 20px; margin: 0 0 24px; font-size: 18px;">
    【{{ config('app.name') }}】買取査定のご依頼
</h2>

<p>以下の内容で買取査定のご依頼が届きました。</p>

<h3 style="border-left: 4px solid #D4140F; padding-left: 10px; margin: 24px 0 12px; font-size: 15px;">🚗 お車の情報</h3>
<table style="width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 24px;">
    <tr>
        <th style="background: #f8f8f8; padding: 8px 12px; text-align: left; border: 1px solid #ddd; width: 120px;">メーカー</th>
        <td style="padding: 8px 12px; border: 1px solid #ddd;">{{ $data['make'] }}</td>
    </tr>
    <tr>
        <th style="background: #f8f8f8; padding: 8px 12px; text-align: left; border: 1px solid #ddd;">車名</th>
        <td style="padding: 8px 12px; border: 1px solid #ddd;">{{ $data['model'] }}</td>
    </tr>
    @if(!empty($data['grade']))
    <tr>
        <th style="background: #f8f8f8; padding: 8px 12px; text-align: left; border: 1px solid #ddd;">グレード</th>
        <td style="padding: 8px 12px; border: 1px solid #ddd;">{{ $data['grade'] }}</td>
    </tr>
    @endif
    <tr>
        <th style="background: #f8f8f8; padding: 8px 12px; text-align: left; border: 1px solid #ddd;">年式</th>
        <td style="padding: 8px 12px; border: 1px solid #ddd;">{{ $data['model_year'] }}年式</td>
    </tr>
    <tr>
        <th style="background: #f8f8f8; padding: 8px 12px; text-align: left; border: 1px solid #ddd;">走行距離</th>
        <td style="padding: 8px 12px; border: 1px solid #ddd;">{{ number_format($data['mileage']) }} km</td>
    </tr>
    @if(!empty($data['color']))
    <tr>
        <th style="background: #f8f8f8; padding: 8px 12px; text-align: left; border: 1px solid #ddd;">車体色</th>
        <td style="padding: 8px 12px; border: 1px solid #ddd;">{{ $data['color'] }}</td>
    </tr>
    @endif
    <tr>
        <th style="background: #f8f8f8; padding: 8px 12px; text-align: left; border: 1px solid #ddd;">車両状態</th>
        <td style="padding: 8px 12px; border: 1px solid #ddd;">
            {{ match($data['condition']) { 'good' => '良好', 'normal' => '普通', 'damaged' => '傷・凹みあり', default => $data['condition'] } }}
        </td>
    </tr>
</table>

<h3 style="border-left: 4px solid #D4140F; padding-left: 10px; margin: 24px 0 12px; font-size: 15px;">👤 お客様情報</h3>
<table style="width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 24px;">
    <tr>
        <th style="background: #f8f8f8; padding: 8px 12px; text-align: left; border: 1px solid #ddd; width: 120px;">お名前</th>
        <td style="padding: 8px 12px; border: 1px solid #ddd;">{{ $data['name'] }}</td>
    </tr>
    <tr>
        <th style="background: #f8f8f8; padding: 8px 12px; text-align: left; border: 1px solid #ddd;">電話番号</th>
        <td style="padding: 8px 12px; border: 1px solid #ddd;">{{ $data['phone'] }}</td>
    </tr>
    <tr>
        <th style="background: #f8f8f8; padding: 8px 12px; text-align: left; border: 1px solid #ddd;">メール</th>
        <td style="padding: 8px 12px; border: 1px solid #ddd;">{{ $data['email'] }}</td>
    </tr>
    @if(!empty($data['zip']))
    <tr>
        <th style="background: #f8f8f8; padding: 8px 12px; text-align: left; border: 1px solid #ddd;">郵便番号</th>
        <td style="padding: 8px 12px; border: 1px solid #ddd;">〒{{ $data['zip'] }}</td>
    </tr>
    @endif
    @if(!empty($data['message']))
    <tr>
        <th style="background: #f8f8f8; padding: 8px 12px; text-align: left; border: 1px solid #ddd;">備考</th>
        <td style="padding: 8px 12px; border: 1px solid #ddd; white-space: pre-wrap;">{{ $data['message'] }}</td>
    </tr>
    @endif
</table>

<p style="font-size: 12px; color: #999; border-top: 1px solid #ddd; padding-top: 16px; margin-top: 24px;">
    このメールは {{ config('app.name') }} の買取査定フォームから自動送信されました。<br>
    受付日時: {{ now()->format('Y年m月d日 H:i') }}
</p>
</body>
</html>
