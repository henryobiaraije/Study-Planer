import {_Ajax, HandleAjax} from "../classes/HandleAjax";
import {InterFuncSuccess, Server} from "../static/server";
import {ref, watch, onMounted, computed, reactive} from "@vue/composition-api";
import {_Card, _Deck, _DeckGroup, _Study, _Tag} from "../interfaces/inter-sp";
import {Store} from "../static/store";
import Chart from "chart.js/auto";
import {CalendarHeatmap} from 'vue-calendar-heatmap'
import 'vue-calendar-heatmap/dist/vue-calendar-heatmap.css'
import VueCalendarHeatmap from 'vue-calendar-heatmap/dist/vue-calendar-heatmap.common'
import Vue from "vue";
import Cookies from 'js-cookie';
// import * as echarts from 'echarts';

Vue.use(VueCalendarHeatmap)
declare var bootstrap;

// declare var CalHeatMap;

// <editor-fold desc="Interfaces">

interface _ProgressChartGraphable {
  days_and_count: Array<{
    count: number;
    date: string;
    day: string;
    day_answer_count: number;
  }>;
  total_current_streak: number;
  total_daily_average: string;
  total_daily_percent: string;
  total_longest_streak: number;
}

interface _ForecastGraphable {
  cumulative: Array<number>;
  heading: Array<string>;
  m: Array<number>;
  y: Array<number>;
  average: number,
  due_tomorrow: number,
  total_reviews: number,
  y_debug: {
    answer: Array<any>;
    new_cards: Array<any>;
  };
  m_debug: {
    answer: Array<any>;
    new_cards: Array<any>;
  };
}

interface _ReviewGraphable {
  cumulative: Array<number>;
  cumulative_m: Array<number>;
  cumulative_y: Array<number>;
  cumulative_newly_learned: Array<number>;
  cumulative_relearned: Array<number>;
  cumulative_m_time: Array<number>;
  cumulative_y_time: Array<number>;
  cumulative_newly_learned_time: Array<number>;
  cumulative_relearned_time: Array<number>;
  heading: Array<string>;
  m: Array<number>;
  y: Array<number>;
  newly_learned: Array<number>;
  relearned: Array<number>;
  m_time: Array<number>;
  y_time: Array<number>;
  newly_learned_time: Array<number>;
  relearned_time: Array<number>;
  average: number,
  due_tomorrow: number,
  total_reviews: number,
  y_debug: {
    answer: Array<any>;
    new_cards: Array<any>;
  };
  m_debug: {
    answer: Array<any>;
    new_cards: Array<any>;
  };
}

interface _ChartAddedGraphable {
  heading: Array<string>,
  average_new_cards_per_day: string,
  cumulative_new_cards: Array<number>,
  new_cards_added: Array<number>;
  total_new_cards: Array<number>;
}

interface _ChartAnswerButtons {
  heading: Array<string>,
  days: {
    learning: {
      correct_percent: number;
      total: number;
      total_correct: number;
    },
    m: {
      correct_percent: number;
      total: number;
      total_correct: number;
    },
    y: {
      correct_percent: number;
      total: number;
      total_correct: number;
    },
  },
  learning: Array<number>;
  y: Array<number>;
  m: Array<number>;
}

interface _ChartIntervalGraphable {
  heading: Array<string>;
  day_diffs: Array<number>;
  day_diff_percentages: Array<number>;
}

interface _ChartHourlyBreakdownGraphable {
  heading: Array<string>;
  answers_per_hour: Array<number>;
  percentage_correct: Array<number>;
}

interface _ChartCardTypesGraphable {
  heading: Array<string>;
  pie_data2: Array<number>;
  pie_data: Array<{
    name: string;
    value: number;
  }>;
  total_cards: number;
  total_mature: number;
  total_new: number;
  total_young: number;
}

// </editor-fold desc="Interfaces">

export default function (status = 'publish') {
  const ajax = ref<_Ajax>({
    sending: false,
    error: false,
    errorMessage: '',
    success: false,
    successMessage: '',
  });
  const ajaxProgressChart = reactive<_Ajax>({
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
  const ajaxReview = reactive<_Ajax>({
    sending: false,
    error: false,
    errorMessage: '',
    success: false,
    successMessage: '',
  });
  const ajaxReviewTime = reactive<_Ajax>({
    sending: false,
    error: false,
    errorMessage: '',
    success: false,
    successMessage: '',
  });
  const ajaxChartAdded = reactive<_Ajax>({
    sending: false,
    error: false,
    errorMessage: '',
    success: false,
    successMessage: '',
  });
  const ajaxChartInterval = reactive<_Ajax>({
    sending: false,
    error: false,
    errorMessage: '',
    success: false,
    successMessage: '',
  });
  const ajaxChartAnswerButtons = reactive<_Ajax>({
    sending: false,
    error: false,
    errorMessage: '',
    success: false,
    successMessage: '',
  });
  const ajaxHourlyBreakdown = reactive<_Ajax>({
    sending: false,
    error: false,
    errorMessage: '',
    success: false,
    successMessage: '',
  });
  const ajaxDeckCardTypeChart = reactive<_Ajax>({
    sending: false,
    error: false,
    errorMessage: '',
    success: false,
    successMessage: '',
  });
  let statsProgressChart = ref<_ProgressChartGraphable>({
    total_current_streak: 0,
    total_daily_average: '',
    total_daily_percent: '',
    total_longest_streak: 0,
    days_and_count: [],
  });
  let statsForecast = ref<_ForecastGraphable>(null);
  let statsReview = ref<_ReviewGraphable>(null);
  let statsReviewTime = ref<_ReviewGraphable>(null);
  let statsChartAdded = ref<_ChartAddedGraphable>(null);
  let statsChartInterval = ref<_ChartIntervalGraphable>(null);
  let statsChartAnserButtons = ref<_ChartAnswerButtons>(null);
  let statsHourlyBreakdown = ref<_ChartHourlyBreakdownGraphable>(null);
  let statsCardTypes = ref<_ChartCardTypesGraphable>(null);
  let forecastSpan = ref('one_month');
  let reviewCountSpan = ref('one_month');
  let reviewTimeSpan = ref('one_month');
  let chartAddedTimeSpan = ref('one_month');
  let chartIntervalTimeSpan = ref('one_month');
  let chartAnswerButtonsTimeSpan = ref('one_month');
  let chartHourlyBreakdownDate = ref('');
  let chartCardTypesSpan = ref('one_month');
  let chartProgressChartYear = ref(new Date().getFullYear());
  let progressHeatMapColors = ref([
    {
      name: 'Green',
      data: [
        '#f9fafb', '#dcfce7', '#bbf7d0', '#86efac', '#4ade80',
        '#22c55e', '#16a34a', '#15803d', '#166534', '#14532d'
      ],
    },
    {
      name: 'Blue',
      data: [
        '#f9fafb', '#dbeafe', '#bfdbfe', '#93c5fd', '#60a5fa',
        '#6b7280', '#2563eb', '#374151', '#1e40af', '#1e3a8a'
      ],
    },
    {
      name: 'Gray',
      data: [
        '#f9fafb', '#f3f4f6', '#e5e7eb', '#d1d5db', '#9ca3af',
        '#6b7280', '#4b5563', '#374151', '#1f2937', '#111827'
      ],
    },
    {
      name: 'Red',
      data: [
        '#f9fafb', '#fee2e2', '#fecaca', '#fca5a5', '#f87171',
        '#ef4444', '#dc2626', '#b91c1c', '#991b1b', '#7f1d1d'
      ],
    },
  ]);
  let progressSelectedColor = ref(Cookies.get('spProgressColor') ? Cookies.get('spProgressColor') : '');
  const currentDeckCardType = ref<_Deck>(null);

  const colorLearning = '#4848bf';
  const colorYoung = '#9cd89c';
  const colorMature = '#2f622e';
  const colorDack = '#484848';
  const colorGray = '#a5a5a5';

  const _getProgressColorLegend = () => {
    let data = progressHeatMapColors.value[0].data;
    const selectedColorCookie = Cookies.get('spProgressColor') ? Cookies.get('spProgressColor') : 'Green';
    const colorLegend = progressHeatMapColors.value.find(color => color.name === selectedColorCookie.toString());
    if (undefined !== colorLegend) {
      data = colorLegend.data;
    }
    // console.log({selectedColorCookie, data});
    return data;
  }

  const _initChartCardTypes = () => {
    setTimeout(() => {
      // @ts-ignore
      new Chart('sp-chart-card-types', {
        type: 'pie',
        data: {
          datasets: [
            {
              // data: [94, 25, 72, 70, 14],
              data: statsCardTypes.value.pie_data2,
              backgroundColor: [colorMature, colorYoung, colorDack],
              label: 'Dataset 1',
            },
          ],
          // labels: ['Red', 'Orange', 'Yellow', 'Green', 'Blue'],
          labels: statsCardTypes.value.heading,
        },
        options: {
          // title: {
          //   display: true,
          //   text: 'Chart.js Doughnut Chart',
          // },
          plugins: {
            legend : {
              labels: {
                // render 'label', 'value', 'percentage', 'image' or custom function, default is 'percentage'
                // @ts-ignore
                render: 'value',
                // precision for percentage, default is 0
                precision: 0,
                // identifies whether or not labels of value 0 are displayed, default is false
                showZero: true,
                // font size, default is defaultFontSize
                fontSize: 12,
                // font color, can be color array for each data or function for dynamic color, default is defaultFontColor
                fontColor: '#fff',
                // font style, default is defaultFontStyle
                fontStyle: 'normal',
                // font family, default is defaultFontFamily
                fontFamily: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif",
                // draw text shadows under labels, default is false
                textShadow: true,
                // text shadow intensity, default is 6
                shadowBlur: 10,
                // text shadow X offset, default is 3
                shadowOffsetX: -5,
                // text shadow Y offset, default is 3
                shadowOffsetY: 5,
                // text shadow color, default is 'rgba(0,0,0,0.3)'
                shadowColor: 'rgba(255,0,0,0.75)',
                // draw label in arc, default is false
                // bar chart ignores this
                arc: true,
                // position to draw label, available value is 'default', 'border' and 'outside'
                // bar chart ignores this
                // default is 'default'
                position: 'default',
                // draw label even it's overlap, default is true
                // bar chart ignores this
                overlap: true,
                // show the real calculated percentages from the values and don't apply the additional logic to fit the percentages to 100 in total, default is false
                showActualPercentages: true,
                // set images when `render` is 'image'
                images: [
                  {
                    src: 'image.png',
                    width: 16,
                    height: 16
                  }
                ],
                // add padding when position is `outside`
                // default is 2
                outsidePadding: 4,
                // add margin of text when position is `outside` or `border`
                // default is 2
                textMargin: 4
              }
            }
          }
        },
      });

    }, 500);
  }
  const _initChartCardTypes2 = () => {
    setTimeout(() => {

      // const chartDom = document.getElementById('sp-chart-card-types');
      // var myChart = echarts.init(chartDom);
      // // Specify configurations and data graphs
      // var option = {
      //   color: [colorMature, colorYoung, colorDack],
      //   title: {
      //     // text: 'How Users Are Finding the Website',
      //     // subtext: 'Fictitious',
      //     x: 'center'
      //   },
      //   tooltip: {
      //     trigger: 'item',
      //     formatter: "{a} <br/>{b} : {c} ({d}%)"
      //   },
      //   legend: {
      //     orient: 'horizontal',
      //     left: 'top',
      //     // data: ['Mature: 426', 'Young+Learn: 7200', 'Unseen: 18675']
      //     data: statsCardTypes.value.heading,
      //   },
      //   series: [
      //     {
      //       name: 'Access Sources',
      //       type: 'pie',
      //       radius: '25%',
      //       center: ['50%', '60%'],
      //       // data: [
      //       //   {value: 335, name: 'Mature: 426'},
      //       //   {value: 310, name: 'Young+Learn: 7200'},
      //       //   {value: 234, name: 'Unseen: 18675'},
      //       // ],
      //       data: statsCardTypes.value.pie_data,
      //       itemStyle: {
      //         emphasis: {
      //           shadowBlur: 0,
      //           shadowOffsetX: 0,
      //           shadowColor: 'rgba(0, 0, 0, 0)'
      //         }
      //       }
      //     }
      //   ]
      // };
      // // Use just the specified configurations and data charts.
      // myChart.setOption(option);
      // console.log('adding 34rr', {chartDom, myChart});

    }, 500);
  }
  const _initChartHourlyBreakdown = () => {
    setTimeout(() => {
      // console.log('Using _initChartHourlyBreakdown', statsHourlyBreakdown.value.answers_per_hour);
      new Chart('sp-chart-chart-hourly-breakdown', {
        type: 'bar',
        data: {
          // labels: ["13:30", "13:40", "13:50", "14:00", "14:10", "14:20", "14:30", "14:40", "14:50", "15:00", "15:10", "15:20"],
          labels: statsHourlyBreakdown.value.heading,
          datasets: [
            // {
            //   type: 'line',
            //   label: 'Cummulative',
            //   yAxisID: "y-axis-2",
            //   backgroundColor: "#000",
            //   data: [0, 30, 62, 100, 100, 100, 114, 77, 57, 54, 10, 10],
            //   // data: statsForecast.value.cumulative,
            //   borderColor: '#000',
            //   borderWidth: 2,
            //   tension: 0.5,
            // },
            {
              label: 'Answers',
              type: 'bar',
              yAxisID: "y-axis-1",
              // data: [5, 2, 7, 9, 35, 34, 12, 45, 21, 31, 34, 5],
              data: statsHourlyBreakdown.value.answers_per_hour,
              backgroundColor: colorGray,
              // borderColor: '#8cbf8c',
              // barThickness: 10,
              categoryPercentage: 0.3,
              // borderWidth: 2
            },
            {
              label: '% Correct',
              // barThickness: 10,
              type: 'bar',
              yAxisID: "y-axis-1",
              // data: [2, 0, 3, 7, 11, 13, 8, 44, 35, 3, 46, 1],
              data: statsHourlyBreakdown.value.percentage_correct,
              backgroundColor: colorDack,
              // borderColor: '#2f622e',
              // borderWidth: 2
            },

          ],
        },
        options: {
          responsive: true,
          elements: {
            point: {
              radius: 0
            }
          },
          scales: {
            //@ts-ignore
            "y-axis-1": {
              position: 'left',
              type: 'linear',
              title: {
                display: true,
                text: '% Correct',
              },
              // beginAtZero: true,
              // stacked: true
            },
            "y-axis-2": {
              position: 'right',
              type: 'linear',
              title: {
                display: true,
                text: 'Reviews',
              },
            },
            x: {
              stacked: true,
            },
            // y: {
            //     // stacked: true,
            //     beginAtZero: true,
            // }
          },
        }
      });
    }, 500);
  }
  const _initChartAnswerButtons = () => {
    setTimeout(() => {
      new Chart('sp-chart-chart-answer-buttons', {
        type: 'bar',
        data: {
          // labels: ["13:30", "13:40", "13:50", "14:00", "14:10", "14:20", "14:30", "14:40", "14:50", "15:00", "15:10", "15:20"],
          labels: ['', '1', '2', '3', '4',
            '', '', '1', '2', '3', '4',
            '', '', '1', '2', '3', '4', ''],
          // labels: statsChartAdded.value.heading,
          datasets: [
            {
              yAxisID: 'y-axis-1',
              label: 'Answers',
              // data: statsReviewTime.value.y_time,
              // data: [
              //   0, 30, 62, 100, 43, 0,
              //   0, 114, 77, 57, 54, 0,
              //   0, 23, 44, 83, 34, 0
              // ],
              data: [
                0, ...statsChartAnserButtons.value.learning, 0, // learning
                0, ...statsChartAnserButtons.value.y, 0, // young
                0, ...statsChartAnserButtons.value.m, 0 // mature
              ],
              backgroundColor: [
                '', colorLearning, colorLearning, colorLearning, colorLearning, '',
                '', colorYoung, colorYoung, colorYoung, colorYoung, '',
                '', colorMature, colorMature, colorMature, colorMature, '',
              ],
            },
            {
              label: 'Learning',
              'type': 'line',
              yAxisID: 'y-axis-1',
              data: [],
              backgroundColor: colorLearning,
            },
            {
              label: 'Young',
              'type': 'line',
              yAxisID: 'y-axis-1',
              data: [],
              backgroundColor: colorYoung,
            },
            {
              label: 'Mature',
              'type': 'line',
              yAxisID: 'y-axis-1',
              data: [],
              backgroundColor: colorMature,
            }
          ],
        },
        options: {
          responsive: true,
          elements: {
            point: {
              radius: 0
            }
          },
          scales: {
            "y-axis-1": {
              // stacked: true,
              position: 'left',
              type: 'linear',
              title: {
                display: true,
                text: 'Answers',
              },
              // beginAtZero: true,
              // stacked: true
            },
            // "y-axis-2": {
            //   position: 'right',
            //   type: 'linear',
            //   title: {
            //     display: true,
            //     text: 'Percentage',
            //   },
            // },
            // x: {
            //   stacked: true
            // },
            // y: {
            //   stacked: true,
            //   title: {
            //     display: true,
            //     text: 'Answeres',
            //   },
          },
          plugins: {
            legend: {
              labels: {
                filter: function (legendItem, chartData) {
                  const noLegend = ['Answers'];
                  // console.log({legendItem, chartData});
                  return noLegend.findIndex((text => text === legendItem.text)) < 0;
                }
              }
            },
          },
        },
      });
    }, 500);
  }
  const _initChartAnswerButtons2 = () => {
    setTimeout(() => {
      new Chart('sp-chart-chart-answer-buttons', {
        type: 'bar',
        data: {
          // labels: ["13:30", "13:40", "13:50", "14:00", "14:10", "14:20", "14:30", "14:40", "14:50", "15:00", "15:10", "15:20"],
          labels: ['', '1', '2', '3', '4',
            '', '', '1', '2', '3', '4',
            '', '', '1', '2', '3', '4', ''],
          // labels: statsChartAdded.value.heading,
          datasets: [
            {
              yAxisID: 'y-axis-1',
              label: 'Cards',
              // data: statsReviewTime.value.y_time,
              data: [
                0, 30, 62, 100, 43, 0,
                0, 114, 77, 57, 54, 0,
                0, 23, 44, 83, 34, 0
              ],
              backgroundColor: [
                '', colorLearning, colorLearning, colorLearning, colorLearning, '',
                '', colorYoung, colorYoung, colorYoung, colorYoung, '',
                '', colorMature, colorMature, colorMature, colorMature, '',
              ],
            },
          ],
        },
        options: {
          responsive: true,
          elements: {
            point: {
              radius: 0
            }
          },
          scales: {
            "y-axis-1": {
              // stacked: true,
              position: 'left',
              type: 'linear',
              title: {
                display: true,
                text: 'Answers',
              },
              // beginAtZero: true,
              // stacked: true
            },
            // "y-axis-2": {
            //   position: 'right',
            //   type: 'linear',
            //   title: {
            //     display: true,
            //     text: 'Percentage',
            //   },
            // },
            // x: {
            //   stacked: true
            // },
            // y: {
            //   stacked: true,
            //   title: {
            //     display: true,
            //     text: 'Answeres',
            //   },
          },
          plugins: {
            legend: {
              labels: {
                filter: function (legendItem, chartData) {
                  return true;
                  // const noLegend = ['Young Cumulative', 'Newly Learned Cumulative', 'Relearned Cumulative', 'Mature Cumulative'];
                  // return noLegend.findIndex((text => text === legendItem.text)) < 0;
                  // console.log({legendItem, chartData});
                  // // if (legendItem.datasetIndex === 0) {
                  // //   return false;
                  // // }
                  // return true;
                }
              }
            },
          },
        },
      });
    }, 500);
  }
  const _initChartInterval = () => {
    setTimeout(() => {
      new Chart('sp-chart-chart-interval', {
        type: 'bar',
        data: {
          // labels: ["13:30", "13:40", "13:50", "14:00", "14:10", "14:20", "14:30", "14:40", "14:50", "15:00", "15:10", "15:20"],
          labels: statsChartInterval.value.heading,
          datasets: [
            {
              label: 'Percentage',
              type: 'line',
              yAxisID: "y-axis-2",
              backgroundColor: "#000000",
              // data: [0, 30, 62, 100, 100, 100, 114, 77, 57, 54, 10, 10],
              data: statsChartInterval.value.day_diff_percentages,
              borderColor: '#000000',
              borderWidth: 2,
              tension: 0.5,
            },
            {
              yAxisID: 'y-axis-1',
              label: 'Cards',
              data: statsChartInterval.value.day_diffs,
              // data: [0, 30, 62, 100, 100, 100, 114, 77, 57, 54, 10, 10],
              backgroundColor: '#499c9c',
            },
          ],
        },
        options: {
          responsive: true,
          elements: {
            point: {
              radius: 0
            }
          },
          scales: {
            "y-axis-1": {
              // stacked: true,
              position: 'left',
              type: 'linear',
              title: {
                display: true,
                text: 'Cards',
              },
              // beginAtZero: true,
              // stacked: true
            },
            "y-axis-2": {
              position: 'right',
              type: 'linear',
              title: {
                display: true,
                text: 'Percentage',
              },
            },
            x: {
              stacked: true
            },
            // y: {
            //   stacked: true,
            //   title: {
            //     display: true,
            //     text: 'Answeres',
            //   },
          },
          plugins: {
            legend: {
              labels: {
                filter: function (legendItem, chartData) {
                  return true;
                  // const noLegend = ['Young Cumulative', 'Newly Learned Cumulative', 'Relearned Cumulative', 'Mature Cumulative'];
                  // return noLegend.findIndex((text => text === legendItem.text)) < 0;
                  // console.log({legendItem, chartData});
                  // // if (legendItem.datasetIndex === 0) {
                  // //   return false;
                  // // }
                  // return true;
                }
              }
            },
          },
        },
      });
    }, 500);
  }
  const _initChartAdded = () => {
    setTimeout(() => {
      new Chart('sp-chart-chart-added', {
        type: 'bar',
        data: {
          // labels: ["13:30", "13:40", "13:50", "14:00", "14:10", "14:20", "14:30", "14:40", "14:50", "15:00", "15:10", "15:20"],
          labels: statsChartAdded.value.heading,
          datasets: [
            {
              label: 'Cards',
              type: 'line',
              yAxisID: "y-axis-1",
              backgroundColor: "#17ac17",
              // data: [10, 20, 62, 15, 25, 10, 80, 120, 50, 40, 75, 85],
              data: statsChartAdded.value.new_cards_added,
              borderColor: '#17ac17',
              borderWidth: 2,
              tension: 0.5,
            },
            {
              label: 'Cumulative Card',
              type: 'line',
              yAxisID: "y-axis-2",
              backgroundColor: "#3f22fc",
              // data: [0, 30, 62, 100, 100, 100, 114, 77, 57, 54, 10, 10],
              data: statsChartAdded.value.cumulative_new_cards,
              borderColor: '#3f22fc',
              borderWidth: 2,
              tension: 0.5,
            },
          ],
        },
        options: {
          responsive: true,
          elements: {
            point: {
              radius: 0
            }
          },
          scales: {
            "y-axis-1": {
              // stacked: true,
              position: 'left',
              type: 'linear',
              title: {
                display: true,
                text: 'Cards',
              },
              // beginAtZero: true,
              // stacked: true
            },
            "y-axis-2": {
              position: 'right',
              type: 'linear',
              title: {
                display: true,
                text: 'Cumulative Cards',
              },
            },
            x: {
              stacked: true
            },
            // y: {
            //   stacked: true,
            //   title: {
            //     display: true,
            //     text: 'Answeres',
            //   },
          },
          plugins: {
            legend: {
              labels: {
                filter: function (legendItem, chartData) {
                  return true;
                  // const noLegend = ['Young Cumulative', 'Newly Learned Cumulative', 'Relearned Cumulative', 'Mature Cumulative'];
                  // return noLegend.findIndex((text => text === legendItem.text)) < 0;
                  // console.log({legendItem, chartData});
                  // // if (legendItem.datasetIndex === 0) {
                  // //   return false;
                  // // }
                  // return true;
                }
              }
            },
          },
        },
      });
    }, 500);
  }
  const _initChartReviewTime = () => {
    setTimeout(() => {
      new Chart('sp-chart-review-time', {
        type: 'bar',
        data: {
          // labels: ["13:30", "13:40", "13:50", "14:00", "14:10", "14:20", "14:30", "14:40", "14:50", "15:00", "15:10", "15:20"],
          labels: statsReviewTime.value.heading,
          datasets: [
            {
              label: 'Young Cumulative',
              type: 'line',
              yAxisID: "y-axis-2",
              backgroundColor: "#9dd99d",
              // data: [0, 30, 62, 100, 100, 100, 114, 77, 57, 54, 10, 10],
              data: statsReviewTime.value.cumulative_y_time,
              borderColor: '#9dd99d',
              borderWidth: 2,
              tension: 0.5,
            },
            {
              label: 'Mature Cumulative',
              type: 'line',
              yAxisID: "y-axis-2",
              backgroundColor: "#489c47",
              // data: [0, 30, 62, 100, 100, 100, 114, 77, 57, 54, 10, 10],
              data: statsReviewTime.value.cumulative_m_time,
              borderColor: '#489c47',
              borderWidth: 2,
              tension: 0.5,
            },
            {
              label: 'Newly Learned Cumulative',
              type: 'line',
              yAxisID: "y-axis-2",
              backgroundColor: "#4848bf",
              // data: [0, 30, 62, 100, 100, 100, 114, 77, 57, 54, 10, 10],
              data: statsReviewTime.value.cumulative_newly_learned_time,
              borderColor: '#4848bf',
              borderWidth: 2,
              tension: 0.5,
            },
            {
              label: 'Relearned Cumulative',
              type: 'line',
              yAxisID: "y-axis-2",
              backgroundColor: "#d08357",
              // data: [0, 30, 62, 100, 100, 100, 114, 77, 57, 54, 10, 10],
              data: statsReviewTime.value.cumulative_relearned_time,
              borderColor: '#d08357',
              borderWidth: 2,
              tension: 0.5,
            },
            {
              yAxisID: 'y-axis-1',
              label: 'Young',
              data: statsReviewTime.value.y_time,
              backgroundColor: '#9dd99d',
            },
            {
              yAxisID: 'y-axis-1',
              label: 'Mature',
              data: statsReviewTime.value.m_time,
              backgroundColor: '#489c47',
            },
            {
              yAxisID: 'y-axis-1',
              label: 'Relerned',
              data: statsReviewTime.value.relearned_time,
              backgroundColor: '#d08357',
            },
            {
              yAxisID: 'y-axis-1',
              label: 'Newly learned',
              data: statsReviewTime.value.newly_learned_time,
              backgroundColor: '#4848bf',
            },
          ],
        },
        options: {
          responsive: true,
          elements: {
            point: {
              radius: 0
            }
          },
          scales: {
            "y-axis-1": {
              stacked: true,
              position: 'left',
              type: 'linear',
              title: {
                display: true,
                text: 'Hours',
              },
              // beginAtZero: true,
              // stacked: true
            },
            "y-axis-2": {
              position: 'right',
              type: 'linear',
              title: {
                display: true,
                text: 'Cumulative Hours',
              },
            },
            x: {
              stacked: true
            },
            // y: {
            //   stacked: true,
            //   title: {
            //     display: true,
            //     text: 'Answeres',
            //   },
          },
          plugins: {
            legend: {
              labels: {
                filter: function (legendItem, chartData) {
                  const noLegend = ['Young Cumulative', 'Newly Learned Cumulative', 'Relearned Cumulative', 'Mature Cumulative'];
                  return noLegend.findIndex((text => text === legendItem.text)) < 0;
                  // console.log({legendItem, chartData});
                  // // if (legendItem.datasetIndex === 0) {
                  // //   return false;
                  // // }
                  // return true;
                }
              }
            },
          },
        },
      });
    }, 500);
  }
  const _initChartReviewCount = () => {
    setTimeout(() => {
      new Chart('sp-chart-review-count', {
        type: 'bar',
        data: {
          // labels: ["13:30", "13:40", "13:50", "14:00", "14:10", "14:20", "14:30", "14:40", "14:50", "15:00", "15:10", "15:20"],
          labels: statsReview.value.heading,
          datasets: [
            {
              label: 'Young Cumulative',
              type: 'line',
              yAxisID: "y-axis-2",
              backgroundColor: "#9dd99d",
              // data: [0, 30, 62, 100, 100, 100, 114, 77, 57, 54, 10, 10],
              data: statsReview.value.cumulative_y,
              borderColor: '#9dd99d',
              borderWidth: 2,
              tension: 0.5,
            },
            {
              label: 'Mature Cumulative',
              type: 'line',
              yAxisID: "y-axis-2",
              backgroundColor: "#489c47",
              // data: [0, 30, 62, 100, 100, 100, 114, 77, 57, 54, 10, 10],
              data: statsReview.value.cumulative_m,
              borderColor: '#489c47',
              borderWidth: 2,
              tension: 0.5,
            },
            {
              label: 'Newly Learned Cumulative',
              type: 'line',
              yAxisID: "y-axis-2",
              backgroundColor: "#4848bf",
              // data: [0, 30, 62, 100, 100, 100, 114, 77, 57, 54, 10, 10],
              data: statsReview.value.cumulative_newly_learned,
              borderColor: '#4848bf',
              borderWidth: 2,
              tension: 0.5,
            },
            {
              label: 'Relearned Cumulative',
              type: 'line',
              yAxisID: "y-axis-2",
              backgroundColor: "#d08357",
              // data: [0, 30, 62, 100, 100, 100, 114, 77, 57, 54, 10, 10],
              data: statsReview.value.cumulative_relearned,
              borderColor: '#d08357',
              borderWidth: 2,
              tension: 0.5,
            },
            {
              yAxisID: 'y-axis-1',
              label: 'Young',
              data: statsReview.value.y,
              backgroundColor: '#9dd99d',
            },
            {
              yAxisID: 'y-axis-1',
              label: 'Mature',
              data: statsReview.value.m,
              backgroundColor: '#489c47',
            },
            {
              yAxisID: 'y-axis-1',
              label: 'Relerned',
              data: statsReview.value.relearned,
              backgroundColor: '#d08357',
            },
            {
              yAxisID: 'y-axis-1',
              label: 'Newly learned',
              data: statsReview.value.newly_learned,
              backgroundColor: '#4848bf',
            },
          ],
        },
        options: {
          responsive: true,
          elements: {
            point: {
              radius: 0
            }
          },
          scales: {
            "y-axis-1": {
              stacked: true,
              position: 'left',
              type: 'linear',
              title: {
                display: true,
                text: 'Answers',
              },
              // beginAtZero: true,
              // stacked: true
            },
            "y-axis-2": {
              position: 'right',
              type: 'linear',
              title: {
                display: true,
                text: 'Cumulative',
              },
            },
            x: {
              stacked: true
            },
            // y: {
            //   stacked: true,
            //   title: {
            //     display: true,
            //     text: 'Answeres',
            //   },
          },
          plugins: {
            legend: {
              labels: {
                filter: function (legendItem, chartData) {
                  const noLegend = ['Young Cumulative', 'Newly Learned Cumulative', 'Relearned Cumulative', 'Mature Cumulative'];
                  return noLegend.findIndex((text => text === legendItem.text)) < 0;
                  // console.log({legendItem, chartData});
                  // // if (legendItem.datasetIndex === 0) {
                  // //   return false;
                  // // }
                  // return true;
                }
              }
            },
          },
        },
      })
    }, 500);
  };
  const _initChartForecast = () => {
    setTimeout(() => {
      new Chart('sp-chart-forecast', {
        type: 'bar',
        data: {
          // labels: ["13:30", "13:40", "13:50", "14:00", "14:10", "14:20", "14:30", "14:40", "14:50", "15:00", "15:10", "15:20"],
          labels: statsForecast.value.heading,
          datasets: [
            {
              type: 'line',
              label: 'Cummulative',
              yAxisID: "y-axis-2",
              backgroundColor: "#000",
              // data: [0, 30, 62, 100, 100, 100, 114, 77, 57, 54, 10, 10],
              data: statsForecast.value.cumulative,
              borderColor: '#000',
              borderWidth: 2,
              tension: 0.5,
            },
            {
              label: 'Mature',
              // barThickness: 10,
              type: 'bar',
              yAxisID: "y-axis-1",
              // data: [2, 0, 3, 7, 11, 13, 8, 44, 35, 3, 46, 1],
              data: statsForecast.value.m,
              backgroundColor: '#489c47',
              borderColor: '#2f622e',
              // borderWidth: 2
            },
            {
              label: 'Young',
              type: 'bar',
              yAxisID: "y-axis-1",
              // data: [2, 0, 3, 7, 11, 13, 8, 44, 35, 3, 46, 1],
              data: statsForecast.value.y,
              backgroundColor: '#9dd99d',
              borderColor: '#8cbf8c',
              // barThickness: 10,
              // categoryPercentage: 0.5,
              // borderWidth: 2
            },

          ],
        },
        options: {
          responsive: true,
          elements: {
            point: {
              radius: 0
            }
          },
          scales: {
            //@ts-ignore
            "y-axis-1": {
              position: 'left',
              type: 'linear',
              title: {
                display: true,
                text: 'Cards',
              },
              // beginAtZero: true,
              // stacked: true
            },
            "y-axis-2": {
              position: 'right',
              type: 'linear',
              title: {
                display: true,
                text: 'Cumulative',
              },
            },
            x: {
              stacked: true,
            },
            // y: {
            //     // stacked: true,
            //     beginAtZero: true,
            // }
          },
        }
      })
    }, 500);
  }
  const _initChartProgressChart = () => {

  }

  const _initChartProgressChart3 = () => {
    const largeScreen = jQuery(window).width() > 760;

    setTimeout(() => {
      //   const theColors = [
      //     'rgb(103,0,31)', 'rgb(178,24,43)', 'rgb(214,96,77)',
      //     'rgb(244,165,130)', 'rgb(253,219,199)', 'rgb(224,224,224)',
      //     'rgb(186,186,186)', 'rgb(135,135,135)', 'rgb(77,77,77)',
      //     'rgb(26,26,26)'
      //   ];
      //   let css = '';
      //   theColors.forEach((d, i) => {
      //     // css += ".q" + i + " {fill:" + d + "; background-color: " + d + "}";
      //     css += `
      //       .q${i}:{
      //         fill:${d};
      //         background-color: ${d};
      //       }
      //     `;
      //   });

      // jQuery('#sp-heatmap-style').remove();
      // jQuery('head').append(`
      //     <style id="sp-heatmap-style">
      //     ${css}
      //     </style>
      //   `);

      // const id = 'sp-chart-progress-chart';
      // console.log({id});
      // var cal = new CalHeatMap();

      // let style = document.createElement('style');
      // style.type = 'text/css';
      // ['rgb(103,0,31)', 'rgb(178,24,43)', 'rgb(214,96,77)', 'rgb(244,165,130)', 'rgb(253,219,199)', 'rgb(224,224,224)', 'rgb(186,186,186)', 'rgb(135,135,135)', 'rgb(77,77,77)', 'rgb(26,26,26)']
      //   .forEach(function (d, i) {
      //     style.innerHTML += ".q" + i + " {fill:" + d + "; background-color: " + d + "}";
      //   });
      // document.getElementsByTagName('head')[0].appendChild(style);

      // let cal = new CalHeatMap();
      // cal.init({
      //   itemSelector: '#' + id,
      //   range: 10,
      //   start: new Date(2000, 0, 1, 1),
      //   // data: "datas-hours.json",
      //   data: {
      //     "1420498800": 2,
      //     "1420585200": 4,
      //     "1420671600": 2,
      //     "1420758000": 1,
      //     "1421103600": 2,
      //     "1421190000": 1,
      //     "1421276400": 1,
      //     "1421362800": 1,
      //     "1421622000": 1,
      //     "1421708400": 1,
      //     "1422226800": 1,
      //     "1422313200": 1,
      //     "1422399600": 2,
      //     "1422486000": 1,
      //     "1422572400": 1,
      //     "1423695600": 3,
      //     "1424127600": 2,
      //     "1424214000": 1,
      //     "1424300400": 3,
      //     "1424386800": 1,
      //     "1424646000": 2,
      //     "1424732400": 1,
      //     "1424818800": 2,
      //     "1424905200": 2,
      //     "1424991600": 1,
      //     "1425337200": 1,
      //     "1425855600": 4,
      //     "1426201200": 2,
      //     "1426460400": 2,
      //     "1426546800": 1,
      //     "1426633200": 2,
      //     "1426719600": 1,
      //     "1426806000": 1,
      //     "1427065200": 1,
      //     "1427151600": 1,
      //     "1427238000": 2,
      //     "1427324400": 1,
      //     "1427670000": 2,
      //     "1428361200": 2,
      //     "1428447600": 2,
      //     "1428534000": 3,
      //     "1428620400": 3,
      //     "1428966000": 2,
      //     "1429138800": 2,
      //     "1429225200": 1,
      //     "1429484400": 2,
      //     "1429570800": 1,
      //     "1429657200": 2,
      //     "1429743600": 2,
      //     "1429830000": 3
      //   },
      // });

      // cal.init({
      //   itemSelector: '#' + id,
      //   itemName: ["Cluster", "Cluster"],
      //   domain: "month",
      //   subDomain: "day",
      //   domainLabelFormat: "%b-%Y",
      //   // data: "data.json",
      //   data: {
      //     "1420498800": 2,
      //     "1420585200": 4,
      //     "1420671600": 2,
      //     "1420758000": 1,
      //     "1421103600": 2,
      //     "1421190000": 1,
      //     "1421276400": 1,
      //     "1421362800": 1,
      //     "1421622000": 1,
      //     "1421708400": 1,
      //     "1422226800": 1,
      //     "1422313200": 1,
      //     "1422399600": 2,
      //     "1422486000": 1,
      //     "1422572400": 1,
      //     "1423695600": 3,
      //     "1424127600": 2,
      //     "1424214000": 1,
      //     "1424300400": 3,
      //     "1424386800": 1,
      //     "1424646000": 2,
      //     "1424732400": 1,
      //     "1424818800": 2,
      //     "1424905200": 2,
      //     "1424991600": 1,
      //     "1425337200": 1,
      //     "1425855600": 4,
      //     "1426201200": 2,
      //     "1426460400": 2,
      //     "1426546800": 1,
      //     "1426633200": 2,
      //     "1426719600": 1,
      //     "1426806000": 1,
      //     "1427065200": 1,
      //     "1427151600": 1,
      //     "1427238000": 2,
      //     "1427324400": 1,
      //     "1427670000": 2,
      //     "1428361200": 2,
      //     "1428447600": 2,
      //     "1428534000": 3,
      //     "1428620400": 3,
      //     "1428966000": 2,
      //     "1429138800": 2,
      //     "1429225200": 1,
      //     "1429484400": 2,
      //     "1429570800": 1,
      //     "1429657200": 2,
      //     "1429743600": 2,
      //     "1429830000": 3
      //   },
      //   // start: new Date(2012, 02),
      //   // maxDate: new Date(2013, 04),
      //   cellSize: 16, //
      //   range: 12, animationDuration: 1000,
      //   subDomainTextFormat: "%d",
      //   nextSelector: "#domainDynamicDimension-next",
      //   previousSelector: "#domainDynamicDimension-previous",
      //   legend: [1, 2, 3, 4, 5, 6, 7, 8, 9],
      //   legendCellSize: 15,
      // });

      // cal.init({
      //   itemSelector: '#' + id,
      //   domain: "month",
      //   subDomain: "day",
      //   cellSize: largeScreen ? 20 : 10,
      //   tooltip: true,
      //   // start : '', // todo add start date of the first answer
      //   // maxDate : '2021-12-27',
      //   subDomainTextFormat: "%d",
      //   range: 12,
      //   displayLegend: true,
      //   domainGutter: 1,
      //   legendColors: {
      //     min: "#b3fea2",
      //     max: "#1d8f05",
      //     empty: "#dfe1df"
      //     // Will use the CSS for the missing keys
      //   }
      // });
    }, 500);
  }

  const _reloadProgressChart = () => {
    setTimeout(() => {
      _loadProgressChart();
    }, 400);
  }
  const _reloadForecast = () => {
    setTimeout(() => {
      console.log('srat reload');
      _loadForecast();
    }, 400);
  }
  const _reloadReviewCount = () => {
    setTimeout(() => {
      console.log('srat reload');
      _loadReviewCount();
    }, 400);
  }
  const _reloadReviewTime = () => {
    setTimeout(() => {
      console.log('srat reload');
      _loadReviewTime();
    }, 400);
  }
  const _reloadChartAdded = () => {
    setTimeout(() => {
      _loadChartAdded();
    }, 400);
  }
  const _reloadChartInterval = () => {
    setTimeout(() => {
      _loadChartAdded();
    }, 400);
  }
  const _reloadChartAnswerButtons = () => {
    setTimeout(() => {
      _loadChartAdded();
    }, 400);
  }

  const _loadProgressChart = () => {
    // if (null === statsForecast.value) {
    xhrLoadProgressChart().then((res) => {
      console.log('Show now');
      _initChartProgressChart();
    }).catch(() => {
      // todo remove later
      console.log('Show now');
      _initChartProgressChart();
    });
    // }
  }
  const _loadForecast = () => {
    // if (null === statsForecast.value) {
    xhrLoadForecast().then((res) => {
      console.log('Show now');
      _initChartForecast();
    }).catch(() => {
      // todo remove later
      console.log('Show now');
      _initChartForecast();
    });
    // }
  }
  const _loadReviewCount = () => {
    xhrLoadReviewCount().then((res) => {
      _initChartReviewCount();
    }).catch(() => {
      // todo remove later
      _initChartReviewCount();
    });
  }
  const _loadReviewTime = () => {
    xhrLoadReviewTime().then((res) => {
      _initChartReviewTime();
    }).catch(() => {
      // todo remove later
      _initChartReviewTime();
    });
  }
  const _loadChartAdded = () => {
    xhrLoadChartAdded().then((res) => {
      _initChartAdded();
    }).catch(() => {
      // todo remove later
      _initChartAdded();
    });
  }
  const _loadChartIntervals = () => {
    xhrLoadChartInterval().then((res) => {
      _initChartInterval();
    }).catch(() => {
      // todo remove later
      _initChartInterval();
    });
  }
  const _loadChartAnswerButtons = () => {
    xhrLoadAnswerButtons().then((res) => {
      _initChartAnswerButtons();
    }).catch(() => {
      // todo remove later
      _initChartAnswerButtons();
    });
  }
  const _loadChartHourlyBreakDown = () => {
    xhrLoadHourlyBreakDown().then((res) => {
      _initChartHourlyBreakdown();
    }).catch(() => {
      // todo remove later
      _initChartHourlyBreakdown();
    });
  }
  const _loadCardTypes = () => {
    xhrLoadCardTypes().then((res) => {
      _initChartCardTypes();
    }).catch(() => {
      // todo remove later
      _initChartCardTypes();
    });
  }
  const _loadAllStats = () => {
    _loadProgressChart();
    _loadForecast();
    _loadReviewCount();
    _loadReviewTime();
    _loadChartAdded();
    _loadChartIntervals();
    _loadChartAnswerButtons();
    _loadChartHourlyBreakDown();
    _loadCardTypes();
  }

  watch(progressSelectedColor, (current, old) => {
    console.log({current, old});
    Cookies.set('spProgressColor', current);
    progressSelectedColor.value = current;
    _reloadProgressChart();
  });

  const xhrLoadProgressChart = () => {
    const handleAjax: HandleAjax = new HandleAjax(ajaxProgressChart);
    return new Promise((resolve, reject) => {
      new Server().send_online({
        data: [
          Store.nonce,
          {
            year: chartProgressChartYear.value,
          }
        ],
        what: "front_sp_ajax_front_load_stats_progress_chart",
        funcBefore() {
          handleAjax.start();
        },
        funcSuccess(done: InterFuncSuccess) {
          handleAjax.stop();
          statsProgressChart.value = done.data.graphable;
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
        what: "front_sp_ajax_front_load_stats_forecast",
        funcBefore() {
          handleAjax.start();
        },
        funcSuccess(done: InterFuncSuccess) {
          handleAjax.stop();
          statsForecast.value = done.data.graphable;
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
  const xhrLoadReviewTime = () => {
    const handleAjax: HandleAjax = new HandleAjax(ajaxReviewTime);
    return new Promise((resolve, reject) => {
      new Server().send_online({
        data: [
          Store.nonce,
          {
            span: reviewTimeSpan.value,
          }
        ],
        what: "front_sp_ajax_front_load_stats_review_time",
        funcBefore() {
          handleAjax.start();
        },
        funcSuccess(done: InterFuncSuccess) {
          handleAjax.stop();
          statsReviewTime.value = done.data.graphable;
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
  const xhrLoadChartAdded = () => {
    const handleAjax: HandleAjax = new HandleAjax(ajaxChartAdded);
    return new Promise((resolve, reject) => {
      new Server().send_online({
        data: [
          Store.nonce,
          {
            span: chartAddedTimeSpan.value,
          }
        ],
        what: "front_sp_ajax_front_load_stats_chart_added",
        funcBefore() {
          handleAjax.start();
        },
        funcSuccess(done: InterFuncSuccess) {
          handleAjax.stop();
          statsChartAdded.value = done.data.graphable;
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
  const xhrLoadChartInterval = () => {
    const handleAjax: HandleAjax = new HandleAjax(ajaxChartInterval);
    return new Promise((resolve, reject) => {
      new Server().send_online({
        data: [
          Store.nonce,
          {
            span: chartIntervalTimeSpan.value,
          }
        ],
        what: "front_sp_ajax_front_load_stats_chart_interval",
        funcBefore() {
          handleAjax.start();
        },
        funcSuccess(done: InterFuncSuccess) {
          handleAjax.stop();
          statsChartInterval.value = done.data.graphable;
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
  const xhrLoadAnswerButtons = () => {
    const handleAjax: HandleAjax = new HandleAjax(ajaxChartAnswerButtons);
    return new Promise((resolve, reject) => {
      new Server().send_online({
        data: [
          Store.nonce,
          {
            span: chartAnswerButtonsTimeSpan.value,
          }
        ],
        what: "front_sp_ajax_front_load_stats_chart_answer_buttons",
        funcBefore() {
          handleAjax.start();
        },
        funcSuccess(done: InterFuncSuccess) {
          handleAjax.stop();
          statsChartAnserButtons.value = done.data.graphable;
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
  const xhrLoadReviewCount = () => {
    const handleAjax: HandleAjax = new HandleAjax(ajaxReview);
    return new Promise((resolve, reject) => {
      new Server().send_online({
        data: [
          Store.nonce,
          {
            span: reviewCountSpan.value,
          }
        ],
        what: "front_sp_ajax_front_load_stats_review_time",
        funcBefore() {
          handleAjax.start();
        },
        funcSuccess(done: InterFuncSuccess) {
          handleAjax.stop();
          statsReview.value = done.data.graphable;
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
  const xhrLoadHourlyBreakDown = () => {
    const handleAjax: HandleAjax = new HandleAjax(ajaxHourlyBreakdown);
    return new Promise((resolve, reject) => {
      new Server().send_online({
        data: [
          Store.nonce,
          {
            date: chartHourlyBreakdownDate.value,
          }
        ],
        what: "front_sp_ajax_front_load_stats_hourly_breakdown",
        funcBefore() {
          handleAjax.start();
        },
        funcSuccess(done: InterFuncSuccess) {
          handleAjax.stop();
          statsHourlyBreakdown.value = done.data.graphable;
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
  const xhrLoadCardTypes = () => {
    console.log('0deg 339')
    const handleAjax: HandleAjax = new HandleAjax(ajaxDeckCardTypeChart);
    return new Promise((resolve, reject) => {
      new Server().send_online({
        data: [
          Store.nonce,
          {
            date: chartCardTypesSpan.value,
          }
        ],
        what: "front_sp_ajax_front_load_stats_card_types",
        funcBefore() {
          handleAjax.start();
        },
        funcSuccess(done: InterFuncSuccess) {
          handleAjax.stop();
          statsCardTypes.value = done.data.graphable;
          resolve(0);
        },
        funcFailue(done) {
          handleAjax.stop();
          console.log('0deg 339')
          // handleAjax.error(done);
          reject();
        },
      });
    });
  };

  return {
    ajax, ajaxProgressChart, ajaxForecast, ajaxReviewTime, ajaxChartAdded, ajaxChartInterval,
    ajaxChartAnswerButtons, ajaxHourlyBreakdown, ajaxDeckCardTypeChart,
    _loadAllStats, _loadForecast, _loadProgressChart,
    forecastSpan, _reloadForecast, _reloadReviewCount, _reloadReviewTime, _reloadChartAnswerButtons,
    _loadChartAnswerButtons, _loadChartHourlyBreakDown,
    statsForecast, ajaxReview, statsReview, reviewCountSpan, reviewTimeSpan,
    statsReviewTime, chartAddedTimeSpan, chartIntervalTimeSpan, statsChartAnserButtons,
    statsHourlyBreakdown, chartProgressChartYear,
    _reloadChartAdded, _reloadChartInterval, statsChartAdded, chartAnswerButtonsTimeSpan,
    chartHourlyBreakdownDate, statsProgressChart,
    progressHeatMapColors, progressSelectedColor, _getProgressColorLegend,
    currentDeckCardType,
  }

}