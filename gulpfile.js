const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const livereload = require('gulp-livereload');
const rename = require('gulp-rename');
const minifyCSS = require('gulp-clean-css');
const terser = require('gulp-terser');
const postcss = require('gulp-postcss');
const autoprefixer = require('autoprefixer');
const replace = require('gulp-replace');
const concat = require('gulp-concat');
const shell = require('gulp-shell');

/**
 * SASS style
 */
function sassTask(cb) {
    return gulp.src('sass/style.scss')
        .pipe(sass({
            outputStyle: 'compressed',
            includePaths: ['sass'],
            silenceDeprecations: ['legacy-js-api']
        }).on('error', sass.logError))
        .pipe(postcss([autoprefixer()]))
        .pipe(gulp.dest('public/static'));
}

/**
 * minify JS
 */
function uglifyTask(cb) {
    return gulp.src(['js/*.js', '!js/*.min.js'])
        .pipe(terser())
        .on('error', printError)
        //.pipe( rename( {
        //    suffix: '.min'
        //} ) )
        .pipe(gulp.dest('public/static/js'))
        .pipe(livereload());
}

/**
 * watch
 */
function watchTask(cb) {
    livereload.listen();
    gulp.watch(['sass/**/*.scss'], sassTask)
    gulp.watch('js/*.js', uglifyTask);
}


/**
 * Copy fonts from node_modules to /public/static/assets
 */
/*
function copyFontsFontAwesome4Task(cb) {
    return gulp.src( './node_modules/font-awesome/fonts/**.*' )
        .pipe( gulp.dest( 'public/static/assets/fonts' ) );
}

function copyFontsFontAwesome5Task(cb) {
    return gulp.src( './node_modules/@fortawesome/fontawesome-free/webfonts/**.*' )
        .pipe( gulp.dest( 'public/static/assets/fonts/font-awesome' ) );
}

function replaceFontAwesome5Webfonts(cb) {
    return gulp
        .src( './node_modules/@fortawesome/fontawesome-free/css/all.css')
        .pipe( replace("../webfonts/", "../fonts/font-awesome/") )
        .pipe( minifyCSS() )
        .pipe( rename("font-awesome5.min.css") )
        .pipe( gulp.dest( 'public/static/assets/css' ) );
}

function copyFontAwesome5JS(cb) {
    return gulp
        .src( './node_modules/@fortawesome/fontawesome-free/js/all.min.js')
        .pipe( rename("font-awesome5.min.js") )
        .pipe( gulp.dest( 'public/static/assets/js' ) );
}
*/

function copyFontsFontAwesome5SVG(cb) {
    return gulp.src('./node_modules/@fortawesome/fontawesome-free/svgs/**/*.svg')
        .pipe(gulp.dest('public/static/assets/svgs/font-awesome'));
}

function copyJSTask(cb) {
    return gulp
        .src([
            './node_modules/flatpickr/dist/flatpickr.min.js',
            './node_modules/leaflet.locatecontrol/dist/L.Control.Locate.min.js',
            './node_modules/leaflet-curve/leaflet.curve.js',
            './node_modules/leaflet-extra-markers/dist/js/leaflet.extra-markers.min.js',
            './node_modules/leaflet-fullscreen/dist/Leaflet.fullscreen.min.js',
            './node_modules/leaflet/dist/leaflet.js',
            './node_modules/leaflet.markercluster/dist/leaflet.markercluster.js',
            './node_modules/moment/min/moment-with-locales.min.js',
            './node_modules/nouislider/dist/nouislider.min.js',
            './node_modules/mobius1-selectr/dist/selectr.min.js',
            './node_modules/easymde/dist/easymde.min.js',
            './node_modules/sortablejs/Sortable.min.js',
            './node_modules/leaflet-routing-machine/dist/leaflet-routing-machine.min.js',
            './node_modules/@tarekraafat/autocomplete.js/dist/autoComplete.min.js',
            './node_modules/html-duration-picker/dist/html-duration-picker.min.js',
            './node_modules/chartjs-adapter-moment/dist/chartjs-adapter-moment.min.js',
            './node_modules/chartjs-plugin-annotation/dist/chartjs-plugin-annotation.min.js',
            './node_modules/chartjs-plugin-zoom/dist/chartjs-plugin-zoom.min.js',
            './node_modules/hammerjs/hammer.min.js',
            './node_modules/file-saver/dist/FileSaver.min.js',
            './node_modules/write-excel-file/bundle/write-excel-file.min.js',
            './node_modules/@jstable/jstable/dist/jstable.min.js'
        ])
        // remove source maps
        .pipe(replace(/\/\/# sourceMappingURL=(.?)*\.js\.map/g, ""))
        .pipe(gulp.dest('public/static/assets/js'));
}

function copyAndMinifyJS(cb) {
    return gulp
        .src([
            './node_modules/randomcolor/randomColor.js',
            './node_modules/leaflet-control-geocoder/dist/Control.Geocoder.js',
        ])
        .pipe(terser())
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(gulp.dest('public/static/assets/js'));
}

function renameJS(cb) {
    return gulp
        .src([
            './node_modules/leaflet-easyprint/dist/bundle.js'
        ])
        // remove source map
        .pipe(replace(/\/\/# sourceMappingURL=(.?)*\.js\.map/g, ""))
        .pipe(rename("leaflet-easyPrint.min.js"))
        .pipe(gulp.dest('public/static/assets/js'));
}

function renameJS2(cb) {
    return gulp
        .src([
            './node_modules/chart.js/dist/chart.umd.js',
        ])
        // remove source map
        .pipe(replace(/\/\/# sourceMappingURL=(.?)*\.js\.map/g, ""))
        .pipe(rename("chart.min.js"))
        .pipe(gulp.dest('public/static/assets/js'));
}

function renameJS3(cb) {
    return gulp
        .src([
            './node_modules/@fullcalendar/core/index.global.min.js',
            './node_modules/@fullcalendar/interaction/index.global.min.js',
            './node_modules/@fullcalendar/list/index.global.min.js',
            './node_modules/@fullcalendar/daygrid/index.global.min.js',
            './node_modules/@fullcalendar/timegrid/index.global.min.js',
            './node_modules/@fullcalendar/multimonth/index.global.min.js',
            './node_modules/@fullcalendar/core/locales-all.global.min.js',
        ])
        .pipe(concat("fullcalendar.min.js"))
        .pipe(gulp.dest('public/static/assets/js'));
}

function copyFlatpickrI10n(cb) {
    return gulp
        .src([
            './node_modules/flatpickr/dist/l10n/de.js'
        ])
        .pipe(gulp.dest('public/static/assets/js/i18n'));
}

function copyFlatpickrI10nEN(cb) {
    return gulp
        .src('./node_modules/flatpickr/dist/l10n/default.js')
        .pipe(rename("en.js"))
        .pipe(gulp.dest('public/static/assets/js/i18n'));
}

function copyCSSTask(cb) {
    return gulp
        .src([
            //'./node_modules/font-awesome/css/font-awesome.min.css',
            './node_modules/flatpickr/dist/flatpickr.min.css',
            './node_modules/leaflet.locatecontrol/dist/L.Control.Locate.min.css',
            './node_modules/leaflet.markercluster/dist/MarkerCluster.css',
            './node_modules/nouislider/dist/nouislider.min.css',
            './node_modules/mobius1-selectr/dist/selectr.min.css',
            './node_modules/easymde/dist/easymde.min.css',
            './node_modules/@jstable/jstable/dist/jstable.css'
        ])
        // remove source map
        .pipe(replace(/\/\*# sourceMappingURL=(.?)*\.css\.map \*\//g, ""))
        .pipe(gulp.dest('public/static/assets/css'));
}

function copyAndMinifyCSS(cb) {
    return gulp
        .src([
            './node_modules/normalize.css/normalize.css',
            './node_modules/leaflet.locatecontrol/dist/L.Control.Locate.css',
        ])
        .pipe(minifyCSS())
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(gulp.dest('public/static/assets/css'));
}

function replaceLeafletFullscreenIcon(cb) {
    return gulp
        .src([
            './node_modules/leaflet-fullscreen/dist/leaflet.fullscreen.css',
        ])
        .pipe(replace("fullscreen.png", "../images/leaflet-fullscreen/fullscreen.png"))
        .pipe(replace("fullscreen@2x.png", "../images/leaflet-fullscreen/fullscreen@2x.png"))
        .pipe(minifyCSS())
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(gulp.dest('public/static/assets/css'));
}

function copyLeafletFullscreenIcons(cb) {
    return gulp.src('./node_modules/leaflet-fullscreen/dist/*.png', { encoding: false })
        .pipe(gulp.dest('public/static/assets/images/leaflet-fullscreen'));
}

function copyLeafletExtraMarkersIcons(cb) {
    return gulp.src('./node_modules/leaflet-extra-markers/dist/img/*.png', { encoding: false })
        .pipe(gulp.dest('public/static/assets/images/leaflet-extra-markers'));
}

function replaceLeafletExtraMarkersIconCSS(cb) {
    return gulp
        .src([
            './node_modules/leaflet-extra-markers/dist/css/leaflet.extra-markers.min.css',
        ])
        .pipe(replace("../img/markers_default.png", "../images/leaflet-extra-markers/markers_default.png"))
        .pipe(replace("../img/markers_default@2x.png", "../images/leaflet-extra-markers/markers_default@2x.png"))
        .pipe(replace("../img/markers_shadow.png", "../images/leaflet-extra-markers/markers_shadow.png"))
        .pipe(replace("../img/markers_shadow@2x.png", "../images/leaflet-extra-markers/markers_shadow@2x.png"))
        .pipe(gulp.dest('public/static/assets/css'));
}

function copyLeafletIcons(cb) {
    return gulp.src('./node_modules/leaflet/dist/images/*.png', { encoding: false })
        .pipe(gulp.dest('public/static/assets/images/leaflet'));
}

function replaceLeafletIconCSS(cb) {
    return gulp
        .src([
            './node_modules/leaflet/dist/leaflet.css',
        ])
        .pipe(replace("images/layers.png", "../images/leaflet/layers.png"))
        .pipe(replace("images/layers-2x.png", "../images/leaflet/layers-2x.png"))
        .pipe(replace("images/marker-icon.png", "../images/leaflet/marker-icon.png"))
        .pipe(minifyCSS())
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(gulp.dest('public/static/assets/css'));
}


function copyLeafletRoutingIcons(cb) {
    return gulp.src('./node_modules/leaflet-routing-machine/dist/*.png', { encoding: false })
        .pipe(gulp.dest('public/static/assets/images/leaflet-routing-machine'));
}

function replaceLeafletRoutingIconCSS(cb) {
    return gulp
        .src([
            './node_modules/leaflet-routing-machine/dist/leaflet-routing-machine.css',
        ])
        .pipe(replace("leaflet.routing.icons.png", "../images/leaflet-routing-machine/leaflet.routing.icons.png"))
        .pipe(replace("routing-icon.png", "../images/leaflet-routing-machine/routing-icon.png"))
        .pipe(minifyCSS())
        .pipe(rename({
            suffix: '.min'
        }))
        .pipe(gulp.dest('public/static/assets/css'));
}

function copyFontsWeatherIconsTask(cb) {
    return gulp.src('./node_modules/weather-icons/font/**.*', { encoding: false })
        .pipe(gulp.dest('public/static/assets/fonts/weather-icons'));
}

function replaceFontWeatherIcons(cb) {
    return gulp
        .src('./node_modules/weather-icons/css/weather-icons.css')
        .pipe(replace("../font/", "../fonts/weather-icons/"))
        .pipe(minifyCSS())
        .pipe(rename("weather-icons.min.css"))
        .pipe(gulp.dest('public/static/assets/css'));
}



function copyAutocompleteIcon(cb) {
    return gulp.src('./node_modules/@tarekraafat/autocomplete.js/dist/css/images/search.svg',)
        .pipe(gulp.dest('public/static/assets/images/autocomplete'));
}

function replaceAutocompleteIcons(cb) {
    return gulp
        .src('./node_modules/@tarekraafat/autocomplete.js/dist/css/autoComplete.css')
        .pipe(replace("./images/", "../images/autocomplete/"))
        .pipe(minifyCSS())
        .pipe(rename("autoComplete.min.css"))
        .pipe(gulp.dest('public/static/assets/css'));
}

function copyDOCXJS(cb) {
    return gulp
        .src([
            './node_modules/docx/dist/index.umd.cjs'
        ])
        // remove source map
        //.pipe(replace(/\/\/# sourceMappingURL=(.?)*\.js\.map/g, ""))
        .pipe(rename("docxjs.min.js"))
        .pipe(gulp.dest('public/static/assets/js'));
}

function browserifyBIP39() {
    return shell.task([
        `browserify -r bip39 -s bip39 \
         --exclude=./wordlists/japanese.json \
         --exclude=./wordlists/spanish.json \
         --exclude=./wordlists/italian.json \
         --exclude=./wordlists/french.json \
         --exclude=./wordlists/korean.json \
         --exclude=./wordlists/czech.json \
         --exclude=./wordlists/portuguese.json \
         --exclude=./wordlists/chinese_traditional.json \
         --exclude=./wordlists/chinese_simplified.json \
         > ./public/static/assets/js/bip39.browser.js`
    ])();
}

function printError(error) {
    console.log('---- Error ----');
    console.log("message", error.cause.message);
    console.log("file", error.cause.filename);
    console.log("line", error.cause.line);
    console.log("col", error.cause.col);
    console.log("pos", error.cause.pos);
    console.log("");

    // this will ensure that gulp will stop processing the pipeline without a crash
    this.emit('end');
}

exports.sass = sassTask;
exports.uglify = uglifyTask;
exports.default = watchTask;
exports.copy = gulp.series(copyJSTask, copyAndMinifyJS, renameJS, renameJS2, renameJS3, copyFlatpickrI10n, copyFlatpickrI10nEN, copyCSSTask, copyAndMinifyCSS, replaceLeafletFullscreenIcon, copyLeafletFullscreenIcons, copyLeafletExtraMarkersIcons, copyLeafletIcons, replaceLeafletIconCSS, replaceLeafletExtraMarkersIconCSS, copyLeafletRoutingIcons, replaceLeafletRoutingIconCSS, copyFontsWeatherIconsTask, replaceAutocompleteIcons, replaceFontWeatherIcons, copyAutocompleteIcon, copyFontsFontAwesome5SVG, copyDOCXJS, browserifyBIP39);

exports.weather = gulp.series(copyFontsWeatherIconsTask, replaceFontWeatherIcons);
exports.test = browserifyBIP39;