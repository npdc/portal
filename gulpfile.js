const gulp = require('gulp')
	, concat = require('gulp-concat')
	, uglify = require('gulp-uglify')
	, rename = require('gulp-rename')
	, sass = require('gulp-sass')
	, bump = require('gulp-bump')
	, fs = require('fs')
	, git = require('gulp-git')
	, insert = require('gulp-insert');

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
function changelogMsg(version){
	var msg = '* REMEMBER TO UPDATE CHANGELOG ';
	if(version !== undefined){
		msg = msg + 'FOR v'+version;
	}
	msg = msg + '*';
	console.log('*'.repeat(msg.length));
	console.log(msg);
	console.log('*'.repeat(msg.length));
}
function changelog(){
	var version = getPackageJsonVersion();
	changelogMsg(version);
	var date = new Date();
	return gulp.src('CHANGELOG.md')
		.pipe(insert.prepend('## v'+ version + ' - ' +date.toISOString().substring(0, 10)+'\n\n\n'))
		.pipe(gulp.dest('./'));
};

function bumpVersion(lvl){
	if(lvl !== 'prerelease'){
		changelogMsg();
	}
	return gulp.src(['package.json'])
		.pipe(bump({type: lvl}))
		.pipe(gulp.dest('./'));
}

function getPackageJsonVersion () {
	return JSON.parse(fs.readFileSync('./package.json', 'utf8')).version;
};

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

gulp.task('bump:test', function(){
	return bumpVersion('prerelease');
});

gulp.task('bump:patch', function(){
	return bumpVersion('patch');
});

gulp.task('bump:minor', function(){
	return bumpVersion('minor');
});

gulp.task('bump:major', function(){
	return bumpVersion('major');
});

gulp.task('changelog', function(){
	return changelog();
});

gulp.task('git:tag', function (done) {
	var version = getPackageJsonVersion();
	git.tag(version, 'Created Tag for version: ' + version, function (error) {
		if (error) {
			return done(error);
		}
		git.push('origin', 'master', {args: '--tags'}, done);
	});
});