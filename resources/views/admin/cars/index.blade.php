<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight">車両管理</h2>
                <p class="text-sm text-gray-500 mt-0.5">在庫車両の一覧・管理</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.cars.export') }}"
                   class="inline-flex items-center gap-1.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold py-2 px-4 rounded-lg text-sm shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    CSV出力
                </a>
                <a href="{{ route('admin.cars.create') }}"
                   class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg text-sm shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    新規登録
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-5">

            @if(session('success'))
                <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg" role="alert">
                    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif

            {{-- サマリーカード --}}
            @php
                $totalCount     = $cars->total();
                $availableCount = $statusCounts['available'] ?? 0;
                $reservedCount  = $statusCounts['reserved']  ?? 0;
                $soldCount      = $statusCounts['sold']       ?? 0;
            @endphp
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl border border-gray-200 border-l-4 border-l-indigo-400 shadow-sm px-5 py-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500">総在庫</p>
                        <p class="text-2xl font-bold text-gray-800 leading-none mt-0.5">{{ number_format($availableCount + $reservedCount + $soldCount) }}<span class="text-sm font-normal text-gray-400 ml-1">台</span></p>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 border-l-4 border-l-green-400 shadow-sm px-5 py-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-green-600">販売中</p>
                        <p class="text-2xl font-bold text-green-700 leading-none mt-0.5">{{ number_format($availableCount) }}<span class="text-sm font-normal text-gray-400 ml-1">台</span></p>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 border-l-4 border-l-yellow-400 shadow-sm px-5 py-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-yellow-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-yellow-600">商談中</p>
                        <p class="text-2xl font-bold text-yellow-700 leading-none mt-0.5">{{ number_format($reservedCount) }}<span class="text-sm font-normal text-gray-400 ml-1">台</span></p>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 border-l-4 border-l-gray-300 shadow-sm px-5 py-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-400">売約済</p>
                        <p class="text-2xl font-bold text-gray-500 leading-none mt-0.5">{{ number_format($soldCount) }}<span class="text-sm font-normal text-gray-400 ml-1">台</span></p>
                    </div>
                </div>
            </div>

            {{-- フィルターパネル（タブ＋検索）--}}
            @php
                $baseParams = request()->except(['status', 'page']);
                $tabDefs = [
                    ['value' => '',          'label' => 'すべて',  'count' => $availableCount + $reservedCount + $soldCount],
                    ['value' => 'available', 'label' => '販売中',  'count' => $availableCount],
                    ['value' => 'reserved',  'label' => '商談中',  'count' => $reservedCount],
                    ['value' => 'sold',      'label' => '売約済',  'count' => $soldCount],
                ];
                $tabActiveColors = [
                    ''          => 'border-indigo-500 text-indigo-600',
                    'available' => 'border-green-500 text-green-700',
                    'reserved'  => 'border-yellow-400 text-yellow-700',
                    'sold'      => 'border-gray-400 text-gray-600',
                ];
                $tabBadgeColors = [
                    ''          => 'bg-indigo-100 text-indigo-700',
                    'available' => 'bg-green-100 text-green-700',
                    'reserved'  => 'bg-yellow-100 text-yellow-700',
                    'sold'      => 'bg-gray-100 text-gray-600',
                ];
                $dotColors = [
                    'available' => 'bg-green-500',
                    'reserved'  => 'bg-yellow-400',
                    'sold'      => 'bg-gray-400',
                ];
                $currentStatus = request('status', '');
            @endphp
            <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">

                {{-- ステータスタブ --}}
                <div class="flex overflow-x-auto border-b border-gray-100 scrollbar-none">
                    @foreach($tabDefs as $tab)
                        @php
                            $tabParams = $tab['value'] === '' ? $baseParams : array_merge($baseParams, ['status' => $tab['value']]);
                            $isActive  = $currentStatus === $tab['value'];
                        @endphp
                        <a href="{{ route('admin.cars.index', $tabParams) }}"
                           class="flex items-center gap-1.5 px-5 py-3.5 text-sm font-semibold border-b-2 whitespace-nowrap transition-colors
                               {{ $isActive
                                   ? $tabActiveColors[$tab['value']] . ' bg-white'
                                   : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-200' }}">
                            @if($tab['value'] !== '')
                                <span class="w-2 h-2 rounded-full {{ $dotColors[$tab['value']] }}"></span>
                            @endif
                            {{ $tab['label'] }}
                            <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-xs font-bold leading-none
                                {{ $isActive ? $tabBadgeColors[$tab['value']] : 'bg-gray-100 text-gray-400' }}">
                                {{ number_format($tab['count']) }}
                            </span>
                        </a>
                    @endforeach
                </div>

                {{-- 検索フォーム --}}
                <form method="get" action="{{ route('admin.cars.index') }}" class="px-4 py-3 bg-gray-50/50">
                    <input type="hidden" name="status" value="{{ request('status') }}">
                    <div class="flex flex-wrap gap-2.5 items-center">
                        <div class="flex-1 min-w-[200px]">
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/></svg>
                                <input type="text" name="q" value="{{ request('q') }}"
                                       placeholder="在庫番号・メーカー・モデル・グレード"
                                       class="pl-9 border-gray-200 bg-white rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 w-full shadow-sm">
                            </div>
                        </div>
                        <div>
                            <select name="make" class="border-gray-200 bg-white rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm pr-8">
                                <option value="">メーカー（すべて）</option>
                                @foreach($makes as $make)
                                    <option value="{{ $make }}" @selected(request('make') === $make)>{{ $make }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow-sm transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/></svg>
                                検索
                            </button>
                            @if(request()->hasAny(['q','status','make']))
                                <a href="{{ route('admin.cars.index') }}"
                                   class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 py-2 px-3 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 shadow-sm transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    クリア
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>

            {{-- 一覧テーブル --}}
            <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between bg-gray-50/50">
                    <p class="text-sm text-gray-500">
                        @if($cars->total() > 0)
                            <span class="font-semibold text-gray-800">{{ number_format($cars->total()) }}</span> 台中
                            <span class="text-gray-600">{{ number_format($cars->firstItem()) }}〜{{ number_format($cars->lastItem()) }} 台</span> を表示
                        @else
                            <span class="text-gray-400">該当する車両はありません</span>
                        @endif
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="pl-5 pr-3 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider w-28">画像</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">車両情報</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider w-36">年式 / 走行</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider w-36">価格</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-400 uppercase tracking-wider w-28">ステータス</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-400 uppercase tracking-wider w-12 whitespace-nowrap">注目</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider w-28 whitespace-nowrap">操作</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($cars as $car)
                                <tr class="hover:bg-gray-50 transition-colors group">

                                    {{-- 画像 --}}
                                    <td class="pl-5 pr-3 py-3">
                                        <a href="{{ route('admin.cars.edit', $car) }}" class="block">
                                            @if($car->image_path)
                                                <img src="{{ '/images/' . $car->image_path }}"
                                                     alt="{{ $car->make }} {{ $car->model }}"
                                                     class="h-16 w-24 object-cover rounded-xl border border-gray-200 shadow-sm group-hover:shadow-md transition-shadow">
                                            @else
                                                <div class="h-16 w-24 bg-gray-100 rounded-xl border border-gray-200 flex items-center justify-center">
                                                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                </div>
                                            @endif
                                        </a>
                                    </td>

                                    {{-- 車両情報 --}}
                                    <td class="px-4 py-3">
                                        <a href="{{ route('admin.cars.edit', $car) }}" class="block group/link">
                                            <p class="text-[10px] font-mono text-gray-400 mb-0.5 tracking-wide">{{ $car->stock_no }}</p>
                                            <p class="text-sm font-bold text-gray-800 group-hover/link:text-indigo-600 transition-colors leading-snug">{{ $car->make }} {{ $car->model }}</p>
                                            @if($car->grade)
                                                <p class="text-xs text-gray-500 mt-0.5">{{ $car->grade }}</p>
                                            @endif
                                            <div class="flex flex-wrap gap-1 mt-1.5">
                                                @if($car->body_type)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600">{{ $car->body_type }}</span>
                                                @endif
                                                @if($car->fuel_type)
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-blue-600">{{ $car->fuel_type }}</span>
                                                @endif
                                            </div>
                                        </a>
                                    </td>

                                    {{-- 年式/走行 --}}
                                    <td class="px-4 py-3">
                                        <p class="text-sm font-semibold text-gray-700">{{ $car->model_year }}<span class="text-xs font-normal text-gray-400 ml-0.5">年式</span></p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ number_format($car->mileage) }}<span class="text-gray-400 ml-0.5">km</span></p>
                                    </td>

                                    {{-- 価格 --}}
                                    <td class="px-4 py-3 text-right">
                                        @if($car->price_negotiable)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">応談</span>
                                        @else
                                            <p class="text-sm font-bold text-gray-800">{{ number_format($car->price) }}<span class="text-xs font-normal text-gray-400 ml-0.5">円</span></p>
                                            @if($car->base_price)
                                                <p class="text-[10px] text-gray-400 mt-0.5">本体 {{ number_format($car->base_price) }}円</p>
                                            @endif
                                        @endif
                                    </td>

                                    {{-- ステータス（クリックでドロップダウン切替）--}}
                                    <td class="px-4 py-3 text-center"
                                        x-data="statusCell('{{ $car->id }}', '{{ $car->status }}', '{{ csrf_token() }}')"
                                        @click.stop>
                                        <button type="button" @click="toggle($el)" @scroll.window="open = false"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold ring-1 transition cursor-pointer"
                                                :class="badgeClass"
                                                :disabled="saving">
                                            <span x-text="badgeLabel"></span>
                                            <svg class="w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                        <template x-teleport="body">
                                            <div x-show="open" x-cloak @click.away="open = false"
                                                 :style="`position:fixed;top:${dropTop}px;left:${dropLeft}px;z-index:9999`"
                                                 class="w-28 bg-white border border-gray-200 rounded-xl shadow-xl py-1.5 text-left overflow-hidden">
                                                <template x-for="opt in options" :key="opt.value">
                                                    <button type="button"
                                                            @click="select(opt.value)"
                                                            class="w-full flex items-center gap-2 px-3 py-2 text-xs font-medium hover:bg-gray-50 transition"
                                                            :class="status === opt.value ? 'text-indigo-600 bg-indigo-50/50' : 'text-gray-700'">
                                                        <span class="w-2 h-2 rounded-full flex-shrink-0" :class="opt.dot"></span>
                                                        <span x-text="opt.label"></span>
                                                        <svg x-show="status === opt.value" class="w-3.5 h-3.5 ml-auto text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                    </button>
                                                </template>
                                            </div>
                                        </template>
                                    </td>

                                    {{-- 注目（即時AJAX切替）--}}
                                    <td class="px-4 py-3 text-center"
                                        x-data="featuredToggle('{{ $car->id }}', {{ $car->featured ? 'true' : 'false' }}, '{{ csrf_token() }}')"
                                        @click.stop>
                                        <button type="button" @click="toggle"
                                                :title="featured ? '注目解除' : '注目に設定'"
                                                :disabled="saving"
                                                class="text-xl leading-none transition-all hover:scale-125 focus:outline-none disabled:opacity-40"
                                                :class="featured ? 'text-yellow-400' : 'text-gray-200 hover:text-yellow-300'">★</button>
                                    </td>

                                    {{-- 操作 --}}
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="{{ route('admin.cars.edit', $car) }}"
                                               class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-white bg-indigo-50 hover:bg-indigo-600 px-2.5 py-1.5 rounded-lg transition whitespace-nowrap">
                                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                編集
                                            </a>
                                            <form action="{{ route('admin.cars.destroy', $car) }}" method="POST" class="inline-block"
                                                  onsubmit="return confirm('「{{ $car->make }} {{ $car->model }}」を削除しますか？\nこの操作は取り消せません。');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 text-xs font-semibold text-red-500 hover:text-white bg-red-50 hover:bg-red-500 px-2.5 py-1.5 rounded-lg transition whitespace-nowrap">
                                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    削除
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-20 text-center">
                                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </div>
                                        <p class="text-sm font-semibold text-gray-500">条件に一致する車両はありません</p>
                                        <p class="text-xs text-gray-400 mt-1">検索条件を変更してお試しください</p>
                                        @if(request()->hasAny(['q','status','make']))
                                            <a href="{{ route('admin.cars.index') }}"
                                               class="mt-4 inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-3 py-1.5 rounded-lg transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                すべてのフィルターをリセット
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($cars->hasPages())
                    <div class="px-5 py-4 border-t border-gray-100">
                        {{ $cars->withQueryString()->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>

@push('scripts')
<script>
function featuredToggle(carId, initialFeatured, csrfToken) {
    return {
        featured: initialFeatured,
        saving: false,
        async toggle() {
            if (this.saving) return;
            this.saving = true;
            const prev = this.featured;
            this.featured = !this.featured;
            try {
                const res = await fetch(`/admin/cars/${carId}/featured`, {
                    method: 'PATCH',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                });
                if (!res.ok) throw new Error();
            } catch {
                this.featured = prev;
            } finally {
                this.saving = false;
            }
        },
    };
}

function statusCell(carId, initialStatus, csrfToken) {
    const meta = {
        available: { label: '販売中', badge: 'bg-green-100 text-green-800 ring-green-200', dot: 'bg-green-500' },
        reserved:  { label: '商談中', badge: 'bg-yellow-100 text-yellow-800 ring-yellow-200', dot: 'bg-yellow-400' },
        sold:      { label: '売約済', badge: 'bg-gray-100 text-gray-600 ring-gray-200', dot: 'bg-gray-400' },
    };
    return {
        status: initialStatus,
        open: false,
        saving: false,
        dropTop: 0,
        dropLeft: 0,
        options: Object.entries(meta).map(([value, m]) => ({ value, label: m.label, dot: m.dot })),
        get badgeLabel() { return meta[this.status]?.label ?? this.status; },
        get badgeClass()  { return meta[this.status]?.badge  ?? 'bg-gray-100 text-gray-600 ring-gray-200'; },
        toggle(btn) {
            if (!this.open) {
                const r = btn.getBoundingClientRect();
                this.dropTop  = r.bottom + 4;
                this.dropLeft = r.left + r.width / 2 - 56;
            }
            this.open = !this.open;
        },
        async select(newStatus) {
            if (newStatus === this.status) { this.open = false; return; }
            this.open = false;
            this.saving = true;
            const prev = this.status;
            this.status = newStatus;
            try {
                const res = await fetch(`/admin/cars/${carId}/status`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ status: newStatus }),
                });
                if (!res.ok) throw new Error();
            } catch {
                this.status = prev;
                alert('ステータスの更新に失敗しました。');
            } finally {
                this.saving = false;
            }
        },
    };
}
</script>
<style>[x-cloak]{display:none!important}</style>
@endpush
</x-app-layout>
