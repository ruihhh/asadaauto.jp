<!DOCTYPE html>
<html>
<body>
    <h2>お問い合わせがありました</h2>
    
    <p><strong>日時:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>
    
    <h3>お客様情報</h3>
    <ul>
        <li><strong>お名前:</strong> {{ $data['name'] }}</li>
        <li><strong>メールアドレス:</strong> {{ $data['email'] }}</li>
        <li><strong>電話番号:</strong> {{ $data['phone'] ?? 'なし' }}</li>
    </ul>

    @if(isset($data['stock_no']))
    <h3>対象車両</h3>
    <p>在庫番号: {{ $data['stock_no'] }}</p>
    @endif

    <h3>お問い合わせ内容</h3>
    <p>{!! nl2br(e($data['message'])) !!}</p>
</body>
</html>
