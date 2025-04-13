const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   .vue({ version: 3 });

mix.js('resources/js/component-app.js', 'public/js')
   .vue({ version: 3 });

mix.version();
