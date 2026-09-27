@props([])
{{--
    比較トレイ（白地・上に赤い線）。$store.compare.count が1以上のとき画面の下に固定表示する（スマホは固定下部バーの上）。
    車名の × で外すと、トースト「◯◯を比較から外しました［元に戻す］」が出る（$store.compare.dismiss）。
    layouts/site から呼ぶ（cars.compare のページでは出さない）。表示中は body に has-compare-tray を付けて下余白を足す。

    使い方: <x-site.compare-tray />（props なし）
    出力: <section class="l-compare-tray" aria-label="比較する車"><div class="l-container l-compare-tray__inner">
          <p class="l-compare-tray__title">比較する車（2台）</p><ul class="l-compare-tray__chips"><li class="l-compare-tray__chip">
          車名<button class="l-compare-tray__remove" aria-label="◯◯を比較から外す">×</button></li></ul>
          <a class="c-btn c-btn--primary c-btn--sm l-compare-tray__go">比較する</a><button class="l-compare-tray__clear">すべて外す</button></div></section>
--}}
<section class="l-compare-tray" aria-label="比較する車"
         x-data x-show="$store.compare.count > 0" x-cloak
         x-effect="document.body.classList.toggle('has-compare-tray', $store.compare.count > 0)">
    <div class="l-container l-compare-tray__inner">
        <p class="l-compare-tray__title"><x-site.icon name="compare" />比較する車（<span x-text="$store.compare.count"></span>台）</p>
        <ul class="l-compare-tray__chips">
            <template x-for="item in $store.compare.items" :key="item.id">
                <li class="l-compare-tray__chip">
                    <span class="l-compare-tray__chip-name" x-text="item.name || ('在庫 ' + item.id)"></span>
                    <button type="button" class="l-compare-tray__remove"
                            :aria-label="(item.name || '選んだ車') + 'を比較から外す'"
                            x-on:click="$store.compare.dismiss(item.id)">
                        <x-site.icon name="close" :size="18" />
                    </button>
                </li>
            </template>
        </ul>
        <a class="c-btn c-btn--primary c-btn--sm l-compare-tray__go" href="{{ route('cars.compare') }}" :href="$store.compare.url">比較する</a>
        <button type="button" class="l-compare-tray__clear" x-on:click="$store.compare.clear()">すべて外す</button>
    </div>
</section>
