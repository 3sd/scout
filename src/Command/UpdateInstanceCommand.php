<?php

namespace Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateInstanceCommand extends ScoutCommand
{
    protected function configure()
    {
        $this
        // the name of the command (the part after "bin/console")
        ->setName('update-instance')
        // the short description shown while running "php bin/console list"
        ->setDescription('Downloads the latest version of CiviCRM and the host CMS and performs any necessary database updates.')
        ->addInstanceOption()

    ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->resolveInstance($input);
    }


    protected function prepare(InputInterface $input, OutputInterface $output)
    {
        if($this->civicrmInstalled){
          $civiVersion = $this->getLatestCiviCRMVersion();
        }

        $twig = $this->getContainer()->get('twig');
        $template = $twig->load('UpdateInstance.twig');
        $this->commands[] = $template->render([
          'path' => $this->path,
          'civicrm_installed' => $this->civicrmInstalled,
          'civicrm_version' => $this->getLatestCiviCRMVersion(),
          'config' => $this->getApplication()->config->getAll()
        ]);

    }
}
