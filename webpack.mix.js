const mix = require('laravel-mix');
const path = require('path');

mix.js('resources/js/cp.js', 'dist/js/favicon-generator.js').vue({ version: 3 });

mix.webpackConfig({
    resolve: {
        alias: {
            'vue$': 'vue/dist/vue.esm-bundler.js'
        }
    }
});
