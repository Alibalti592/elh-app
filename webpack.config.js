const Encore = require('@symfony/webpack-encore');
const BrowserSyncPlugin = require('browser-sync-webpack-plugin');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

let path = require('path');
Encore .addAliases({
      '@': path.resolve(__dirname, 'assets', 'js', 'vue'),
      styles: path.resolve(__dirname, 'assets', 'sass'),
      '@fontPath': path.resolve(__dirname, './public/fonts'),
      '@imgPath': path.resolve(__dirname, './public/images'),
    })

    // directory where compiled assets will be stored
    .setOutputPath(Encore.isProduction() ? 'public/build/' : 'public/build-dev/')
      // public path used by the web server to access the output path
    .setPublicPath(Encore.isProduction() ? '/build' : '/build-dev')

  //JS
    .addEntry('main-app', './assets/js/app')


    // SASS
    .addStyleEntry('css/admin', './assets/sass/admin/admin.scss')
    .addStyleEntry('css/site', './assets/sass/site/site.scss')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    .enableVueLoader()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // configure Babel
    // .configureBabel((config) => {
    //     config.plugins.push('@babel/a-babel-plugin');
    // })

    // enables and configure @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.23';
    })

    .enablePostCssLoader()

    // enables Sass/SCSS support
    .enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()
    // .configureDevServerOptions(options => {
    //   options.host = 'elh.loc';
    //   options.allowedHosts = 'all';
    //   options.hot = true;
    //   options.open = true;
    //   options.port = 3000;
    //   options.server = 'http';
    //   options.headers = {
    //     "Access-Control-Allow-Origin": "*",
    //     "X-Content-Type-Options": "nosniff"
    //   };
    // })
  .addPlugin(new BrowserSyncPlugin(
    {
      host: 'elh.loc',
      proxy: 'elh.loc',
      open: 'external',
      cors: true,
      files: [ // watch on changes
        {
          match: ['assets/js/**/*.vue', 'assets/js/**/*.js'],
          fn: function (event, file) {
            if (event === 'change') {
              const bs = require('browser-sync').get('bs-webpack-plugin');
              bs.reload();
            }
          }
        },
        {
          match: ['public/**/*.css'],
          fn: function (event, file) {
            if (event === 'change') {
              const bs = require('browser-sync').get('bs-webpack-plugin');
              // bs.stream(); //marche pas ...
              bs.reload();
            }
          }
        },
      ],
      injectChanges: true,
      injectCss: true,
      notify: false
    },
    {
      // prevent BrowserSync from reloading the page
      // and let Webpack Dev Server take care of this
      reload: false
    }))

;

module.exports = Encore.getWebpackConfig();
