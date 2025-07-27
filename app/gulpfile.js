// Include gulp
var gulp = require('gulp');
// Include plugins
var concat = require('gulp-concat');
// Concatenate JS Files
gulp.task('build', function() {
    gulp.src('routes/*.php')
    .pipe(concat('main.php', {newLine: '?>'}))
    .pipe(gulp.dest('.'));
});

// Default task

gulp.task('default', ['build']);
