<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                査定依頼詳細
            </h2>
            <a href="{{ route('admin.appraisals.index') }}" class="text-sm text-gray-600 hover:text-gray-900">← 一覧へ戻る</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <div class="flex items-center justify-between mb-6">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">受信日時: {{ $appraisal->created_at->format('Y年m月d日 H:i') }}</p>
                        @if(is_null($appraisal->read_at))
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">未読</span>
                        @else
                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">既読 {{ $appraisal->read_at->format('Y/m/d H:i') }}</span>
                        @endif
                    </div>
                    <form action="{{ route('admin.appraisals.destroy', $appraisal) }}" method="POST" onsubmit="return confirm('削除しますか？');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-medium rounded hover:bg-red-700">削除</button>
                    </form>
                </div>

                <h3 class="font-bold text-base text-gray-700 border-b-2 border-red-500 pb-2 mb-4">🚗 お車の情報</h3>
                <dl class="grid grid-cols-2 gap-x-8 gap-y-3 text-sm mb-8">
                    <div>
                        <dt class="text-gray-500 font-medium">メーカー</dt>
                        <dd class="text-gray-900 font-bold">{{ $appraisal->make }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">車名</dt>
                        <dd class="text-gray-900 font-bold">{{ $appraisal->model }}</dd>
                    </div>
                    @if($appraisal->grade)
                    <div>
                        <dt class="text-gray-500 font-medium">グレード</dt>
                        <dd class="text-gray-900">{{ $appraisal->grade }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-gray-500 font-medium">年式</dt>
                        <dd class="text-gray-900">{{ $appraisal->model_year }}年式</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">走行距離</dt>
                        <dd class="text-gray-900">{{ number_format($appraisal->mileage) }} km</dd>
                    </div>
                    @if($appraisal->color)
                    <div>
                        <dt class="text-gray-500 font-medium">車体色</dt>
                        <dd class="text-gray-900">{{ $appraisal->color }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-gray-500 font-medium">車両状態</dt>
                        <dd class="text-gray-900">{{ $appraisal->condition_label }}</dd>
                    </div>
                </dl>

                <h3 class="font-bold text-base text-gray-700 border-b-2 border-red-500 pb-2 mb-4">👤 お客様情報</h3>
                <dl class="grid grid-cols-2 gap-x-8 gap-y-3 text-sm mb-8">
                    <div>
                        <dt class="text-gray-500 font-medium">お名前</dt>
                        <dd class="text-gray-900 font-bold">{{ $appraisal->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">電話番号</dt>
                        <dd class="text-gray-900"><a href="tel:{{ $appraisal->phone }}" class="text-blue-600 hover:underline">{{ $appraisal->phone }}</a></dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">メールアドレス</dt>
                        <dd class="text-gray-900"><a href="mailto:{{ $appraisal->email }}" class="text-blue-600 hover:underline">{{ $appraisal->email }}</a></dd>
                    </div>
                    @if($appraisal->zip)
                    <div>
                        <dt class="text-gray-500 font-medium">郵便番号</dt>
                        <dd class="text-gray-900">〒{{ $appraisal->zip }}</dd>
                    </div>
                    @endif
                </dl>

                @if($appraisal->message)
                <h3 class="font-bold text-base text-gray-700 border-b-2 border-red-500 pb-2 mb-4">備考・ご要望</h3>
                <div class="bg-gray-50 rounded p-4 text-sm text-gray-700 whitespace-pre-wrap mb-6">{{ $appraisal->message }}</div>
                @endif

                <div class="flex gap-3">
                    <a href="mailto:{{ $appraisal->email }}" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700">メールを送る</a>
                    <a href="tel:{{ $appraisal->phone }}" class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded hover:bg-green-700">電話をかける</a>
                    <a href="{{ route('admin.appraisals.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded hover:bg-gray-300">一覧へ戻る</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
