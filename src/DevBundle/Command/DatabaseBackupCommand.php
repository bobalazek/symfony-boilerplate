<?php

namespace DevBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class DatabaseBackupCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:database:backup')
            ->setDescription(
                'Makes a backup of the current database.'
            )
            ->addOption(
                'dump-dir',
                'd',
                InputOption::VALUE_OPTIONAL,
                'In which directory should the backups be saved?',
                ''
            )
            ->addOption(
                'mysqldump-path',
                'm',
                InputOption::VALUE_OPTIONAL,
                'What is the path to the mysqldump binary?',
                ''
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dumpDirOption = $input->getOption('dump-dir');
        $mysqldumpPathOption = $input->getOption('mysqldump-path');
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();
        $connection = $em->getConnection();
        $driver = $connection->getDriver();

        $dumpDir = !empty($dumpDirOption)
            ? $dumpDirOption
            : $container->get('kernel')->getCacheDir().'/database_backups';
        if (false === @mkdir($dumpDir, 0777, true) && !is_dir($dumpDir)) {
            $output->writeln(
                '<error>'.
                sprintf(
                    'Unable to create the %s directory',
                    $dumpDir
                ).
                '</error>'
            );

            return false;
        }

        $fileName = (new \Datetime())->format('Y-m-d_H:i:s').'.sql';
        $filePath = $dumpDir.'/'.$fileName;

        if ($driver->getName() === 'pdo_mysql') {
            $database = $connection->getDatabase();
            $host = $connection->getHost();
            $port = $connection->getPort();
            $username = $connection->getUsername();
            $password = $connection->getPassword();

            $mysqldumpPath = 'mysqldump';

            // Check if mysqldump is available
            exec('which '.$mysqldumpPath, $mysqldumpCheckOutput, $mysqldumpCheckResult);
            if ($mysqldumpCheckResult !== 0) {
                // We are probably on MacOS
                $mysqldumpPath = !empty($mysqldumpPathOption)
                    ? $mysqldumpPathOption
                    : '/Applications/MAMP/Library/bin/mysqldump';

                exec('which '.$mysqldumpPath, $mysqldumpCheck2Output, $mysqldumpCheck2Result);

                if ($mysqldumpCheck2Result !== 0) {
                    $output->writeln(
                        '<error>No "mysqldump" found. Skipping backup ...</error>'
                    );

                    return false;
                }
            }

            $execCommand = sprintf(
                '(%s -u%s -p%s %s > %s) 2>&1',
                $mysqldumpPath,
                $username,
                $password,
                $database,
                $filePath
            );
            exec($execCommand, $mysqldumpOutput, $mysqldumpResult);

            if ($mysqldumpResult === 0) {
                $output->writeln(
                    '<info>'.
                    'The database backup was sucesfully created!'.
                    '</info>'
                );
            } else {
                $output->writeln(
                    '<error>'.
                    'Something went wrong.'.
                    'Error: '.
                    implode($mysqldumpOutput, "\n").
                    '</error>'
                );
            }
        } else {
            // TODO: implement other drivers

            $output->writeln(
                '<info>'.
                'This database driver is not supported! Skipping database backup ...'.
                '</info>'
            );
        }
    }
}
