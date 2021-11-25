import {_Ajax, HandleAjax} from "../classes/HandleAjax";
import {InterFuncSuccess, Server} from "../static/server";
import {ref, onMounted, computed, reactive} from "@vue/composition-api";
import {_Card, _Deck, _DeckGroup, _Study, _Tag} from "../interfaces/inter-sp";
import {Store} from "../static/store";

declare var bootstrap;

export default function (status = 'publish') {
  const ajax          = ref<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });
  const ajaxForecast  = reactive<_Ajax>({
    sending       : false,
    error         : false,
    errorMessage  : '',
    success       : false,
    successMessage: '',
  });
  const statsForecast = ref(null);
  let forecastSpan    = ref('one_month');

  const _loadAllStats = () => {
    if (null === statsForecast.value) {
      xhrLoadForecast();
    }
  }

  const xhrLoadForecast = () => {
    const handleAjax: HandleAjax = new HandleAjax(ajaxForecast);
    return new Promise((resolve, reject) => {
      new Server().send_online({
        data: [
          Store.nonce,
          {
            span: forecastSpan.value,
          }
        ],
        what: "admin_sp_ajax_front_load_stats_forecast",
        funcBefore() {
          handleAjax.start();
        },
        funcSuccess(done: InterFuncSuccess) {
          handleAjax.stop();
          resolve(0);
        },
        funcFailue(done) {
          handleAjax.error(done);
        },
      });
    });
  };

  return {
    ajax, ajaxForecast,
    _loadAllStats,
    forecastSpan,
  };

}