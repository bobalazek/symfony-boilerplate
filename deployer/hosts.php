<?php

namespace Deployer;

host('production_server')
    ->hostname('128.128.128.128')
    ->user('root')
    ->port(22)
    ->forwardAgent(true)
    ->multiplexing(true)
    ->stage('prod')
    ->set('branch', 'master')
    ->set('env', 'prod')
    ->set('deploy_path', '/var/www/html/deployment/prod');
