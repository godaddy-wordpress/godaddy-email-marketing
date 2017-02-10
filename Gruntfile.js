/* global module, require */

module.exports = function( grunt ) {

	'use strict';

	var pkg = grunt.file.readJSON( 'package.json' );

	grunt.initConfig( {

		pkg: pkg,

		clean: {
			build: [ 'build/' ]
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
							'css/**',
							'images/**',
							'includes/**',
							'js/**',
							'languages/*.{mo,pot}',
							'!**/*.{ai,eps,psd}'
						],
						dest: 'build/'
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
						cwd: 'css/',
						src: [ '**/*.css', '!**/*.min.css' ],
						dest: 'css/',
						ext: '.min.css'
					}
				]
			}
		},

		devUpdate: {
			packages: {
				options: {
					packageJson: null,
					packages: {
						devDependencies: true,
						dependencies: false
					},
					reportOnlyPkgs: [],
					reportUpdated: false,
					semver: true,
					updateType: 'force'
				}
			}
		},

		imagemin: {
			options: {
				optimizationLevel: 3
			},
			assets: {
				expand: true,
				cwd: 'assets/',
				src: [ '**/*.{gif,jpeg,jpg,png,svg}' ],
				dest: 'assets/'
			},
			images: {
				expand: true,
				cwd: 'images/',
				src: [ '**/*.{gif,jpeg,jpg,png,svg}' ],
				dest: 'images/'
			}
		},

		jshint: {
			all: [ 'Gruntfile.js', 'js/**/*.js', '!js/**/*.min.js' ]
		},

		makepot: {
			target: {
				options: {
					domainPath: 'languages/',
					include: [ '/*.php', 'includes/.+\.php' ],
					mainFile: 'godaddy-email-marketing.php',
					potComments: 'Copyright (c) {year} GoDaddy Operating Company, LLC. All Rights Reserved.',
					potFilename: 'godaddy-email-marketing.pot',
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
				cwd: 'languages/',
				src: [ '*.po' ],
				dest: 'languages/',
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
						from: /GEM_VERSION(['"]\s*?,\s*?['"])[a-zA-Z0-9\.\-\+]+/mg,
						to: 'GEM_VERSION$1' + pkg.version
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
				cwd: 'js/',
				src: [ '**/*.js', '!**/*.min.js' ],
				dest: 'js/',
				ext: '.min.js'
			}
		},

		watch: {
			css: {
				files: [ '**/*.css', '!**/*.min.css' ],
				tasks: [ 'imagemin' ]
			},
			images: {
				files: [ 'assets/**/*.{gif,jpeg,jpg,png,svg}', 'images/**/*.{gif,jpeg,jpg,png,svg}' ],
				tasks: [ 'imagemin' ]
			},
			js: {
				files: [ '**/*.js', '!**/*.min.js' ],
				tasks: [ 'jshint', 'uglify' ]
			},
			readme: {
				files: 'readme.txt',
				tasks: [ 'readme' ]
			}
		},

		wp_deploy: {
			options: {
				plugin_slug: pkg.name,
				build_dir: 'build/',
				assets_dir: 'assets/',
				plugin_main_file: 'godaddy-email-marketing.php',
				svn_user: grunt.file.exists( 'svn-username' ) ? grunt.file.read( 'svn-username' ).trim() : ''
			}
		},

		wp_readme_to_markdown: {
			options: {
				post_convert: function( readme ) {
					var matches = readme.match( /\*\*Tags:\*\*(.*)\r?\n/ ),
					    tags    = matches[1].trim().split( ', ' ),
					    section = matches[0];

					for ( var i = 0; i < tags.length; i++ ) {
						section = section.replace( tags[i], '[' + tags[i] + '](https://wordpress.org/plugins/tags/' + tags[i] + '/)' );
					}

					// Tag links
					readme = readme.replace( matches[0], section );

					// Badges
					readme = readme.replace( '## Description ##', grunt.template.process( pkg.badges.join( ' ' ) ) + "  \r\n\r\n## Description ##" );

					return readme;
				}
			},
			main: {
				files: {
					'readme.md': 'readme.txt'
				}
			}
		}

	} );

	require( 'matchdep' ).filterDev( 'grunt-*' ).forEach( grunt.loadNpmTasks );

	grunt.registerTask( 'default',    [ 'cssmin', 'jshint', 'uglify', 'imagemin', 'readme' ] );
	grunt.registerTask( 'check',      [ 'devUpdate' ] );
	grunt.registerTask( 'build',      [ 'default', 'clean:build', 'copy:build' ] );
	grunt.registerTask( 'deploy',     [ 'build', 'wp_deploy', 'clean:build' ] );
	grunt.registerTask( 'readme',     [ 'wp_readme_to_markdown' ] );
	grunt.registerTask( 'update-pot', [ 'makepot' ] );
	grunt.registerTask( 'update-mo',  [ 'potomo' ] );
	grunt.registerTask( 'version',    [ 'replace', 'readme' ] );

};
