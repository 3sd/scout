<?php

namespace Command;

use Symfony\Component\Console\Command\Command;

class UpgradeCommand extends ScoutCommand
{
    protected function configure()
    {
        $this
        // the name of the command (the part after "bin/console")
        ->setName('update')

        // the short description shown while running "php bin/console list"
        ->setDescription('Downloads the latest version of CiviCRM and the host CMS and performs any necessary database updates.')

        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('This command allows you to create a user...')
    ;
    }
}
