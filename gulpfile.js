const gulp = require( 'gulp' );
const sass = require( 'gulp-sass' );
const livereload = require( 'gulp-livereload' );
const rename = require( 'gulp-rename' );
const less = require( 'gulp-less' );
const minifyCSS = require( 'gulp-clean-css' );
const terser = require( 'gulp-terser' );
const autoprefixer = require( 'gulp-autoprefixer' );
const bourbon = require( 'bourbon' ).includePaths;
const neat = require( 'bourbon-neat' ).includePaths;
const replace = require('gulp-replace');


/**
 * SASS style
 */
function sassTask(cb) {
    return gulp.src( 'sass/style.scss' )
        .pipe( sass( { outputStyle: 'compressed', includePaths: [ 'sass' ].concat( [ bourbon, neat ] ) } )
         .on( 'error', printError ) )
        .pipe( autoprefixer() )
        .pipe( gulp.dest( 'public/static' ) )
        .pipe( livereload() );
}

/**
 * minify JS
 */
function uglifyTask(cb) {
    return gulp.src( [ 'js/*.js', '!js/*.min.js' ] )
        .pipe( terser() )
        .on( 'error', printError )
        //.pipe( rename( {
        //    suffix: '.min'
        //} ) )
        .pipe( gulp.dest( 'public/static/js' ) )
        .pipe( livereload() );
}

/**
 * watch
 */
function watchTask(cb) {
    livereload.listen();
    gulp.watch( [ 'sass/**/*.scss' ], sassTask )
    gulp.watch( 'js/*.js', uglifyTask );
}


/**
 * Copy fonts from node_modules to /public/static/assets
 */
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

function copyJSTask(cb) {
    return gulp
        .src( [ 
            './node_modules/flatpickr/dist/flatpickr.min.js',
            './node_modules/chart.js/dist/Chart.min.js',
            './node_modules/leaflet.locatecontrol/dist/L.Control.Locate.min.js',
            './node_modules/leaflet-curve/leaflet.curve.js',
            './node_modules/leaflet-extra-markers/dist/js/leaflet.extra-markers.min.js',
            './node_modules/leaflet-fullscreen/dist/Leaflet.fullscreen.min.js',
            './node_modules/leaflet/dist/leaflet.js',
            './node_modules/leaflet.markercluster/dist/leaflet.markercluster.js',
            './node_modules/moment/min/moment-with-locales.min.js',
            './node_modules/mustache/mustache.min.js',
            './node_modules/nouislider/distribute/nouislider.min.js',
            './node_modules/mobius1-selectr/dist/selectr.min.js',
            './node_modules/simplemde/dist/simplemde.min.js',
            './node_modules/sortablejs/Sortable.min.js',
            './node_modules/leaflet-routing-machine/dist/leaflet-routing-machine.min.js',
            './node_modules/leaflet-control-geocoder/dist/Control.Geocoder.min.js',
            './node_modules/@tarekraafat/autocomplete.js/dist/js/autoComplete.min.js',
            ] )
        // remove source maps
        .pipe(replace(/\/\/# sourceMappingURL=(.?)*\.js\.map/g, ""))
        .pipe( gulp.dest( 'public/static/assets/js' ) );
}

function copyAndMinifyJS(cb) {
    return gulp
        .src( [ 
            './node_modules/randomcolor/randomColor.js'
            ] )
        .pipe( terser() )
        .pipe( rename( {
            suffix: '.min'
        } ) )
        .pipe( gulp.dest( 'public/static/assets/js' ) );
}

function renameJS(cb) {
    return gulp
        .src( [ 
            './node_modules/leaflet-easyprint/dist/bundle.js'
            ] )
         // remove source map
        .pipe(replace(/\/\/# sourceMappingURL=(.?)*\.js\.map/g, ""))
        .pipe( rename("leaflet-easyPrint.min.js") )
        .pipe( gulp.dest( 'public/static/assets/js' ) );
}

function copyFlatpickrI10n(cb) {
    return gulp
        .src( [ 
            './node_modules/flatpickr/dist/l10n/de.js'
            ] )
        .pipe( gulp.dest( 'public/static/assets/js/i18n' ) );
}

function copyFlatpickrI10nEN(cb) {
    return gulp
        .src('./node_modules/flatpickr/dist/l10n/default.js')
        .pipe( rename("en.js") )
        .pipe( gulp.dest( 'public/static/assets/js/i18n' ) );
}

function copyCSSTask(cb) {
    return gulp
        .src( [ 
            //'./node_modules/font-awesome/css/font-awesome.min.css',
            './node_modules/flatpickr/dist/flatpickr.min.css',
            './node_modules/leaflet.locatecontrol/dist/L.Control.Locate.min.css',
            './node_modules/leaflet.markercluster/dist/MarkerCluster.css',
            './node_modules/nouislider/distribute/nouislider.min.css',
            './node_modules/mobius1-selectr/dist/selectr.min.css',
            './node_modules/simplemde/dist/simplemde.min.css',
            ] )
        // remove source map
        .pipe(replace(/\/\*# sourceMappingURL=(.?)*\.css\.map \*\//g, ""))
        .pipe( gulp.dest( 'public/static/assets/css' ) );
}

function copyAndMinifyCSS(cb) {
    return gulp
        .src( [ 
            './node_modules/normalize.css/normalize.css',
            ] )
        .pipe( minifyCSS() )
        .pipe( rename( {
            suffix: '.min'
        } ) )
        .pipe( gulp.dest( 'public/static/assets/css' ) );
}

function replaceLeafletFullscreenIcon(cb) {
    return gulp
        .src( [ 
            './node_modules/leaflet-fullscreen/dist/leaflet.fullscreen.css',
            ] )
        .pipe( replace("fullscreen.png", "../images/leaflet-fullscreen/fullscreen.png") )
        .pipe( replace("fullscreen@2x.png", "../images/leaflet-fullscreen/fullscreen@2x.png") )
        .pipe( minifyCSS() )
        .pipe( rename( {
            suffix: '.min'
        } ) )
        .pipe( gulp.dest( 'public/static/assets/css' ) );
}

function copyLeafletFullscreenIcons(cb) {
    return gulp.src( './node_modules/leaflet-fullscreen/dist/*.png', )
        .pipe( gulp.dest( 'public/static/assets/images/leaflet-fullscreen' ) );
}

function copyLeafletExtraMarkersIcons(cb) {
    return gulp.src( './node_modules/leaflet-extra-markers/dist/img/*.png', )
        .pipe( gulp.dest( 'public/static/assets/images/leaflet-extra-markers' ) );
}

function replaceLeafletExtraMarkersIconCSS(cb) {
    return gulp
        .src( [ 
            './node_modules/leaflet-extra-markers/dist/css/leaflet.extra-markers.min.css',
            ] )
        .pipe( replace("../img/markers_default.png", "../images/leaflet-extra-markers/markers_default.png") )
        .pipe( replace("../img/markers_default@2x.png", "../images/leaflet-extra-markers/markers_default@2x.png") )
        .pipe( replace("../img/markers_shadow.png", "../images/leaflet-extra-markers/markers_shadow.png") )
        .pipe( replace("../img/markers_shadow@2x.png", "../images/leaflet-extra-markers/markers_shadow@2x.png") )
        .pipe( gulp.dest( 'public/static/assets/css' ) );
}

function copyLeafletIcons(cb) {
    return gulp.src( './node_modules/leaflet/dist/images/*.png', )
        .pipe( gulp.dest( 'public/static/assets/images/leaflet' ) );
}

function replaceLeafletIconCSS(cb) {
    return gulp
        .src( [ 
            './node_modules/leaflet/dist/leaflet.css',
            ] )
        .pipe( replace("images/layers.png", "../images/leaflet/layers.png") )
        .pipe( replace("images/layers-2x.png", "../images/leaflet/layers-2x.png") )
        .pipe( replace("images/marker-icon.png", "../images/leaflet/marker-icon.png") )
        .pipe( minifyCSS() )
        .pipe( rename( {
            suffix: '.min'
        } ) )
        .pipe( gulp.dest( 'public/static/assets/css' ) );
}


function copyLeafletRoutingIcons(cb) {
    return gulp.src( './node_modules/leaflet-routing-machine/dist/*.png', )
        .pipe( gulp.dest( 'public/static/assets/images/leaflet-routing-machine' ) );
}

function replaceLeafletRoutingIconCSS(cb) {
    return gulp
        .src( [ 
            './node_modules/leaflet-routing-machine/dist/leaflet-routing-machine.css',
            ] )
        .pipe( replace("leaflet.routing.icons.png", "../images/leaflet-routing-machine/leaflet.routing.icons.png") )
        .pipe( replace("routing-icon.png", "../images/leaflet-routing-machine/routing-icon.png") )
        .pipe( minifyCSS() )
        .pipe( rename( {
            suffix: '.min'
        } ) )
        .pipe( gulp.dest( 'public/static/assets/css' ) );
}

function copyFontsWeatherIconsTask(cb) {
    return gulp.src( './node_modules/weather-icons/font/**.*' )
        .pipe( gulp.dest( 'public/static/assets/fonts/weather-icons' ) );
}

function replaceFontWeatherIcons(cb){
    return gulp
        .src( './node_modules/weather-icons/css/weather-icons.css')
        .pipe( replace("../font/", "../fonts/weather-icons/") )
        .pipe( minifyCSS() )
        .pipe( rename("weather-icons.min.css") )
        .pipe( gulp.dest( 'public/static/assets/css' ) );
}



function copyAutocompleteIcon(cb) {
    return gulp.src( './node_modules/@tarekraafat/autocomplete.js/dist/css/images/search.svg', )
        .pipe( gulp.dest( 'public/static/assets/images/autocomplete' ) );
}

function replaceAutocompleteIcons(cb){
    return gulp
        .src( './node_modules/@tarekraafat/autocomplete.js/dist/css/autoComplete.css')
        .pipe( replace("./images/", "../images/autocomplete/") )
        .pipe( minifyCSS() )
        .pipe( rename("autoComplete.min.css") )
        .pipe( gulp.dest( 'public/static/assets/css' ) );
}

function printError( error ) {
    console.log( '---- Error ----' );
    console.log( "message", error.cause.message );
    console.log( "file", error.cause.filename );
    console.log( "line", error.cause.line );
    console.log( "col", error.cause.col );
    console.log( "pos", error.cause.pos );
    console.log( "" );

    // this will ensure that gulp will stop processing the pipeline without a crash
    this.emit( 'end' );
}

exports.sass = sassTask;
exports.uglify = uglifyTask;
exports.default = watchTask;
exports.copy = gulp.series(copyFontsFontAwesome5Task, copyJSTask, copyAndMinifyJS, renameJS, copyFlatpickrI10n, copyFlatpickrI10nEN, copyCSSTask, copyAndMinifyCSS, replaceLeafletFullscreenIcon, copyLeafletFullscreenIcons, copyLeafletExtraMarkersIcons, copyLeafletIcons, replaceLeafletIconCSS, replaceLeafletExtraMarkersIconCSS, copyLeafletRoutingIcons, replaceLeafletRoutingIconCSS, copyFontsWeatherIconsTask, replaceAutocompleteIcons, copyAutocompleteIcon);

exports.test = replaceFontAwesome5Webfonts;
exports.weather = gulp.series(copyFontsWeatherIconsTask, replaceFontWeatherIcons);