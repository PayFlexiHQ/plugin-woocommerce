const mix = require('laravel-mix');
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

mix.scripts([
    'assets/js/pf-checkout-frontend.js'
], 'assets/js/pf-checkout-frontend.min.js');

mix.scripts([
    'assets/js/pf-checkout-admin.js'
], 'assets/js/pf-checkout-admin.min.js');


