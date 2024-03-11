import {reactive} from "vue";

/**
 * Holds deck groups, decks that are currently toggled.
 */
const toggleStore = reactive<string[]>([]);
export default function () {

    const toggle = (keyPlusId: string): void => {
        if (toggleStore.includes(keyPlusId)) {
            toggleStore.splice(toggleStore.indexOf(keyPlusId), 1);
        } else {
            toggleStore.push(keyPlusId);
        }
    }
    const isToggled = (keyPlusId: string): boolean => {
        return toggleStore.includes(keyPlusId);
    }

    return {
        toggle, isToggled, toggleStore
    }
}