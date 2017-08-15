/**
 * @file
 * Gulp pipe.
 */

'use strict';
const gulp = require('gulp');
const sass = require('gulp-sass');
const watch = require('gulp-watch');
const plumber = require('gulp-plumber');
const sourcemaps = require('gulp-sourcemaps');

gulp.task('hello', function (callback) {
  console.log('hello');
  callback();
});

gulp.task('bootstrap', function () {
    return gulp.src('./bootstrap4/alpha6-scss/bootstrap.scss')
      .pipe(plumber())
      .pipe(sourcemaps.init())
      .pipe(sass())
      .pipe(sass({outputStyle: 'compressed'}))
      .pipe(sourcemaps.write('./maps'))
      .pipe(gulp.dest('./bootstrap4/alpha6/css'));
});

gulp.task('sass', function () {
    return gulp.src('./scss/**', { nodir: true/*, since: gulp.lastRun('sass')*/ })
      .pipe(plumber())
      .pipe(sourcemaps.init())
      .pipe(sass())
      .pipe(sourcemaps.write('./_maps'))
      .pipe(gulp.dest('./css/'));
});

gulp.task('watch', function () {
    gulp.series('sass');
    gulp.watch('./scss/**', gulp.series('sass'));
});

gulp.task('default', gulp.parallel('bootstrap', 'watch'));
