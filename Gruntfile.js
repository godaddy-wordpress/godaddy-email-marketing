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
			all: [ 'Gruntfile.js', 'js/**/*.js', '!js/blocks.js', '!js/**/*.min.js' ]
		},

		makepot: {
			target: {
				options: {
					domainPath: 'languages/',
					include: [ 'godaddy-email-marketing.php', 'includes/.+\.php' ],
					mainFile: 'godaddy-email-marketing.php',
					potComments: 'Copyright (c) {year} GoDaddy Operating Company, LLC. All Rights Reserved.',
					potFilename: 'godaddy-email-marketing-sign-up-forms.pot',
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

		shell: {
			blocks: [
				'cross-env BABEL_ENV=default webpack'
			].join( ' && ' )
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
				files: [ 'js/**/*.js', '!js/**/*.min.js' ],
				tasks: [ 'jshint', 'uglify' ]
			},
			readme: {
				files: 'readme.txt',
				tasks: [ 'readme' ]
			},
			blocks: {
				files: [ 'includes/blocks/**/*.js', '!includes/blocks/**/*.min.js' ],
				tasks: [ 'shell:blocks' ]
			},
		},

		wp_deploy: {
			plugin: {
				options: {
					plugin_slug: pkg.name,
					build_dir: 'build/',
					assets_dir: 'assets/',
					plugin_main_file: 'godaddy-email-marketing.php',
					svn_user: grunt.file.exists( 'svn-username' ) ? grunt.file.read( 'svn-username' ).trim() : false
				}
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

					// Banner
					if ( grunt.file.exists( 'assets/banner-1544x500.png' ) ) {
						readme = readme.replace( '**Contributors:**', "![Banner Image](assets/banner-1544x500.png)\r\n\r\n**Contributors:**" );
					}

					// Tag links
					readme = readme.replace( matches[0], section );

					// Badges
					readme = readme.replace( '## Description ##', grunt.template.process( pkg.badges.join( ' ' ) ) + "  \r\n\r\n## Description ##" );

					// YouTube
					readme = readme.replace( /\[youtube\s+(?:https?:\/\/www\.youtube\.com\/watch\?v=|https?:\/\/youtu\.be\/)(.+?)\]/g, '[![Play video on YouTube](https://img.youtube.com/vi/$1/hqdefault.jpg)](https://www.youtube.com/watch?v=$1)' );

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
