<?php

namespace Deployer;

// Make sure that stuff works before you push it to the server!
task('test', function () {
    // TODO: maybe prepend the {{bin/php}}?
    runLocally('{{env_vars}} vendor/bin/simple-phpunit');
    runLocally('{{env_vars}} bin/console lint:yaml src');
    runLocally('{{env_vars}} bin/console lint:yaml app');
    runLocally('{{env_vars}} bin/console lint:twig src');
    runLocally('{{env_vars}} bin/console lint:twig app');
})->desc('Running tests before the deployment');
before('deploy', 'test');

/*** Bower vendors ***/
task('deploy:vendors_bower', function () {
    $command = 'export BOWER_TOKEN='.get('BOWER_TOKEN').' && '.
        'bower --allow-root login -t '.get('BOWER_TOKEN').' &&  '.
        'bower --allow-root install';
    run('cd {{release_path}} && '.$command);
})->desc('Installing bower vendors');
after('deploy:vendors', 'deploy:vendors_bower');

/*** Database backup ***/
task('database:backup', function () {
    run('{{env_vars}} {{bin/php}} {{bin/console}} app:database:backup');
})->desc('Backing up the current database');
before('database:migrate', 'database:backup');

/*** Doctrine schema update ***/
// At least until we don't have a stable production version.
// From there on on, we shall better use only doctrine migrations
//   (see the "database:migrate" task).
task('database:schema_update', function () {
    run('{{env_vars}} {{bin/php}} {{bin/console}} doctrine:schema:update --force');
})->desc('Updating database schema');
after('database:migrate', 'database:schema_update');

/*** Migrate database ***/
before('deploy:symlink', 'database:migrate');

/*** Notify success ***/
task('notify:success', function () {
    $host = host(get('hostname'));
    $stage = get('stage');
    $serverName = $host->getHostname();
    $serverHost = $host->getConfig()->has('host')
        ? $host->getConfig()->get('host')
        : 'localhost';
    $branch = get('branch');
    $scheme = get('scheme');
    $baseUrl = get('base_url');

    $lastTag = '';
    $commitsSinceLastTag = '';
    try {
        $lastTag = trim(
            runLocally('git describe --tags --abbrev=0')->getOutput()
        );
        $commitsSinceLastTag = trim(
            runLocally('git log '.$lastTag.'..HEAD --oneline')->getOutput()
        );
    } catch (\Exception $e) {
    }

    $lastReleaseTime = '0000-00-00T00:00:00+00:00';
    if (has('previous_release')) {
        try {
            $lastReleaseTime = trim(
                run('find {{previous_release}} -maxdepth 0 -printf "%TY-%Tm-%Td %TH:%TM:%TS\n"')->getOutput()
            );
            $lastReleaseTimeExploded = explode('.', $lastReleaseTime);
            $lastReleaseTime = $lastReleaseTimeExploded[0];
            $lastReleaseTime = new \Datetime($lastReleaseTime);
            $offset = $lastReleaseTime->getOffset();
            $lastReleaseTime->add(new \DateInterval('PT'.$offset.'S'));
            $lastReleaseTime = $lastReleaseTime->format(\DateTime::ISO8601);
        } catch (\Exception $e) {
            // TODO: make it also work for non-unix systems
        }
    }

    $commitsSinceLastDeployment = trim(
        runLocally('git log --date=local --since="'.$lastReleaseTime.'" --oneline')->getOutput()
    );

    run('{{env_vars}} {{bin/php}} {{bin/console}} app:deployment:success '.
        '--stage="'.$stage.'" '.
        '--server-name="'.$serverName.'" '.
        '--server-host="'.$serverHost.'" '.
        '--branch="'.$branch.'" '.
        '--scheme="'.$scheme.'" '.
        '--base-url="'.$baseUrl.'" '.
        '--last-tag="'.$lastTag.'" '.
        '--last-release-time="'.$lastReleaseTime.'" '.
        '--commits-since-last-tag="'.escapeMultilineString($commitsSinceLastTag).'" '.
        '--commits-since-last-deployment="'.escapeMultilineString($commitsSinceLastDeployment).'"'
    );
})->desc('Notifying team about the successful deplyment');
after('success', 'notify:success');

/*** Unlock on failed deployment ***/
after('deploy:failed', 'deploy:unlock');

/********** Helpers **********/
function escapeMultilineString($string)
{
    return htmlspecialchars(json_encode(explode("\n", $string)));
}
