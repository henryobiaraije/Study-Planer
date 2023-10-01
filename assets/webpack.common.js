/**
 import { getPluginData } from './getPluginDetails';
 * External Dependencies
 */
// const { getPluginData } = require("./getPluginDetails");
const fs = require("fs");
const path = require("path");

const parentFolder = path.basename(path.dirname(__dirname)); // First-level parent folder name
const twoLevelsUp = path.join(__dirname, ".."); // Go two levels up
const mainPluginFile = path.join(twoLevelsUp, `${parentFolder}.php`);
const pluginDetails = getPluginData(mainPluginFile);
const pluginVersion = pluginDetails.Version;
const theAppVersion = "-" + pluginVersion;
// console.log({ parentFolder, twoLevelsUp, mainPluginFile, pluginDetails });

/**
 * WordPress Dependencies
 */
const defaultConfig = require("@wordpress/scripts/config/webpack.config.js");
const {VueLoaderPlugin} = require("vue-loader");

// const globFiles = glob.sync('./src/blocks/block-test/*.ts*');
// console.log("Glob files", globFiles);
// console.log({JSON: JSON.stringify(defaultConfig)})
module.exports = {
    ...defaultConfig,
    entry: {
        // 'admin/admin-deck-groups': './src/admin/admin-deck-groups.ts',
        // 'admin/admin-tags': './src/admin/admin-tags.ts',
        // 'admin/admin-decks': './src/admin/admin-decks.ts',
        'admin/admin-topics': './src/admin/admin-topics.ts',
        // 'admin/admin-basic-card': './src/admin/admin-basic-card.ts',
        // 'admin/admin-gap-card': './src/admin/admin-gap-card.ts',
        // 'admin/admin-table-card': './src/admin/admin-table-card.ts',
        // 'admin/admin-image-card': './src/admin/admin-image-card.ts',
        // 'admin/admin-settings': './src/admin/admin-settings.ts',
        // 'admin/admin-all-cards': './src/admin/admin-all-cards.ts',
        // 'public/sc-user-dashboard': './src/shortcodes/sc-user-dashboard.ts',
    },
    output: {
        filename: "[name].js",
        path: path.resolve(__dirname, "js"),
        clean: true,
    },
    resolve: {
        extensions: [".ts", ".tsx", ".js", ".jsx", ".vue"],
        alias: {
            // "@": path.resolve(__dirname, "src"),
            "@": path.resolve(__dirname, "src"),
            vue: 'vue/dist/vue.js' // to prevent (you are running the run time version of vue)
        },
    },
    module: {
        rules: [
            {
                test: /\.tsx?$/,
                // use: 'ts-loader', // remove to be able to user options
                loader: 'ts-loader',
                options: {
                    onlyCompileBundledFiles: true,
                    appendTsSuffixTo: [/\.vue$/], // to make script lang=ts work,
                },
                exclude: /node_modules/,
            },
            {
                test: /\.vue$/,
                loader: 'vue-loader'
            },
        ],
    },
    plugins: [
        new VueLoaderPlugin(),
    ]
};

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
