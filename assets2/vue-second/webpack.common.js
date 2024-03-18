const path = require('path');
const {VueLoaderPlugin} = require('vue-loader');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const devMode = process.env.NODE_ENV !== "production";


module.exports = {
    entry: {
        'admin/admin-1': './src/admin-1.ts',
        'admin/admin-topics': './src/admin/admin-topics.ts',
        'admin/admin-deck-groups': './src/admin/admin-deck-groups.ts',
        'admin/admin-tags': './src/admin/admin-tags.ts',
        'admin/admin-decks': './src/admin/admin-decks.ts',
        'admin/admin-basic-card': './src/admin/admin-basic-cards.ts',
        'admin/admin-gap-card': './src/admin/admin-gap-card.ts',
        'admin/admin-table-card': './src/admin/admin-table-card.ts',
        'admin/admin-image-card': './src/admin/admin-image-card.ts',
        // 'admin/admin-settings': './src/admin/admin-settings.ts',
        // 'admin/admin-all-cards': './src/admin/admin-all-cards.ts',
        // 'public/sc-user-dashboard': './src/shortcodes/sc-user-dashboard.ts',
    },
    module: {
        rules: [
            {
                test: /\.tsx?$/,
                // use: 'ts-loader', // remove to be able to user options
                loader: 'ts-loader',
                options: {appendTsSuffixTo: [/\.vue$/]}, // to make script lang=ts work
                exclude: /node_modules/,
            },
            {
                test: /\.vue$/,
                loader: 'vue-loader'
            },
            {
                test: /\.css$/i,
                use: [
                    {
                        loader: MiniCssExtractPlugin.loader
                    },
                    // devMode ? "style-loader" : MiniCssExtractPlugin.loader,
                    // "style-loader",
                    "css-loader",
                    'postcss-loader'
                ],
            },
            {
                test: /\.s[ac]ss$/i,

                // use: [
                //     MiniCssExtractPlugin.loader,
                //     {
                //         loader: "css-loader",
                //         options: {
                //             modules: true,
                //             sourceMap: true,
                //             importLoaders: 2
                //         }
                //     },
                //     "sass-loader",
                //     'postcss-loader'
                // ],
                use: [
                    {
                        loader: MiniCssExtractPlugin.loader
                    },
                    // devMode ? "style-loader" : MiniCssExtractPlugin.loader,
                    // 'vue-style-loader',
                    // Creates `style` nodes from JS strings
                    // "style-loader",
                    // Translates CSS into CommonJS
                    "css-loader",
                    // Compiles Sass to CSS
                    "sass-loader",
                    'postcss-loader'
                ],

            },
            {
                test: /\.m?js$/,
                exclude: /(node_modules|bower_components)/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@babel/preset-env'],
                        plugins: ['@babel/plugin-proposal-object-rest-spread']
                    }
                }
            },
        ],
    },
    optimization: {
        minimizer: [
            // For webpack@5 you can use the `...` syntax to extend existing minimizers (i.e. `terser-webpack-plugin`), uncomment the next line
            // `...`,
            new CssMinimizerPlugin(),
        ],
    },
    plugins: [
        new VueLoaderPlugin(),
        new MiniCssExtractPlugin({
            // Options similar to the same options in webpackOptions.output
            // both options are optional
            filename: "[name].css",
            chunkFilename: "[id].css",
        }),
        new MiniCssExtractPlugin()
    ],
    resolve: {
        alias: {
            // vue: 'vue/dist/vue.js' // to prevent (you are running the run time version of vue)
            "@": path.resolve(__dirname, 'src'),
        },
        extensions: ['.tsx', '.ts', '.js', '.vue'],
        // modules: ['node_modules', path.resolve(__dirname, 'core')]
    },
    output: {
        filename: '[name].js',
        path: path.resolve(__dirname, 'js'),
        clean: true,
    },
}