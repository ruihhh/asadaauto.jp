@extends('layouts.site')

@section('title', 'お気に入り一覧')
@section('meta_robots', 'noindex, follow')

@section('content')
<div class="container" style="padding-top:28px;padding-bottom:28px;" x-data="favoritesPage()">
    <h2 style="font-size:1.4rem;font-weight:700;margin-bottom:8px;">お気に入り一覧</h2>

    {{-- JS で IDs を取得してページをリロード --}}
    <div x-show="!loaded" style="text-align:center;padding:48px;">読み込み中...</div>

    <div x-show="loaded && favIds.length === 0" style="text-align:center;padding:48px;">
        <p style="color:var(--muted);">お気に入りに追加された車両はありません。</p>
        <a href="{{ route('cars.index') }}" class="btn-primary" style="display:inline-flex;margin-top:16px;">在庫一覧へ</a>
    </div>

    <div x-show="loaded && favIds.length > 0">
        @if($cars->isNotEmpty())
        <p style="color:var(--muted);margin-bottom:16px;">{{ $cars->count() }}台</p>
        <section class="inventory-grid">
            @foreach($cars as $car)
            <article class="car-card">
                <a href="{{ route('cars.show', $car) }}" class="car-card-link">
                    @if($car->image_path)
                        <div class="car-card-image">
                            <img src="{{ '/images/' . $car->image_path }}" alt="{{ $car->make }} {{ $car->model }}">
                        </div>
                    @endif
                    <div class="car-card-head">
                        <p class="stock-no">{{ $car->stock_no }}</p>
                        @if($car->featured) <span class="featured">注目車両</span> @endif
                    </div>
                    <h3>{{ $car->make }} {{ $car->model }}</h3>
                    <p class="grade">{{ $car->grade ?: 'グレード情報なし' }}</p>
                    <p class="price">{{ number_format($car->price) }}円</p>
                    <dl>
                        <div><dt>年式</dt><dd>{{ $car->model_year }}年</dd></div>
                        <div><dt>走行距離</dt><dd>{{ number_format($car->mileage) }}km</dd></div>
                    </dl>
                </a>
                <div class="car-card-actions" x-data="favBtn({{ $car->id }})">
                    <button @click="toggle" class="card-action-btn">
                        <span>❤ お気に入り解除</span>
                    </button>
                </div>
            </article>
            @endforeach
        </section>
        @else
        {{-- favIds があるが DB に該当なし（売却済みなど） --}}
        <p style="color:var(--muted);padding:48px;text-align:center;">
            お気に入りの車両は現在在庫にありません。
            <a href="{{ route('cars.index') }}" style="color:var(--brand);">在庫一覧へ</a>
        </p>
        @endif
    </div>
</div>

<script>
function favoritesPage() {
    return {
        favIds: [],
        loaded: false,
        init() {
            this.favIds = JSON.parse(localStorage.getItem('car_favorites') || '[]');
            this.loaded = true;
            if (this.favIds.length > 0) {
                const urlIds = new URLSearchParams(window.location.search).get('ids');
                const currentIds = this.favIds.join(',');
                if (urlIds !== currentIds) {
                    window.location.search = '?ids=' + currentIds;
                }
            }
        }
    };
}
function favBtn(id) {
    return {
        id: id,
        get isFav() {
            return JSON.parse(localStorage.getItem('car_favorites') || '[]').includes(this.id);
        },
        toggle() {
            let favs = JSON.parse(localStorage.getItem('car_favorites') || '[]');
            favs = favs.filter(v => v !== this.id);
            localStorage.setItem('car_favorites', JSON.stringify(favs));
            window.location.reload();
        }
    };
}
</script>
@endsection
