import {createApp} from "vue";

(function () {

    let elem = ".admin-topics";

    // @ts-ignore
    let exist1 = jQuery(elem).length;
    console.log({exist1}, elem);

    function loadInstance1() {
        const app = createApp({
            created() {
                jQuery(elem).css("display", "block");
                jQuery(".mpereere-vue-loading").css("display", "none");
            }
        })

        app.mount(elem);
    }

    if (exist1) {
        loadInstance1();
    } else {
        console.log({exist1});
    }

})();