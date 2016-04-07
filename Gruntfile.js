module.exports = function(grunt) {

	require('matchdep').filterDev('grunt-*').forEach( grunt.loadNpmTasks );

	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

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
				src: [ 'godaddy-email-marketing.php', 'includes/*.php', ],
				expand: true
			}
		},

		po2mo: {
			files: {
				src: 'languages/*.po',
				expand: true
			}
		}

	});

	// Default task(s).
	grunt.registerTask('default', ['cssmin', 'uglify']);
	grunt.registerTask('update_translation', ['pot','po2mo']);

};
