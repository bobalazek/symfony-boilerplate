<?php

namespace Deployer;

require 'recipe/symfony3.php';

include dirname(__FILE__).'/deployer/config.php';
include dirname(__FILE__).'/deployer/tasks.php';
include dirname(__FILE__).'/deployer/hosts.php';

// Include the local stuff if present
if (file_exists(dirname(__FILE__).'/local.php')) {
    include dirname(__FILE__).'/deployer/local.php';
}
