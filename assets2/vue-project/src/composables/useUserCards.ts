// @ts-ignore
import {Ref, ref, watch, watchEffect} from "vue";
import type {_Card, _CardGroup, _Deck, _DeckGroup, _Tag, _Topic, CardType} from "@/interfaces/inter-sp";
import type {_Ajax} from "@/classes/HandleAjax";
import {HandleAjax} from "@/classes/HandleAjax";
import {type InterFuncSuccess, Server} from "@/static/server";
import Cookies from "js-cookie";
import {spClientData} from "@/functions";
import {toast} from "vue3-toastify";

declare var bootstrap;

const tableData = ref({
    columns: [
        {
            label: 'Name',
            field: 'name',
            tooltip: 'Endpoint Name',
        },
        {
            label: 'Deck group',
            field: 'deck_group',
            tooltip: 'Deck group',
        },
        {
            label: 'Tags',
            field: 'tags',
        },
        {
            label: 'Created At',
            field: 'created_at',
        },
        {
            label: 'Updated At',
            field: 'updated_at',
        },
        // {
        //   label: 'Decks',
        //   field: 'decks',
        // },
        // {
        //   label: 'Cards',
        //   field: 'cards',
        // },
    ],
    rows: [],
    isLoading: true,
    totalRecords: 0,
    totalTrashed: 0,
    serverParams: {
        columnFilters: {},
        sort: {
            created_at: '',
            modified_at: '',
        },
        page: 1,
        perPage: 10
    },
    paginationOptions: {
        enabled: true,
        mode: 'page',
        perPage: Cookies.get('spPerPage') ? Number(Cookies.get('spPerPage')) : 2,
        position: 'bottom',
        perPageDropdown: [2, 5, 10, 15, 20, 25, 30, 40, 50, 60, 70, 80, 90, 100, 150, 200, 300, 400, 500, 600, 700],
        dropdownAllowAll: true,
        setCurrentPage: 1,
        nextLabel: 'next',
        prevLabel: 'prev',
        rowsPerPageLabel: 'Rows per page',
        ofLabel: 'of',
        pageLabel: 'page', // for 'pages' mode
        allLabel: 'All',
        //
        // mode: 'records',
        // infoFn: (params) => `my own page ${params.firstRecordOnPage}`,
    },
    searchOptions: {
        enabled: true,
        trigger: '', // can be "enter"
        skipDiacritics: true,
        placeholder: 'Search links',
    },
    sortOption: {
        enabled: false,
    },
    //
    post_status: 'publish',
    selectedRows: [] as Array<_Deck>,
    searchKeyword: '',
});
const totals = ref({
    active: 0,
    trashed: 0
});

export default function (status = 'publish') {
    const ajaxSave = ref<_Ajax>({
        sending: false,
        error: false,
        errorMessage: '',
        success: false,
        successMessage: '',
    });
    const ajaxAssignTopics = ref<_Ajax>({
        sending: false,
        error: false,
        errorMessage: '',
        success: false,
        successMessage: '',
    });
    const ajaxAddCards = ref<_Ajax>({
        sending: false,
        error: false,
        errorMessage: '',
        success: false,
        successMessage: '',
    });
    const ajaxIgnoreCard = ref<_Ajax>({
        sending: false,
        error: false,
        errorMessage: '',
        success: false,
        successMessage: '',
    });
    const ajaxRemoveCard = ref<_Ajax>({
        sending: false,
        error: false,
        errorMessage: '',
        success: false,
        successMessage: '',
    });
    const ajaxLoadUserCard = ref<_Ajax>({
        sending: false,
        error: false,
        errorMessage: '',
        success: false,
        successMessage: '',
    });
    const ajaxLoadDebugForm = ref<_Ajax>({
        sending: false,
        error: false,
        errorMessage: '',
        success: false,
        successMessage: '',
    });


    const theForm = {
        topicToAssign: null as null | _Topic,
        selectedCards: [] as _CardGroup[],
        group: null as null | _DeckGroup,
        deck: null as null | _Deck,
        topic: null as null | _Topic,
        cardTypes: [] as CardType[],
        page: 1,
        activeTab: 'found' as 'found' | 'selected',
        whatToDo: 'selected_cards' as 'selected_cards' | 'selected_group' | 'selected_deck' | 'selected_topic'
    };
    const form = ref<typeof theForm>(theForm);
    const oneSpecificCard = ref<_CardGroup | null>(null)
    // selectedCards: [] as _CardGroup[],
    // page: 1,
    // activeTab: 'found',
    const userDeckGroups = ref<_DeckGroup[]>([]) as Ref<_DeckGroup[]>;
    const debugForm = ref<{
        current_study_date: string,
    }>({
        current_study_date: '',
    });

    watchEffect(() => {
        if (oneSpecificCard.value) {
            // assignForm.value.selectedCards = [oneSpecificCard.value];
            const index = form.value.selectedCards.findIndex((c) => {
                return c.id === oneSpecificCard.value?.id
            });
            if (index === -1) {
                form.value.selectedCards.push(oneSpecificCard.value);
            } else {
                form.value.selectedCards.splice(index, 1);
            }
        }
    });

    const xhrSave = () => {
        const handleAjax: HandleAjax = new HandleAjax(ajaxSave.value);
        return new Server().send_online({
            data: [
                spClientData().nonce,
                {
                    params: {
                        cards: form.value.selectedCards,
                        group: form.value.group,
                        deck: form.value.deck,
                        topic: form.value.topic,
                        what_to_do: form.value.whatToDo,
                    },
                }
            ],
            what: "admin_sp_ajax_admin_save_user_cards",
            funcBefore() {
                handleAjax.start();
            },
            funcSuccess(done: InterFuncSuccess<any>) {
                handleAjax.stop();
            },
            funcFailue(done) {
                handleAjax.error(done);
            },
        });
    };
    const xhrAssignTopics = () => {
        const handleAjax: HandleAjax = new HandleAjax(ajaxAssignTopics.value);
        return new Server().send_online({
            data: [
                spClientData().nonce,
                {
                    params: {
                        cards: form.value.selectedCards,
                        group: form.value.group,
                        deck: form.value.deck,
                        topic: form.value.topic,
                        what_to_do: form.value.whatToDo,
                        topic_to_assign: form.value.topicToAssign,
                    },
                }
            ],
            what: "admin_sp_ajax_admin_assign_topics",
            funcBefore() {
                console.log('before');
                handleAjax.start();
            },
            funcSuccess(done: InterFuncSuccess<any>) {
                handleAjax.stop();
                toast.success(done.message);
            },
            funcFailue(done) {
                handleAjax.error(done);
                toast.error(done.message);
            },
        });
    };
    const xhrAddCards = () => {
        const handleAjax: HandleAjax = new HandleAjax(ajaxAddCards.value);
        return new Promise((resolve, reject) => {
            new Server().send_online({
                data: [
                    spClientData().nonce,
                    {
                        params: {
                            cards: form.value.selectedCards,
                        },
                    }
                ],
                what: "admin_sp_ajax_front_add_user_cards",
                funcBefore() {
                    handleAjax.start();
                },
                funcSuccess(done: InterFuncSuccess<any>) {
                    handleAjax.stop();
                    form.value = {
                        ...form.value,
                        selectedCards: [],
                        group: null,
                        deck: null,
                        topic: null,
                    };
                    toast.success(done.message);
                    resolve(done);
                },
                funcFailue(done) {
                    handleAjax.error(done);
                    toast.error(done.message);
                    reject(done);
                },
            });
        });
    };
    const xhrIgnoreCard = (cardId: number) => {
        const handleAjax: HandleAjax = new HandleAjax(ajaxIgnoreCard.value);
        return new Server().send_online({
            data: [
                spClientData().nonce,
                {
                    params: {
                        card_id: cardId,
                    },
                }
            ],
            what: "admin_sp_ajax_front_ignore_card",
            funcBefore() {
                handleAjax.start();
            },
            funcSuccess(done: InterFuncSuccess<any>) {
                handleAjax.stop();
            },
            funcFailue(done) {
                handleAjax.error(done);
            },
        });
    };
    const xhrRemoveCard = (cardIds: number[]) => {
        const handleAjax: HandleAjax = new HandleAjax(ajaxRemoveCard.value);
        return new Promise((resolve, reject) => {
            new Server().send_online({
                data: [
                    spClientData().nonce,
                    {
                        params: {
                            card_group_ids: cardIds,
                        },
                    }
                ],
                what: "admin_sp_ajax_front_remove_card",
                funcBefore() {
                    handleAjax.start();
                },
                funcSuccess(done: InterFuncSuccess<any>) {
                    handleAjax.stop();
                    toast.success(done.message);
                    resolve(done);
                },
                funcFailue(done) {
                    handleAjax.error(done);
                    toast.error(done.message);
                    reject(done);
                },
            });
        })
    };
    const xhrLoadUserCards = () => {
        const handleAjax: HandleAjax = new HandleAjax(ajaxLoadUserCard.value);
        return new Promise((resolve, reject) => {
            new Server().send_online({
                data: [
                    spClientData().nonce,
                    {
                        params: {},
                    }
                ],
                what: "admin_sp_ajax_front_load_user_cards",
                funcBefore() {
                    handleAjax.start();
                },
                funcSuccess(done: InterFuncSuccess<{ deck_groups: Array<_DeckGroup> }>) {
                    handleAjax.stop();
                    userDeckGroups.value = done.data.deck_groups;
                    resolve(done);
                },
                funcFailue(done) {
                    handleAjax.error(done);
                    toast.error(done.message);
                    reject(done);
                },
            });
        })
    };

    const xhrLoadUserDebugForm = () => {
        const handleAjax: HandleAjax = new HandleAjax(ajaxLoadUserCard.value);
        return new Promise((resolve, reject) => {
            new Server().send_online({
                data: [
                    spClientData().nonce,
                    {
                        params: {
                            ...debugForm.value,
                        },
                    }
                ],
                what: "admin_sp_ajax_front_load_user_debug_form",
                funcBefore() {
                    handleAjax.start();
                },
                funcSuccess(done: InterFuncSuccess<{
                    current_study_date: string
                }>) {
                    handleAjax.stop();
                    resolve(done);
                    debugForm.value.current_study_date = done.data.current_study_date
                },
                funcFailue(done) {
                    handleAjax.error(done);
                    reject(done);
                },
            });
        })
    };
    const xhrSaveUserDebugForm = () => {
        const handleAjax: HandleAjax = new HandleAjax(ajaxLoadUserCard.value);
        return new Promise((resolve, reject) => {
            new Server().send_online({
                data: [
                    spClientData().nonce,
                    {
                        params: {
                            ...debugForm.value,
                        },
                    }
                ],
                what: "admin_sp_ajax_front_save_user_debug_form",
                funcBefore() {
                    handleAjax.start();
                },
                funcSuccess(done: InterFuncSuccess<{
                    date: string
                }>) {
                    handleAjax.stop();
                    resolve(done);
                },
                funcFailue(done) {
                    handleAjax.error(done);
                    reject(done);
                },
            });
        })
    };


    return {
        ajaxSave: ajaxSave, save: xhrSave, form: form,
        oneSpecificCard, assignTopics: xhrAssignTopics, ajaxAssignTopics,
        ajaxAddCards, addCards: xhrAddCards,
        ajaxIgnoreCard, ignoreCard: xhrIgnoreCard,
        ajaxRemoveCard, removeCard: xhrRemoveCard,
        ajaxLoadUserCard, loadUserCards: xhrLoadUserCards, userDeckGroups,
        ajaxLoadDebugForm, loadDebugForm: xhrLoadUserDebugForm, debugForm, saveDebugForm: xhrSaveUserDebugForm,
    };

}