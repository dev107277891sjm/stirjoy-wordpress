
const mix = require('laravel-mix');

var LiveReloadWebpackPlugin = require('@kooneko/livereload-webpack-plugin');

mix.webpackConfig({
    plugins: [new LiveReloadWebpackPlugin()]
});

mix.sass('./assests/scss/app.scss', './assests/css/base.css');
