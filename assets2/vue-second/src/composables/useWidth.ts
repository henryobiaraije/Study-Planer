import {onMounted, onUnmounted, ref} from "vue";


export default function () {
    const width = ref(window.innerWidth)
    const updateWidth = () => {
        width.value = window.innerWidth
    }
    onMounted(() => {
        window.addEventListener('resize', updateWidth)
    })
    onUnmounted(() => {
        window.removeEventListener('resize', updateWidth)
    })

    const isMobile = () => {
        return width.value < 768;
    }

    return {
        width,
        isMobile
    };
}