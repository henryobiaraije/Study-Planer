import { _Ajax, HandleAjax } from "../classes/HandleAjax";
import { InterFuncSuccess, Server } from "../static/server";
import { ref, onMounted, computed, reactive } from "@vue/composition-api";
import { _Card, _Deck, _DeckGroup, _Study, _Tag } from "../interfaces/inter-sp";
import { Store } from "../static/store";
import Chart from "chart.js/auto";

declare var bootstrap;

export default function (status = 'publish') {
  const ajax = ref<_Ajax>({
    sending: false,
    error: false,
    errorMessage: '',
    success: false,
    successMessage: '',
  });
  const ajaxForecast = reactive<_Ajax>({
    sending: false,
    error: false,
    errorMessage: '',
    success: false,
    successMessage: '',
  });
  const statsForecast = ref(null);
  let forecastSpan = ref('one_month');

  const _initChartForcast = () => {
    setTimeout(() => {
      new Chart('sp-chart-forecast', {
        type: 'bar',
        data: {
          labels: ["13:30", "13:40", "13:50", "14:00", "14:10", "14:20", "14:30", "14:40", "14:50", "15:00", "15:10", "15:20"],
          datasets: [
            {
              type: 'line',
              label: 'Cummulative',
              yAxisID: "y-axis-2",
              backgroundColor: "rgba(255,255,255,0.5)",
              data: [0, 30, 62, 100, 100, 100, 114, 77, 57, 54, 10, 10],
              borderColor: 'rgba(255, 93, 0, 0.6)',
              borderWidth: 2
            },
            {
              label: 'Young',
              type: 'bar',
              yAxisID: "y-axis-1",
              data: [2, 0, 3, 7, 11, 13, 8, 44, 35, 3, 46, 1],
              backgroundColor: '#9dd99d',
              borderColor: '#8cbf8c',
              borderWidth: 2
            },
            {
              label: 'Mature',
              type: 'bar',
              yAxisID: "y-axis-1",
              data: [2, 0, 3, 7, 11, 13, 8, 44, 35, 3, 46, 1],
              backgroundColor: '#489c47',
              borderColor: '#2f622e',
              borderWidth: 2
            },

          ],
        },
        options: {
          scales: {
            //@ts-ignore
            "y-axis-1": {
              position: 'left',
              type: 'linear',
              title: {
                display: true,
                text: 'Cards',
              },
            },
            "y-axis-2": {
              position: 'right',
              type: 'linear',
              title: {
                display: true,
                text: 'Cumulative',
              },
            },

            // x: {
            //   stacked: true
            // },
            // y: { 
            //   stacked: true
            // }
          },
        }
      });
    }, 500);
  }

  const _loadAllStats = () => {
    if (null === statsForecast.value) {
      xhrLoadForecast().then((res) => {
        console.log('Show now');
        _initChartForcast();
      }).catch(() => {
        // todo remove later
        console.log('Show now');
        _initChartForcast();
      });
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
          handleAjax.stop();
          // handleAjax.error(done);
          reject();
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