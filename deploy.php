<?php

namespace Deployer;

require 'deployer/recipe.php';

// Local options
set('LOCAL_MYSQL', 'mysql -u root dxomark_corp');
set('LOCAL_APACHE', 'docker compose exec --user www-data apache');

// Common project options
set('application', 'dxomark_corp');

// Hosts
host('prod')
    ->set('hostname', 'sigmapix_grape')
    ->set('deploy_path', '/home/vema5145/dxomark/corp.dxomark.sigmapix.org/web/')
    ->set('dcea', '')
    ->set('db_host', 'mysql')
    ->set('db_user', 'root')
    ->set('db_password', '')
    ->set('db_name', '')
    ->set('bin/mysql', 'mysql ')
    ->set('bin/composer', '{{dcea}} {{sudo}} php composer.phar')
;



// Project tasks
task('deploy', ['deploy:git:pull']);


task('wordpress:get', function () {
    cd('{{deploy_path}}');
    run('wp db export {{alias}}.db.sql --add-drop-table');
    info('Database dumped!');
    run('gzip -f {{alias}}.db.sql');
    info('Database zipped!');
    run('cd .. && tar -czf {{alias}}.tar.gz web/');
    download('{{deploy_path}}/../{{alias}}.tar.gz', '{{alias}}.tar.gz');
    info('Wordpress downloaded!');
    //run('rm {{alias}}.db.sql.gz');
});

task('wordpress:init', function () {
    info('Delete web!');
    runLocally('rm -rf web/*');
    info('Extract files!');
    runLocally('tar -xzf {{alias}}.tar.gz web/');
    runLocally('{{LOCAL_APACHE}} sh -c \'wp config set DB_NAME $WORDPRESS_DB_NAME\'');
    runLocally('{{LOCAL_APACHE}} sh -c \'wp config set DB_USER $WORDPRESS_DB_USER\'');
    runLocally('{{LOCAL_APACHE}} sh -c \'wp config set DB_PASSWORD "$WORDPRESS_DB_PASSWORD"\'');
    runLocally('{{LOCAL_APACHE}} sh -c \'wp config set DB_HOST $WORDPRESS_DB_HOST\'');

    info('Import database!');
    runLocally('cd web/ && gzip -dk {{alias}}.db.sql.gz');
    runLocally('{{LOCAL_APACHE}} sh -c \'wp db import {{alias}}.db.sql\'');
    runLocally('cd web/ && rm {{alias}}.db.sql');
    runLocally('{{LOCAL_APACHE}} sh -c \'wp search-replace "https://corp.dxomark.sigmapix.org" "http://corp.dxomark.docker.casa" --precise --recurse-objects --all-tables\'');

});


