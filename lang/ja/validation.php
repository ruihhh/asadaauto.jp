<?php

return [

    /*
    |--------------------------------------------------------------------------
    | バリデーションのエラーメッセージ（日本語）
    |--------------------------------------------------------------------------
    |
    | Laravel 12 の全ルール分。フォームの欄の直下に出す前提で、
    | 「何をすればよいか」がわかる文にしている（です・ます）。
    |
    */

    'accepted' => ':attributeに同意してください。',
    'accepted_if' => ':otherが:valueの場合は、:attributeに同意してください。',
    'active_url' => ':attributeには正しいURLを入力してください。',
    'after' => ':attributeには:dateより後の日付を入力してください。',
    'after_or_equal' => ':attributeには:date以降の日付を入力してください。',
    'alpha' => ':attributeは文字だけで入力してください。',
    'alpha_dash' => ':attributeは英数字・ハイフン（-）・アンダースコア（_）だけで入力してください。',
    'alpha_num' => ':attributeは英数字だけで入力してください。',
    'any_of' => ':attributeの内容が正しくありません。',
    'array' => ':attributeの形式が正しくありません。',
    'ascii' => ':attributeは半角の英数字と記号だけで入力してください。',
    'before' => ':attributeには:dateより前の日付を入力してください。',
    'before_or_equal' => ':attributeには:date以前の日付を入力してください。',
    'between' => [
        'array' => ':attributeは:min〜:max個の範囲で指定してください。',
        'file' => ':attributeは:min〜:maxKBのファイルにしてください。',
        'numeric' => ':attributeは:min〜:maxの範囲で入力してください。',
        'string' => ':attributeは:min〜:max文字で入力してください。',
    ],
    'boolean' => ':attributeの内容が正しくありません。',
    'can' => ':attributeに使えない値が含まれています。',
    'confirmed' => ':attributeが確認用の入力と一致しません。',
    'contains' => ':attributeに必要な値が含まれていません。',
    'current_password' => 'パスワードが正しくありません。',
    'date' => ':attributeには正しい日付を入力してください。',
    'date_equals' => ':attributeには:dateと同じ日付を入力してください。',
    'date_format' => ':attributeは「:format」の形式で入力してください。',
    'decimal' => ':attributeは小数点以下:decimal桁で入力してください。',
    'declined' => ':attributeは「いいえ」を選んでください。',
    'declined_if' => ':otherが:valueの場合は、:attributeは「いいえ」を選んでください。',
    'different' => ':attributeには:otherと違う値を入力してください。',
    'digits' => ':attributeは:digits桁の半角数字で入力してください。',
    'digits_between' => ':attributeは:min〜:max桁の半角数字で入力してください。',
    'dimensions' => ':attributeの画像の縦横のサイズが正しくありません。',
    'distinct' => ':attributeに同じ値が重複しています。',
    'doesnt_contain' => ':attributeには次の値を含めないでください（:values）。',
    'doesnt_end_with' => ':attributeの末尾を次のいずれかにしないでください（:values）。',
    'doesnt_start_with' => ':attributeの先頭を次のいずれかにしないでください（:values）。',
    'email' => ':attributeの形式が正しくありません（例：taro@example.com）。',
    'encoding' => ':attributeの文字コードは:encodingにしてください。',
    'ends_with' => ':attributeの末尾は次のいずれかにしてください（:values）。',
    'enum' => ':attributeは選択肢の中から選んでください。',
    'exists' => '入力された:attributeは見つかりませんでした。わからない場合は空欄のままお送りください。',
    'extensions' => ':attributeは次の形式のファイルにしてください（:values）。',
    'file' => ':attributeにはファイルを指定してください。',
    'filled' => ':attributeを入力してください。',
    'gt' => [
        'array' => ':attributeは:value個より多く指定してください。',
        'file' => ':attributeは:valueKBより大きいファイルにしてください。',
        'numeric' => ':attributeには:valueより大きい数を入力してください。',
        'string' => ':attributeは:value文字より多く入力してください。',
    ],
    'gte' => [
        'array' => ':attributeは:value個以上指定してください。',
        'file' => ':attributeは:valueKB以上のファイルにしてください。',
        'numeric' => ':attributeには:value以上の数を入力してください。',
        'string' => ':attributeは:value文字以上で入力してください。',
    ],
    'hex_color' => ':attributeには正しいカラーコード（例：#1F2A44）を入力してください。',
    'image' => ':attributeには画像ファイルを指定してください。',
    'in' => ':attributeは選択肢の中から選んでください。',
    'in_array' => ':attributeは:otherに含まれる値にしてください。',
    'in_array_keys' => ':attributeには次のいずれかの項目を含めてください（:values）。',
    'integer' => ':attributeは半角数字で入力してください。',
    'ip' => ':attributeには正しいIPアドレスを入力してください。',
    'ipv4' => ':attributeには正しいIPv4アドレスを入力してください。',
    'ipv6' => ':attributeには正しいIPv6アドレスを入力してください。',
    'json' => ':attributeには正しいJSON形式の文字列を入力してください。',
    'list' => ':attributeの形式が正しくありません。',
    'lowercase' => ':attributeは小文字で入力してください。',
    'lt' => [
        'array' => ':attributeは:value個より少なくしてください。',
        'file' => ':attributeは:valueKBより小さいファイルにしてください。',
        'numeric' => ':attributeには:valueより小さい数を入力してください。',
        'string' => ':attributeは:value文字より少なく入力してください。',
    ],
    'lte' => [
        'array' => ':attributeは:value個以下にしてください。',
        'file' => ':attributeは:valueKB以下のファイルにしてください。',
        'numeric' => ':attributeには:value以下の数を入力してください。',
        'string' => ':attributeは:value文字以内で入力してください。',
    ],
    'mac_address' => ':attributeには正しいMACアドレスを入力してください。',
    'max' => [
        'array' => ':attributeは:max個以下にしてください。',
        'file' => ':attributeは:maxKB以下のファイルにしてください。',
        'numeric' => ':attributeには:max以下の数を入力してください。',
        'string' => ':attributeは:max文字以内で入力してください。',
    ],
    'max_digits' => ':attributeは:max桁以内で入力してください。',
    'mimes' => ':attributeは次の形式のファイルにしてください（:values）。',
    'mimetypes' => ':attributeは次の形式のファイルにしてください（:values）。',
    'min' => [
        'array' => ':attributeは:min個以上指定してください。',
        'file' => ':attributeは:minKB以上のファイルにしてください。',
        'numeric' => ':attributeには:min以上の数を入力してください。',
        'string' => ':attributeは:min文字以上で入力してください。',
    ],
    'min_digits' => ':attributeは:min桁以上で入力してください。',
    'missing' => ':attributeは入力しないでください。',
    'missing_if' => ':otherが:valueの場合は、:attributeは入力しないでください。',
    'missing_unless' => ':otherが:valueでない場合は、:attributeは入力しないでください。',
    'missing_with' => ':valuesを入力した場合は、:attributeは入力しないでください。',
    'missing_with_all' => ':valuesをすべて入力した場合は、:attributeは入力しないでください。',
    'multiple_of' => ':attributeには:valueの倍数を入力してください。',
    'not_in' => '入力された:attributeは使えません。',
    'not_regex' => ':attributeの形式が正しくありません。',
    'numeric' => ':attributeは半角数字で入力してください。',
    'password' => [
        'letters' => ':attributeには英字を1文字以上含めてください。',
        'mixed' => ':attributeには英大文字と英小文字をそれぞれ1文字以上含めてください。',
        'numbers' => ':attributeには数字を1文字以上含めてください。',
        'symbols' => ':attributeには記号を1文字以上含めてください。',
        'uncompromised' => 'この:attributeは過去に流出したことがあるため使えません。別の:attributeにしてください。',
    ],
    'present' => ':attributeの項目が必要です。',
    'present_if' => ':otherが:valueの場合は、:attributeの項目が必要です。',
    'present_unless' => ':otherが:valueでない場合は、:attributeの項目が必要です。',
    'present_with' => ':valuesがある場合は、:attributeの項目が必要です。',
    'present_with_all' => ':valuesがすべてある場合は、:attributeの項目が必要です。',
    'prohibited' => ':attributeは入力できません。',
    'prohibited_if' => ':otherが:valueの場合は、:attributeは入力できません。',
    'prohibited_if_accepted' => ':otherに同意した場合は、:attributeは入力できません。',
    'prohibited_if_declined' => ':otherに同意しない場合は、:attributeは入力できません。',
    'prohibited_unless' => ':otherが:valuesのいずれかでない場合は、:attributeは入力できません。',
    'prohibits' => ':attributeを入力した場合は、:otherは入力できません。',
    'regex' => ':attributeの形式が正しくありません。',
    'required' => ':attributeを入力してください。',
    'required_array_keys' => ':attributeには次の項目を含めてください（:values）。',
    'required_if' => ':otherが:valueの場合は、:attributeを入力してください。',
    'required_if_accepted' => ':otherに同意した場合は、:attributeを入力してください。',
    'required_if_declined' => ':otherに同意しない場合は、:attributeを入力してください。',
    'required_unless' => ':otherが:valuesのいずれかでない場合は、:attributeを入力してください。',
    'required_with' => ':valuesを入力した場合は、:attributeも入力してください。',
    'required_with_all' => ':valuesをすべて入力した場合は、:attributeも入力してください。',
    'required_without' => ':valuesを入力しない場合は、:attributeを入力してください。',
    'required_without_all' => ':valuesのどれも入力しない場合は、:attributeを入力してください。',
    'same' => ':attributeと:otherが一致しません。',
    'size' => [
        'array' => ':attributeは:size個にしてください。',
        'file' => ':attributeは:sizeKBのファイルにしてください。',
        'numeric' => ':attributeには:sizeを入力してください。',
        'string' => ':attributeは:size文字で入力してください。',
    ],
    'starts_with' => ':attributeの先頭は次のいずれかにしてください（:values）。',
    'string' => ':attributeは文字で入力してください。',
    'timezone' => ':attributeには正しいタイムゾーンを入力してください。',
    'unique' => 'この:attributeはすでに使われています。',
    'uploaded' => ':attributeのアップロードに失敗しました。もう一度お試しください。',
    'uppercase' => ':attributeは大文字で入力してください。',
    'url' => ':attributeには正しいURL（例：https://example.com）を入力してください。',
    'ulid' => ':attributeには正しいULIDを入力してください。',
    'uuid' => ':attributeには正しいUUIDを入力してください。',

    /*
    |--------------------------------------------------------------------------
    | 項目ごとのメッセージ
    |--------------------------------------------------------------------------
    */

    'custom' => [
        // 買取査定：お車の状態は3択で選ぶ
        'condition' => [
            'required' => 'お車の状態を選んでください。',
            'in' => 'お車の状態は選択肢の中から選んでください。',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 項目名（:attribute に入る日本語）
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        // お問い合わせ・買取査定フォーム
        'name' => 'お名前',
        'email' => 'メールアドレス',
        'phone' => 'お電話番号',
        'stock_no' => '在庫番号',
        'message' => 'お問い合わせ内容',
        'make' => 'メーカー',
        'model' => '車種',
        'grade' => 'グレード',
        'model_year' => '年式',
        'mileage' => '走行距離',
        'color' => 'ボディカラー',
        'condition' => 'お車の状態',
        'zip' => '郵便番号',

        // 管理画面・ログイン
        'password' => 'パスワード',
        'password_confirmation' => 'パスワード（確認用）',
        'current_password' => '現在のパスワード',
        'price' => '支払総額',
        'base_price' => '車両本体価格',
        'price_negotiable' => '価格応談',
        'body_type' => 'ボディタイプ',
        'transmission' => 'ミッション',
        'fuel_type' => '燃料',
        'location' => '展示場所',
        'status' => '販売状況',
        'featured' => '店長おすすめ',
        'published_at' => '掲載日',
        'accident_count' => '修復歴（回数）',
        'has_service_record' => '点検記録簿',
        'inspection_type' => '車検',
        'inspection_expiry' => '車検の有効期限',
        'description' => '車両の説明',
        'equipment' => '装備',
        'image' => '車両写真',
        'images' => '車両写真',
        'images.*' => '車両写真',
    ],

];
