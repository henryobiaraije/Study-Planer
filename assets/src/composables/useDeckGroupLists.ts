import {_Ajax, HandleAjax} from "../classes/HandleAjax";
import {InterFuncSuccess, Server} from "../static/server";
import {Store} from "../static/store";
import {ref, onMounted} from "@vue/composition-api";
import Cookies from 'js-cookie';
import {vdata} from "../admin/admin-deck-groups";
import {_DeckGroup} from "../interfaces/inter-sp";

declare var bootstrap;

const tableData = ref({
  columns          : [
    {
      label  : 'Name',
      field  : 'name',
      tooltip: 'Endpoint Name',
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
  rows             : [],
  isLoading        : true,
  totalRecords     : 0,
  totalTrashed     : 0,
  serverParams     : {
    columnFilters: {},
    sort         : {
      created_at : '',
      modified_at: '',
    },
    page         : 1,
    perPage      : 10
  },
  paginationOptions: {
    enabled         : true,
    mode            : 'page',
    perPage         : Cookies.get('spPerPage') ? Number(Cookies.get('spPerPage')) : 2,
    position        : 'bottom',
    perPageDropdown : [2, 5, 10, 15, 20, 25, 30, 40, 50, 60, 70, 80, 90, 100, 150, 200, 300, 400, 500, 600, 700],
    dropdownAllowAll: true,
    setCurrentPage  : 1,
    nextLabel       : 'next',
    prevLabel       : 'prev',
    rowsPerPageLabel: 'Rows per page',
    ofLabel         : 'of',
    pageLabel       : 'page', // for 'pages' mode
    allLabel        : 'All',
  },
  searchOptions    : {
    enabled       : true,
    trigger       : '', // can be "enter"
    skipDiacritics: true,
    placeholder   : 'Search links',
  },
  sortOption       : {
    enabled: false,
  },
  //
  post_status  : 'publish',
  selectedRows : [] as Array<_DeckGroup>,
  searchKeyword: '',
});
const totals    = ref({
  active : 0,
  trashed: 0
});

export default function (status = 'publish') {
  const ajax                  = ref<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });
  const ajaxUpdate            = ref<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });
  const ajaxTrash             = ref<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });
  const ajaxDelete            = ref<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });
  const modalEditId           = ref('');
  tableData.value.post_status = status;
  // console.log('in function', {status});
  const editedItems           = ref([]);
  let sendOnline              = null;
  let deckGroupToEdit         = ref<_DeckGroup>(null);
  let total                   = ref<number>(0);
  //
  const updateEditing         = () => {
    xhrUpdateBatch([deckGroupToEdit.value]);
  }
  const batchUpdate           = () => {
    xhrUpdateBatch(tt().selectedRows);
  }
  const batchTrash            = () => {
    xhrTrashBatch(tt().selectedRows);
  }
  const batchDelete           = () => {
    xhrDeleteBatch(tt().selectedRows);
  }
  const load                  = () => {
    xhrLoad();
  }
  //
  const onSelect              = (items: { selectedRows: Array<_DeckGroup> }) => {
    console.log('selected', {items});
    tt().selectedRows = items.selectedRows;
  };
  const onEdit                = (item: _DeckGroup) => {
    console.log('edited', {item});
    if (undefined === editedItems.value[item.id]) {
      editedItems.value[item.id] = {
        editCounter: 0,
      };
    }
    editedItems.value[item.id].editCounter++;
    setTimeout(() => {
      editedItems.value[item.id].editCounter--;
      if (editedItems.value[item.id].editCounter === 0) {
        xhrUpdateBatch([item]);
      }
    }, 500);
  };
  const onSearch              = (params: { searchTerm: string }) => {
    tt().searchKeyword = params.searchTerm;
    xhrLoad();
  };
  const onPageChange          = (params: { currentPage: number, currentPerPage: number, prevPage: number, total: number }) => {
    tt().paginationOptions.setCurrentPage = params.currentPage;
    tt().paginationOptions.perPage        = params.currentPerPage;
    xhrLoad();
  };
  const onSortChange          = (params) => {

  };
  const onColumnFilter        = (params) => {
    //
    console.log('sort change');
  };
  const onPerPageChange       = (params: { currentPage: number; currentPerPage: number; total: number; }) => {
    tt().paginationOptions.setCurrentPage = params.currentPage;
    tt().paginationOptions.perPage        = params.currentPerPage;
    // Cookies.set('spPerPage', params.currentPerPage);
    xhrLoad();
  };
  const loadItems             = () => {
    xhrLoad();
  };
  const openEditModal         = (item: _DeckGroup, modalId: string) => {
    deckGroupToEdit.value = item;
    modalEditId.value     = modalId;
    const modalElement    = jQuery(modalId)[0];
    const myModal         = new bootstrap.Modal(modalElement);
    myModal.show();
    modalElement.addEventListener('shown.bs.modal', function () {

    });
    modalElement.addEventListener('hidden.bs.modal', function () {
      deckGroupToEdit.value = null;
    });
  }
  const closeEditModal        = () => {
    const modalElement = jQuery(modalEditId.value)[0];
    const myModal      = new bootstrap.Modal(modalElement);
    myModal.hide();
    deckGroupToEdit.value = null;
  };
  const tt                    = () => tableData.value;

  const xhrLoad        = () => {
    console.log('start loading');
    const handleAjax: HandleAjax = new HandleAjax(ajax.value);
    sendOnline                   = new Server().send_online({
      data: [
        vdata.localize.nonce,
        {
          params: {
            per_page      : tt().paginationOptions.perPage,
            page          : tt().paginationOptions.setCurrentPage,
            search_keyword: tt().searchKeyword,
            status        : tt().post_status,
          },
        }
      ],
      what: "admin_sp_ajax_admin_load_deck_group",
      funcBefore() {
        handleAjax.start();
        tt().isLoading = true;
      },
      funcSuccess(done: InterFuncSuccess) {
        handleAjax.stop();
        const groups    = done.data.details.deck_groups;
        const total     = done.data.details.total;
        const theTotals = done.data.totals;
        console.log({done, groups, total, totals});
        tt().isLoading       = false;
        tableData.value.rows = groups;
        totals.value         = theTotals;
        tt().totalRecords    = total;
      },
      funcFailue(done) {
        handleAjax.error(done);
        tt().isLoading = false;
      },
    });
  };
  const xhrUpdateBatch = (items: Array<_DeckGroup>) => {
    const handleAjax: HandleAjax = new HandleAjax(ajaxUpdate.value);
    sendOnline                   = new Server().send_online({
      data: [
        vdata.localize.nonce,
        {
          deck_groups: items,
        }
      ],
      what: "admin_sp_ajax_admin_update_deck_group",
      funcBefore() {
        handleAjax.start();
        // vdata.tableData.isLoading = true;
      },
      funcSuccess(done: InterFuncSuccess) {
        handleAjax.success(done);
      },
      funcFailue(done) {
        handleAjax.error(done);
        // vdata.tableData.isLoading = false;
      },
    });
  };
  const xhrTrashBatch  = (items: Array<_DeckGroup>) => {
    if (!confirm('Are you sure you want to trash these items?')) {
      return;
    }
    const handleAjax: HandleAjax = new HandleAjax(ajaxTrash.value);
    sendOnline                   = new Server().send_online({
      data: [
        vdata.localize.nonce,
        {
          deck_groups: items,
        }
      ],
      what: "admin_sp_ajax_admin_trash_deck_group",
      funcBefore() {
        handleAjax.start();
      },
      funcSuccess(done: InterFuncSuccess) {
        handleAjax.success(done);
        xhrLoad();
      },
      funcFailue(done) {
        handleAjax.error(done);
      },
    });
  };
  const xhrDeleteBatch = (items: Array<_DeckGroup>) => {
    if (!confirm('Are you sure you want to delete these items? This action is not reversible.')) {
      return;
    }
    const handleAjax: HandleAjax = new HandleAjax(ajaxDelete.value);
    sendOnline                   = new Server().send_online({
      data: [
        vdata.localize.nonce,
        {
          deck_groups: items,
        }
      ],
      what: "admin_sp_ajax_admin_delete_deck_group",
      funcBefore() {
        handleAjax.start();
      },
      funcSuccess(done: InterFuncSuccess) {
        handleAjax.success(done);
        xhrLoad();
      },
      funcFailue(done) {
        handleAjax.error(done);
      },
    });
  };

  // onMounted(() => {
  //   tableData.value.post_status = status;
  //   console.log('function mounted');
  // });

  return {
    ajax, ajaxUpdate, ajaxTrash, ajaxDelete,
    total, deckGroupToEdit, editedItems, tableData, load,
    onSelect, onEdit, onSearch, onPageChange, onPerPageChange, loadItems,
    onSortChange, onColumnFilter, updateEditing,
    batchUpdate, batchDelete, batchTrash,
    totals, closeEditModal, openEditModal
  };

}