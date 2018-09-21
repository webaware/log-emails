module.exports = function (grunt) {

	grunt.initConfig({
		pkg: grunt.file.readJSON("package.json"),

		clean: [ "dist/**" ],

		copy: {
			main: {
				files: [
					{
						src: ["./**", "!./node_modules/**", "!./Gruntfile.js", "!./package*.json"],
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
					src: [ "<%= pkg.name %>/**" ]
				}]
			}
		},

		eslint: {
			all: [
				"Gruntfile.js",
				"js/*.js",
				"!js/*.min.js"
			]
		},

		sass: {
			// @link https://github.com/gruntjs/grunt-contrib-sass#options
			options: {
				implementation: require('node-sass'),	// FIXME: kludge to work around npm dependency issue; remove!
				sourceMap: true
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
					require("autoprefixer")(),
					require("postcss-discard-duplicates")()
				]
			},
			dist: {
				src: "css/*.css"
			}
		}

	});

	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks("grunt-contrib-compress");
	grunt.loadNpmTasks("grunt-contrib-copy");
	grunt.loadNpmTasks("grunt-eslint");
	grunt.loadNpmTasks("grunt-postcss");
	grunt.loadNpmTasks("grunt-sass");

	grunt.registerTask("release", ["clean","copy","compress"]);
	grunt.registerTask("scss", ["sass","postcss"]);

};
