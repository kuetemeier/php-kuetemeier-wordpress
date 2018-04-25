'use strict';

const gulp = require('gulp');
const gulpSequence = require('gulp-sequence');
const fs = require('fs');
// replace
const replace = require('gulp-replace');

const del = require('del');

// exec
const exec = require('child_process').exec;


// get config
var pkg = JSON.parse(fs.readFileSync('./package.json'));


gulp.task('clean', function() {
  return del([ 'docs' ]);
});


gulp.task('replace_in_readme_md', function() {
  return gulp.src(["./README.md"], {base: './'})
    .pipe(replace(/(Description: )(.*)/, '$1' + pkg.description))
    .pipe(replace(/([v,V]ersion: )(.*)/, '$1' + pkg.version + '  '))
    .pipe(replace(/(Latest Stable Version: )(.*)/, '$1' + pkg.version_stable))
    .pipe(gulp.dest('./'));
});


gulp.task('replace_in_composer', function() {
  return gulp.src(["./composer.json"], {base: './'})
    .pipe(replace(/(Description: )(.*)/, '$1' + pkg.description))
    .pipe(replace(/("[v,V]ersion": ")(.*)",/, '$1' + pkg.version + '",'))
    .pipe(gulp.dest('./'));
});


gulp.task('replace', ['replace_in_readme_md', 'replace_in_composer']);


gulp.task('phpdoc', function() {
  exec('./vendor/bin/phpdoc -d ./src -t ./docs/phpdoc', function (err, stdout, stderr) {
    console.log(stderr);
  });
});


gulp.task('watch', function()
{
  gulp.start('phpdoc');

  gulp.watch(['./src/**/*.php'], ['phpdoc']);
});


gulp.task('test', function() {
  exec('./vendor/bin/phpunit --bootstrap vendor/autoload.php tests', function (err, stdout, stderr) {
    console.log(stdout);
    console.log(stderr);
  });
});


gulp.task('lint', function() {
  exec('./vendor/bin/phpcs', function (err, stdout, stderr) {
    console.log(stdout);
    console.log(stderr);
  });
});


gulp.task('build', gulpSequence('clean', 'replace', 'lint', 'test', 'phpdoc'));


gulp.task('default', ['build']);
