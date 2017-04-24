# Blendist Server

## Server requirements

* [Symfony3 requirements](http://symfony.com/doc/current/reference/requirements.html)
* PHP 7.0 - minimum
* MySQL 5.5 - minimum
* Apache 2.4 - minimum
* Ubuntu 16.04
* SSH access
* Ability to install [NodeJS](https://nodejs.org/en/) for [Bower](https://bower.io/) & [Gulp](http://gulpjs.com/)
* Ability to set [VirtualHost(-s)](http://symfony.com/doc/current/setup/web_server_configuration.html) for Apache OR set the DocumentRoot via the server's admin panel (cPanel or similar)
* A SSL certificate

## Development

* Clone the repository: `git clone git@github.com:bobalazek/blendist-server.git`
* Navigate inside the project: `cd blendist-server`
* **Back-end**
  * Prepare a database for the project.
  * Install back-end dependencies with composer: `composer install` and enter your database parameters
  * Update the database schema: `php bin/console doctrine:schema:update --force`
  * Load the database fixtures: `php bin/console doctrine:fixtures:load` (default credentials are `bobalazek124@gmail.com:password`) OR `php bin/console hautelook:fixtures:load` (default credentials are `superadmin@app.com:password`) or
* **Front-end**
  * Install NPM dependencies: `npm install` (you'll need to have [NPM](https://www.npmjs.com/) installed)
  * Install front-end dependencies with bower: `bower install`
* (optional) You can run the app via a PHP server with: `php bin/console server:run`

## Deployment
**IMPORTANT:** It's SSH Key protected, so your public key will need to be added to the server, if you want to do the deployment or access the server.

* We use [Deployer](http://deployer.org/)
* Run `vendor/bin/dep deploy production`
* **(first time only)**
  * SSH to the server: `ssh root@123.123.123.123`
  * Set parameters for the application
    * Navigate to the project shared folder: `cd /var/www/html/deployment/production/shared/app/config`
    * Open the file with nano: `nano parameters.yml`
    * Edit the parameters & save the file
  * Load the fixtures
    * Navigate to the project current folder (which is just a symlink for the current project folder): `cd /var/www/html/deployment/production/current`
    * Run the fixtures: `php bin/console doctrine:fixtures:load`

## Server

* Should be the typical LAMP stack:
  * Ubuntu 16.04.2
  * Apache 2.4.18
    * After the setup, change the `DocumentRoot` (in `/etc/apache2/sites-available/000-default.conf` & `/etc/apache2/sites-available/default-ssl.conf`) to `/var/www/html/deployment/production/current/web`
    * Set the `AllowOverride` to `All` in the `/etc/apache2/apache2.conf` file
    * Enabled SSL (`sudo a2enmod ssl`)
    * Enabled Rewrite (`sudo a2enmod rewrite`)
    * Installed libapache2mod (`sudo apt-get install libapache2-mod-php`)
  * MySQL 5.7.17
  * PHP 7.0.13
    * Installed extensions: `sudo apt-get install php-cli php-mysql php-mcrypt php-curl php-zip php-gd`
  * phpMyAdmin 4.5.4
  * NodeJS 4.2.6 (note: after installing NodeJS, make an alias for it: `sudo ln -fs /usr/bin/nodejs /usr/local/bin/node`) & NPM
  * Bower (`npm install -g bower`)
  * GIT (`sudo apt-get install git`)
  * Other stuff: `sudo apt-get install zip unzip wget curl`

Commands
----------------
* Coding Standard fixes: `gulp csfix`
  * Fixes both front- and back-end files

## Tests

* You'll need to create a new database:
  * Name: `testdb`
  * User: `testdb`
  * Password: `testdb`
* You can simply run `gulp test` to execute both, phpunit tests & linting
  * PHPUnit: `php vendor/bin/simple-phpunit`
  * Linting: `gulp lint`, which basically just executes the following commands:
    * `php bin/console lint:yaml src`
    * `php bin/console lint:yaml app`
    * `php bin/console twig:yaml src`
    * `php bin/console twig:yaml app`

## Coding Standards

* **Back-end**
  * We use the [PHP-CS-Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer)
  * If you experience issues with missing **PhpCsFixer/Finder** class, then try to install it globally via composer: [PHP-CS-Fixer#globally-composer](https://github.com/FriendsOfPHP/PHP-CS-Fixer#globally-composer)
  * Run `php-cs-fixer fix` or `gulp csfix-backend`
* **Front-end**
  * We use [ESLint](https://github.com/adametry/gulp-eslint) (with the [jQuery ESLint Config](https://github.com/jquery/eslint-config-jquery))
  * Run `gulp csfix-frontend`

## Cron Jobs

* Email spool (in production): `* * * * * php /var/www/html/deployment/production/current/bin/console swiftmailer:spool:send --message-limit=10 --env=prod`

## License

Proprietary. All rights reserved.