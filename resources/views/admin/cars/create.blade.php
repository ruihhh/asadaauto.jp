<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.cars.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-gray-800 leading-tight">車両の新規登録</h2>
                <p class="text-sm text-gray-500 mt-0.5">新しい在庫車両を登録します</p>
            </div>
        </div>
    </x-slot>

    {{-- ページ固有データ＆Alpine関数（@push非対応のため直接配置） --}}
    <script>
        window.__carCreate = {
            masterData: @json($masterData),
        };

        function carMasterForm() {
            const d = window.__carCreate;
            return {
                make: '',
                model: '',
                grade: '',
                masterData: d.masterData ?? { makes: [], models: {}, grades: {} },
                get filteredModels() {
                    return this.masterData.models[this.make] ?? [];
                },
                get filteredGrades() {
                    return this.masterData.grades[this.make + '@@' + this.model] ?? [];
                },
                onMakeChange() {
                    if (!this.filteredModels.includes(this.model)) {
                        this.model = '';
                        this.grade = '';
                    }
                },
                onModelChange() {
                    if (!this.filteredGrades.includes(this.grade)) {
                        this.grade = '';
                    }
                },
            };
        }

        function imagePreview() {
            return {
                previewUrl: null,
                fileName: '',
                onFile(event) {
                    const file = event.target.files[0];
                    if (!file) return;
                    this.fileName = file.name;
                    const reader = new FileReader();
                    reader.onload = (e) => { this.previewUrl = e.target.result; };
                    reader.readAsDataURL(file);
                },
                clear() {
                    this.previewUrl = null;
                    this.fileName = '';
                    document.getElementById('image').value = '';
                },
            };
        }
    </script>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('admin.cars.store') }}" method="POST" enctype="multipart/form-data"
                  x-data="carMasterForm()"
                  id="car-form">
                @csrf

                <div class="space-y-6">

                    {{-- 基本情報 --}}
                    <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                            <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                基本情報
                            </h3>
                        </div>
                        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <x-input-label for="stock_no" value="在庫番号 *" />
                                <x-text-input id="stock_no" name="stock_no" type="text" class="mt-1 block w-full font-mono" :value="old('stock_no')" required placeholder="例: TK-2024-001" />
                                <x-input-error class="mt-1.5" :messages="$errors->get('stock_no')" />
                            </div>

                            <div x-data="{ statusVal: '{{ old('status', 'available') }}' }">
                                <x-input-label value="ステータス *" />
                                <div class="mt-1 grid grid-cols-3 gap-2">

                                    {{-- 販売中 --}}
                                    <label class="cursor-pointer" @click="statusVal = 'available'">
                                        <input type="radio" name="status" value="available" class="sr-only"
                                               @if(old('status', 'available') === 'available') checked @endif>
                                        <span class="flex flex-col items-center gap-1.5 rounded-lg border-2 px-2 py-3 transition-all select-none"
                                              :class="statusVal === 'available'
                                                  ? 'border-green-500 bg-green-50 shadow-sm'
                                                  : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50'">
                                            <svg class="w-5 h-5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                 :class="statusVal === 'available' ? 'text-green-600' : 'text-gray-300'">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <span class="text-xs font-bold transition-colors"
                                                  :class="statusVal === 'available' ? 'text-green-700' : 'text-gray-400'">販売中</span>
                                        </span>
                                    </label>

                                    {{-- 商談中 --}}
                                    <label class="cursor-pointer" @click="statusVal = 'reserved'">
                                        <input type="radio" name="status" value="reserved" class="sr-only"
                                               @if(old('status') === 'reserved') checked @endif>
                                        <span class="flex flex-col items-center gap-1.5 rounded-lg border-2 px-2 py-3 transition-all select-none"
                                              :class="statusVal === 'reserved'
                                                  ? 'border-yellow-400 bg-yellow-50 shadow-sm'
                                                  : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50'">
                                            <svg class="w-5 h-5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                 :class="statusVal === 'reserved' ? 'text-yellow-500' : 'text-gray-300'">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                            </svg>
                                            <span class="text-xs font-bold transition-colors"
                                                  :class="statusVal === 'reserved' ? 'text-yellow-700' : 'text-gray-400'">商談中</span>
                                        </span>
                                    </label>

                                    {{-- 売約済 --}}
                                    <label class="cursor-pointer" @click="statusVal = 'sold'">
                                        <input type="radio" name="status" value="sold" class="sr-only"
                                               @if(old('status') === 'sold') checked @endif>
                                        <span class="flex flex-col items-center gap-1.5 rounded-lg border-2 px-2 py-3 transition-all select-none"
                                              :class="statusVal === 'sold'
                                                  ? 'border-gray-500 bg-gray-100 shadow-sm'
                                                  : 'border-gray-200 bg-white hover:border-gray-300 hover:bg-gray-50'">
                                            <svg class="w-5 h-5 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                 :class="statusVal === 'sold' ? 'text-gray-600' : 'text-gray-300'">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                            <span class="text-xs font-bold transition-colors"
                                                  :class="statusVal === 'sold' ? 'text-gray-700' : 'text-gray-400'">売約済</span>
                                        </span>
                                    </label>

                                </div>
                                <x-input-error class="mt-1.5" :messages="$errors->get('status')" />
                            </div>

                            <div>
                                <x-input-label for="make" value="メーカー *" />
                                <input id="make" name="make" type="text" list="make-list"
                                    x-model="make" @change="onMakeChange"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    required autocomplete="off" placeholder="例: トヨタ">
                                <datalist id="make-list">
                                    <template x-for="m in masterData.makes" :key="m">
                                        <option :value="m"></option>
                                    </template>
                                </datalist>
                                <x-input-error class="mt-1.5" :messages="$errors->get('make')" />
                            </div>

                            <div>
                                <x-input-label for="model" value="モデル *" />
                                <input id="model" name="model" type="text" list="model-list"
                                    x-model="model" @change="onModelChange"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    required autocomplete="off" placeholder="例: プリウス">
                                <datalist id="model-list">
                                    <template x-for="m in filteredModels" :key="m">
                                        <option :value="m"></option>
                                    </template>
                                </datalist>
                                <x-input-error class="mt-1.5" :messages="$errors->get('model')" />
                            </div>

                            <div>
                                <x-input-label for="grade" value="グレード" />
                                <input id="grade" name="grade" type="text" list="grade-list"
                                    x-model="grade"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    autocomplete="off" placeholder="例: Z">
                                <datalist id="grade-list">
                                    <template x-for="g in filteredGrades" :key="g">
                                        <option :value="g"></option>
                                    </template>
                                </datalist>
                                <x-input-error class="mt-1.5" :messages="$errors->get('grade')" />
                            </div>

                            <div class="sm:col-span-2" x-data="{ bodyTypeVal: '{{ old('body_type') }}' }">
                                <x-input-label value="ボディタイプ *" />
                                <input type="hidden" name="body_type" :value="bodyTypeVal">
                                <div class="mt-1 grid grid-cols-5 gap-2">
                                    @foreach([['セダン','セダン'],['SUV','SUV'],['ミニバン','ミニバン'],['HB','ハッチバック'],['クーペ','クーペ'],['コンパクト','コンパクト'],['軽自動車','軽自動車'],['ワゴン','ステーションワゴン'],['トラック','トラック'],['その他','その他']] as [$label, $value])
                                    <button type="button"
                                            @click="bodyTypeVal = '{{ $value }}'"
                                            class="flex items-center justify-center rounded-lg border-2 py-2.5 px-1 text-xs font-semibold text-center min-h-[44px] transition-all select-none"
                                            :class="bodyTypeVal === '{{ $value }}'
                                                ? 'border-indigo-500 bg-indigo-50 text-indigo-700 shadow-sm'
                                                : 'border-gray-200 bg-white text-gray-500 hover:border-indigo-300 hover:bg-indigo-50'">
                                        {{ $label }}
                                    </button>
                                    @endforeach
                                </div>
                                <x-input-error class="mt-1.5" :messages="$errors->get('body_type')" />
                            </div>
                        </div>
                    </div>

                    {{-- スペック --}}
                    <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                            <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"/></svg>
                                スペック
                            </h3>
                        </div>
                        <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-5">
                            <div x-data="{ negotiable: {{ old('price_negotiable') ? 'true' : 'false' }} }">
                                <x-input-label value="価格" />
                                <label class="flex items-center gap-2 mt-1 mb-2 cursor-pointer select-none">
                                    <input type="checkbox" name="price_negotiable" value="1" x-model="negotiable"
                                           {{ old('price_negotiable') ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="text-sm font-semibold text-gray-700">応談にする</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-700 border border-amber-300 ml-1">応談</span>
                                </label>
                                <div x-show="!negotiable" x-cloak class="space-y-3">
                                    <div>
                                        <x-input-label for="price" value="支払総額（税込）*" />
                                        <div class="relative mt-1">
                                            <x-text-input id="price" name="price" type="number" min="0" class="block w-full pr-8" :value="old('price')" placeholder="0" />
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-400">円</span>
                                        </div>
                                        <x-input-error class="mt-1.5" :messages="$errors->get('price')" />
                                    </div>
                                    <div>
                                        <x-input-label for="base_price" value="車両本体価格" />
                                        <div class="relative mt-1">
                                            <x-text-input id="base_price" name="base_price" type="number" min="0" class="block w-full pr-8" :value="old('base_price')" placeholder="0" />
                                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-400">円</span>
                                        </div>
                                        <x-input-error class="mt-1.5" :messages="$errors->get('base_price')" />
                                    </div>
                                </div>
                                <div x-show="negotiable" x-cloak>
                                    <p class="text-sm text-amber-700 font-semibold bg-amber-50 border border-amber-200 rounded px-3 py-2">価格は「応談」として表示されます</p>
                                </div>
                            </div>

                            <div>
                                <x-input-label for="model_year" value="年式 *" />
                                <div class="relative mt-1">
                                    <x-text-input id="model_year" name="model_year" type="number" min="1900" :max="date('Y')+1" class="block w-full pr-8" :value="old('model_year')" required placeholder="{{ date('Y') }}" />
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-400">年</span>
                                </div>
                                <x-input-error class="mt-1.5" :messages="$errors->get('model_year')" />
                            </div>

                            <div>
                                <x-input-label for="mileage" value="走行距離 *" />
                                <div class="relative mt-1">
                                    <x-text-input id="mileage" name="mileage" type="number" min="0" class="block w-full pr-8" :value="old('mileage')" required placeholder="0" />
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-400">km</span>
                                </div>
                                <x-input-error class="mt-1.5" :messages="$errors->get('mileage')" />
                            </div>

                            <div x-data="{ transmissionVal: '{{ old('transmission') }}' }">
                                <x-input-label value="トランスミッション *" />
                                <input type="hidden" name="transmission" :value="transmissionVal">
                                <div class="mt-1 flex gap-1.5">
                                    @foreach(['AT','CVT','MT','AMT','DCT'] as $t)
                                    <button type="button"
                                            @click="transmissionVal = '{{ $t }}'"
                                            class="flex-1 flex items-center justify-center rounded-lg border-2 py-2 text-xs font-bold transition-all select-none"
                                            :class="transmissionVal === '{{ $t }}'
                                                ? 'border-indigo-500 bg-indigo-50 text-indigo-700 shadow-sm'
                                                : 'border-gray-200 bg-white text-gray-500 hover:border-indigo-300 hover:bg-indigo-50'">
                                        {{ $t }}
                                    </button>
                                    @endforeach
                                </div>
                                <x-input-error class="mt-1.5" :messages="$errors->get('transmission')" />
                            </div>

                            <div x-data="{ fuelTypeVal: '{{ old('fuel_type') }}' }">
                                <x-input-label value="燃料 *" />
                                <input type="hidden" name="fuel_type" :value="fuelTypeVal">
                                <div class="mt-1 grid grid-cols-3 gap-1.5">
                                    @foreach([
                                        ['⛽','ガソリン','ガソリン'],
                                        ['🛢','ディーゼル','ディーゼル'],
                                        ['⚡','HV','ハイブリッド'],
                                        ['🔌','PHV','プラグインハイブリッド'],
                                        ['🔋','EV','電気'],
                                        ['🔵','LPG','LPG'],
                                    ] as [$icon, $label, $value])
                                    <button type="button"
                                            @click="fuelTypeVal = '{{ $value }}'"
                                            class="flex flex-col items-center justify-center rounded-lg border-2 py-2 px-1 transition-all select-none"
                                            :class="fuelTypeVal === '{{ $value }}'
                                                ? 'border-indigo-500 bg-indigo-50 shadow-sm'
                                                : 'border-gray-200 bg-white hover:border-indigo-300 hover:bg-indigo-50'">
                                        <span class="text-base leading-none mb-0.5">{{ $icon }}</span>
                                        <span class="text-[10px] font-bold leading-none"
                                              :class="fuelTypeVal === '{{ $value }}' ? 'text-indigo-700' : 'text-gray-500'">{{ $label }}</span>
                                    </button>
                                    @endforeach
                                </div>
                                <x-input-error class="mt-1.5" :messages="$errors->get('fuel_type')" />
                            </div>

                            <div>
                                <x-input-label for="color" value="車体色" />
                                <x-text-input id="color" name="color" type="text" class="mt-1 block w-full" :value="old('color')" placeholder="例: パールホワイト" />
                                <x-input-error class="mt-1.5" :messages="$errors->get('color')" />
                            </div>

                            <div>
                                <x-input-label for="location" value="保管場所" />
                                <x-text-input id="location" name="location" type="text" class="mt-1 block w-full" :value="old('location')" placeholder="例: 東京都港区" />
                                <x-input-error class="mt-1.5" :messages="$errors->get('location')" />
                            </div>
                        </div>
                    </div>

                    {{-- 車両履歴 --}}
                    <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                            <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                車両履歴
                            </h3>
                        </div>
                        <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-5">
                            <div>
                                <x-input-label for="accident_count" value="事故歴（回数）" />
                                <x-text-input id="accident_count" name="accident_count" type="number" min="0" max="99" class="mt-1 block w-full" :value="old('accident_count', 0)" />
                                <x-input-error class="mt-1.5" :messages="$errors->get('accident_count')" />
                            </div>

                            <div>
                                <x-input-label for="inspection_expiry" value="車検有効期限" />
                                <x-text-input id="inspection_expiry" name="inspection_expiry" type="date" class="mt-1 block w-full" :value="old('inspection_expiry')" />
                                <x-input-error class="mt-1.5" :messages="$errors->get('inspection_expiry')" />
                            </div>

                            <div class="flex flex-col justify-end gap-4">
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <div class="relative">
                                        <input id="has_service_record" type="checkbox" name="has_service_record" value="1"
                                               class="sr-only peer"
                                               @checked(old('has_service_record'))>
                                        <div class="w-10 h-6 bg-gray-200 rounded-full peer-checked:bg-indigo-500 transition-colors"></div>
                                        <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                                    </div>
                                    <span class="text-sm text-gray-700">整備記録あり</span>
                                </label>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <div class="relative">
                                        <input id="featured" type="checkbox" name="featured" value="1"
                                               class="sr-only peer"
                                               @checked(old('featured'))>
                                        <div class="w-10 h-6 bg-gray-200 rounded-full peer-checked:bg-yellow-400 transition-colors"></div>
                                        <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                                    </div>
                                    <span class="text-sm text-gray-700">★ 注目車両として表示</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- 画像 --}}
                    <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                            <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                画像
                            </h3>
                        </div>
                        <div class="p-6 space-y-5">
                            <div x-data="imagePreview()" class="space-y-2">
                                <x-input-label value="メイン画像" />
                                <div class="flex items-start gap-4">
                                    <div x-show="previewUrl" class="flex-shrink-0">
                                        <img :src="previewUrl" class="h-32 w-44 object-cover rounded-lg border border-gray-200 shadow-sm">
                                    </div>
                                    <div class="flex-1">
                                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition" :class="previewUrl ? 'hidden' : ''">
                                            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                            <span class="text-sm text-gray-500">クリックして画像を選択</span>
                                            <span class="text-xs text-gray-400 mt-1">JPEG / PNG / WebP、最大 5MB</span>
                                            <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="hidden"
                                                   @change="onFile($event)">
                                        </label>
                                        <div x-show="previewUrl" class="flex items-center gap-2 mt-2">
                                            <span class="text-xs text-gray-500" x-text="fileName"></span>
                                            <button type="button" @click="clear()" class="text-xs text-red-500 hover:text-red-700">変更する</button>
                                        </div>
                                    </div>
                                </div>
                                <x-input-error class="mt-1" :messages="$errors->get('image')" />
                            </div>

                            <div class="border-t border-gray-100 pt-5">
                                <x-input-label for="images" value="ギャラリー画像（複数可）" />
                                <div class="mt-2">
                                    <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition">
                                        <svg class="w-6 h-6 text-gray-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/></svg>
                                        <span class="text-sm text-gray-500">ギャラリー画像を追加</span>
                                        <span class="text-xs text-gray-400">最大10枚、各5MBまで</span>
                                        <input id="images" name="images[]" type="file" accept="image/jpeg,image/png,image/webp" multiple class="hidden">
                                    </label>
                                </div>
                                <x-input-error class="mt-1.5" :messages="$errors->get('images.*')" />
                            </div>
                        </div>
                    </div>

                    {{-- 説明文 --}}
                    <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                            <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h12"/></svg>
                                車両詳細説明
                            </h3>
                        </div>
                        <div class="p-6">
                            <textarea id="description" name="description"
                                      class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                      rows="8" placeholder="車両の特徴や状態など詳細を記入してください">{{ old('description') }}</textarea>
                            <x-input-error class="mt-1.5" :messages="$errors->get('description')" />
                        </div>
                    </div>

                    {{-- 装備仕様 --}}
                    <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                            <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                装備仕様
                            </h3>
                        </div>
                        <div class="p-6 space-y-6">
                            @php $oldEquipment = old('equipment', []); @endphp
                            @foreach(\App\Models\Car::EQUIPMENT_CATEGORIES as $category => $items)
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">{{ $category }}</p>
                                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                                    @foreach($items as $item)
                                    <label class="flex items-center gap-2 cursor-pointer select-none">
                                        <input type="checkbox" name="equipment[]" value="{{ $item }}"
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                               @checked(in_array($item, $oldEquipment))>
                                        <span class="text-sm text-gray-700">{{ $item }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                </div>

                {{-- フッターアクション --}}
                <div class="sticky bottom-0 mt-6 bg-white border border-gray-200 rounded-xl shadow-lg px-6 py-4 flex items-center justify-between">
                    <a href="{{ route('admin.cars.index') }}"
                       class="text-sm text-gray-600 hover:text-gray-900 font-medium transition">
                        ← 一覧に戻る
                    </a>
                    <x-primary-button class="px-8">
                        登録する
                    </x-primary-button>
                </div>

            </form>
        </div>
    </div>
</x-app-layout>

