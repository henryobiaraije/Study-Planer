import {ref} from "vue";
import type {_CardGroup, _Deck, _DeckGroup, _Tag, _Topic, CardType} from "@/interfaces/inter-sp";
import type {_Ajax} from "@/classes/HandleAjax";
import {HandleAjax} from "@/classes/HandleAjax";
import {type InterFuncSuccess, Server} from "@/static/server";
import useDeckGroupLists from "@/composables/useDeckGroupLists";
import Cookies from "js-cookie";
import {spClientData} from "@/functions";

declare var bootstrap;

const tableData = ref({
    columns: [
        {
            label: 'Name',
            field: 'name',
            tooltip: 'Endpoint Name',
        },
        {
            label: 'Type',
            field: 'type',
        },
        {
            label: 'Total Cards',
            field: 'total_cards',
        },
        {
            label: 'Deck Group',
            field: 'deck_group',
        },
        {
            label: 'Deck',
            field: 'deck',
        },
        {
            label: 'Topic',
            field: 'topic',
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
    selectedRows: [] as Array<_CardGroup>,
    searchKeyword: '',
});
const totals = ref({
    active: 0,
    trashed: 0
});

export default function (status = 'publish') {
    const ajax = ref<_Ajax>({
        sending: false,
        error: false,
        errorMessage: '',
        success: false,
        successMessage: '',
    });
    const ajaxSearch = ref<_Ajax>({
        sending: false,
        error: false,
        errorMessage: '',
        success: false,
        successMessage: '',
    });
    const ajaxCreate = ref<_Ajax>({
        sending: false,
        error: false,
        errorMessage: '',
        success: false,
        successMessage: '',
    });
    const ajaxUpdate = ref<_Ajax>({
        sending: false,
        error: false,
        errorMessage: '',
        success: false,
        successMessage: '',
    });
    const ajaxTrash = ref<_Ajax>({
        sending: false,
        error: false,
        errorMessage: '',
        success: false,
        successMessage: '',
    });
    const ajaxDelete = ref<_Ajax>({
        sending: false,
        error: false,
        errorMessage: '',
        success: false,
        successMessage: '',
    });
    const ajaxRestore = ref<_Ajax>({
        sending: false,
        error: false,
        errorMessage: '',
        success: false,
        successMessage: '',
    });
    const modalEditId = ref('');
    tableData.value.post_status = status;
    const editedItems = ref<Array<_CardGroup>>([]);
    let sendOnline = null;
    let itemToEdit = ref<_CardGroup>(null);
    let total = ref<number>(0);
    let newItem = ref({
        name: '',
        deckGroup: null as _DeckGroup,
        tags: [] as Array<_Tag>
    })
    let searchResults = ref<Array<_CardGroup>>([]);
    const _editCounter = ref<{ [key: number]: { counter: number } }>({});
    const page = ref(1);
    const fromFrontend = ref(false);
    const forAddToStudyDeck = ref(false);
    const forRemoveFromStudyDeck = ref(false);
    const forNewCards = ref(false);
    //
    const create = () => {
        xhrCreateNew();
    }
    const updateEditing = () => {
        xhrUpdateBatch([itemToEdit.value]);
    }
    const batchUpdate = () => {
        xhrUpdateBatch(tt().selectedRows);
    }
    const batchTrash = () => {
        xhrTrashBatch(tt().selectedRows);
    }
    const batchDelete = () => {
        xhrDeleteBatch(tt().selectedRows);
    }
    const batchRestore = () => {
        xhrRestoreBatch(tt().selectedRows);
    }
    const load = () => {
        xhrLoad();
    }

    // const search = (query: string) => {
    //     return xhrSearch(query);
    // }
    //
    const onSelect = (items: { selectedRows: Array<_CardGroup> }) => {
        console.log('selected', {items});
        tt().selectedRows = items.selectedRows;
    };
    const onEdit = (item: _CardGroup) => {
        if (undefined === _editCounter.value[item.id]) {
            _editCounter.value[item.id] = {
                counter: 0,
            };
        }
        _editCounter.value[item.id].counter++;
        setTimeout(() => {
            _editCounter.value[item.id].counter--;
            if (_editCounter.value[item.id].counter === 0) {
                xhrUpdateBatch([item]);
            }
        }, 500);
    };
    const onSearch = (params: { searchTerm: string }) => {
        tt().searchKeyword = params.searchTerm;
        xhrLoad();
    };
    const onPageChange = (params: { currentPage: number, currentPerPage: number, prevPage: number, total: number }) => {
        tt().paginationOptions.setCurrentPage = params.currentPage;
        tt().paginationOptions.perPage = params.currentPerPage;
        xhrLoad();
    };
    const onSortChange = (params) => {

    };
    const onColumnFilter = (params) => {
        //
        console.log('sort change');
    };
    const onPerPageChange = (params: { currentPage: number; currentPerPage: number; total: number; }) => {
        tt().paginationOptions.setCurrentPage = params.currentPage;
        tt().paginationOptions.perPage = params.currentPerPage;
        Cookies.set('spPerPage', params.currentPerPage.toString());
        xhrLoad();
    };
    const loadItems = () => {
        xhrLoad();
    };
    const openEditModal = (item: _CardGroup, modalId: string) => {
        itemToEdit.value = item;
        modalEditId.value = modalId;
        const modalElement = jQuery(modalId)[0];
        const myModal = new bootstrap.Modal(modalElement);
        myModal.show();
        modalElement.addEventListener('shown.bs.modal', function () {

        });
        modalElement.addEventListener('hidden.bs.modal', function () {
            editedItems.value = null;
        });
    }
    const closeEditModal = () => {
        const modalElement = jQuery(modalEditId.value)[0];
        const myModal = new bootstrap.Modal(modalElement);
        myModal.hide();
        editedItems.value = null;
    };
    const tt = () => tableData.value;

    const xhrLoad = () => {
        const handleAjax: HandleAjax = new HandleAjax(ajax.value);
        sendOnline = new Server().send_online({
            data: [
                spClientData().nonce,
                {
                    params: {
                        per_page: tt().paginationOptions.perPage,
                        page: tt().paginationOptions.setCurrentPage,
                        search_keyword: tt().searchKeyword,
                        status: tt().post_status,
                    },
                }
            ],
            what: "admin_sp_pro_ajax_admin_load_cards_groups",
            funcBefore() {
                handleAjax.start();
                tt().isLoading = true;
            },
            funcSuccess(done: InterFuncSuccess<any>) {
                handleAjax.stop();
                const items = done.data.details.card_groups;
                const total = done.data.details.total;
                const theTotals = done.data.totals;
                // console.log({done, items, total, totals});
                tt().isLoading = false;
                tableData.value.rows = items;
                totals.value = theTotals;
                tt().totalRecords = total;
            },
            funcFailue(done) {
                handleAjax.error(done);
                tt().isLoading = false;
            },
        });
    };
    const xhrSearch = (
        query: string,
        deckGroup: null | _DeckGroup = null,
        deck: null | _Deck = null,
        topic: null | _Topic = null,
        cardTypes: [] | CardType[] = [],
    ) => {
        const handleAjax: HandleAjax = new HandleAjax(ajaxSearch.value);
        sendOnline = new Server().send_online({
            data: [
                spClientData().nonce,
                {
                    params: {
                        per_page: 10,
                        page: page.value,
                        search_keyword: query,
                        status: 'publish',
                        deck_group: deckGroup,
                        deck: deck,
                        topic: topic,
                        card_types: cardTypes,
                        from_frontend: fromFrontend.value,
                        for_add_to_study_deck: forAddToStudyDeck.value,
                        for_remove_from_study_deck: forRemoveFromStudyDeck.value,
                        for_new_cards: forNewCards.value,
                    },
                }
            ],
            what: "admin_sp_pro_ajax_admin_search_all_cards",
            // what: "admin_sp_pro_ajax_admin_search_decks",
            funcBefore() {
                handleAjax.start();
                tt().isLoading = true;
            },
            funcSuccess(done: InterFuncSuccess<{
                total: number,
                items: Array<_CardGroup>,
            }>) {
                handleAjax.stop();
                searchResults.value = done.data.items;
                tt().totalRecords = done.data.total;
            },
            funcFailue(done) {
                handleAjax.error(done);
                tt().isLoading = false;
            },
        });
    };
    const xhrUpdateBatch = (items: Array<_CardGroup>) => {
        const handleAjax: HandleAjax = new HandleAjax(ajaxUpdate.value);
        sendOnline = new Server().send_online({
            data: [
                spClientData().nonce,
                {
                    decks: items,
                }
            ],
            what: "admin_sp_pro_ajax_admin_update_decks",
            funcBefore() {
                handleAjax.start();
                // vdata.tableData.isLoading = true;
            },
            funcSuccess(done: InterFuncSuccess<any>) {
                handleAjax.success(done);
            },
            funcFailue(done) {
                handleAjax.error(done);
                // vdata.tableData.isLoading = false;
            },
        });
    };
    const xhrTrashBatch = (items: Array<_CardGroup>) => {
        if (!confirm('Are you sure you want to trash these items?')) {
            return;
        }
        const handleAjax: HandleAjax = new HandleAjax(ajaxTrash.value);
        sendOnline = new Server().send_online({
            data: [
                spClientData().nonce,
                {
                    card_groups: items,
                }
            ],
            what: "admin_sp_pro_ajax_admin_trash_cards",
            funcBefore() {
                handleAjax.start();
            },
            funcSuccess(done: InterFuncSuccess<any>) {
                handleAjax.success(done);
                xhrLoad();
            },
            funcFailue(done) {
                handleAjax.error(done);
            },
        });
    };
    const xhrDeleteBatch = (items: Array<_CardGroup>) => {
        if (!confirm('Are you sure you want to delete these items? This action is not reversible.')) {
            return;
        }
        const handleAjax: HandleAjax = new HandleAjax(ajaxDelete.value);
        sendOnline = new Server().send_online({
            data: [
                spClientData().nonce,
                {
                    card_groups: items,
                }
            ],
            what: "admin_sp_pro_ajax_admin_delete_card_group",
            funcBefore() {
                handleAjax.start();
            },
            funcSuccess(done: InterFuncSuccess<any>) {
                handleAjax.success(done);
                xhrLoad();
            },
            funcFailue(done) {
                handleAjax.error(done);
            },
        });
    };
    const xhrRestoreBatch = (items: Array<_CardGroup>) => {
        const handleAjax: HandleAjax = new HandleAjax(ajaxRestore.value);
        sendOnline = new Server().send_online({
            data: [
                spClientData().nonce,
                {
                    card_groups: items,
                }
            ],
            what: "admin_sp_pro_ajax_admin_restore_card_group",
            funcBefore() {
                handleAjax.start();
            },
            funcSuccess(done: InterFuncSuccess<any>) {
                handleAjax.success(done);
                xhrLoad();
            },
            funcFailue(done) {
                handleAjax.error(done);
            },
        });
    };
    const xhrCreateNew = () => {
        const handleAjax: HandleAjax = new HandleAjax(ajaxCreate.value);
        new Server().send_online({
            data: [
                spClientData().nonce,
                {
                    name: newItem.value.name,
                    deck_group: newItem.value.deckGroup,
                    tags: newItem.value.tags,
                }
            ],
            what: "admin_sp_pro_ajax_admin_create_new_deck",
            funcBefore() {
                handleAjax.start();
            },
            funcSuccess(done: InterFuncSuccess<any>) {
                handleAjax.success(done);
                useDeckGroupLists().load();
                newItem.value.name = '';
                newItem.value.tags = [];
                newItem.value.deckGroup = null;
                xhrLoad();
            },
            funcFailue(done) {
                handleAjax.error(done);
            },
        });
    };

    const search = xhrSearch;

    // onMounted(() => {
    //   tableData.value.post_status = status;
    //   console.log('function mounted');
    // });

    return {
        ajax, ajaxUpdate, ajaxTrash, ajaxDelete, ajaxCreate, ajaxSearch, ajaxRestore,
        page,
        total, create, itemToEdit, editedItems, tableData, load, newItem,
        onSelect, onEdit, onSearch, onPageChange, onPerPageChange, loadItems,
        onSortChange, onColumnFilter, updateEditing,
        batchUpdate, batchDelete, batchTrash, batchRestore,
        totals, closeEditModal, openEditModal, search, searchResults,
        fromFrontend, forAddToStudyDeck, forRemoveFromStudyDeck, forNewCards,
    };

}