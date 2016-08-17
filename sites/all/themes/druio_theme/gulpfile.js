var gulp = require('gulp');
var sass = require('gulp-sass');
var sourcemaps = require('gulp-sourcemaps');

gulp.task('sass', function () {
    gulp.src('./styles/scss/styles.scss')
      .pipe(sourcemaps.init())
      .pipe(sass())
      .pipe(sourcemaps.write('./maps'))
      .pipe(gulp.dest('./styles/css/'));
});

gulp.task('watch', function () {
    gulp.start('sass');
    gulp.watch('./styles/scss/**', function () {
        setTimeout(function () {
            gulp.start('sass');
        }, 200);
    });
});
