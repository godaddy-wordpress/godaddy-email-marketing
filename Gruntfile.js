/* jshint node:true */
module.exports = function( grunt ) {
	'use strict';

	// Project configuration.
	grunt.initConfig( {
		pkg: grunt.file.readJSON( 'package.json' ),

		cssmin: {
			options: {
				shorthandCompacting: false,
				roundingPrecision: -1,
				processImport: false
			},
			target: {
				files: [{
					expand: true,
					cwd: 'css',
					src: ['*.css', '!*.min.css'],
					dest: 'css',
					ext: '.min.css'
				}]
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
				src: ['*.js', '!*.min.js']
			}
		},

		watch: {
			css: {
				files: ['*.css', '!*.min.css'],
				options: {
					cwd: 'css',
					nospawn: true
				},
				tasks: ['cssmin']
			},
			uglify: {
				files: ['*.js', '!*.js.css'],
				options: {
					cwd: 'js',
					nospawn: true
				},
				tasks: ['uglify']
			}
		},

		pot: {
			options: {
				omit_header: true,
				text_domain: 'godaddy-email-marketing',
				encoding: 'UTF-8',
				dest: 'languages/',
				keywords: [ '__', '_e', '__ngettext:1,2', '_n:1,2', '__ngettext_noop:1,2', '_n_noop:1,2', '_c', '_nc:4c,1,2', '_x:1,2c', '_nx:4c,1,2', '_nx_noop:4c,1,2', '_ex:1,2c', 'esc_attr__', 'esc_attr_e', 'esc_attr_x:1,2c', 'esc_html__', 'esc_html_e', 'esc_html_x:1,2c' ],
				msgmerge: true
			},
			files: {
				src: [ 'godaddy-email-marketing.php', 'includes/*.php' ],
				expand: true
			}
		},

		po2mo: {
			files: {
				src: 'languages/*.po',
				expand: true
			}
		},

		// Build a deployable plugin.
		copy: {
			build: {
				src: [
					'**',
					'!.*',
					'!.*/**',
					'!.DS_Store',
					'!build/**',
					'!composer.json',
					'!dev-lib/**',
					'!Gruntfile.js',
					'!node_modules/**',
					'!npm-debug.log',
					'!package.json',
					'!phpcs.ruleset.xml',
					'!phpunit.xml.dist',
					'!readme.md',
					'!tests/**'
				],
				dest: 'build',
				expand: true,
				dot: true
			}
		},

		// Deploys a git Repo to the WordPress SVN repo.
		wp_deploy: {
			deploy: {
				options: {
					plugin_slug: '<%= pkg.name %>',
					build_dir: 'build'
				}
			}
		}

	} );

	// Load tasks
	grunt.loadNpmTasks( 'grunt-contrib-copy' );
	grunt.loadNpmTasks( 'grunt-contrib-cssmin' );
	grunt.loadNpmTasks( 'grunt-contrib-uglify' );
	grunt.loadNpmTasks( 'grunt-contrib-watch' );
	grunt.loadNpmTasks( 'grunt-po2mo' );
	grunt.loadNpmTasks( 'grunt-pot' );
	grunt.loadNpmTasks( 'grunt-wp-deploy' );

	// Default task.
	grunt.registerTask( 'default', [
		'cssmin',
		'uglify'
	] );

	// Translates strings.
	grunt.registerTask( 'update_translation', [
		'pot',
		'po2mo'
	] );

	/*
	 * Deploys to wordpress.org.
	 *
	 * Execute the default & update_translation commands then commit changes before deploying.
	 */
	grunt.registerTask( 'deploy', [
		'copy',
		'wp_deploy',
		'clean'
	] );

};
