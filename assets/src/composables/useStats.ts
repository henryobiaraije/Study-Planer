import {_Ajax, HandleAjax} from "../classes/HandleAjax";
import {InterFuncSuccess, Server} from "../static/server";
import {ref, onMounted, computed, reactive} from "@vue/composition-api";
import {_Card, _Deck, _DeckGroup, _Study, _Tag} from "../interfaces/inter-sp";
import {Store} from "../static/store";
import Chart from "chart.js/auto";

declare var bootstrap;

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
  let statsForecast = ref<_ForecastGraphable>(null);
  let statsReview = ref<_ReviewGraphable>(null);
  let statsReviewTime = ref<_ReviewGraphable>(null);
  let statsChartAdded = ref<_ChartAddedGraphable>(null);
  let statsChartInterval = ref<_ChartIntervalGraphable>(null);
  let statsChartAnserButtons = ref<_ChartAnswerButtons>(null);
  let statsHourlyBreakdown = ref<_ChartAnswerButtons>(null);
  let forecastSpan = ref('one_month');
  let reviewCountSpan = ref('one_month');
  let reviewTimeSpan = ref('one_month');
  let chartAddedTimeSpan = ref('one_month');
  let chartIntervalTimeSpan = ref('one_month');
  let chartAnswerButtonsTimeSpan = ref('one_month');
  let chartHourlyBreakdownDate = ref('');
  const colorLearning = '#4848bf';
  const colorYoung = '#9cd89c';
  const colorMature = '#2f622e';
  const colorDack = '#484848';
  const colorGray = '#a5a5a5';

  const _initChartHourlyBreakdown = () => {
    setTimeout(() => {
      new Chart('sp-chart-chart-hourly-breakdown', {
        type: 'bar',
        data: {
          labels: ["13:30", "13:40", "13:50", "14:00", "14:10", "14:20", "14:30", "14:40", "14:50", "15:00", "15:10", "15:20"],
          // labels: statsForecast.value.heading,
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
              data: [5, 2, 7, 9, 35, 34, 12, 45, 21, 31, 34, 5],
              // data: statsForecast.value.y,
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
              data: [2, 0, 3, 7, 11, 13, 8, 44, 35, 3, 46, 1],
              // data: statsForecast.value.m,
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

  const _loadAllStats = () => {
    _loadForecast();
    _loadReviewCount();
    _loadReviewTime();
    _loadChartAdded();
    _loadChartIntervals();
    _loadChartAnswerButtons();
    _loadChartHourlyBreakDown();
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

  return {
    ajax, ajaxForecast, ajaxReviewTime, ajaxChartAdded, ajaxChartInterval,
    ajaxChartAnswerButtons, ajaxHourlyBreakdown,
    _loadAllStats, _loadForecast,
    forecastSpan, _reloadForecast, _reloadReviewCount, _reloadReviewTime, _reloadChartAnswerButtons,
    _loadChartAnswerButtons, _loadChartHourlyBreakDown,
    statsForecast, ajaxReview, statsReview, reviewCountSpan, reviewTimeSpan,
    statsReviewTime, chartAddedTimeSpan, chartIntervalTimeSpan, statsChartAnserButtons,
    statsHourlyBreakdown,
    _reloadChartAdded, _reloadChartInterval, statsChartAdded, chartAnswerButtonsTimeSpan,
    chartHourlyBreakdownDate,
  };

}