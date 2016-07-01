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
		},

		pot: {
			options: {
				text_domain: "log-emails",
				msgid_bugs_address: "translate@webaware.com.au",
				encoding: "UTF-8",
				dest: "languages/",
				keywords: [
					"gettext",
					"__",
					"_e",
					"_n:1,2",
					"_x:1,2c",
					"_ex:1,2c",
					"_nx:4c,1,2",
					"esc_attr__",
					"esc_attr_e",
					"esc_attr_x:1,2c",
					"esc_html__",
					"esc_html_e",
					"esc_html_x:1,2c",
					"_n_noop:1,2",
					"_nx_noop:3c,1,2",
					"__ngettext_noop:1,2"
				],
				comment_tag: "translators:"
			},
			files: {
				src: [
					"**/*.php",
					"!lib/**/*",
					"!node_modules/**/*"
				],
				expand: true
			}
		}

	});

	grunt.loadNpmTasks("grunt-contrib-jshint");
	grunt.loadNpmTasks("grunt-contrib-sass");
	grunt.loadNpmTasks("grunt-postcss");
	grunt.loadNpmTasks("grunt-pot");

	grunt.registerTask("scss", ["sass","postcss"]);
	grunt.registerTask("default", [ "jshint" ]);

};
