'use strict';

var _ = require('underscore');
var fs = require('fs');
var path = require('path');
var less = require('less');
var uglify = require('uglify-js');
var mkdirp = require('mkdirp');

var OPTIMIZE = true;

module.exports = function(grunt){
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-less');
    grunt.loadNpmTasks('grunt-contrib-uglify');

    var config = {
        less: {
            "dark-one-ui": {
                options: {
                    compress: OPTIMIZE,
                },
                files: {
                    "resources/dest/Kafoso/Tools/Debug/Dumper/HtmlFormatter/theme/css/dark-one-ui.css": "resources/src/Kafoso/Tools/Debug/Dumper/HtmlFormatter/theme/less/dark-one-ui.less",
                },
                watch: {
                    files: [
                        "resources/src/Kafoso/Tools/Debug/Dumper/HtmlFormatter/theme/less/dark-one-ui.less",
                        "resources/src/Kafoso/Tools/Debug/Dumper/HtmlFormatter/theme/less/dark-one-ui/*.less",
                        "resources/src/Kafoso/Tools/Debug/Dumper/HtmlFormatter/theme/less/dark-one-ui/**/*.less",
                    ],
                },
            },
        },
        uglify: {
            "main.js": {
                options: {
                    beautify: !OPTIMIZE,
                    mangle: OPTIMIZE,
                },
                files: [{
                    expand: true,
                    src: [
                        "resources/src/Kafoso/Tools/Debug/Dumper/HtmlFormatter/js/main/each.js",
                        "resources/src/Kafoso/Tools/Debug/Dumper/HtmlFormatter/js/main/Cookie.js",
                        "resources/src/Kafoso/Tools/Debug/Dumper/HtmlFormatter/js/main.js",
                    ],
                    dest: "resources/dest/Kafoso/Tools/Debug/Dumper/HtmlFormatter/js/main.js",
                    cwd: __dirname,
                    rename: function(dest, src){
                        return dest;
                    },
                }],
                watch: {
                    files: [
                        "resources/src/Kafoso/Tools/Debug/Dumper/HtmlFormatter/js/main.js",
                        "resources/src/Kafoso/Tools/Debug/Dumper/HtmlFormatter/js/main/*.js",
                        "resources/src/Kafoso/Tools/Debug/Dumper/HtmlFormatter/js/main/**/*.js",
                    ],
                },
            },
        },
        watch: {},
    };

    _.each(config.less, function(task, taskId){
        if (_.isObject(task.watch)) {
            config.watch["less_" + taskId] = task.watch;
            config.watch["less_" + taskId].options = {
                spawn: false,
                interrupt: true,
            };
            config.watch["less_" + taskId].tasks = ["less:" + taskId];
        }
    });
    _.each(config.uglify, function(task, taskId){
        if (_.isObject(task)) {
            config.watch["uglify_" + taskId] = task.watch;
            config.watch["uglify_" + taskId].options = {
                spawn: false,
                interrupt: true,
            };
            config.watch["uglify_" + taskId].tasks = ["uglify:" + taskId];
        }
    });

    grunt.config.init(config);
    grunt.registerTask('default', ["less", "uglify"]);
    grunt.registerTask('daemon', ["watch"]);
};
