<?php

namespace Deployer;

require 'recipe/symfony3.php';

/***** Config *****/
include dirname(__FILE__).'/deployment/config.php';

/***** Tasks *****/
include dirname(__FILE__).'/deployment/tasks.php';

/***** Servers *****/
serverList('deployment/servers.yml');
