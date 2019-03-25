const gulp = require('gulp')
	, concat = require('gulp-concat')
	, uglify = require('gulp-uglify')
	, rename = require('gulp-rename')
	, sass = require('gulp-sass')
	, bump = require('gulp-bump')
	, conventionalChangelog = require('gulp-conventional-changelog')
	, fs = require('fs');

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

changelog = function(){
	console.log('=!= REMEMBER TO UPDATE CHANGELOG =!=');
	return gulp.src('CHANGELOG.md')
		.pipe(conventionalChangelog({
				preset: 'conventionalcommits'
			})
		)
		.pipe(gulp.dest('./'));
};

bumpVersion = function(lvl){
	return gulp.src(['package.json'])
		.pipe(bump({type: lvl}))
		.pipe(gulp.dest('./'));
}

gulp.task('cl', function(){
	return changelog();
});

gulp.task('build:css', function(){
	return css2scss();
});

gulp.task('build:js-editor', function(){
	return minifyJS('editor');
});

gulp.task('build:js-npdc', function(){
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

gulp.task('bump:patch', function(){
	changelog();
	return bumpVersion('patch');
});

gulp.task('bump:minor', function(){
	changelog();
	return bumpVersion('minor');
});

gulp.task('bump:major', function(){
	changelog();
	return bumpVersion('major');
});

gulp.task('bump:test', function(){
	return bumpVersion('prerelease');
});