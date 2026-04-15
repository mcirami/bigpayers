const mix = require("laravel-mix");
require("core-js");
require("laravel-mix-polyfill");

const path = require("path");
/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */
module.exports = {
    resolve: {
        alias: {
            myApp: path.resolve(__dirname, "assets/js"),
        },
    },
    module: {
        rules: [
            {
                test: /\.ts$/,
                use: "ts-loader",
                exclude: /node_modules/,
            },
        ],
    },
};

mix.options({ processCssUrls: false })
    .js("./ctpupgrade/assets/js/app.js", "./ctpupgrade/js/built.js")
    .sass("./ctpupgrade/assets/sass/app.scss", "./ctpupgrade/css/app.css")
    .minify("./ctpupgrade/js/built.js", "./ctpupgrade/js/built.min.js")
    .minify("./ctpupgrade/css/app.css", "./ctpupgrade/css/app.min.css")
    .polyfill({
        enabled: true,
        useBuiltIns: "entry",
        targets: false,
        entryPoints: "stable",
        corejs: 3,
    })
    .extract(["jquery"])
    .sourceMaps();

/*.styles('assets/css/normalize.css', 'css/app.css')*/
/*mix.browserSync('wisdom.test');*/
