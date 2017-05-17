<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DumpInstanceCommand extends ScoutCommand
{
    protected function configure()
    {
        $this
        // the name of the command (the part after "bin/console")
        ->setName('dump-instance')

        ->addInstanceOption()

        ->setDescription('Create a database dump for this instance');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->resolveInstance($input);

    }

    protected function prepare(InputInterface $input, OutputInterface $output)
    {

        $this->dumpCiviCRM = realpath("{$this->path}/sites/default/civicrm.settings.php");

        $twig = $this->getContainer()->get('twig');
        $template = $twig->load('DumpInstance.twig');
        $this->commands[] = $template->render([
            'name' => $this->name,
            'dump_civicrm' => $this->dumpCiviCRM,
            'config' => $this->getApplication()->config->getAll()
        ]);
    }

}
