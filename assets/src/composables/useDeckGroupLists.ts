import {_Ajax, HandleAjax} from "../classes/HandleAjax";
import {InterFuncSuccess, Server} from "../static/server";
import {Store} from "../static/store";
import {ref} from "@vue/composition-api";
import Cookies from 'js-cookie';
import {vdata} from "../admin/admin-deck-groups";
import {_DeckGroup} from "../interfaces/inter-sp";

export default function () {

  const tableData = ref({
    columns          : [
      {
        label  : 'Name',
        field  : 'name',
        tooltip: 'Endpoint Name',
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
      perPage         : Cookies.get('alfPerPage') ? Number(Cookies.get('alfPerPage')) : 2,
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
    post_status         : 'publish',
    selectedRowsToDelete: [] as Array<_DeckGroup>,
    searchKeyword       : '',
  });

  const editedItems   = ref([]);
  let sendOnline      = null;
  let deckGroupToEdit = ref<_DeckGroup>(null);
  let total           = ref<number>(0);
  const ajax          = ref<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });
  const ajaxUpdate    = ref<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });

  //
  const onCheckboxSelected = (deckGroups: Array<_DeckGroup>) => {
    tt().selectedRowsToDelete = deckGroups;
  };
  const onEdit             = (deckGroups: _DeckGroup) => {
    if (undefined === editedItems[deckGroups.id]) {
      editedItems[deckGroups.id] = {
        editCounter: 0,
      };
    }
    editedItems[deckGroups.id].editCounter++;
    setTimeout(() => {
      editedItems[deckGroups.id].editCounter--;
      if (editedItems[deckGroups.id].editCounter === 0) {
        xhrUpdate(deckGroups);
      }
    }, 500);
  };
  const onSearch           = (params: { searchTerm: string }) => {
    tt().searchKeyword = params.searchTerm;
    xhrLoad();
  };
  const onPageChange       = (params: { currentPage: number, currentPerPage: number, prevPage: number, total: number }) => {
    tt().paginationOptions.setCurrentPage = params.currentPage;
    tt().paginationOptions.perPage        = params.currentPerPage;
    xhrLoad();
  };
  const onSortChange       = (params) => {

  };
  const onColumnFilter     = (params) => {
    //
    console.log('sort change');
  };
  const onPerPageChange    = (params: { currentPage: number; currentPerPage: number; total: number; }) => {
    tt().paginationOptions.setCurrentPage = params.currentPage;
    tt().paginationOptions.perPage        = params.currentPerPage;
    xhrLoad();
  };
  const loadItems          = () => {
    xhrLoad();
  };
  const tt                 = () => tableData.value;

  const xhrLoad = () => {
    const handleAjax: HandleAjax = new HandleAjax(ajaxUpdate.value);
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
        const groups = done.data.deck_groups;
        const total  = done.data.total;
        console.log({done, groups, total});
        tt().isLoading       = false;
        tableData.value.rows = groups;
        tt().totalRecords    = total;
      },
      funcFailue(done) {
        handleAjax.error(done);
        tt().isLoading = false;
      },
    });
  };

  const xhrUpdate = (deckGroup: _DeckGroup) => {
    const handleAjax: HandleAjax = new HandleAjax(ajax.value);
    sendOnline                   = new Server().send_online({
      data: [
        vdata.localize.nonce,
        {
          deck_group: deckGroupToEdit,
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

  return {
    ajax, ajaxUpdate, total, deckGroupToEdit, editedItems, tableData,
    onCheckboxSelected, onEdit, onSearch, onPageChange, onPerPageChange, loadItems,
    onSortChange, onColumnFilter,
  };

}