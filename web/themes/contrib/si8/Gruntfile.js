module.exports = function(grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        less: {
            styles :{
                /*options: {
                    strictMath: true,
                    sourceMap: true,
                    outputSourceFiles: true,
                    sourceMapURL: 'style.css.map',
                    sourceMapFilename: 'style.css.map'
                },*/
                files: {
                    "css/style.css": "less/style.less"
                }
            }
        },
        watch: {
            styles: {
                options: {
                    event: ["added", "deleted", "changed"]
                },
                files: ["less/*.less", "less/*/*.less"],
                tasks: [ "less" ]
            }
        }
    });

    grunt.loadNpmTasks("grunt-contrib-less");
    grunt.loadNpmTasks("grunt-contrib-watch");

    // the default task can be run just by typing "grunt" on the command line
    grunt.registerTask("default", ["watch"]);
};