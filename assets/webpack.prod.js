const { merge } = require("webpack-merge");
const common = require("./webpack.common.js");
const UglifyJsPlugin = require("uglifyjs-webpack-plugin");

module.exports = merge(common, {
  // mode: 'production',
  optimization: {
    minimizer: [
      new UglifyJsPlugin({
        uglifyOptions: {
          output: {
            // comments: /^!|@(?:author|website)/,
            // comments: /^\/\*\*!/,
            // comments: /@license/i,
            comments: /^\**!|@preserve|@license|@cc_on/i,
          },
          compress: {
            drop_console: true,
          },
        },
      }),
    ],
  },
});
