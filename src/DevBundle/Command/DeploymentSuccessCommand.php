<?php

namespace DevBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class DeploymentSuccessCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:deployment:success')
            ->setDescription(
                'Informs the team about the successful deployment.'
            )
            ->addOption(
                'stage',
                's',
                InputOption::VALUE_REQUIRED,
                'To which stage was it deployed?',
                'production'
            )
            ->addOption(
                'server-host',
                't',
                InputOption::VALUE_REQUIRED,
                'To which host was it deployed?',
                'localhost'
            )
            ->addOption(
                'server-name',
                'a',
                InputOption::VALUE_REQUIRED,
                'What is the name of the server that it was deployed to?',
                'production_server'
            )
            ->addOption(
                'branch',
                'c',
                InputOption::VALUE_REQUIRED,
                'From which GIT branch?',
                'master'
            )
            ->addOption(
                'scheme',
                'm',
                InputOption::VALUE_REQUIRED,
                'What scheme (http/https)?',
                'http'
            )
            ->addOption(
                'base-url',
                'b',
                InputOption::VALUE_OPTIONAL,
                'What is the base url of the app?',
                ''
            )
            ->addOption(
                'last-tag',
                'k',
                InputOption::VALUE_OPTIONAL,
                'Which is the last tag/release?',
                ''
            )
            ->addOption(
                'last-release-time',
                'r',
                InputOption::VALUE_OPTIONAL,
                'When was the last version deployed?',
                ''
            )
            ->addOption(
                'commits-since-last-tag',
                'l',
                InputOption::VALUE_OPTIONAL,
                'What commits have been made since the last tag/release?',
                ''
            )
            ->addOption(
                'commits-since-last-deployment',
                'd',
                InputOption::VALUE_OPTIONAL,
                'What commits have been made since the last deployment?',
                ''
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $version = $container->getParameter('version');
        $assetsVersion = $container->getParameter('assets_version');
        $emails = $container->getParameter('deployment_emails');
        $stage = $input->getOption('stage');
        $serverName = $input->getOption('server-name');
        $serverHost = $input->getOption('server-host');
        $scheme = $input->getOption('scheme');
        $baseUrl = $input->getOption('base-url');
        $branch = $input->getOption('branch');
        $lastTag = $input->getOption('last-tag');
        $lastReleaseTime = $input->getOption('last-release-time');
        $commitsSinceLastTag = json_decode(htmlspecialchars_decode(
            $input->getOption('commits-since-last-tag')
        ), true);
        $commitsSinceLastDeployment = json_decode(htmlspecialchars_decode(
            $input->getOption('commits-since-last-deployment')
        ), true);

        // Router context
        $context = $container->get('router')->getContext();
        $context->setHost($serverHost);
        $context->setScheme($scheme);
        $context->setBaseUrl($baseUrl);

        $container->get('app.mailer')
            ->swiftMessageInitializeAndSend([
                'subject' => $container->get('translator')->trans(
                    'deployment.email.subject',
                    [
                        '%app_name%' => $container->getParameter('app_name'),
                    ]
                ),
                'to' => $emails,
                'body' => 'AppBundle:Emails:Deployment/success.html.twig',
                'template_data' => [
                    'version' => $version,
                    'assets_version' => $assetsVersion,
                    'stage' => $stage,
                    'server_name' => $serverName,
                    'server_host' => $serverHost,
                    'branch' => $branch,
                    'last_tag' => $lastTag,
                    'last_release_time' => $lastReleaseTime,
                    'commits_since_last_tag' => $commitsSinceLastTag,
                    'commits_since_last_deployment' => $commitsSinceLastDeployment,
                ],
            ])
        ;

        $output->writeln('<info>The team successfully informed about the successful deployment!</info>');
    }
}
