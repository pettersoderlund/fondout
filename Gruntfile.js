'use strict';

// # Globbing
// for performance reasons we're only matching one level down:
// 'test/spec/{,*/}*.js'
// use this if you want to recursively match all subfolders:
// 'test/spec/**/*.js'

module.exports = function (grunt) {

  // load all grunt tasks
  require('load-grunt-tasks')(grunt);

  // show elapsed time at the end
  require('time-grunt')(grunt);

  // Define the configuration for all the tasks
  grunt.initConfig({

    pkg: grunt.file.readJSON('package.json'),

    // configurable paths
    directory: {
      php: 'module',
      web: 'public',
    },

    watch: {
      options: {
        livereload: true
      },
      php: {
        files: '<%= directory.php %>/**/*.php'
      },
      templates: {
        files: '<%= directory.php %>/**/*.phtml'
      },
      less: {
        files: '<%= directory.web %>/less/{,*/}*.less',
        tasks: 'less'
      },
      javascript: {
        files: '<%= directory.web %>/js/{,*/}*.js'
      },
    },

    // make sure the PSR2 standard is followed
    phpcs: {
      all: {
        dir: ['<%= directory.php %>/**/*.php']
      },
      options: {
        bin: 'vendor/bin/phpcs',
        standard: 'PSR2'
      }
    },

    // lint all php files for errors
    phplint: {
      options: {
        swapPath: '/tmp'
      },
      all: ['<%= directory.php %>/**/*.php']
    },

    // Make sure code styles are up to par and there are no obvious mistakes
    jshint: {
      options: {
        jshintrc: '.jshintrc',
        reporter: require('jshint-stylish')
      },
      all: [
        'Gruntfile.js',
        '<%= directory.web %>/js/**/*.js'
      ]
    },

    less: {
      all: {
        files: {
          '<%= directory.web %>/css/style.css': '<%= directory.web %>/less/style.less'
        }
      }
    }
  });

  grunt.registerTask('test', [
    'phplint',
    'phpcs',
    // 'phpunit'
    'jshint'
  ]);

  grunt.registerTask('build', [
    'less'
  ]);

  grunt.registerTask('default', [
    'test',
    'build'
  ]);
};
