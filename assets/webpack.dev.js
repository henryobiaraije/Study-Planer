const { merge } = require("webpack-merge");
const webpack = require("webpack");
const common = require("./webpack.common.js");

module.exports = merge(common, {
  // mode: 'development',
  // devtool: 'eval-source-map',
  // devServer: {
  //     contentBase: './dist',
  // },
  // plugins: [
  //     new webpack.SourceMapDevToolPlugin({})
  // ]
});
