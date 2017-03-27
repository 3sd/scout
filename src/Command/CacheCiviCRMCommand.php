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

        ->addArgument('version', InputArgument::OPTIONAL, 'Version of CiviCRM you would like to download.', file_get_contents('http://latest.civicrm.org/stable.php'))

        ;
    }

    protected function prepare(InputInterface $input, OutputInterface $output)
    {
        $fs = $this->getContainer()->get('fs');
        $files = $this->getApplication()->config['civicrm_source_files'];
        $missingFiles = [];
        foreach ($files as $file) {
            if (!$fs->exists($this->getApplication()->config['cache_path']."/civicrm-{$input->getArgument('version')}-{$file}")) {
                $missingFiles[] = $file;
            }
        }
        if ($missingFiles) {
            $twig = $this->getContainer()->get('twig');
            $template = $twig->load('CacheCiviCRM.twig');
            $this->commands[] = $template->render([
                'version' => $input->getArgument('version'),
                'missingFiles' => $missingFiles,
                'config' => $this->getApplication()->config,
            ]);
        }
    }
}
