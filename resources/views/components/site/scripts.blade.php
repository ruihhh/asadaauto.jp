@props([])
{{--
    公開サイト共通の Alpine ストアと補助スクリプト。layouts/site の末尾で1回だけ呼ぶ（@stack('scripts') より前）。
    Alpine 本体は @vite の resources/js/app.js（type=module＝このスクリプトより後に実行）で読み込まれ、
    ここで登録した alpine:init のリスナーから下のストアが作られる。

    使い方: <x-site.scripts />（props なし）

    $store.favorites（localStorage の既存キー car_favorites をそのまま使う。値は車両 id の配列）
        ids: number[] / count / has(id) / add(id) / remove(id)
        toggle(id) → 'added' | 'removed'（トーストを出す。外したときは［元に戻す］付き）
    $store.compare（localStorage のキー car_compare。値は [{id, name}]）
        items: {id, name}[] / ids / count / max（3）/ has(id) / clear()
        remove(id)  … 何も知らせずに外す（ページ側で知らせるとき・表示できない車を外すとき）
        dismiss(id) … 外して、トースト「◯◯を比較から外しました［元に戻す］」を出す（比較トレイの ×）。［元に戻す］で同じ位置に戻る
        toggle(id, name) → 'added' | 'removed' | 'full'（4台目はトースト「比較できるのは3台までです」。外すときは dismiss と同じ）
        url（route('cars.compare') に ?ids=1,2,3 を付けたもの）
    $store.toast
        show(message, action = null)  … action は { label: '元に戻す', handler: () => {…} }。4秒で消える（ポインタ・フォーカス中は止める）
        hide() / runAction()
    $store.menu（スマホメニュー）
        open / show(opener) / close(restoreFocus = true) / toggle(opener) / trap(event, panel)

    そのほか：入力欄にフォーカスしている間は html に is-typing を付ける（固定下部バー・比較トレイを隠す）。
    別のタブでお気に入り・比較を変えたときは storage イベントで反映する。
    <form data-submit-once> は、送信したら送信ボタンを disabled にして「送信しています…」に変える（ボタンの data-busy-label で文言を変更可）。
    ページ側の x-on:submit.prevent などで送信を止めたとき（defaultPrevented）は何もしない。
    localStorage の読み書きはすべて try/catch（プライベートブラウズなどで保存できないときはトーストで知らせる）。
--}}
<script>
(() => {
    'use strict';

    const FAVORITES_KEY = 'car_favorites'; // 既存の保存キー。変えると利用者の登録が消えるので変更しない
    const COMPARE_KEY = 'car_compare';
    const COMPARE_URL = @js(route('cars.compare'));
    const COMPARE_MAX = 3;
    const TOAST_MS = 4000;
    const SAVE_FAILED = 'この端末のブラウザに保存できませんでした（プライベートブラウズなどでは保存されません）';

    const readList = (key) => {
        try {
            const value = JSON.parse(window.localStorage.getItem(key) || '[]');
            return Array.isArray(value) ? value : [];
        } catch (e) {
            return [];
        }
    };

    const writeList = (key, list) => {
        try {
            window.localStorage.setItem(key, JSON.stringify(list));
            return true;
        } catch (e) {
            return false;
        }
    };

    const toId = (value) => {
        const id = Number(value);
        return Number.isInteger(id) && id > 0 ? id : null;
    };

    const normalizeIds = (list) => {
        const ids = [];
        list.forEach((value) => {
            const id = toId(value);
            if (id !== null && !ids.includes(id)) ids.push(id);
        });
        return ids;
    };

    const normalizeItems = (list) => {
        const items = [];
        list.forEach((value) => {
            const isObject = value !== null && typeof value === 'object';
            const id = toId(isObject ? value.id : value);
            if (id === null || items.some((item) => item.id === id)) return;
            items.push({ id, name: isObject && value.name ? String(value.name) : '' });
        });
        return items.slice(0, COMPARE_MAX);
    };

    // DOM 要素はストア（リアクティブ）に入れず、ここで持つ
    let menuOpener = null;
    let toastTimer = null;
    let toastHandler = null;

    document.addEventListener('alpine:init', () => {
        const Alpine = window.Alpine;

        Alpine.store('toast', {
            visible: false,
            message: '',
            actionLabel: '',
            show(message, action = null) {
                window.clearTimeout(toastTimer);
                this.message = String(message);
                this.actionLabel = action && action.label ? String(action.label) : '';
                toastHandler = action && typeof action.handler === 'function' ? action.handler : null;
                this.visible = true;
                this.resume();
            },
            hide() {
                window.clearTimeout(toastTimer);
                this.visible = false;
                this.actionLabel = '';
                toastHandler = null;
            },
            runAction() {
                const handler = toastHandler;
                this.hide();
                if (handler) handler();
            },
            pause() {
                window.clearTimeout(toastTimer);
            },
            resume() {
                window.clearTimeout(toastTimer);
                if (this.visible) toastTimer = window.setTimeout(() => this.hide(), TOAST_MS);
            },
        });

        Alpine.store('favorites', {
            ids: [],
            init() {
                this.ids = normalizeIds(readList(FAVORITES_KEY));
            },
            get count() {
                return this.ids.length;
            },
            has(id) {
                return this.ids.includes(toId(id));
            },
            add(id, index = null) {
                id = toId(id);
                if (id === null || this.has(id)) return false;
                const ids = this.ids.slice();
                ids.splice(index === null ? ids.length : index, 0, id);
                this.ids = ids;
                return this.save();
            },
            remove(id) {
                id = toId(id);
                if (!this.has(id)) return false;
                this.ids = this.ids.filter((value) => value !== id);
                return this.save();
            },
            toggle(id) {
                id = toId(id);
                if (id === null) return null;
                const toast = Alpine.store('toast');
                if (this.has(id)) {
                    const index = this.ids.indexOf(id);
                    const saved = this.remove(id);
                    toast.show(saved ? 'お気に入りから外しました' : SAVE_FAILED, { label: '元に戻す', handler: () => this.add(id, index) });
                    return 'removed';
                }
                const saved = this.add(id);
                toast.show(saved ? 'お気に入りに追加しました（この端末のブラウザに保存されます）' : SAVE_FAILED);
                return 'added';
            },
            save() {
                return writeList(FAVORITES_KEY, this.ids);
            },
        });

        Alpine.store('compare', {
            items: [],
            max: COMPARE_MAX,
            init() {
                this.items = normalizeItems(readList(COMPARE_KEY));
            },
            get count() {
                return this.items.length;
            },
            get ids() {
                return this.items.map((item) => item.id);
            },
            get url() {
                return this.count > 0 ? COMPARE_URL + '?ids=' + this.ids.join(',') : COMPARE_URL;
            },
            has(id) {
                id = toId(id);
                return this.items.some((item) => item.id === id);
            },
            toggle(id, name = '') {
                id = toId(id);
                if (id === null) return null;
                if (this.has(id)) {
                    this.dismiss(id);
                    return 'removed';
                }
                if (this.count >= this.max) {
                    Alpine.store('toast').show('比較できるのは' + this.max + '台までです');
                    return 'full';
                }
                this.items = this.items.concat([{ id, name: name ? String(name) : '' }]);
                if (!this.save()) Alpine.store('toast').show(SAVE_FAILED);
                return 'added';
            },
            remove(id) {
                id = toId(id);
                this.items = this.items.filter((item) => item.id !== id);
                this.save();
            },
            // 外して、［元に戻す］付きのトーストを出す（お気に入りの toggle と同じふるまい）
            dismiss(id) {
                id = toId(id);
                const index = this.items.findIndex((item) => item.id === id);
                if (index < 0) return false;
                const removed = this.items[index];
                this.remove(id);
                Alpine.store('toast').show((removed.name || '選んだ車') + 'を比較から外しました', {
                    label: '元に戻す',
                    handler: () => {
                        if (this.has(removed.id) || this.count >= this.max) return;
                        const items = this.items.slice();
                        items.splice(Math.min(index, items.length), 0, removed);
                        this.items = items;
                        if (!this.save()) Alpine.store('toast').show(SAVE_FAILED);
                    },
                });
                return true;
            },
            clear() {
                this.items = [];
                this.save();
            },
            save() {
                return writeList(COMPARE_KEY, this.items.map((item) => ({ id: item.id, name: item.name })));
            },
        });

        Alpine.store('menu', {
            open: false,
            show(opener = null) {
                menuOpener = opener || document.activeElement;
                this.open = true;
                document.documentElement.classList.add('is-menu-open');
                Alpine.nextTick(() => {
                    const close = document.querySelector('#site-menu [data-menu-close]');
                    if (close) close.focus();
                });
            },
            close(restoreFocus = true) {
                if (!this.open) return;
                this.open = false;
                document.documentElement.classList.remove('is-menu-open');
                const opener = menuOpener;
                menuOpener = null;
                if (restoreFocus && opener && typeof opener.focus === 'function') opener.focus();
            },
            toggle(opener = null) {
                if (this.open) {
                    this.close();
                } else {
                    this.show(opener);
                }
            },
            // Tab / Shift+Tab でパネルの外へ出ないようにする
            trap(event, panel) {
                const focusable = Array.from(panel.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'))
                    .filter((el) => el.getClientRects().length > 0);
                if (focusable.length === 0) return;
                const first = focusable[0];
                const last = focusable[focusable.length - 1];
                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            },
        });

        // 別のタブでの変更を反映する
        window.addEventListener('storage', (event) => {
            if (event.key === FAVORITES_KEY) Alpine.store('favorites').ids = normalizeIds(readList(FAVORITES_KEY));
            if (event.key === COMPARE_KEY) Alpine.store('compare').items = normalizeItems(readList(COMPARE_KEY));
        });

        // PC幅になったらスマホメニューを閉じる（背面のスクロール止めを残さない）
        const pc = window.matchMedia('(min-width: 960px)');
        pc.addEventListener('change', (event) => {
            if (event.matches) Alpine.store('menu').close(false);
        });
    });

    // 入力中は固定下部バー・比較トレイを隠す（スマホのキーボードとの重なりを防ぐ）
    const TYPING = 'input:not([type="checkbox"]):not([type="radio"]):not([type="submit"]):not([type="button"]):not([type="reset"]):not([type="hidden"]):not([type="range"]):not([type="color"]):not([type="file"]), select, textarea';
    document.addEventListener('focusin', (event) => {
        if (event.target instanceof Element && event.target.matches(TYPING)) document.documentElement.classList.add('is-typing');
    });
    document.addEventListener('focusout', (event) => {
        if (event.target instanceof Element && event.target.matches(TYPING)) document.documentElement.classList.remove('is-typing');
    });

    // 二度押しを防ぐ：<form data-submit-once> を送信したら、送信ボタンを無効にして「送信しています…」（data-busy-label で変更可）に変える。
    // 送る内容は submit の時点で確定しているので、無効にするのは次のタイミングにする（押したボタンの値を送り損ねないため）
    const SUBMIT_BUTTONS = 'button[type="submit"], button:not([type]), input[type="submit"]';
    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || !form.hasAttribute('data-submit-once') || event.defaultPrevented) return;
        const buttons = Array.from(form.querySelectorAll(SUBMIT_BUTTONS));
        window.setTimeout(() => {
            buttons.forEach((button) => {
                const label = button.getAttribute('data-busy-label') || '送信しています…';
                button.dataset.idleLabel = button.tagName === 'INPUT' ? button.value : button.innerHTML;
                button.disabled = true;
                if (button.tagName === 'INPUT') {
                    button.value = label;
                } else {
                    button.textContent = label;
                }
            });
        }, 0);
    });
    // 「戻る」でページが復元されたとき（bfcache）は、送信ボタンを元に戻す
    window.addEventListener('pageshow', (event) => {
        if (!event.persisted) return;
        document.querySelectorAll('form[data-submit-once] [data-idle-label]').forEach((button) => {
            button.disabled = false;
            if (button.tagName === 'INPUT') {
                button.value = button.dataset.idleLabel;
            } else {
                button.innerHTML = button.dataset.idleLabel;
            }
            delete button.dataset.idleLabel;
        });
    });
})();
</script>
