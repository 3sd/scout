<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Yaml\Dumper;

class DumpCommand extends ScoutCommand
{
    protected function configure()
    {
        $this
        // the name of the command (the part after "bin/console")
        ->setName('dump')

        ->addArgument('file')
        // the short description shown while running "php bin/console list"
        ->setDescription('Dumps various files.')

        ->addOption('full-name', null, InputOption::VALUE_REQUIRED)
        // the full command description shown when running the command with
        // the "--help" option
    ;
    }

    function execute(InputInterface $input, OutputInterface $output)
    {
        switch($input->getArgument('file')){

            case 'project.yaml':{
                $this->dumpProjectYaml($input->getOption('full-name'));
                break;
            }
        }
    }

    function dumpProjectYaml($fullName){
        $dumper = new Dumper;
        echo $dumper->dump([
          'full-name' => $fullName,
          'contacts' => [[
              'name' => trim(`git config --get user.name`),
              'email' => trim(`git config --get user.email`),
              'role' => 'sysadmin'
          ]]

        ], 3);
    }
}
