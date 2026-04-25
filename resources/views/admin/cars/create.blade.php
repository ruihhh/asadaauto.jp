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

                            <div>
                                <x-input-label for="status" value="ステータス *" />
                                <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="available" @selected(old('status', 'available') === 'available')>販売中</option>
                                    <option value="reserved"  @selected(old('status') === 'reserved')>商談中</option>
                                    <option value="sold"      @selected(old('status') === 'sold')>売約済</option>
                                </select>
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

                            <div>
                                <x-input-label for="body_type" value="ボディタイプ *" />
                                <select id="body_type" name="body_type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">選択してください</option>
                                    @foreach(['セダン','SUV','ミニバン','ハッチバック','クーペ','コンパクト','軽自動車','ステーションワゴン','トラック','その他'] as $bt)
                                        <option value="{{ $bt }}" @selected(old('body_type') === $bt)>{{ $bt }}</option>
                                    @endforeach
                                </select>
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
                            <div>
                                <x-input-label for="price" value="価格（税込）*" />
                                <div class="relative mt-1">
                                    <x-text-input id="price" name="price" type="number" min="0" class="block w-full pr-8" :value="old('price')" required placeholder="0" />
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-gray-400">円</span>
                                </div>
                                <x-input-error class="mt-1.5" :messages="$errors->get('price')" />
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

                            <div>
                                <x-input-label for="transmission" value="トランスミッション *" />
                                <select id="transmission" name="transmission" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">選択してください</option>
                                    @foreach(['AT','CVT','MT','AMT','DCT'] as $t)
                                        <option value="{{ $t }}" @selected(old('transmission') === $t)>{{ $t }}</option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-1.5" :messages="$errors->get('transmission')" />
                            </div>

                            <div>
                                <x-input-label for="fuel_type" value="燃料 *" />
                                <select id="fuel_type" name="fuel_type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">選択してください</option>
                                    @foreach(['ガソリン','ディーゼル','ハイブリッド','プラグインハイブリッド','電気','LPG'] as $f)
                                        <option value="{{ $f }}" @selected(old('fuel_type') === $f)>{{ $f }}</option>
                                    @endforeach
                                </select>
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

                            <div class="flex flex-col justify-end gap-3">
                                <label class="inline-flex items-center gap-2.5 cursor-pointer">
                                    <input id="has_service_record" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 w-4 h-4" name="has_service_record" value="1" @checked(old('has_service_record'))>
                                    <span class="text-sm text-gray-700">整備記録あり</span>
                                </label>
                                <label class="inline-flex items-center gap-2.5 cursor-pointer">
                                    <input id="featured" type="checkbox" class="rounded border-gray-300 text-yellow-500 shadow-sm focus:ring-yellow-400 w-4 h-4" name="featured" value="1" @checked(old('featured'))>
                                    <span class="text-sm text-gray-700">★ 注目車両として表示する</span>
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

