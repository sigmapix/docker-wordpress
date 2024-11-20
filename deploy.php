<?php

namespace Deployer;

require 'deployer/recipe.php';

set('bin/composer', '{{dcea}} php composer.phar');

// Hosts
host('dxo-www-stage')
    ->set('hostname', 'dxo-www-stage')
    ->set('deploy_path', '/var/www/wp/')
;
localhost('docker-local')
    ->setLabels(['LoadDBAllowed'=>'ok'])
    ->set('dcea', 'docker compose exec -T --user www-data apache')
    ->set('deploy_path', '.')
;

task('wordpress:database:get', function () {
    cd('{{deploy_path}}');
    run('wp db export {{alias}}.db.sql --add-drop-table --exclude_tables=wp_redirection_404');
    info('Database dumped!');
    run('gzip -f {{alias}}.db.sql');
    info('Database zipped!');
    download('{{deploy_path}}/{{alias}}.db.sql.gz', 'database/{{alias}}.db.sql.gz');
    info('Wordpress Database downloaded!');
    run('rm {{alias}}.db.sql.gz');
});

Deployer::get()->tasks->remove('database:load');
task('database:load', ['database:loadnew']);

after('database:load', 'database:postload');
task('database:postload', function () {
    run('{{dcea}} sh -c \'wp search-replace "stage-www.dxo.com" "dxo.docker.casa" --precise --recurse-objects --all-tables\'');
});
// rsync -aL --exclude='wp-content/themes'  --exclude='wp-content/uploads/[0-9]*/' --exclude='sites' --exclude='backtrace.log' dxo-www-stage:/var/www/wp/ web
// rsync -aL dxo-www-stage:/var/www/dxodev/dxo-wp/ web/wp-content/plugins/dxo-wp/
// HTTP_X_FORWARDED_PROTO