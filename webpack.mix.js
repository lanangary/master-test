const mix = require('laravel-mix');
const fs = require('fs');
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
const packageJson = JSON.parse(fs.readFileSync('./package.json'));
const path = require('path');
const PurgecssPlugin = require('purgecss-webpack-plugin');
const glob = require('glob-all');
const exec = require('child_process').exec;
require('dotenv').config();

// Custom PurgeCSS extractor for Tailwind that allows special characters in
// class names.
//
// https://github.com/FullHuman/purgecss#extractor
class TailwindExtractor {
    static extract(content) {
        return content.match(/[A-z0-9-:\/]+/g) || [];
    }
}

const webpackConfig = {
    plugins: [],
    // Use the below plugin to check bundle size.
    //plugins: [new BundleAnalyzerPlugin()],
    output: {
        jsonpFunction: 'jbWebpackJsonp'
    },
    module: {
        rules: [
            {
                enforce: 'pre',
                test: /\.js?$/,
                include: [
                    /\/www\/app\/themes\/.+\/js\/src\/.+/,
                    /\/www\/app\/themes\/.+\/src\/.+/
                ],
                use: [
                    {
                        loader: 'eslint-loader',
                        options: {
                            fix: true,
                            emitWarning: true,
                            format: 'pretty'
                        }
                    }
                ]
            },
            {
                test: /\.js?$/,
                use: [
                    {
                        loader: 'babel-loader',
                        options: mix.config.babel()
                    }
                ]
            }
        ]
    },
    resolve: {
        extensions: ['*', '.js', '.jsx', '.scss'],

        modules: ['node_modules'],

        alias: {
            JBSrc: path.resolve(__dirname, `www/app/themes/${packageJson.name}/js/src/`),
            JBModule: path.resolve(
                __dirname,
                `www/app/themes/${packageJson.name}/src/JuiceBox/Modules/`
            ),
            JbNodeModules: path.resolve(__dirname, 'node_modules'),
            JBComponent: path.resolve(
                __dirname,
                `www/app/themes/${packageJson.name}/src/JuiceBox/Components/`
            )
        }
    },
    stats: {
        hash: false,
        version: false,
        timings: true,
        children: false,
        errors: true
    },
    externals: {
        jquery: 'jQuery'
    }
};

const mixOptions = {
    processCssUrls: false
};

mix.extract()
    .setPublicPath(`./`)
    .js(
        `www/app/themes/${packageJson.name}/js/main.js`,
        `www/app/themes/${packageJson.name}/dist/js/bundle.js`
    )
    .js(
        `www/app/themes/${packageJson.name}/js/admin.js`,
        `www/app/themes/${packageJson.name}/dist/js/admin.js`
    )
    .js(
        `www/app/themes/${packageJson.name}/js/modernizr.js`,
        `www/app/themes/${packageJson.name}/dist/js/modernizr.js`
    )
    .sass(
        `www/app/themes/${packageJson.name}/scss/editor-style.scss`,
        `www/app/themes/${packageJson.name}/dist/css/editor-style.css`
    )
    .sass(
        `www/app/themes/${packageJson.name}/scss/admin.scss`,
        `www/app/themes/${packageJson.name}/dist/css/admin.css`
    )
    .sass(
        `www/app/themes/${packageJson.name}/scss/login.scss`,
        `www/app/themes/${packageJson.name}/dist/css/login.css`
    )
    .sass(
        `www/app/themes/${packageJson.name}/scss/main.scss`,
        `www/app/themes/${packageJson.name}/dist/css/main.css`
    );

if (mix.inProduction()) {
    mix.disableNotifications();

    const purgeCssPlugin = new PurgecssPlugin({
        // Specify the locations of any files you want to scan for class names.
        paths: glob.sync([
            path.join(__dirname, `www/app/themes/${packageJson.name}/**/*.js`),
            path.join(__dirname, `www/app/themes/${packageJson.name}/**/*.twig`),
            path.join(__dirname, `www/app/themes/${packageJson.name}/**/*.php`)
        ]),
        extractors: [
            {
                extractor: TailwindExtractor,

                // Specify the file extensions to include when scanning for
                // class names.
                extensions: ['twig', 'js', 'php']
            }
        ],
        whitelist: [
            'rtl',
            'home',
            'blog',
            'archive',
            'date',
            'error404',
            'logged-in',
            'admin-bar',
            'no-customize-support',
            'custom-background',
            'wp-custom-logo'
        ],
        whitelistPatterns: () => {
            return [
                /^search(-.*)?$/,
                /^(.*)-template(-.*)?$/,
                /^(.*)?-?single(-.*)?$/,
                /^postid-(.*)?$/,
                /^attachmentid-(.*)?$/,
                /^attachment(-.*)?$/,
                /^page(-.*)?$/,
                /^(post-type-)?archive(-.*)?$/,
                /^author(-.*)?$/,
                /^category(-.*)?$/,
                /^tag(-.*)?$/,
                /^tax-(.*)?$/,
                /^term-(.*)?$/,
                /^(.*)?-?paged(-.*)?$/,
                /^js-/,
                /^jb-scroll/,
                /^in-view/,
                /^nav/,
                /^admin-bar/,
                /^icon-/,
                /^login/,
                /^slideout/,
                /^wpml/,
                /^js-/,
                /^ais/,
                /^noUi/,
                /^menu-item/,
                /^page-header/,
                /^text/,
                /^tooltipster/,
                /^gform/,
                /^ginput/,
                /^gf/,
                /^fileupload/,
                /^screen-reader-text/,
                /^id-/,
                /^has-error/,
                /^validated/,
                /^gform_validation_error/,
                /^validation_error/,
                /^ginput_preview/,
                /^gform_delete/,
                /^wpadminbar/,
                /^wp-submit/,
                /^gfield_description/,
                /^help-block/
            ];
        }
    });

    //webpackConfig.plugins.push(purgeCssPlugin);
} else {
    if (process.env.ENABLE_SOURCEMAPS === 'yes') {
        mix.sourceMaps(true, 'source-map');
    }

    if (process.env.ENABLE_BUILD_NOTIFICATIONS === 'no') {
        mix.disableNotifications();
    }

    if (!process.env.BROWSERSYNC_URL) {
        console.warn('*****************');
        console.warn(
            'Please set BROWSERSYNC_URL in your .env file to your development URL to enable browsersync'
        );
        console.warn('*****************');
    } else {
        mix.browserSync({
            proxy: process.env.BROWSERSYNC_URL,
            files: [
                `www/app/themes/${packageJson.name}/dist/css/main.css`,
                `www/app/themes/${packageJson.name}/dist/js/bundle.js`,
                `www/app/themes/${packageJson.name}/img/**/*'`,
                `www/app/themes/${packageJson.name}/*.php`,
                `www/app/themes/${packageJson.name}/views/**/*.twig`,
                `www/app/themes/${packageJson.name}/src/JuiceBox/Components/**/*.twig`,
                `www/app/themes/${packageJson.name}/src/JuiceBox/Modules/**/*.twig`
            ]
        });
    }
}

mix.options(mixOptions);
mix.webpackConfig(webpackConfig);
mix.then(function() {
    exec(
        'echo \'{ "version": \'$(date +%s)\', "git_hash": "\'$(git rev-parse HEAD)\'", "git_date": "\'$(git --no-pager log -1 --format=%cd)\'"}\' > www/release.json',
        function(err, stdout, stderr) {
            if (err) {
                return console.log(err);
            }
            console.log('The release.json was saved!');
        }
    );
});

function includeNodeModulesExcept(modules) {
    var pathSep = path.sep;
    if (pathSep == '\\')
        // must be quoted for use in a regexp:
        pathSep = '\\\\';
    var moduleRegExps = modules.map(function(modName) {
        return new RegExp('node_modules' + pathSep + modName);
    });

    return function(modulePath) {
        if (/node_modules/.test(modulePath)) {
            for (var i = 0; i < moduleRegExps.length; i++)
                if (moduleRegExps[i].test(modulePath)) return true;
            return false;
        }
        return true;
    };
}
