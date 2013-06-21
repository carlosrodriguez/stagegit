module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    coffee: {
      options: {
        banner: '/*! <%= pkg.name %> <%= grunt.template.today("yyyy-mm-dd") %> */\n'
      },
      build: {
        src: 'app.coffee',
        dest: 'app.js'
      }
    }
  });

  grunt.loadNpmTasks('grunt-contrib-coffee');

  // Default task(s).
  grunt.registerTask('default', ['coffee']);

};