module.exports = function( grunt ) {

	'use strict';

	var BUILD_DIR = 'build/';

	var pkg = grunt.file.readJSON( 'package.json' );

	var svn_username = false;

	if ( grunt.file.exists( 'svn-username' ) ) {

		svn_username = grunt.file.read( 'svn-username' ).trim();

	}

	grunt.initConfig( {

		pkg: pkg,

		clean: {
			build: [ BUILD_DIR + '*' ],
			options: {
				force: true
			}
		},

		copy: {
			files: {
				cwd: '.',
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
					'languages/*.{mo,pot}'
				],
				dest: BUILD_DIR
			}
		},

		cssmin: {
			options: {
				shorthandCompacting: false,
				roundingPrecision: 5,
				processImport: false
			},
			target: {
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
			main: {
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
			}
		},

		imagemin: {
			dynamic: {
				options: {
					optimizationLevel: 3
				},
				files: [
					{
						expand: true,
						cwd: 'assets',
						src: [ '*.{gif,jpeg,jpg,png,svg}' ],
						dest: 'assets'
					},
					{
						expand: true,
						cwd: 'images',
						src: [ '*.{gif,jpeg,jpg,png,svg}' ],
						dest: 'images'
					}
				]
			}
		},

		jshint: {
			all: [ 'Gruntfile.js', 'js/**/*.js', '!js/**/*.min.js' ]
		},

		makepot: {
			target: {
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
			version_php: {
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
			version_readme: {
				src: 'readme.*',
				overwrite: true,
				replacements: [ {
					from: /^(\*\*|)Stable tag:(\*\*|)(\s*?)[a-zA-Z0-9.-]+(\s*?)$/mi,
					to: '$1Stable tag:$2$3<%= pkg.version %>$4'
				} ]
			}
		},

		uglify: {
			options: {
				ASCIIOnly: true
			},
			core: {
				expand: true,
				cwd: 'js',
				dest: 'js',
				ext: '.min.js',
				src: ['**/*.js', '!**/*.min.js']
			}
		},

		watch: {
			css: {
				files: [ '**/*.css', '!**/*.min.css' ],
				options: {
					cwd: 'css',
					nospawn: true
				},
				tasks: [ 'cssmin' ]
			},
			uglify: {
				files: [ '**/*.js', '!**/*.min.js' ],
				options: {
					cwd: 'js',
					nospawn: true
				},
				tasks: [ 'jshint', 'uglify' ]
			}
		},

		wp_deploy: {
			deploy: {
				options: {
					plugin_slug: pkg.name,
					build_dir: BUILD_DIR,
					assets_dir: 'wp-org-assets',
					plugin_main_file: 'godaddy-email-marketing.php',
					svn_user: svn_username
				}
			}
		}

	} );

	require( 'matchdep' ).filterDev( 'grunt-*' ).forEach( grunt.loadNpmTasks );

	grunt.registerTask( 'default', [ 'cssmin', 'jshint', 'uglify' ] );
	grunt.registerTask( 'build', [ 'default', 'version', 'clean', 'copy', 'imagemin' ] );
	grunt.registerTask( 'deploy', [ 'build', 'wp_deploy', 'clean' ] );
	grunt.registerTask( 'update-pot', [ 'makepot' ] );
	grunt.registerTask( 'update-mo', [ 'potomo' ] );
	grunt.registerTask( 'version', [ 'replace' ] );

};
