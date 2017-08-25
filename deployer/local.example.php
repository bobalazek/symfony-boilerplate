<?php

namespace Deployer;

localhost('local_server')
    ->stage('dev')
    ->set('branch', 'master')
    ->set('deploy_path', '/var/www/html/deployment/symfony-boilerplate')
    ->set('symfony_env', 'dev')
;
