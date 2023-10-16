import {ref} from "vue";
import type {_Deck, _Tag, _Topic, _TopicNew} from "@/interfaces/inter-sp";
import type {_Ajax} from "@/classes/HandleAjax";
import {HandleAjax} from "@/classes/HandleAjax";
import {type InterFuncSuccess, Server} from "@/static/server";
import {Store} from "@/static/store";
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
            label: 'Deck',
            field: 'deck',
            tooltip: 'Deck',
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
    selectedRows: [] as Array<_Topic>,
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
    const modalEditId = ref('');
    tableData.value.post_status = status;
    const editedItemTracker = ref<{ [key: number]: { editCounter: number } }>({});
    let sendOnline = null;
    let itemToEdit = ref<_Topic>(null as unknown as _Topic);
    let total = ref<number>(0);
    let newItem = ref<_TopicNew>({
        name: '',
        deck: null as unknown as _Deck,
        tags: [] as Array<_Tag>,
    })
    let searchResults = ref<Array<_Topic>>([]);
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
    const load = () => {
        return xhrLoad();
    }

    //
    const onSelect = (items: { selectedRows: Array<_Topic> }) => {
        console.log('selected', {items});
        tt().selectedRows = items.selectedRows;
    };
    const onEdit = (item: _Topic) => {
        console.log('edited', {item});
        if (undefined === editedItemTracker.value[item.id]) {
            editedItemTracker.value[item.id] = {
                editCounter: 0,
            };
        }
        editedItemTracker.value[item.id].editCounter++;
        setTimeout(() => {
            editedItemTracker.value[item.id].editCounter--;
            if (editedItemTracker.value[item.id].editCounter === 0) {
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
    const openEditModal = (item: _Topic, modalId: string) => {
        itemToEdit.value = item;
        modalEditId.value = modalId;
        const modalElement = jQuery(modalId)[0];
        const myModal = new bootstrap.Modal(modalElement);
        myModal.show();
        modalElement.addEventListener('shown.bs.modal', function () {

        });
        modalElement.addEventListener('hidden.bs.modal', function () {
            editedItemTracker.value = null as unknown as { [key: number]: { editCounter: number } };
        });
    }
    const closeEditModal = () => {
        const modalElement = jQuery(modalEditId.value)[0];
        const myModal = new bootstrap.Modal(modalElement);
        myModal.hide();
        editedItemTracker.value = null;
    };
    const tt = () => tableData.value;
    const xhrLoad = () => {
        const handleAjax: HandleAjax = new HandleAjax(ajax.value);
        sendOnline = new Server().send_online({
            data: [
                Store.nonce,
                {
                    params: {
                        per_page: tt().paginationOptions.perPage,
                        page: tt().paginationOptions.setCurrentPage,
                        search_keyword: tt().searchKeyword,
                        status: tt().post_status,
                    },
                }
            ],
            what: "admin_sp_ajax_admin_load_topics",
            funcBefore() {
                handleAjax.start();
                tt().isLoading = true;
            },
            funcSuccess(done: InterFuncSuccess<any>) {
                handleAjax.stop();
                const items: _Topic[] = done.data.details.topics;
                const total = done.data.details.total;
                const theTotals = done.data.totals;
                console.log({done, items, total, totals});
                tt().isLoading = false;
                tableData.value.rows = items;
                totals.value = theTotals;
                tt().totalRecords = total;
                searchResults.value = items;
            },
            funcFailue(done) {
                handleAjax.error(done);
                tt().isLoading = false;
            },
        });
    };
    const xhrSearch = (query: string, deck: null | _Deck = null) => {
        const handleAjax: HandleAjax = new HandleAjax(ajaxSearch.value);
        sendOnline = new Server().send_online({
            data: [
                spClientData().nonce,
                {
                    params: {
                        per_page: 5,
                        page: 1,
                        search_keyword: query,
                        status: 'publish',
                        deck: deck,
                    },
                }
            ],
            what: "admin_sp_ajax_admin_search_topics",
            funcBefore() {
                handleAjax.start();
                tt().isLoading = true;
            },
            funcSuccess(done: InterFuncSuccess<{ items: Array<_Topic>, total: number }>) {
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
    const xhrUpdateBatch = (items: Array<_Topic>) => {
        const handleAjax: HandleAjax = new HandleAjax(ajaxUpdate.value);
        sendOnline = new Server().send_online({
            data: [
                Store.nonce,
                {
                    topics: items,
                }
            ],
            what: "admin_sp_ajax_admin_update_topics",
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
    const xhrTrashBatch = (items: Array<_Topic>) => {
        if (!confirm('Are you sure you want to trash these items?')) {
            return;
        }
        const handleAjax: HandleAjax = new HandleAjax(ajaxTrash.value);
        sendOnline = new Server().send_online({
            data: [
                Store.nonce,
                {
                    topics: items,
                }
            ],
            what: "admin_sp_ajax_admin_trash_topics",
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
    const xhrDeleteBatch = (items: Array<_Topic>) => {
        if (!confirm('Are you sure you want to delete these items? This action is not reversible.')) {
            return;
        }
        const handleAjax: HandleAjax = new HandleAjax(ajaxDelete.value);
        sendOnline = new Server().send_online({
            data: [
                Store.nonce,
                {
                    topics: items,
                }
            ],
            what: "admin_sp_ajax_admin_delete_topics",
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
                Store.nonce,
                {
                    name: newItem.value.name,
                    deck: newItem.value.deck,
                    tags: newItem.value.tags,
                }
            ],
            what: "admin_sp_ajax_admin_create_new_topic",
            funcBefore() {
                handleAjax.start();
            },
            funcSuccess(done: InterFuncSuccess<any>) {
                handleAjax.success(done);
                useDeckGroupLists().load();
                newItem.value.name = '';
                newItem.value.deck = null;
                newItem.value.tags = [];
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
        ajax, ajaxUpdate, ajaxTrash, ajaxDelete, ajaxCreate, ajaxSearch,
        total, create, itemToEdit, editedItemTracker, tableData, load, newItem,
        onSelect, onEdit, onSearch, onPageChange, onPerPageChange, loadItems,
        onSortChange, onColumnFilter, updateEditing,
        batchUpdate, batchDelete, batchTrash,
        totals, closeEditModal, openEditModal, search, searchResults,
    };

}