const mix = require("laravel-mix");

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix
  .ts("resources/ts/app.tsx", "public/js")
  .react()
  .sass("resources/sass/app.scss", "public/css")
  .sourceMaps(true, "inline-source-map")
  .version();

mix.browserSync({
  proxy: {
    target: "laravel.test",
  },
  files: ["resources/views/**/*.blade.php", "public/js/**/*.js"],
});
