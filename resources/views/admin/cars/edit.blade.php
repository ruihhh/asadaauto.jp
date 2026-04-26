<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3 min-w-0">
                <a href="{{ route('admin.cars.index') }}"
                   class="flex-shrink-0 w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-500 hover:text-gray-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div class="min-w-0">
                    <h2 class="font-bold text-xl text-gray-800 leading-tight truncate">
                        {{ $car->make }} {{ $car->model }}
                        @if($car->grade)<span class="text-gray-400 font-normal"> / {{ $car->grade }}</span>@endif
                    </h2>
                    <p class="text-xs text-gray-400 mt-0.5 font-mono">{{ $car->stock_no }}</p>
                </div>
            </div>
            <button type="submit" form="car-edit-form"
                    class="hidden lg:inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-5 rounded-lg text-sm shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                更新する
            </button>
        </div>
    </x-slot>

    {{-- ページ固有データを window 変数へ（HTML属性内の@jsonを避けるため） --}}
    <script>
        window.__carEdit = {
            make:       @json(old('make',  $car->make)),
            model:      @json(old('model', $car->model)),
            grade:      @json(old('grade', $car->grade)),
            masterData: @json($masterData),
            price:      @json(old('price', $car->price)),
            base_price: @json(old('base_price', $car->base_price)),
        };
    </script>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6" role="alert">
                    <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif

            <form action="{{ route('admin.cars.update', $car) }}" method="POST" enctype="multipart/form-data"
                  id="car-edit-form"
                  x-data="carEditForm()"
                  @submit.once="isDirty = false">
                @csrf
                @method('PATCH')

                <div class="flex flex-col lg:flex-row gap-6 items-start">

                    {{-- ===== メインフォーム（左） ===== --}}
                    <div class="flex-1 min-w-0 space-y-5">

                        {{-- 基本情報 --}}
                        <section id="sec-basic" class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden scroll-mt-6">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a2 2 0 012-2z"/></svg>
                                <h3 class="text-sm font-semibold text-gray-700">基本情報</h3>
                            </div>
                            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">

                                <div>
                                    <x-input-label for="stock_no" value="在庫番号 *" />
                                    <x-text-input id="stock_no" name="stock_no" type="text"
                                                  class="mt-1 block w-full font-mono text-sm"
                                                  :value="old('stock_no', $car->stock_no)" required
                                                  @change="isDirty = true" />
                                    <x-input-error class="mt-1.5" :messages="$errors->get('stock_no')" />
                                </div>

                                <div x-data="{ statusVal: '{{ old('status', $car->status) }}' }">
                                    <x-input-label value="ステータス *" />
                                    <div class="mt-1 grid grid-cols-3 gap-2">

                                        {{-- 販売中 --}}
                                        <label class="cursor-pointer" @click="statusVal = 'available'; isDirty = true">
                                            <input type="radio" name="status" value="available" class="sr-only"
                                                   @if(old('status', $car->status) === 'available') checked @endif>
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
                                        <label class="cursor-pointer" @click="statusVal = 'reserved'; isDirty = true">
                                            <input type="radio" name="status" value="reserved" class="sr-only"
                                                   @if(old('status', $car->status) === 'reserved') checked @endif>
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
                                        <label class="cursor-pointer" @click="statusVal = 'sold'; isDirty = true">
                                            <input type="radio" name="status" value="sold" class="sr-only"
                                                   @if(old('status', $car->status) === 'sold') checked @endif>
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
                                           x-model="make" @change="onMakeChange(); isDirty = true"
                                           class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                           required autocomplete="off">
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
                                           x-model="model" @change="onModelChange(); isDirty = true"
                                           class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                           required autocomplete="off">
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
                                           x-model="grade" @change="isDirty = true"
                                           class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                                           autocomplete="off">
                                    <datalist id="grade-list">
                                        <template x-for="g in filteredGrades" :key="g">
                                            <option :value="g"></option>
                                        </template>
                                    </datalist>
                                    <x-input-error class="mt-1.5" :messages="$errors->get('grade')" />
                                </div>

                                <div class="sm:col-span-2" x-data="{ bodyTypeVal: '{{ old('body_type', $car->body_type) }}' }">
                                    <x-input-label value="ボディタイプ *" />
                                    <input type="hidden" name="body_type" :value="bodyTypeVal">
                                    <div class="mt-1 grid grid-cols-5 gap-2">
                                        @foreach([['セダン','セダン'],['SUV','SUV'],['ミニバン','ミニバン'],['HB','ハッチバック'],['クーペ','クーペ'],['コンパクト','コンパクト'],['軽自動車','軽自動車'],['ワゴン','ステーションワゴン'],['トラック','トラック'],['その他','その他']] as [$label, $value])
                                        <button type="button"
                                                @click="bodyTypeVal = '{{ $value }}'; isDirty = true"
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

                                <div>
                                    <x-input-label for="color" value="車体色" />
                                    <x-text-input id="color" name="color" type="text" class="mt-1 block w-full text-sm"
                                                  :value="old('color', $car->color ?? '')"
                                                  @change="isDirty = true" />
                                    <x-input-error class="mt-1.5" :messages="$errors->get('color')" />
                                </div>

                                <div>
                                    <x-input-label for="location" value="保管場所" />
                                    <x-text-input id="location" name="location" type="text" class="mt-1 block w-full text-sm"
                                                  :value="old('location', $car->location ?? '')"
                                                  @change="isDirty = true" />
                                    <x-input-error class="mt-1.5" :messages="$errors->get('location')" />
                                </div>
                            </div>
                        </section>

                        {{-- スペック --}}
                        <section id="sec-spec" class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden scroll-mt-6">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                <h3 class="text-sm font-semibold text-gray-700">スペック</h3>
                            </div>
                            <div class="p-6 grid grid-cols-2 sm:grid-cols-3 gap-5">
                                <div x-data="{ negotiable: {{ old('price_negotiable', $car->price_negotiable) ? 'true' : 'false' }} }">
                                    <x-input-label value="価格" />
                                    <label class="flex items-center gap-2 mt-1 mb-2 cursor-pointer select-none">
                                        <input type="checkbox" name="price_negotiable" value="1"
                                               x-model="negotiable" @change="isDirty = true"
                                               {{ old('price_negotiable', $car->price_negotiable) ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span class="text-xs font-semibold text-gray-700">応談にする</span>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-bold bg-amber-100 text-amber-700 border border-amber-300">応談</span>
                                    </label>
                                    <div x-show="!negotiable" x-cloak class="space-y-3">
                                        <div>
                                            <x-input-label for="price" value="支払総額（税込）*" />
                                            <div class="relative mt-1">
                                                <x-text-input id="price" name="price" type="number" min="0"
                                                              class="block w-full pr-8 text-sm"
                                                              value="{{ old('price', $car->price) }}"
                                                              @input="updatePriceDisplay($el.value); isDirty = true" />
                                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">円</span>
                                            </div>
                                            <p class="mt-1 text-xs text-indigo-600 font-medium h-4" x-text="priceDisplay"></p>
                                            <x-input-error class="mt-0.5" :messages="$errors->get('price')" />
                                        </div>
                                        <div>
                                            <x-input-label for="base_price" value="車両本体価格" />
                                            <div class="relative mt-1">
                                                <x-text-input id="base_price" name="base_price" type="number" min="0"
                                                              class="block w-full pr-8 text-sm"
                                                              value="{{ old('base_price', $car->base_price) }}"
                                                              @change="isDirty = true" />
                                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">円</span>
                                            </div>
                                            <x-input-error class="mt-0.5" :messages="$errors->get('base_price')" />
                                        </div>
                                    </div>
                                    <div x-show="negotiable" x-cloak>
                                        <p class="text-xs text-amber-700 font-semibold bg-amber-50 border border-amber-200 rounded px-2 py-1.5">価格は「応談」として表示されます</p>
                                    </div>
                                </div>

                                <div>
                                    <x-input-label for="model_year" value="年式 *" />
                                    <div class="relative mt-1">
                                        <x-text-input id="model_year" name="model_year" type="number"
                                                      min="1900" max="{{ date('Y') + 1 }}"
                                                      class="block w-full pr-8 text-sm"
                                                      value="{{ old('model_year', $car->model_year) }}" required
                                                      @change="isDirty = true" />
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">年</span>
                                    </div>
                                    <x-input-error class="mt-1.5" :messages="$errors->get('model_year')" />
                                </div>

                                <div>
                                    <x-input-label for="mileage" value="走行距離 *" />
                                    <div class="relative mt-1">
                                        <x-text-input id="mileage" name="mileage" type="number" min="0"
                                                      class="block w-full pr-8 text-sm"
                                                      value="{{ old('mileage', $car->mileage) }}" required
                                                      @change="isDirty = true" />
                                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">km</span>
                                    </div>
                                    <x-input-error class="mt-1.5" :messages="$errors->get('mileage')" />
                                </div>

                                <div x-data="{ transmissionVal: '{{ old('transmission', $car->transmission) }}' }">
                                    <x-input-label value="トランスミッション *" />
                                    <input type="hidden" name="transmission" :value="transmissionVal">
                                    <div class="mt-1 flex gap-1.5">
                                        @foreach(['AT','CVT','MT','AMT','DCT'] as $t)
                                        <button type="button"
                                                @click="transmissionVal = '{{ $t }}'; isDirty = true"
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

                                <div x-data="{ fuelTypeVal: '{{ old('fuel_type', $car->fuel_type) }}' }">
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
                                                @click="fuelTypeVal = '{{ $value }}'; isDirty = true"
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
                            </div>
                        </section>

                        {{-- 車両履歴 --}}
                        <section id="sec-history" class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden scroll-mt-6">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                <h3 class="text-sm font-semibold text-gray-700">車両履歴</h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                                    <div>
                                        <x-input-label for="accident_count" value="事故歴（回数）" />
                                        <x-text-input id="accident_count" name="accident_count" type="number"
                                                      min="0" max="99" class="mt-1 block w-full text-sm"
                                                      value="{{ old('accident_count', $car->accident_count) }}"
                                                      @change="isDirty = true" />
                                        <x-input-error class="mt-1.5" :messages="$errors->get('accident_count')" />
                                    </div>

                                    <div>
                                        <x-input-label for="inspection_expiry" value="車検有効期限" />
                                        <x-text-input id="inspection_expiry" name="inspection_expiry" type="date"
                                                      class="mt-1 block w-full text-sm"
                                                      value="{{ old('inspection_expiry', $car->inspection_expiry?->format('Y-m-d')) }}"
                                                      @change="isDirty = true" />
                                        <x-input-error class="mt-1.5" :messages="$errors->get('inspection_expiry')" />
                                    </div>

                                    <div>
                                        <x-input-label for="published_at" value="公開日時" />
                                        <x-text-input id="published_at" name="published_at" type="datetime-local"
                                                      class="mt-1 block w-full text-sm"
                                                      value="{{ old('published_at', $car->published_at?->format('Y-m-d\TH:i')) }}"
                                                      @change="isDirty = true" />
                                        <p class="mt-1 text-xs text-gray-400">空欄の場合は即時公開</p>
                                        <x-input-error class="mt-1" :messages="$errors->get('published_at')" />
                                    </div>
                                </div>

                                <div class="mt-5 flex flex-wrap gap-6">
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <div class="relative">
                                            <input id="has_service_record" type="checkbox" name="has_service_record" value="1"
                                                   class="sr-only peer"
                                                   @checked(old('has_service_record', $car->has_service_record))
                                                   @change="isDirty = true">
                                            <div class="w-10 h-6 bg-gray-200 rounded-full peer-checked:bg-indigo-500 transition-colors"></div>
                                            <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                                        </div>
                                        <span class="text-sm text-gray-700">整備記録あり</span>
                                    </label>

                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <div class="relative">
                                            <input id="featured" type="checkbox" name="featured" value="1"
                                                   class="sr-only peer"
                                                   @checked(old('featured', $car->featured))
                                                   @change="isDirty = true">
                                            <div class="w-10 h-6 bg-gray-200 rounded-full peer-checked:bg-yellow-400 transition-colors"></div>
                                            <div class="absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></div>
                                        </div>
                                        <span class="text-sm text-gray-700">★ 注目車両として表示</span>
                                    </label>
                                </div>
                            </div>
                        </section>

                        {{-- 画像管理 --}}
                        <section id="sec-images" class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden scroll-mt-6">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <h3 class="text-sm font-semibold text-gray-700">画像管理</h3>
                                </div>
                                @if($car->images->isNotEmpty())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">
                                        ギャラリー {{ $car->images->count() }}枚
                                    </span>
                                @endif
                            </div>
                            <div class="p-6 space-y-7">

                                {{-- メイン画像 --}}
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">メイン画像</p>
                                        <button type="button" x-show="mainPreview"
                                                @click="mainPreview = null; mainFileName = ''; document.getElementById('image').value = ''"
                                                class="text-xs text-red-500 hover:text-red-700 font-medium">
                                            選択をキャンセル
                                        </button>
                                    </div>
                                    <div class="flex flex-col sm:flex-row gap-4">
                                        @if($car->image_path)
                                            <div class="flex-shrink-0 relative" x-show="!mainPreview">
                                                <img src="{{ '/images/' . $car->image_path }}" alt="メイン画像"
                                                     class="h-44 w-60 object-cover rounded-xl border border-gray-200 shadow-sm">
                                                <span class="absolute bottom-2 left-2 bg-black/60 text-white text-xs px-2 py-0.5 rounded-md">現在の画像</span>
                                            </div>
                                        @endif
                                        <div class="flex-shrink-0 relative" x-show="mainPreview">
                                            <img :src="mainPreview" class="h-44 w-60 object-cover rounded-xl border-2 border-indigo-400 shadow-sm">
                                            <span class="absolute bottom-2 left-2 bg-indigo-600/80 text-white text-xs px-2 py-0.5 rounded-md">新しい画像</span>
                                        </div>
                                        <label class="flex-1 flex flex-col items-center justify-center min-h-[11rem] border-2 border-dashed border-gray-200 rounded-xl cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/50 transition group">
                                            <svg class="w-8 h-8 text-gray-300 group-hover:text-indigo-400 mb-2 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                            <p class="text-sm text-gray-500 group-hover:text-indigo-600 font-medium transition">{{ $car->image_path ? '画像を差し替える' : '画像を選択' }}</p>
                                            <p class="text-xs text-gray-400 mt-1">JPEG / PNG / WebP、最大 5MB</p>
                                            <span x-show="mainFileName" class="mt-2 text-xs text-indigo-600 font-medium truncate max-w-[180px]" x-text="mainFileName"></span>
                                            <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp"
                                                   class="hidden" @change="onMainImage($event); isDirty = true">
                                        </label>
                                    </div>
                                    <x-input-error :messages="$errors->get('image')" />
                                </div>

                                {{-- ギャラリー --}}
                                <div class="border-t border-gray-100 pt-6 space-y-3">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">ギャラリー画像</p>

                                    @if($car->images->isNotEmpty())
                                        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                                            @foreach($car->images->sortBy('sort_order') as $img)
                                                <div class="relative group aspect-[4/3]">
                                                    <img src="{{ '/images/' . $img->path }}"
                                                         class="w-full h-full object-cover rounded-lg border border-gray-200 shadow-sm transition group-hover:brightness-75">
                                                    <button type="button"
                                                            class="absolute top-1.5 right-1.5 opacity-0 group-hover:opacity-100 transition w-7 h-7 rounded-full bg-red-600 hover:bg-red-700 text-white flex items-center justify-center shadow-md"
                                                            onclick="if(confirm('この画像を削除しますか？')){
                                                                fetch('{{ route('admin.cars.images.destroy', [$car, $img]) }}', {
                                                                    method: 'POST',
                                                                    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/x-www-form-urlencoded'},
                                                                    body: '_method=DELETE'
                                                                }).then(() => location.reload());
                                                            }">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-sm text-gray-400">ギャラリー画像はまだありません</p>
                                    @endif

                                    {{-- アップロード前プレビュー --}}
                                    <template x-if="galleryPreviews.length > 0">
                                        <div>
                                            <p class="text-xs text-indigo-600 font-medium mb-2">追加予定（<span x-text="galleryPreviews.length"></span>枚）</p>
                                            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
                                                <template x-for="(p, i) in galleryPreviews" :key="i">
                                                    <div class="relative aspect-[4/3]">
                                                        <img :src="p" class="w-full h-full object-cover rounded-lg border-2 border-indigo-300 shadow-sm">
                                                        <button type="button" @click="removeGalleryPreview(i)"
                                                                class="absolute top-1 right-1 w-6 h-6 rounded-full bg-red-600 text-white flex items-center justify-center shadow text-xs">×</button>
                                                        <span class="absolute bottom-1 left-1 bg-indigo-600/80 text-white text-[10px] px-1.5 py-0.5 rounded">追加</span>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    <label class="flex flex-col items-center justify-center w-full h-20 border-2 border-dashed border-gray-200 rounded-xl cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/50 transition group">
                                        <svg class="w-5 h-5 text-gray-300 group-hover:text-indigo-400 mb-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        <span class="text-sm text-gray-500 group-hover:text-indigo-600 transition">ギャラリー画像を追加</span>
                                        <span class="text-xs text-gray-400">複数選択可、各5MBまで</span>
                                        <input id="images" name="images[]" type="file"
                                               accept="image/jpeg,image/png,image/webp" multiple
                                               class="hidden" @change="onGalleryImages($event); isDirty = true">
                                    </label>
                                    <x-input-error :messages="$errors->get('images.*')" />
                                </div>
                            </div>
                        </section>

                        {{-- 説明文 --}}
                        <section id="sec-desc" class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden scroll-mt-6">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h12"/></svg>
                                    <h3 class="text-sm font-semibold text-gray-700">車両詳細説明</h3>
                                </div>
                                <span class="text-xs text-gray-400"><span x-text="descCharCount"></span> 文字</span>
                            </div>
                            <div class="p-6">
                                <textarea id="description" name="description"
                                          class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm leading-relaxed"
                                          rows="10"
                                          @input="descCharCount = $el.value.length; isDirty = true"
                                          placeholder="車両の特徴、装備内容、状態など詳細を記入してください">{{ old('description', $car->description) }}</textarea>
                                <x-input-error class="mt-1.5" :messages="$errors->get('description')" />
                            </div>
                        </section>

                    </div>{{-- /メインフォーム --}}

            </form>{{-- /car-edit-form ここで閉じる（サイドバーのネスト防止） --}}

                    {{-- ===== 右サイドバー ===== --}}
                    <aside class="w-full lg:w-72 flex-shrink-0 space-y-4 lg:sticky lg:top-6">

                        {{-- 車両サマリー --}}
                        <div class="bg-white border border-gray-200 shadow-sm rounded-xl overflow-hidden">
                            @if($car->image_path)
                                <img src="{{ '/images/' . $car->image_path }}" alt="{{ $car->make }} {{ $car->model }}"
                                     class="w-full h-40 object-cover">
                            @else
                                <div class="w-full h-32 bg-gray-100 flex items-center justify-center">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif
                            <div class="px-4 py-4 space-y-3">
                                <div>
                                    <p class="font-bold text-gray-800 text-sm">{{ $car->make }} {{ $car->model }}</p>
                                    @if($car->grade)<p class="text-xs text-gray-500">{{ $car->grade }}</p>@endif
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div class="bg-gray-50 rounded-lg px-3 py-2">
                                        <p class="text-gray-400">支払総額</p>
                                        @if($car->price_negotiable)
                                            <p class="font-bold text-amber-600 mt-0.5">応談</p>
                                        @else
                                            <p class="font-bold text-gray-700 mt-0.5">{{ number_format($car->price) }}円</p>
                                        @endif
                                    </div>
                                    <div class="bg-gray-50 rounded-lg px-3 py-2">
                                        <p class="text-gray-400">年式</p>
                                        <p class="font-bold text-gray-700 mt-0.5">{{ $car->model_year }}年</p>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg px-3 py-2">
                                        <p class="text-gray-400">走行</p>
                                        <p class="font-bold text-gray-700 mt-0.5">{{ number_format($car->mileage) }}km</p>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg px-3 py-2">
                                        <p class="text-gray-400">登録</p>
                                        <p class="font-bold text-gray-700 mt-0.5">{{ $car->created_at->format('Y/m/d') }}</p>
                                    </div>
                                </div>
                                @if($car->images->isNotEmpty())
                                    <div class="flex gap-1 overflow-hidden rounded-md">
                                        @foreach($car->images->take(4) as $img)
                                            <img src="{{ '/images/' . $img->path }}" class="h-10 flex-1 object-cover min-w-0">
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- セクションナビ --}}
                        <div class="bg-white border border-gray-200 shadow-sm rounded-xl px-4 py-4 hidden lg:block">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-3">セクション</p>
                            <nav class="space-y-1">
                                @foreach([
                                    ['id' => 'sec-basic',   'label' => '基本情報'],
                                    ['id' => 'sec-spec',    'label' => 'スペック'],
                                    ['id' => 'sec-history', 'label' => '車両履歴'],
                                    ['id' => 'sec-images',  'label' => '画像管理'],
                                    ['id' => 'sec-desc',    'label' => '詳細説明'],
                                ] as $nav)
                                    <a href="#{{ $nav['id'] }}"
                                       class="block text-sm text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 px-3 py-1.5 rounded-md transition">
                                        {{ $nav['label'] }}
                                    </a>
                                @endforeach
                            </nav>
                        </div>

                        {{-- 保存ボタン --}}
                        <button type="submit" form="car-edit-form"
                                class="w-full flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-5 rounded-xl text-sm shadow-sm transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            更新する
                        </button>

                        {{-- 未保存インジケーター --}}
                        <div x-show="isDirty"
                             class="flex items-center gap-2 text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            未保存の変更があります
                        </div>

                        {{-- 危険ゾーン --}}
                        <div class="bg-white border border-red-100 shadow-sm rounded-xl px-4 py-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-red-400 mb-3">操作</p>
                            <form action="{{ route('admin.cars.destroy', $car) }}" method="POST"
                                  onsubmit="return confirm('「{{ $car->make }} {{ $car->model }}」を完全に削除しますか？\nこの操作は取り消せません。');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="w-full flex items-center justify-center gap-2 text-sm font-semibold text-red-600 hover:text-red-700 border border-red-200 hover:border-red-300 hover:bg-red-50 rounded-lg py-2.5 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    この車両を削除する
                                </button>
                            </form>
                        </div>

                    </aside>{{-- /サイドバー --}}

                </div>{{-- /flex --}}
        </div>
    </div>

@push('scripts')
<script>
function carEditForm() {
    const d = window.__carEdit;
    return {
        // マスターデータ連動
        make:       d.make  ?? '',
        model:      d.model ?? '',
        grade:      d.grade ?? '',
        masterData: d.masterData ?? { makes: [], models: {}, grades: {} },

        // UI状態
        isDirty:        false,
        mainPreview:    null,
        mainFileName:   '',
        galleryPreviews: [],
        galleryFiles:   [],
        priceDisplay:   '',
        descCharCount:  {{ mb_strlen(old('description', $car->description ?? '')) }},

        init() {
            // 価格表示初期化
            const p = d.price;
            if (p) this.priceDisplay = this.formatWan(parseInt(p));

            // 離脱警告
            window.addEventListener('beforeunload', (e) => {
                if (this.isDirty) { e.preventDefault(); e.returnValue = ''; }
            });
        },

        // メーカー/モデル連動
        get filteredModels() {
            return this.masterData.models[this.make] ?? [];
        },
        get filteredGrades() {
            return this.masterData.grades[this.make + '@@' + this.model] ?? [];
        },
        onMakeChange() {
            if (!this.filteredModels.includes(this.model)) { this.model = ''; this.grade = ''; }
        },
        onModelChange() {
            if (!this.filteredGrades.includes(this.grade)) { this.grade = ''; }
        },

        // 価格表示
        formatWan(n) {
            if (!n || isNaN(n)) return '';
            if (n >= 10000) {
                const man = Math.floor(n / 10000);
                const rem = n % 10000;
                return rem > 0 ? `約 ${man}万${rem.toLocaleString()}円` : `${man}万円`;
            }
            return n.toLocaleString() + '円';
        },
        updatePriceDisplay(val) {
            this.priceDisplay = this.formatWan(parseInt(val));
        },

        // メイン画像プレビュー
        onMainImage(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.mainFileName = file.name;
            const reader = new FileReader();
            reader.onload = (e) => { this.mainPreview = e.target.result; };
            reader.readAsDataURL(file);
        },

        // ギャラリー画像プレビュー
        onGalleryImages(event) {
            Array.from(event.target.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = (e) => { this.galleryPreviews.push(e.target.result); };
                reader.readAsDataURL(file);
                this.galleryFiles.push(file);
            });
        },
        removeGalleryPreview(index) {
            this.galleryPreviews.splice(index, 1);
            this.galleryFiles.splice(index, 1);
            const input = document.getElementById('images');
            const dt = new DataTransfer();
            this.galleryFiles.forEach(f => dt.items.add(f));
            input.files = dt.files;
        },
    };
}
</script>
@endpush
</x-app-layout>
