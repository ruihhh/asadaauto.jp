@extends('layouts.site')

@section('title', '車両比較')
@section('meta_robots', 'noindex, follow')

@section('content')
<div class="container" style="padding-top:28px;padding-bottom:28px;">
    <h2 style="font-size:1.4rem;font-weight:700;margin-bottom:24px;">車両比較</h2>

    @if($cars->isEmpty())
        <div class="card-lg" style="text-align:center;padding:48px;">
            <p style="color:var(--muted);">比較する車両が選択されていません。</p>
            <a href="{{ route('cars.index') }}" class="btn-primary" style="display:inline-flex;margin-top:16px;">在庫一覧へ</a>
        </div>
    @else
    <div class="compare-table-wrapper">
        <table class="compare-table">
            <thead>
                <tr>
                    <th class="compare-label-col">項目</th>
                    @foreach($cars as $car)
                    <th>
                        <a href="{{ route('cars.show', $car) }}" style="color:inherit;text-decoration:none;">
                            {{ $car->make }} {{ $car->model }}
                        </a>
                    </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="compare-label">画像</td>
                    @foreach($cars as $car)
                    <td>
                        @php $img = $car->image_path ?? $car->images->first()?->path @endphp
                        @if($img)
                            <img src="{{ '/images/' . $img }}" alt="{{ $car->make }} {{ $car->model }}" style="width:100%;max-width:200px;height:120px;object-fit:cover;border-radius:8px;">
                        @else
                            <div style="width:200px;height:120px;background:var(--line);border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--muted);font-size:.8rem;">No Image</div>
                        @endif
                    </td>
                    @endforeach
                </tr>
                @php
                $rows = [
                    '価格' => fn($c) => $c->price_negotiable ? '応談' : number_format($c->price) . '円',
                    '年式' => fn($c) => $c->model_year . '年',
                    '走行距離' => fn($c) => number_format($c->mileage) . 'km',
                    'ボディタイプ' => fn($c) => $c->body_type,
                    '燃料' => fn($c) => $c->fuel_type,
                    '変速機' => fn($c) => $c->transmission,
                    '車体色' => fn($c) => $c->color ?: '—',
                    '事故歴' => fn($c) => $c->accident_count > 0 ? $c->accident_count . '回' : 'なし',
                    '整備記録' => fn($c) => $c->has_service_record ? 'あり' : 'なし',
                    '車検期限' => fn($c) => $c->inspection_expiry?->format('Y年m月') ?: '—',
                    '保管場所' => fn($c) => $c->location ?: '—',
                    '在庫番号' => fn($c) => $c->stock_no,
                ];
                @endphp
                @foreach($rows as $label => $fn)
                <tr>
                    <td class="compare-label">{{ $label }}</td>
                    @foreach($cars as $car)
                    <td>{{ $fn($car) }}</td>
                    @endforeach
                </tr>
                @endforeach
                <tr>
                    <td class="compare-label"></td>
                    @foreach($cars as $car)
                    <td>
                        <a href="{{ route('contact.index', ['stock_no' => $car->stock_no]) }}" class="btn-primary" style="font-size:.85rem;padding:10px 16px;">問い合わせる</a>
                    </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>

    <div style="margin-top:24px;">
        <a href="{{ route('cars.index') }}" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            在庫一覧に戻る
        </a>
    </div>
    @endif
</div>
@endsection
