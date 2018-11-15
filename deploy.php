<?php
require 'vendor/autoload.php';

server('main', 'mysite.com', 22)
    ->user('someuser')
    ->forwardAgent()
    ->stage('prod')
    ->env('deploy_path', '/path/to/site')
    ->identityFile();

set('repository', 'git@somerepo.com/repo.git');
set('keep_releases', 2);
set('shared_dirs', ['assets']);

task('silverstripe:migrate', function() {
    run('php {{release_path}}/framework/cli-script.php dev/build flush=1');
})->desc('Run SilverStripe migration');

after('deploy:update_code', 'deploy:shared');
after('deploy:vendors', 'silverstripe:migrate');