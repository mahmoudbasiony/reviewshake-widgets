"use strict";

/**
 * NPM packages.
 */
const gulp      = require( 'gulp' ),
      rename    = require( 'gulp-rename' ),
      babel     = require( 'gulp-babel'),
      uglify    = require( 'gulp-uglify' ),
      uglifycss = require( 'gulp-uglifycss' ),
      sass      = require( 'gulp-sass' )(require('sass')),
      del       = require( 'del' ),
      jshint    = require( 'gulp-jshint' ),
      wpPot     = require('gulp-wp-pot'),
      zip       = require('gulp-zip');

/**
 * Admin scripts.
 */
gulp.task( 'compileAdminScripts', () => {
    return gulp.src( ['assets/src/js/admin/*.js'], { allowEmpty: true } )
        .pipe( jshint() )
        .pipe( jshint.reporter( 'default' ) )
        .pipe( babel(
            { 'presets': ['@babel/preset-env'] }
        ) )
        .on( 'error', (err) => console.log(err) )
        .pipe( rename( { prefix: 'wpblc-broken-links-checker-' } ) )
        .pipe( gulp.dest( 'assets/dist/js/admin' ) );
} );

gulp.task( 'minifyAdminScripts', gulp.series('compileAdminScripts', () => {
    return gulp.src( [
        'assets/dist/js/admin/*.js',
        '!assets/dist/js/admin/*.min.js',
    ], { allowEmpty: true } )
        .pipe( uglify() )
        .pipe( rename( { suffix: '.min' } ) )
        .pipe( gulp.dest( 'assets/dist/js/admin' ) );
}));

/**
 * Frontend scripts.
 */
gulp.task( 'compileFrontendScripts', () => {
    return gulp.src( ['assets/src/js/public/*.js'], { allowEmpty: true } )
        .pipe( jshint() )
        .pipe( jshint.reporter( 'default' ) )
        .pipe( babel(
            { 'presets': ['@babel/preset-env'] }
        ) )
        .on( 'error', (err) => console.log(err) )
        .pipe( rename( { prefix: 'wpblc-broken-links-checker-' } ) )
        .pipe( gulp.dest( 'assets/dist/js/public' ) );
} );

gulp.task( 'minifyFrontendScripts', gulp.series('compileFrontendScripts', () => {
    return gulp.src( [
        'assets/dist/js/public/*.js',
        '!assets/dist/js/public/*.min.js',
    ], { allowEmpty: true } )
        .pipe( uglify() )
        .pipe( rename( { suffix: '.min' } ) )
        .pipe( gulp.dest( 'assets/dist/js/public' ) );
}));

/**
 * Admin styles.
 */
gulp.task( 'compileAdminSass', () => {
    return gulp.src( [
        'assets/src/scss/admin/main.scss'
    ], { allowEmpty: true } )
        .pipe( sass() )
        .on( 'error', (err) => console.log(err) )
        .pipe( rename( 'wpblc-broken-links-checker-admin-styles.css' ) )
        .pipe( gulp.dest( 'assets/dist/css/admin' ) );
} );

gulp.task( 'minifyAdminCSS', gulp.series('compileAdminSass', () => {
    return gulp.src( [
        'assets/dist/css/admin/wpblc-broken-links-checker-admin-styles.css'
    ], { allowEmpty: true } )
        .pipe( uglifycss( {
            uglyComments: true
        } ) )
        .pipe( rename( 'wpblc-broken-links-checker-admin-styles.min.css' ) )
        .pipe( gulp.dest( 'assets/dist/css/admin' ) );
}));

/**
 * Frontend styles.
 */
gulp.task( 'compileFrontendSass', () => {
    return gulp.src( [
        'assets/src/scss/public/main.scss'
    ], { allowEmpty: true } )
        .pipe( sass() )
        .on( 'error', (err) => console.log(err) )
        .pipe( rename( 'wpblc-broken-links-checker-styles.css' ) )
        .pipe( gulp.dest( 'assets/dist/css/public' ) );
} );

gulp.task( 'minifyFrontendCSS', gulp.series('compileFrontendSass', () => {
    return gulp.src( [
        'assets/dist/css/public/wpblc-broken-links-checker-styles.css'
    ], { allowEmpty: true } )
        .pipe( uglifycss( {
            uglyComments: true
        } ) )
        .pipe( rename( 'wpblc-broken-links-checker-styles.min.css' ) )
        .pipe( gulp.dest( 'assets/dist/css/public' ) );
}));

/**
 * Translation.
 */
gulp.task( 'makePOT', () => {
    return gulp.src(
        '**/*.php'
    )
    .pipe( wpPot(
        {
          domain: 'wpblc-broken-links-checker',
          package: 'WPBLC_Broken_Links_Checker'
        }
    ) )
    .pipe( gulp.dest( 'languages/wpblc-broken-links-checker.pot' ) );
} );

gulp.task( 'makePluginFile', () => {
    return gulp.src([
        '**/*',
        '!node_modules/',
        '!node_modules/**',
        '!vendor/',
        '!vendor/**',
        '!.git/**',
        '!assets/src/',
        '!assets/src/**',
        '!.gitignore',
        '!gulpfile.js',
        '!package.json',
        '!package-lock.json',
        '!npm-shrinkwrap.json',
        '!composer.json',
        '!composer.lock',
        '!phpcs.xml',
        '!README.md',
        '!.jshintrc',
        '!wpblc-broken-links-checker.zip'
    ])
    .pipe(zip('wpblc-broken-links-checker.zip'))
    .pipe(gulp.dest('.'))
} );


/**
 * Main tasks.
 */
gulp.task( 'clean', () => {
    del(
        [
            'assets/dist/css/admin/**/*.css',
            'assets/dist/css/public/**/*.css',
            'assets/dist/js/admin/**/*.js',
            'assets/dist/js/public/**/*.js',
            'languages/*.pot',
        ]
    );
} );

gulp.task( 'watch', () => {
    gulp.watch( 'assets/src/scss/admin/**/*.scss', gulp.series('minifyAdminCSS') );
    gulp.watch( 'assets/src/js/admin/**/*.js', gulp.series('minifyAdminScripts') );
    gulp.watch( 'assets/src/scss/public/**/*.scss', gulp.series('minifyFrontendCSS') );
    gulp.watch( 'assets/src/js/public/**/*.js', gulp.series('minifyFrontendScripts') );
} );

gulp.task( 'build', gulp.parallel('minifyAdminScripts', 'minifyAdminCSS', 'minifyFrontendScripts', 'minifyFrontendCSS', 'makePOT') );

gulp.task( 'buildPlugin', gulp.series('build', 'makePluginFile') );

gulp.task( 'default', gulp.series('build') );