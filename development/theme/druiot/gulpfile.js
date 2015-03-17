var gulp = require('gulp');
var sass = require('gulp-sass');
var livereload = require('gulp-livereload');


gulp.task('sass', function () {
    gulp.src('./styles/scss/styles.scss')
        .pipe(sass({
            errLogToConsole: true,
            sourceComments : 'normal'
        }))
        .pipe(gulp.dest('./styles/css/'))
	.pipe(livereload());
});


gulp.task('watch', function () {
	livereload.listen();   
	gulp.start('sass');
	gulp.watch('./styles/scss/**', function() {
    	setTimeout(function () {
        	gulp.start('sass');
    }, 200);
    });
});
