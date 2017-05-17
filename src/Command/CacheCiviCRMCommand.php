<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class CacheCiviCRMCommand extends ScoutCommand
{
    protected function configure()
    {
        $this
        // the name of the command (the part after "bin/console")
        ->setName('cache-civicrm')
        // the short description shown while running "php bin/console list"
        ->setDescription("Downloads source files for a version of CiviCRM to Scout's cache.")
        ->addArgument('version', InputArgument::OPTIONAL, 'Version of CiviCRM you would like to download.')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
      if(!$input->getArgument('version')){
        $input->setArgument('version', $this->getLatestCiviCRMVersion());
      }
    }

    protected function prepare(InputInterface $input, OutputInterface $output)
    {
        $fs = $this->getContainer()->get('fs');
        $files = $this->getApplication()->config->get('civicrm_source_files');
        $missingFiles = [];
        foreach ($files as $file) {
            if (!$fs->exists($this->getApplication()->config->get('cache_path')."/civicrm-{$input->getArgument('version')}-{$file}")) {
                $missingFiles[] = $file;
            }
        }
        if ($missingFiles) {
            $twig = $this->getContainer()->get('twig');
            $template = $twig->load('CacheCiviCRM.twig');
            $this->commands[] = $template->render([
                'version' => $input->getArgument('version'),
                'missingFiles' => $missingFiles,
                'config' => $this->getApplication()->config->getAll(),
            ]);
        }
    }
}
