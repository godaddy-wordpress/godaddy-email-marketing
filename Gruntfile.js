module.exports = function( grunt ) {

	'use strict';

	var pkg = grunt.file.readJSON( 'package.json' );

	var BUILD_DIR    = 'build/',
	    SVN_USERNAME = false;

	if ( grunt.file.exists( 'svn-username' ) ) {

		SVN_USERNAME = grunt.file.read( 'svn-username' ).trim();

	}

	grunt.initConfig( {

		pkg: pkg,

		clean: {
			build: [ BUILD_DIR ]
		},

		copy: {
			build: {
				files: [
					{
						expand: true,
						src: [
							'*.php',
							'license.txt',
							'readme.txt',
							'assets/**',
							'css/**',
							'images/**',
							'includes/**',
							'js/**',
							'languages/*.{mo,pot}',
							'!**/*.{ai,eps,psd}'
						],
						dest: BUILD_DIR
					}
				]
			}
		},

		cssmin: {
			options: {
				shorthandCompacting: false,
				roundingPrecision: 5,
				processImport: false
			},
			all: {
				files: [
					{
						expand: true,
						cwd: 'css',
						src: [ '**/*.css', '!**/*.min.css' ],
						dest: 'css',
						ext: '.min.css'
					}
				]
			}
		},

		devUpdate: {
			options: {
				updateType: 'force',
				reportUpdated: false,
				semver: true,
				packages: {
					devDependencies: true,
					dependencies: false
				},
				packageJson: null,
				reportOnlyPkgs: []
			}
		},

		imagemin: {
			build: {
				options: {
					optimizationLevel: 5
				},
				files: [
					{
						expand: true,
						cwd: BUILD_DIR,
						src: [ '**/*.{gif,jpeg,jpg,png,svg}' ],
						dest: BUILD_DIR
					}
				]
			}
		},

		jshint: {
			all: [ 'Gruntfile.js', 'js/**/*.js', '!js/**/*.min.js' ]
		},

		makepot: {
			options: {
				domainPath: 'languages/',
				include: [ '*.php', 'includes/.+\.php' ],
				potComments: 'Copyright (c) {year} GoDaddy Operating Company, LLC. All Rights Reserved.',
				potHeaders: {
					'x-poedit-keywordslist': true
				},
				processPot: function( pot, options ) {
					pot.headers['report-msgid-bugs-to'] = pkg.bugs.url;
					return pot;
				},
				type: 'wp-plugin',
				updatePoFiles: true
			}
		},

		potomo: {
			files: {
				expand: true,
				cwd: 'languages',
				src: [ '*.po' ],
				dest: 'languages',
				ext: '.mo'
			}
		},

		replace: {
			php: {
				src: [
					'*.php',
					'includes/**/*.php'
				],
				overwrite: true,
				replacements: [
					{
						from: /Version:(\s*?)[a-zA-Z0-9\.\-\+]+$/m,
						to: 'Version:$1' + pkg.version
					},
					{
						from: /@version(\s*?)[a-zA-Z0-9\.\-\+]+$/m,
						to: '@version$1' + pkg.version
					},
					{
						from: /@since(.*?)NEXT/mg,
						to: '@since$1' + pkg.version
					},
					{
						from: /'GEM_VERSION',(\s*?)'[a-zA-Z0-9\.\-\+]+'/mg,
						to: '\'GEM_VERSION\',$1\'' + pkg.version + '\''
					}
				]
			},
			readme: {
				src: 'readme.*',
				overwrite: true,
				replacements: [
					{
						from: /^(\*\*|)Stable tag:(\*\*|)(\s*?)[a-zA-Z0-9.-]+(\s*?)$/mi,
						to: '$1Stable tag:$2$3<%= pkg.version %>$4'
					}
				]
			}
		},

		uglify: {
			options: {
				ASCIIOnly: true
			},
			all: {
				expand: true,
				cwd: 'js',
				src: ['**/*.js', '!**/*.min.js'],
				dest: 'js',
				ext: '.min.js'
			}
		},

		watch: {
			css: {
				options: {
					nospawn: true,
					cwd: 'css'
				},
				files: [ '**/*.css', '!**/*.min.css' ],
				tasks: [ 'cssmin' ]
			},
			js: {
				options: {
					nospawn: true,
					cwd: 'js'
				},
				files: [ '**/*.js', '!**/*.min.js' ],
				tasks: [ 'jshint', 'uglify' ]
			}
		},

		wp_deploy: {
			options: {
				plugin_slug: pkg.name,
				build_dir: BUILD_DIR,
				assets_dir: 'assets',
				plugin_main_file: 'godaddy-email-marketing.php',
				svn_user: SVN_USERNAME
			}
		}

	} );

	require( 'matchdep' ).filterDev( 'grunt-*' ).forEach( grunt.loadNpmTasks );

	grunt.registerTask( 'default', [ 'cssmin', 'jshint', 'uglify' ] );
	grunt.registerTask( 'build', [ 'default', 'version', 'clean:build', 'copy:build', 'imagemin:build' ] );
	grunt.registerTask( 'deploy', [ 'build', 'wp_deploy', 'clean:build' ] );
	grunt.registerTask( 'update-pot', [ 'makepot' ] );
	grunt.registerTask( 'update-mo', [ 'potomo' ] );
	grunt.registerTask( 'version', [ 'replace' ] );

};
