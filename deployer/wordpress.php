<?php

namespace Deployer;

require_once __DIR__ . '/recipe.php';

task('wordpress:database:get', function () {
    cd('{{deploy_path}}');
    if (empty(run('wp --version', no_throw: true))) {
        // WP Cli n'est pas présent
        $crendentialsAsString = run("php -r \"require 'wp-config.php'; echo DB_NAME.PHP_EOL.DB_USER.PHP_EOL.DB_PASSWORD.PHP_EOL.DB_HOST;\"");
        [$dbName, $dbUser, $dbPassword, $dbHost] = explode(PHP_EOL, $crendentialsAsString);
        run("mysqldump --opt --single-transaction --no-autocommit -Q --compress -h $dbHost -u $dbUser -p$dbPassword $dbName | gzip -c > /tmp/{{alias}}.db.sql.gz");
    } else {
        run('wp db export /tmp/{{alias}}.db.sql --add-drop-table --exclude_tables=wp_redirection_404');
    }
    info('Database dumped!');
    download('/tmp/{{alias}}.db.sql.gz', 'database/{{alias}}.db.sql.gz');
    info('Wordpress Database downloaded!');
    run('rm /tmp/{{alias}}.db.sql.gz');
});

task('wordpress:config:set', function () {
    cd('{{deploy_path}}');
    list($exportsAsArray, $exportsAsString) = generateImportEnvStatement('.env.local');
    $wpConfig = [
        'MYSQL_HOST' => 'DB_HOST',
        'MYSQL_PASSWORD' => 'DB_PASSWORD',
        'MYSQL_USER' => 'DB_USER',
        'MYSQL_DATABASE' => 'DB_NAME',
    ];
    foreach ($exportsAsArray as $key => $value) {
        if (array_key_exists($key, $wpConfig)) {
            run("{{dcea}} wp config set $wpConfig[$key] '$value'");
        }
    }
})
->select('LoadDBAllowed=ok');
;