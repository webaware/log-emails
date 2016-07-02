module.exports = function (grunt) {

	grunt.initConfig({
		pkg: grunt.file.readJSON("package.json"),

		jshint: {
			all: [
				"Gruntfile.js",
				"js/*.js",
				"!js/*.min.js"
			],
			options: {
				jshintrc: ".jshintrc",
				force: true
			}
		},

		sass: {
			// @link https://github.com/gruntjs/grunt-contrib-sass#options
			options: {
				loadPath: "~/sass",
				cacheLocation: "/tmp/.sass-cache"
			},
			dev: {
				options: {
					style: "expanded",
					lineNumbers: true
				},
				files: {
					"css/admin.dev.css" : "css/admin.scss"
				}
			},
			dist: {
				options: {
					style: "compressed"
				},
				files: {
					"css/admin.min.css" : "css/admin.scss"
				}
			}
		},

		postcss: {
			options: {
				// @link https://github.com/postcss/autoprefixer#grunt
				map: true,
				processors: [
					require("autoprefixer")({
						// @link https://github.com/ai/browserslist#queries
						browsers: [
							"last 3 versions",
							"ie 11",
							"ios >= 7",
							"android >= 4"
						]
					})
				]
			},
			dist: {
				src: "css/*.css"
			}
		}

	});

	grunt.loadNpmTasks("grunt-contrib-jshint");
	grunt.loadNpmTasks("grunt-contrib-sass");
	grunt.loadNpmTasks("grunt-postcss");

	grunt.registerTask("scss", ["sass","postcss"]);
	grunt.registerTask("default", [ "jshint" ]);

};
