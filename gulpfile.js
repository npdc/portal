const gulp = require('gulp')
	, concat = require('gulp-concat')
	, sourcemaps = require('gulp-sourcemaps')
	, uglify = require('gulp-uglify')
	, rename = require('gulp-rename')
	, filter = require('gulp-filter')
	, sass = require('gulp-sass');

function minifyJS(file) {
	return gulp.src('private/npdc/javascript/' + file + '/*', {sourcemaps: true})
		.pipe(concat(file + '.js'))
		.pipe(uglify())
		.pipe(rename({ extname: '.min.js' }))
		.pipe(gulp.dest('web/js/npdc', {sourcemaps: '.'}));
}

function css2scss(){
	return gulp.src(['private/npdc/scss/*.scss', '!private/npdc/scss/[a-z]_*.scss'], {sourcemaps: true})
		.pipe(sass({outputStyle: 'compressed'}))
		.pipe(rename({extname: '.min.css'}))
		.pipe(gulp.dest('web/css/npdc', {sourcemaps: '.'}));
}

gulp.task('css', function(){
	return css2scss();
});

gulp.task('js-editor', function(){
	return minifyJS('editor');
});

gulp.task('js-npdc', function(){
	return minifyJS('npdc');
});

gulp.task('watch', function(){
	gulp.watch('private/npdc/javascript/editor/*.js', function(){
		return minifyJS('editor');
	});
	gulp.watch('private/npdc/javascript/main/*.js', function(){
		return minifyJS('npdc');
	});
	gulp.watch('private/npdc/scss/*.scss', function(){
		return css2scss();
	});
});