<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">ダッシュボード</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- 在庫サマリー --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                @foreach([
                    ['label' => '総在庫', 'value' => $stats['total'], 'color' => 'blue'],
                    ['label' => '販売中', 'value' => $stats['available'], 'color' => 'green'],
                    ['label' => '商談中', 'value' => $stats['reserved'], 'color' => 'yellow'],
                    ['label' => '売約済', 'value' => $stats['sold'], 'color' => 'gray'],
                    ['label' => '注目車両', 'value' => $stats['featured'], 'color' => 'orange'],
                ] as $stat)
                <div class="bg-white rounded-lg shadow-sm p-5 text-center">
                    <p class="text-sm text-gray-500 mb-1">{{ $stat['label'] }}</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $stat['value'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">台</p>
                </div>
                @endforeach
            </div>

            {{-- 問い合わせサマリー --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-lg shadow-sm p-5 text-center">
                    <p class="text-sm text-gray-500 mb-1">今月の問い合わせ</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $inquiryStats['this_month'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">件</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5 text-center">
                    <p class="text-sm text-gray-500 mb-1">未読</p>
                    <p class="text-3xl font-bold {{ $inquiryStats['unread'] > 0 ? 'text-red-600' : 'text-gray-800' }}">{{ $inquiryStats['unread'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">件</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5 text-center">
                    <p class="text-sm text-gray-500 mb-1">問い合わせ総数</p>
                    <p class="text-3xl font-bold text-gray-800">{{ $inquiryStats['total'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">件</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- 最新問い合わせ --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-semibold text-gray-800">最新の問い合わせ</h3>
                            <a href="{{ route('admin.inquiries.index') }}" class="text-sm text-blue-600 hover:underline">すべて見る</a>
                        </div>
                        @forelse($recentInquiries as $inquiry)
                        <div class="py-2 border-b border-gray-100 last:border-0 flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-gray-800 flex items-center gap-2">
                                    {{ $inquiry->name }}
                                    @if(is_null($inquiry->read_at))
                                        <span class="inline-flex text-xs px-1.5 py-0.5 rounded bg-blue-100 text-blue-700">未読</span>
                                    @endif
                                </p>
                                <p class="text-xs text-gray-400">{{ $inquiry->created_at->format('m/d H:i') }}{{ $inquiry->stock_no ? ' — ' . $inquiry->stock_no : '' }}</p>
                            </div>
                            <a href="{{ route('admin.inquiries.show', $inquiry) }}" class="text-xs text-blue-600 hover:underline ml-2">詳細</a>
                        </div>
                        @empty
                        <p class="text-sm text-gray-400">問い合わせはまだありません</p>
                        @endforelse
                    </div>
                </div>

                {{-- 最新登録車両 --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-semibold text-gray-800">最近登録した車両</h3>
                            <a href="{{ route('admin.cars.index') }}" class="text-sm text-blue-600 hover:underline">すべて見る</a>
                        </div>
                        @forelse($recentCars as $car)
                        <div class="py-2 border-b border-gray-100 last:border-0 flex justify-between items-center">
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $car->make }} {{ $car->model }}</p>
                                <p class="text-xs text-gray-400">{{ $car->stock_no }} — {{ $car->price_negotiable ? '応談' : number_format($car->price) . '円' }}</p>
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded-full
                                {{ $car->status === 'available' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $car->status }}
                            </span>
                        </div>
                        @empty
                        <p class="text-sm text-gray-400">車両はまだ登録されていません</p>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- クイックリンク --}}
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.cars.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">+ 車両を新規登録</a>
                <a href="{{ route('admin.inquiries.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">お問い合わせ管理</a>
                <a href="{{ route('admin.cars.export') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">CSV エクスポート</a>
            </div>

        </div>
    </div>
</x-app-layout>
