module.exports = function (grunt) {

	const autoprefixer					= require("autoprefixer");
	const clean							= require("postcss-clean");
	const sass							= require("sass");

	grunt.initConfig({
		pkg: grunt.file.readJSON("package.json"),

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
				implementation: sass,
				outputStyle: "expanded",
				sourceMap: false,
			},
			dev: {
				files: {
					"css/admin.dev.css" : "scss/admin.scss"
				}
			},
			dist: {
				files: {
					"css/admin.min.css" : "scss/admin.scss"
				}
			}
		},

		postcss: {
			// @link https://github.com/postcss/autoprefixer#grunt
			// @link https://github.com/jakubpawlowicz/clean-css
			dev: {
				options: {
					map: false,
					processors: [
						autoprefixer(),
						clean({
							format: "beautify",
							level: {
								1: {
									removeQuotes: false,
								},
								2: {
								}
							},
						}),
					]
				},
				src: "css/*.dev.css"
			},
			dist: {
				options: {
					map: false,
					processors: [
						autoprefixer(),
						clean({
							level: {
								1: {
									specialComments: 0,
									removeQuotes: false,
								},
								2: {
								}
							},
						}),
					]
				},
				src: "css/*.min.css"
			}
		},

		shell: {
			// @link https://github.com/sindresorhus/grunt-shell
			dist: {
				command: [
					"rm -rf .dist",
					"mkdir .dist",
					"git archive HEAD --prefix=<%= pkg.name %>/ --format=zip -9 -o .dist/<%= pkg.name %>-<%= pkg.version %>.zip",
				].join("&&")
			},
			wpsvn: {
				command: [
					"svn up .wordpress.org",
					"rm -rf .wordpress.org/trunk",
					"mkdir .wordpress.org/trunk",
					"git archive HEAD --format=tar | tar x --directory=.wordpress.org/trunk",
				].join("&&")
			}
		}

	});

	grunt.loadNpmTasks("grunt-eslint");
	grunt.loadNpmTasks("@lodder/grunt-postcss");
	grunt.loadNpmTasks("grunt-sass");
	grunt.loadNpmTasks("grunt-shell");
	grunt.loadNpmTasks("grunt-stylelint");

	grunt.registerTask("release", ["shell:dist"]);
	grunt.registerTask("scss", ["stylelint","sass","postcss"]);
	grunt.registerTask("wpsvn", ["shell:wpsvn"]);

};
