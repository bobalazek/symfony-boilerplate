<?php

namespace Deployer;

host('qa_server')
    ->hostname('ltu1.corcosoft.com')
    ->user('root')
    ->port(22)
    ->forwardAgent(true)
    ->multiplexing(true)
    ->stage('qa')
    ->set('branch', 'master')
    ->set('deploy_path', '/var/www/projects/symfony-boilerplate/qa')
    ->set('symfony_env', 'qa')
;
