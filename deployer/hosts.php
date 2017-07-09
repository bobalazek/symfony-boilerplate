<?php

namespace Deployer;

localhost('production_server')
    ->hostname('128.128.128.128')
    ->user('root')
    ->port(22)
    ->forwardAgent(true)
    ->multiplexing(true)
    ->stage('prod')
    ->set('branch', 'master')
    ->set('env', 'prod')
    ->set('deploy_path', '/var/www/html/deployment/prod');

// Include the local hosts if present
if (file_exists(dirname(__FILE__).'/hosts-local.php')) {
    include dirname(__FILE__).'/hosts-local.php';
}
