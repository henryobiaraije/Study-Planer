export function initDevTools() {
    setTimeout(() => {
        // @ts-ignore
        const app = Array.from(document.querySelectorAll('*')).find((e) => e.__vue_app__).__vue_app__
        const version = app.version
        // @ts-ignore
        const devtools = window.__VUE_DEVTOOLS_GLOBAL_HOOK__
        devtools.enabled = true
        devtools.emit('app:init', app, version, {})
        console.log('vue devtools initialized')
    }, 5000);
}