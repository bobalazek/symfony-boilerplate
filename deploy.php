<?php

namespace Deployer;

require 'recipe/symfony3.php';

/***** Config *****/
include dirname(__FILE__).'/deployer/config.php';

/***** Tasks *****/
include dirname(__FILE__).'/deployer/tasks.php';

/***** Servers *****/
serverList('deployer/servers.yml');
