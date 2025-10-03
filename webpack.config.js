const Encore = require('@symfony/webpack-encore');

Encore
    // the project directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // the public path used by the web server to access the previous directory
    .setPublicPath('/build')
    // main entry for JS (and CSS imported in it)
    .addEntry('app', './assets/app.js')
    // enable Sass/SCSS if needed
    //.enableSassLoader()
    // enable source maps during dev
    .enableSourceMaps(!Encore.isProduction())
    // enable versioning (hashed filenames) in production
    .enableVersioning(Encore.isProduction())
    // clean output before build
    .cleanupOutputBeforeBuild()
    // enable PostCSS (for Tailwind, autoprefixer)
    .enablePostCssLoader()
;

module.exports = Encore.getWebpackConfig();
