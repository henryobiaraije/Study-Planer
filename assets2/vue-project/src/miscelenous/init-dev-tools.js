export function initDevTools() {
    setTimeout(() => {
        const app = Array.from(document.querySelectorAll('*')).find((e) => e.__vue_app__).__vue_app__
        const version = app.version
        const devtools = window.__VUE_DEVTOOLS_GLOBAL_HOOK__
        devtools.enabled = true
        devtools.emit('app:init', app, version, {})
    }, 3000);
}