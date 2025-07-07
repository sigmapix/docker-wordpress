<?php

namespace Deployer;

require 'deployer/recipe.php';
require 'deployer/wordpress.php';

set('bin/composer', '{{dcea}} php composer.phar');

// Hosts
host('dxo-iav-prod')
    ->set('hostname', 'dxo-iav-prod')
    ->set('deploy_path', '/var/www/iav/')
;
host('dxo-iav-preprod')
    ->set('hostname', 'dxo-iav-preprod')
    ->set('deploy_path', '/var/www/preprod-iav/')
;
localhost('docker-local')
    ->setLabels(['LoadDBAllowed'=>'ok'])
    ->set('dcea', 'docker compose exec --user www-data apache')
    ->set('deploy_path', '.')
;

Deployer::get()->tasks->remove('database:load');
task('database:load', ['database:loadnew']);

after('database:load', 'database:postload');
task('database:postload', function () {
    run('{{dcea}} sh -c \'wp search-replace "iav.dxo.com" "iav.dxo.docker.casa" --precise --recurse-objects --all-tables\'', ['timeout' => null]);
})
->select('LoadDBAllowed=ok');

task('wordpress:files:init', function () {
    download('{{deploy_path}}/', 'web/');
});

//define( 'DOMAIN_CURRENT_SITE', 'iav.dxo.com' );