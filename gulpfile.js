var gulp =          require('gulp');
var gulpsass =      require('gulp-sass')(require('sass'));
var sourcemaps =    require('gulp-sourcemaps');
var autoprefixer =  require('gulp-autoprefixer');
var concat =        require('gulp-concat');
var jshint =        require('gulp-jshint');
var minify =        require('gulp-minify');
var stylish =       require('jshint-stylish');
var pxtorem =       require('gulp-pxtorem');
var urlencode =     require('gulp-css-urlencode-inline-svgs');
var babel =         require('gulp-babel');
var args = process.argv;
var lastArg = args[args.length - 1];

var config = {
    srcPath: 'src',
    themePath: 'public_html/themes/custom/now_here_this',
    nodeModulesPath: 'node_modules'
};

function sass() {
    return (
        gulp
            .src(config.srcPath + '/scss/**/*.scss')
            .pipe(sourcemaps.init())
            .pipe(gulpsass({
                style: 'expanded',
                includePaths: [
                    config.srcPath + '/scss',
                    // config.nodeModulesPath + '/bootstrap/',
                ]
            }))
            .on('error', function (errorObject, callback) {
                console.log(errorObject.messageFormatted.toString());

                if (lastArg === '--ci') {
                    process.exit(1);
                }
                else {
                    if (typeof this.emit === 'function') {
                        this.emit('end');
                    }
                }
            })
            .pipe(autoprefixer(['last 3 versions', 'ios >= 7', 'IE >= 11']))
            .pipe(pxtorem())
            .pipe(sourcemaps.write('./'))
            .pipe(gulp.dest(config.themePath + '/css'))
    )
}

function lint() {
    return gulp.src([config.srcPath + '/js/**/*.js', '!src/js/news-ticker-library/*.js'])
        .pipe(jshint({esnext:true}))
        .pipe(jshint.reporter(stylish));
}


function transpileJS() {
    return (
        gulp
            .src([
                config.nodeModulesPath + '/babel-polyfill/dist/polyfill.js',
                config.srcPath + '/js/**/*.js'
            ])
            .pipe(sourcemaps.init())
            .pipe(babel({
                presets: ['@babel/preset-env']
            }))
            .pipe(sourcemaps.write('./'))
            .pipe(gulp.dest(config.themePath + '/js'))
    );
}

function watch() {
    gulp.watch(config.srcPath + '/**/*.js', gulp.parallel(scripts));
    gulp.watch(config.srcPath + '/**/*.scss', gulp.parallel(css));
}

function libraries() {
    return gulp
        .src(
            [
                // `${config.nodeModulesPath}/@popperjs/core/dist/umd/popper.js`,
                `${config.nodeModulesPath}/bootstrap/dist/js/bootstrap.js`,
                `${config.nodeModulesPath}/jquery-ui-touch-punch/jquery.ui.touch-punch.js`,
                `${config.nodeModulesPath}/@fortawesome/fontawesome-free/**/*`
            ],
            { base: config.nodeModulesPath }
        )
        .pipe(gulp.dest('web/libraries'));
}

const css = gulp.series(sass);
const scripts = gulp.series(lint, transpileJS);
const build = gulp.parallel(css);

exports.css = css;
exports.build = build;
exports.scripts = scripts;
exports.lint = lint;
exports.transpileJS = transpileJS;
exports.watch = watch;


