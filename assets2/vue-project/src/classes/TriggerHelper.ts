import type {_CardGroup, TriggerType} from "@/interfaces/inter-sp";


export class TriggerHelper {

    /**
     * The callbackToOff is needed as the callback is wrapped in another function.
     * @private
     */
    private static eventsAndCallbacks: Array<{
        type: keyof TriggerType,
        callback: (data: TriggerType[keyof TriggerType]) => void,
        callbackToOff: (e: any, data: { data: TriggerType[keyof TriggerType] }) => void,
    }> = [];

    /**
     *
     * @param type
     * @param data The corresponding value of the key in TriggerType.
     */
    public static trigger<T extends keyof TriggerType>(type: T, data?: TriggerType[T]) {
        if (data === undefined) {
            data = null;
        }
        // Enclose the data in an object as if not, the data gets messed up for some reason.
        jQuery('body').trigger(type, {data: data});
    }

    public static on<T extends keyof TriggerType>(type: T, callback: (data: TriggerType[T]) => void) {
        // Get a custom callback to work with.
        const callbackToOff = (e: any, dataInner: { data: TriggerType[T] }) => {
            callback(dataInner.data);
        };

        // Add the custom callback to the event stack.
        jQuery('body').on(type, callbackToOff);

        // Store the custom callback to remove it later.
        TriggerHelper.eventsAndCallbacks.push({
            type: type,
            callback: callback,
            callbackToOff: callbackToOff,
        });
    }

    /**
     * Remove the callback from event stack.
     * @param type
     * @param callback
     */
    public static off<T extends keyof TriggerType>(type: T, callback: (e: any, data: TriggerType[T]) => void) {
        TriggerHelper.eventsAndCallbacks = TriggerHelper.eventsAndCallbacks.filter((item) => {
            if (item.type === type && item.callback === callback) {
                jQuery('body').off(type, item.callbackToOff);
                return false;
            }
            return true;
        });
    }
}
