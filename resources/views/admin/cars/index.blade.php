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
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @php
                    $totalCount = $cars->total();
                    $availableCount = $statusCounts['available'] ?? 0;
                    $reservedCount  = $statusCounts['reserved']  ?? 0;
                    $soldCount      = $statusCounts['sold']       ?? 0;
                @endphp
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-5 py-4">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">総在庫</p>
                    <p class="mt-1 text-2xl font-bold text-gray-800">{{ number_format($totalCount) }}<span class="text-sm font-normal text-gray-400 ml-1">台</span></p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-5 py-4">
                    <p class="text-xs font-medium text-green-600 uppercase tracking-wide">販売中</p>
                    <p class="mt-1 text-2xl font-bold text-green-700">{{ number_format($availableCount) }}<span class="text-sm font-normal text-gray-400 ml-1">台</span></p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-5 py-4">
                    <p class="text-xs font-medium text-yellow-600 uppercase tracking-wide">商談中</p>
                    <p class="mt-1 text-2xl font-bold text-yellow-700">{{ number_format($reservedCount) }}<span class="text-sm font-normal text-gray-400 ml-1">台</span></p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-5 py-4">
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">売約済</p>
                    <p class="mt-1 text-2xl font-bold text-gray-500">{{ number_format($soldCount) }}<span class="text-sm font-normal text-gray-400 ml-1">台</span></p>
                </div>
            </div>

            {{-- 検索フォーム --}}
            <form method="get" action="{{ route('admin.cars.index') }}"
                  class="bg-white border border-gray-200 shadow-sm rounded-xl p-4">
                <div class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-[180px]">
                        <label class="block text-xs font-medium text-gray-600 mb-1">キーワード</label>
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z"/></svg>
                            <input type="text" name="q" value="{{ request('q') }}"
                                   placeholder="在庫番号・メーカー・モデル・グレード"
                                   class="pl-9 border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 w-full">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">ステータス</label>
                        <select name="status" class="border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 pr-8">
                            <option value="">すべて</option>
                            <option value="available" @selected(request('status') === 'available')>販売中</option>
                            <option value="reserved"  @selected(request('status') === 'reserved')>商談中</option>
                            <option value="sold"      @selected(request('status') === 'sold')>売約済</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">メーカー</label>
                        <select name="make" class="border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 pr-8">
                            <option value="">すべて</option>
                            @foreach($makes as $make)
                                <option value="{{ $make }}" @selected(request('make') === $make)>{{ $make }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-2">
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2 px-4 rounded-lg transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                            絞り込み
                        </button>
                        @if(request()->hasAny(['q','status','make']))
                            <a href="{{ route('admin.cars.index') }}"
                               class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 py-2 px-3 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                リセット
                            </a>
                        @endif
                    </div>
                </div>
            </form>

            {{-- テーブル --}}
            <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                    <p class="text-sm text-gray-500">
                        @if($cars->total() > 0)
                            <span class="font-semibold text-gray-800">{{ number_format($cars->total()) }}</span> 台中
                            {{ number_format($cars->firstItem()) }}〜{{ number_format($cars->lastItem()) }} 台を表示
                        @else
                            0件
                        @endif
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-24">画像</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">在庫番号</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">車両情報</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">年式 / 走行</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">支払総額</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">ステータス</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-16">注目</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider w-28">操作</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($cars as $car)
                                <tr class="hover:bg-gray-50 transition-colors group">
                                    <td class="px-5 py-3">
                                        @if($car->image_path)
                                            <img src="{{ '/images/' . $car->image_path }}"
                                                 alt="{{ $car->make }} {{ $car->model }}"
                                                 class="h-14 w-20 object-cover rounded-lg border border-gray-200 shadow-sm">
                                        @else
                                            <div class="h-14 w-20 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        <span class="text-sm font-mono font-semibold text-gray-700">{{ $car->stock_no }}</span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <p class="text-sm font-semibold text-gray-800">{{ $car->make }} {{ $car->model }}</p>
                                        @if($car->grade)
                                            <p class="text-xs text-gray-500 mt-0.5">{{ $car->grade }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        <p class="text-sm text-gray-700">{{ $car->model_year }}年式</p>
                                        <p class="text-xs text-gray-500 mt-0.5">{{ number_format($car->mileage) }} km</p>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        @if($car->price_negotiable)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-700 border border-amber-300">応談</span>
                                        @else
                                            <span class="text-sm font-bold text-gray-800">{{ number_format($car->price) }}<span class="text-xs font-normal text-gray-400 ml-0.5">円</span></span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-center"
                                        x-data="statusCell('{{ $car->id }}', '{{ $car->status }}', '{{ csrf_token() }}')"
                                        @click.stop>
                                        <button type="button" @click="toggle($el)" @scroll.window="open = false"
                                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold ring-1 transition cursor-pointer"
                                                :class="badgeClass"
                                                :disabled="saving">
                                            <span x-text="badgeLabel"></span>
                                            <svg class="w-3 h-3 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                        <template x-teleport="body">
                                            <div x-show="open" x-cloak @click.away="open = false"
                                                 :style="`position:fixed;top:${dropTop}px;left:${dropLeft}px;z-index:9999`"
                                                 class="w-28 bg-white border border-gray-200 rounded-lg shadow-xl py-1 text-left">
                                                <template x-for="opt in options" :key="opt.value">
                                                    <button type="button"
                                                            @click="select(opt.value)"
                                                            class="w-full flex items-center gap-2 px-3 py-1.5 text-xs font-medium hover:bg-gray-50 transition"
                                                            :class="status === opt.value ? 'text-indigo-600 font-semibold' : 'text-gray-700'">
                                                        <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" :class="opt.dot"></span>
                                                        <span x-text="opt.label"></span>
                                                        <svg x-show="status === opt.value" class="w-3 h-3 ml-auto text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                    </button>
                                                </template>
                                            </div>
                                        </template>
                                    </td>
                                    <td class="px-5 py-3 text-center"
                                        x-data="featuredToggle('{{ $car->id }}', {{ $car->featured ? 'true' : 'false' }}, '{{ csrf_token() }}')"
                                        @click.stop>
                                        <button type="button" @click="toggle"
                                                :title="featured ? '注目解除' : '注目に設定'"
                                                :disabled="saving"
                                                class="text-xl leading-none transition-transform hover:scale-125 focus:outline-none disabled:opacity-50"
                                                :class="featured ? 'text-yellow-400' : 'text-gray-200 hover:text-yellow-300'">★</button>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2 opacity-70 group-hover:opacity-100 transition-opacity">
                                            <a href="{{ route('admin.cars.edit', $car) }}"
                                               class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1.5 rounded-md transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                編集
                                            </a>
                                            <form action="{{ route('admin.cars.destroy', $car) }}" method="POST" class="inline-block"
                                                  onsubmit="return confirm('「{{ $car->make }} {{ $car->model }}」を削除しますか？\nこの操作は取り消せません。');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 text-xs font-semibold text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 px-2.5 py-1.5 rounded-md transition">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    削除
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-16 text-center">
                                        <svg class="mx-auto w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <p class="text-sm font-medium text-gray-500">条件に一致する車両はありません</p>
                                        @if(request()->hasAny(['q','status','make']))
                                            <a href="{{ route('admin.cars.index') }}" class="mt-2 inline-block text-xs text-indigo-600 hover:underline">フィルターをリセット</a>
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
        get badgeClass() { return meta[this.status]?.badge ?? 'bg-gray-100 text-gray-600 ring-gray-200'; },
        toggle(btn) {
            if (!this.open) {
                const r = btn.getBoundingClientRect();
                this.dropTop = r.bottom + 4;
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
