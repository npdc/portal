const gulp = require('gulp')
	, concat = require('gulp-concat')
	, sourcemaps = require('gulp-sourcemaps')
	, uglify = require('gulp-uglify')
	, rename = require('gulp-rename')
	, filter = require('gulp-filter');

function minifyJS(sourceFiles, destinationFolder, filenameRoot) {
	return gulp.src(sourceFiles)
		.pipe(sourcemaps.init())
		.pipe(concat(filenameRoot + '.js'))
		.pipe(sourcemaps.write('.'))
		.pipe(gulp.dest(destinationFolder))
		.pipe(filter('**/*.js'))
		.pipe(uglify())
		.pipe(rename({ extname: '.min.js' }))
		.pipe(sourcemaps.write('.'))
		.pipe(gulp.dest(destinationFolder));
}

gulp.task('js-editor', function(){
	return minifyJS('private/npdc/javascript/editor/*', 'web/js', 'editor');
});

gulp.task('js-main', function(){
	return minifyJS('private/npdc/javascript/main/*', 'web/js', 'npdc');
});