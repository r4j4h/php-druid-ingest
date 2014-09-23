module.exports = function(grunt) {

    // Project configuration.
    grunt.initConfig({

        pkg: grunt.file.readJSON('package.json'),

        clean: ["docs/main"],

        phpunit: {

            classes: {
                dir: 'tests/'
            },

            options: {
                bin: 'vendor/bin/phpunit',
                colors: true,
                followOutput: true
            }

        },

        phpdocumentor: {

            src_documentation : {
                options: {
                    directory : 'src/,clicommands/,vendor/r4j4h/php-druid-query',
                    target : 'docs/main'
                }
            }

        }

    });

    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-phpunit');
    grunt.loadNpmTasks('grunt-phpdocumentor');


    grunt.registerTask('test', ['phpunit']);

    grunt.registerTask('doc', ['clean', 'phpdocumentor']);
    grunt.registerTask('docs', ['doc']);


    grunt.registerTask('default', ['test']);

};
