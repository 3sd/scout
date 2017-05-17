<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ChoiceQuestion;

class GenerateCommand extends ScoutCommand
{
    private $files = [
      'project.json',
      'instance.json',
      'vhost',
      'cron',
    ];

    protected function configure()
    {
        $this
        // the name of the command (the part after "bin/console")
        ->setName('generate')

        ->addArgument('file')
        // the short description shown while running "php bin/console list"
        ->setDescription('Creates various files for consumption by scout.')
        ->addProjectOption()
        ->addOption('full-name', null, InputOption::VALUE_REQUIRED)
        ->addOption('domain', null, InputOption::VALUE_REQUIRED);
        // the full command description shown when running the command with
        // the "--help" option
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->resolveProject($input);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getArgument('file')) {
            $helper = $this->getHelper('question');
            $question = new ChoiceQuestion(
          'What file would you like to generate?',
          $this->files
        );
            $question->setErrorMessage('Please select a file from the list.');

            $file = $helper->ask($input, $output, $question);
            $input->setArgument('file', $file);
        }

        switch ($input->getArgument('file')) {

            case 'project.json':{
                $this->generateProjectJson($input->getOption('full-name'));
                break;
            }

            case 'instance.json':{
                $this->generateInstanceJson($input->getOption('domain'));
                break;
            }
            case 'vhost':{
                $this->generateVHost($input->getOption('domain'));
                break;
            }
            case 'cron':{
                $this->generateCron();
                break;
            }
            default:
                throw new \Exception("Unknown file '{$input->getArgument('file')}'");
        }
    }

    public function generateProjectJson($fullName)
    {
        $json['contacts'][] = [
            'name' => $this->getApplication()->config->get('git_name'),
            'email' => $this->getApplication()->config->get('git_email'),
            'roles' => ['System administrator'],
        ];

        if ($fullName) {
            $json['full-name'] = $fullName;
        }

        echo json_encode($json, JSON_PRETTY_PRINT);
    }
    public function generateInstanceJson($domain)
    {
        $json['domain'] = $domain;
        $json['origins'] = [];

        echo json_encode($json, JSON_PRETTY_PRINT);
    }
    public function generateVHost($domain)
    {
        if (!$domain) {
            $domain = $this->name;
        }

        $twig = $this->getContainer()->get('twig');
        $template = $twig->load('nginx.conf.twig');
        echo $template->render([
            'path' => $this->path,
            'domain' => $this->name,
            'fastcgi_pass' => $this->getApplication()->config->get('fastcgi_pass'),
        ]);
    }
    public function generateCron()
    {
        $twig = $this->getContainer()->get('twig');
        $template = $twig->load('cron.twig');
        echo $template->render([
            'name' => $this->path,
            'whoami' => $this->getApplication()->config->get('whoami'),
        ]);
    }
}
