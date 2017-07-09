<?php

namespace Deployer;

localhost('local_server')
    ->stage('dev')
    ->set('branch', 'master')
    ->set('env', 'dev')
    ->set('deploy_path', '/var/www/html/deployment/symfony-boilerplate');
