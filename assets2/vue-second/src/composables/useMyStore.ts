import {reactive} from "vue";

const store = reactive({
    inAddCards: false,
});

export default function () {

    return {
        store
    }
}