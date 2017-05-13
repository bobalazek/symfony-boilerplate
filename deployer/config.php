<?php

namespace Deployer;

set('timezone', 'Europe/Berlin');

set('ssh_type', 'native');
set('ssh_multiplexing', true);

set('repository', 'git@github.com:bobalazek/symfony-boilerplate.git');
set('default_stage', 'production');
set('BOWER_TOKEN', '17c512d0e630fad6d10bb34c3d4183d6daa5e24c');

set('shared_dirs', ['var/logs', 'var/sessions', 'web/assets/uploads']);
set('writable_dirs', ['var/cache', 'var/logs', 'var/sessions', 'web/assets/uploads']);

set('dump_assets', true);

// Symfony deployment notification config
set('scheme', 'https');
set('base_url', '');

// We have removed the "--no-dev" flag here (temporary, until the production release),
// because we will manually update the database schema via the SSH & NOT use migrations (for now!).
set('composer_options', '{{composer_action}} --verbose --prefer-dist --no-progress --no-interaction --optimize-autoloader');
