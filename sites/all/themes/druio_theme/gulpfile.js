var gulp = require('gulp');
var sass = require('gulp-sass');

gulp.task('sass', function () {
    gulp.src('./styles/scss/styles.scss')
        .pipe(sass({
            errLogToConsole: true,
            sourceComments: 'normal'
        }))
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
