module.exports = function (grunt) {

	grunt.initConfig({
		pkg: grunt.file.readJSON("package.json"),

		clean: [ "dist/**" ],

		copy: {
			main: {
				files: [
					{
						src: [
							"./**",
							"!./es6/**",
							"!./node_modules/**",
							"!./scss/**",
							"!./vendor/**",
							"!./composer.*",
							"!./Gruntfile.js",
							"!./package*.json",
							"!./phpcs*.xml",
						],
						dest: "dist/<%= pkg.name %>/"
					}
				]
			}
		},

		compress: {
			options: {
				archive: "./dist/<%= pkg.name %>-<%= pkg.version %>.zip",
				mode: "zip"
			},
			all: {
				files: [{
					expand: true,
					cwd: "./dist/",
					date: new Date(),
					src: [ "<%= pkg.name %>/**" ]
				}]
			}
		},

		eslint: {
			all: [
				"Gruntfile.js",
				"es6/*.js"
			]
		},

		stylelint: {
			// @link https://stylelint.io/user-guide/configuration/
			options: {
				configFile: ".stylelintrc.yml",
				failOnError: true,
			},
			src: [
				"scss/*.scss",
			]
		},

		sass: {
			// @link https://github.com/gruntjs/grunt-contrib-sass#options
			options: {
				implementation: require('node-sass')
			},
			dev: {
				options: {
					style: "expanded",
					sourceMap: true,
					lineNumbers: true
				},
				files: {
					"css/admin.dev.css" : "scss/admin.scss"
				}
			},
			dist: {
				options: {
					style: "compressed"
				},
				files: {
					"css/admin.min.css" : "scss/admin.scss"
				}
			}
		},

		postcss: {
			// @link https://github.com/postcss/autoprefixer#grunt
			dev: {
				options: {
					map: true,
					processors: [
						require("autoprefixer")({
							grid: true
						}),
						require("postcss-discard-duplicates")()
					]
				},
				src: "css/*.dev.css"
			},
			dist: {
				options: {
					map: false,
					processors: [
						require("autoprefixer")({
							grid: true
						}),
						require("postcss-discard-duplicates")(),
						require("cssnano")()
					]
				},
				src: "css/*.min.css"
			}
		}

	});

	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks("grunt-contrib-compress");
	grunt.loadNpmTasks("grunt-contrib-copy");
	grunt.loadNpmTasks("grunt-eslint");
	grunt.loadNpmTasks("grunt-postcss");
	grunt.loadNpmTasks("grunt-sass");
	grunt.loadNpmTasks("grunt-stylelint");

	grunt.registerTask("release", ["clean","copy","compress"]);
	grunt.registerTask("scss", ["stylelint","sass","postcss"]);

};
