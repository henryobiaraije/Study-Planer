const path = require('path');
const {VueLoaderPlugin} = require('vue-loader');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const devMode = process.env.NODE_ENV !== "production";

const fs = require("fs");
// const path = require("path");

const parentFolder = path.basename(path.dirname(__dirname)); // First-level parent folder name
const parentParentFolder = path.basename(path.dirname(path.dirname(__dirname)));
const twoLevelsUp = path.join(__dirname, ".."); // Go two levels up
const threeLevelUp = path.join(__dirname, "..", "..");
const mainPluginFile = path.join(threeLevelUp, `${parentParentFolder}.php`);
// console.log({mainPluginFile, threeLevelUp, parentFolder,parentParentFolder})
const pluginDetails = getPluginData(mainPluginFile);
const pluginVersion = pluginDetails.Version;
const theAppVersion = "-" + pluginVersion;

module.exports = {
    entry: {
        ["admin/pages/admin-page-settings" + theAppVersion]: "/src/admin/pages/admin-page-settings",
        ['admin/admin-1' + theAppVersion]: './src/admin-1.ts',
        ['admin/admin-topics' + theAppVersion]: './src/admin/admin-topics.ts',
        ['admin/admin-deck-groups' + theAppVersion]: './src/admin/admin-deck-groups.ts',
        ['admin/admin-tags' + theAppVersion]: './src/admin/admin-tags.ts',
        ['admin/admin-decks' + theAppVersion]: './src/admin/admin-decks.ts',
        ['admin/admin-basic-card' + theAppVersion]: './src/admin/admin-basic-cards.ts',
        ['admin/admin-gap-card' + theAppVersion]: './src/admin/admin-gap-card.ts',
        ['admin/admin-table-card' + theAppVersion]: './src/admin/admin-table-card.ts',
        ['admin/admin-image-card' + theAppVersion]: './src/admin/admin-image-card.ts',
        ['admin/admin-collection' + theAppVersion]: './src/admin/admin-collection.ts',
        ['admin/admin-assign-topics' + theAppVersion]: './src/admin/admin-assign-topics.ts',
        // 'admin/admin-settings': './src/admin/admin-settings.ts',
        // 'admin/admin-all-cards': './src/admin/admin-all-cards.ts',
        ['shortcodes/sc-study-dashboard' + theAppVersion]: './src/shortcodes/sc-study-dashboard.ts',
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
                        // plugins: ['@babel/plugin-proposal-object-rest-spread']
                        plugins: ["@babel/plugin-transform-object-rest-spread"]
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


function getPluginData(pluginFilePath) {
    const defaultHeaders = {
        Name: "Plugin Name",
        PluginURI: "Plugin URI",
        Version: "Version",
        Description: "Description",
        Author: "Author",
        AuthorURI: "Author URI",
        TextDomain: "Text Domain",
        DomainPath: "Domain Path",
        Network: "Network",
        RequiresWP: "Requires at least",
        RequiresPHP: "Requires PHP",
        UpdateURI: "Update URI",
        _sitewide: "Site Wide Only", // Deprecated header
    };

    const pluginContent = fs.readFileSync(pluginFilePath, "utf8");
    console.log("pluginContent", pluginContent);
    const pluginLines = pluginContent.split("\n");

    const pluginData = {};

    pluginLines.forEach((line) => {
        const matches = line.match(/^[\s\*]*([\w]+)[\s\*]*:(.*)/);
        if (matches && matches.length >= 3) {
            const key = matches[1].trim();
            const value = matches[2].trim();
            if (defaultHeaders[key]) {
                pluginData[defaultHeaders[key]] = value;
            }
        }
    });

    // Additional processing or adjustments may be required here

    return pluginData;
}
