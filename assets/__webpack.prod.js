const { merge } = require('webpack-merge');
const common = require('./__webpack.common.js');

module.exports = merge(common, {
    mode: 'production',
});