import {ref} from "vue";
import type {_Collection, _Deck, _DeckGroup} from "@/interfaces/inter-sp";
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
            label: 'Cards',
            field: 'cards',
            tooltip: 'Total Cards',
        },
        {
            label: 'Publish',
            field: 'publish',
            tooltip: 'Publish Collection',
        },
        {
            label: 'Delete',
            field: 'delete',
            tooltip: 'Delete Collection',
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
    selectedRows: [] as Array<_Collection>,
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
    const ajaxPublish = ref<_Ajax>({
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
    let itemToEdit = ref<_Deck>(null as unknown as _Deck);
    let total = ref<number>(0);
    let newItem = ref({
        name: '',
    })
    let searchResults = ref<Array<_DeckGroup>>([]);
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

    const load = () => {
        xhrLoad();
    }
    const search = (query: string) => {
        xhrSearch(query);
    }
    //
    const onSelect = (items: { selectedRows: Array<_Deck> }) => {
        console.log('selected', {items});
        tt().selectedRows = items.selectedRows;
    };
    const onEdit = (item: _Deck) => {
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
    const openEditModal = (item: _Deck, modalId: string) => {
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
            what: "admin_sp_ajax_admin_load_collections",
            funcBefore() {
                handleAjax.start();
                tt().isLoading = true;
            },
            funcSuccess(done: InterFuncSuccess<any>) {
                handleAjax.stop();
                const items = done.data.details.collections;
                const total = done.data.details.total;
                const theTotals = done.data.totals;
                console.log({done, items, total, totals});
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
    const xhrSearch = (query: string) => {
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
                    },
                }
            ],
            what: "admin_sp_ajax_admin_search_collections",
            funcBefore() {
                handleAjax.start();
                tt().isLoading = true;
            },
            funcSuccess(done: InterFuncSuccess<any>) {
                handleAjax.stop();
                searchResults.value = done.data;
            },
            funcFailue(done) {
                handleAjax.error(done);
                tt().isLoading = false;
            },
        });
    };
    const xhrUpdateBatch = (items: Array<_Collection>) => {
        const handleAjax: HandleAjax = new HandleAjax(ajaxUpdate.value);
        sendOnline = new Server().send_online({
            data: [
                Store.nonce,
                {
                    collections: items,
                }
            ],
            what: "admin_sp_ajax_admin_update_collections",
            funcBefore() {
                handleAjax.start();
                // vdata.tableData.isLoading = true;
            },
            funcSuccess(done: InterFuncSuccess<any>) {
                handleAjax.success(done);
                xhrLoad();
            },
            funcFailue(done) {
                handleAjax.error(done);
                // vdata.tableData.isLoading = false;
            },
        });
    };
    const xhrTrashBatch = (items: Array<_Collection>) => {
        if (!confirm('Are you sure you want to trash these items?')) {
            return;
        }
        const handleAjax: HandleAjax = new HandleAjax(ajaxTrash.value);
        sendOnline = new Server().send_online({
            data: [
                Store.nonce,
                {
                    collections: items,
                }
            ],
            what: "admin_sp_ajax_admin_trash_collections",
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
    const xhrDeleteBatch = (items: Array<_Collection>) => {
        if (!confirm('Are you sure you want to delete these items? This action is not reversible.')) {
            return;
        }
        const handleAjax: HandleAjax = new HandleAjax(ajaxDelete.value);
        return new Promise((resolve, reject) => {
            sendOnline = new Server().send_online({
                data: [
                    Store.nonce,
                    {
                        collections: items,
                    }
                ],
                what: "admin_sp_ajax_admin_delete_collections",
                funcBefore() {
                    handleAjax.start();
                },
                funcSuccess(done: InterFuncSuccess<any>) {
                    handleAjax.success(done);
                    xhrLoad();
                    resolve(done);
                },
                funcFailue(done) {
                    handleAjax.error(done);
                    reject(done);
                },
            });
        });
    };
    const xhrPublishBatch = (items: Array<_Collection>) => {
        if (!confirm('Are you sure you want to publish the cards in the collection? This action is not reversible.')) {
            return;
        }
        const handleAjax: HandleAjax = new HandleAjax(ajaxPublish.value);
        return new Promise((resolve, reject) => {
            sendOnline = new Server().send_online({
                data: [
                    Store.nonce,
                    {
                        collections: items,
                    }
                ],
                what: "admin_sp_ajax_admin_publish_collections",
                funcBefore() {
                    handleAjax.start();
                },
                funcSuccess(done: InterFuncSuccess<any>) {
                    handleAjax.success(done);
                    xhrLoad();
                    resolve(done);
                },
                funcFailue(done) {
                    handleAjax.error(done);
                    reject(done);
                },
            });
        });
    };

    const xhrCreateNew = () => {
        const handleAjax: HandleAjax = new HandleAjax(ajaxCreate.value);
        new Server().send_online({
            data: [
                Store.nonce,
                {
                    name: newItem.value.name,
                }
            ],
            what: "admin_sp_ajax_admin_create_new_collection",
            funcBefore() {
                handleAjax.start();
            },
            funcSuccess(done: InterFuncSuccess<any>) {
                handleAjax.success(done);
                useDeckGroupLists().load();
                newItem.value.name = '';
                xhrLoad();
            },
            funcFailue(done) {
                handleAjax.error(done);
            },
        });
    };

    const batchDelete = () => {
        xhrDeleteBatch(tt().selectedRows);
    }

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
        delete: xhrDeleteBatch,
        publish: xhrPublishBatch, ajaxPublish,
    };

}