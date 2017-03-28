var gulp = require('gulp');
var eslint = require('gulp-eslint');
var exec = require('child_process').exec;

gulp.task('default', ['fix-cs']);

/***** CSFix *****/
gulp.task('csfix', [
    'csfix-frontend',
    'csfix-backend',
]);

/*** Frontend ***/
gulp.task('csfix-frontend', function() {
    return gulp.src([
        'web/assets/**/*.js',
        '!web/assets/vendor/**/*.js',
        '!web/assets/vendor_3rd_party/**/*.js',
    ])
        .pipe(eslint({
            fix: true,
        }))
        .pipe(gulp.dest('web/assets'));
});

/*** Backend ***/
gulp.task('csfix-backend', function(cb) {
    exec('php-cs-fixer fix', function (err, stdout, stderr) {
        console.log(stdout);
        cb(err);
    });
});

/***** Test *****/
gulp.task('test', [
    'test-backend',
]);

/*** Backend ***/
gulp.task('test-backend', [
    'test-backend-phpunit',
    'lint-backend',
]);

gulp.task('test-backend-phpunit', function(cb) {
    exec('php vendor/bin/simple-phpunit', function (err, stdout, stderr) {
        console.log(stdout);
        cb(err);
    });
});

/***** Lint *****/
gulp.task('lint', [
    'lint-backend',
]);

/*** Backend ***/
gulp.task('lint-backend', [
    'lint-backend-yaml',
    'lint-backend-twig',
]);

/* YAML */
gulp.task('lint-backend-yaml', [
    'lint-backend-yaml-src',
    'lint-backend-yaml-app',
]);

gulp.task('lint-backend-yaml-src', function(cb) {
    exec('php bin/console lint:yaml src', function (err, stdout, stderr) {
        console.log(stdout);
        cb(err);
    });
});

gulp.task('lint-backend-yaml-app', function(cb) {
    exec('php bin/console lint:yaml app', function (err, stdout, stderr) {
        console.log(stdout);
        cb(err);
    });
});

/* Twig */
gulp.task('lint-backend-twig', [
    'lint-backend-twig-src',
    'lint-backend-twig-app',
]);

gulp.task('lint-backend-twig-src', function(cb) {
    exec('php bin/console lint:twig src', function (err, stdout, stderr) {
        console.log(stdout);
        cb(err);
    });
});

gulp.task('lint-backend-twig-app', function(cb) {
    exec('php bin/console lint:twig app', function (err, stdout, stderr) {
        console.log(stdout);
        cb(err);
    });
});
