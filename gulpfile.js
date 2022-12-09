var gulp = require('gulp'),
  watch = require('gulp-watch'),
  concat = require('gulp-concat'),
  sass = require('gulp-sass'),
  autoprefixer = require('gulp-autoprefixer'),
  livereload = require('gulp-livereload'),
  sourcemaps = require('gulp-sourcemaps'),
  minify = require('gulp-minify');

//css
gulp.task('css', function() {
  return gulp.src(['stage/css/**/*.css', 'stage/css/**/*.scss'])
    .pipe(sourcemaps.init())
    .pipe(sass({ outputStyle: 'compressed' }).on('error', sass.logError))
    .pipe(autoprefixer())
    .pipe(concat('main.css'))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('dist/assets/css'))
    .pipe(livereload())
});

//js
gulp.task('js', function() {
  return gulp.src('stage/js/*.js')
    .pipe(minify())
    .pipe(gulp.dest('dist/assets/js'))
    .pipe(livereload())
});

// watch 
gulp.task('watch', function() {
  require('./server.js');
  livereload.listen();
  gulp.watch(['stage/css/**/*.css', 'stage/css/**/*.scss'], gulp.series(['css']));
  gulp.watch('stage/js/*.js', gulp.series(['js']));
});