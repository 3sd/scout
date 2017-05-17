<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class OriginCommand extends ScoutCommand
{
    protected function configure()
    {
        $this
        // the name of the command (the part after "bin/console")
        ->setName('origin')

        ->addArgument('action', InputArgument::REQUIRED, "'add' or 'remove', to add or remove an origin.")
        ->addArgument('name', InputArgument::REQUIRED, "An origin, as defined in the this intstances 'scout-instance.json' file.")
        ->addArgument('source', InputArgument::OPTIONAL, "An origin, as defined in the this intstances 'scout-instance.json' file.")
        ->addInstanceOption()
        ->setDescription('synchronise database and files from a remote scout instance');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->resolveInstance($input);
    }

    protected function prepare(InputInterface $input, OutputInterface $output)
    {

        // $path = $input->getArgument('path');
        // $instanceFile = "{$path}/scout-instance.json";
        // // Check that a scout-project.json does not already exist
        // if (!realpath($instanceFile)) {
        //     throw new \Exception("Could not find a scout-instance.json file.");
        // }


        switch ($input->getArgument('action')) {

            case 'add':{
                $this->addOrigin($input, $output);
                break;
            }

            case 'rm':{
                $this->rmOrigin($input, $output);
                break;
            }
        }
    }

    private function addOrigin(InputInterface $input, OutputInterface $output)
    {
        if(!$input->getArgument('source')){
          throw new \Exception("You must specify a source when adding an origin");
        }

        $input->getOption('instance-path');

        foreach($this->instance->origins as $origin){
          if($origin->name == $input->getArgument('name')){
            throw new \Exception("Origin '{$input->getArgument('name')}' already exists.");
          }
        }
        $this->instance->origins[] = [
          'name' => $input->getArgument('name'),
          'source' => $input->getArgument('source'),
        ];

        $this->updateInstanceJson($input);

        // $origins = $this->getOrigins($input->getOption('instance-path'));

    }
}
