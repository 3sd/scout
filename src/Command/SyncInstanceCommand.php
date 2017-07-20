<?php

namespace Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class SyncInstanceCommand extends ScoutCommand
{
    protected function configure()
    {
        $this
        // the name of the command (the part after "bin/console")
        ->setName('sync-instance')

        ->addArgument('origin', InputArgument::REQUIRED, "An origin, as defined in the this intstances 'scout-instance.json' file.")

        ->addInstanceOption()

        ->setDescription('git pull on a local or an origin');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->resolveInstance($input);
    }

    protected function prepare(InputInterface $input, OutputInterface $output)
    {

        $origin = $this->getOrigin($input->getArgument('origin'));
        $sourceParts =explode(':', $origin->source);
        $remoteHost = $sourceParts[0];
        $remotePath = $sourceParts[1];

        // TODO implement `scout status --is-instance` command which returns 1 if no instance is found
        exec("ssh {$remoteHost} stat {$remotePath}/scout-instance.json", $output, $return_var);
        if($return_var != 0){
            throw new \Exception ("Could not find origin: {$origin->source} (looking for {$remoteHost}:{$remotePath}/scout-instance.json)");
        }

        $twig = $this->getContainer()->get('twig');
        $template = $twig->load('SyncInstance.twig');
        $this->commands[] = $template->render([
            'name' => $this->name,
            'db_name' => $this->dbName,
            'path' => $this->path,
            'remote_host' => $remoteHost,
            'remote_path' => $remotePath,
            'civicrm_installed' => $this->civicrmInstalled,
            'config' => $this->getApplication()->config->getAll()
        ]);

    }
}
