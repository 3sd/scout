<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PullInstanceCommand extends ScoutCommand
{
    protected function configure()
    {
        $this
        // the name of the command (the part after "bin/console")
        ->setName('pull-instance')

        ->addArgument('origin', InputArgument::REQUIRED, "An origin, as defined in the this intstances 'scout-instance.json' file.")

        ->addInstanceOption()

        ->setDescription('synchronise database and files from a remote scout instance');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->resolveInstance($input);
    }

    protected function prepare(InputInterface $input, OutputInterface $output)
    {

        $origin = $this->getOrigin($input->getArgument('origin'));
        $sourceParts =explode(':', $origin->source);
        $remote_host = $sourceParts[0];
        $remote_path = $sourceParts[1];

        // TODO implement `scout status --is-instance` command which returns 1 if no instance is found
        exec("ssh {$remote_host} stat {$remote_path}/scout-instance.json", $output, $return_var);
        if($return_var != 0){
            throw new \Exception ("Could not find origin: {$origin->source}");
        }

        $twig = $this->getContainer()->get('twig');
        $template = $twig->load('PullInstance.twig');
        $this->commands[] = $template->render([
            'name' => $this->name,
            'path' => $this->path,
            'remote_host' => $remote_host,
            'remote_path' => $remote_path,
            'config' => $this->getApplication()->config->getAll()
        ]);

    }
}
