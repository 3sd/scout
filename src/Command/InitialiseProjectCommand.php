<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class InitialiseProjectCommand extends ScoutCommand
{
    protected function configure()
    {
        $this
        // the name of the command (the part after "bin/console")
        ->setName('initialise-project')

        // the short description shown while running "php bin/console list"

        ->addArgument('path', InputArgument::OPTIONAL, 'Root path of the existing project.')
        // TODO allow specifying a major version 4.x, minor version 4.7, or revision 4.7.17.
        ->addOption('full-name', null, InputOption::VALUE_REQUIRED, 'Full name for this project (defaults to the short name)')
        // ->addOption('cms', null, InputOption::VALUE_REQUIRED, 'The CMS you would like to use.')
        ->setDescription('Create a project from existing code')
        ;
    }

    protected function initialize(InputInterface $input)
    {
        if (!$input->getArgument('path')) {
            $input->setArgument('path', trim(`pwd`));
        }
    }

    protected function prepare(InputInterface $input, OutputInterface $output)
    {

        $path = $input->getArgument('path');

        // Calculate name
        $pathElements = explode('/', $path);
        $name = array_pop($pathElements);
        if(!preg_match("/^[0-9a-z\-]+$/", $name)){
          throw new \Exception("'{$name}' is not a valid project name. Only lowercase alphanumeric and dash characters are allowed.");
        }

        // Check that a scout-project.json does not already exist
        $projectFile = "{$path}/scout-project.json";
        if (realpath($projectFile)) {
            throw new \Exception("scout-project.json already exists in {$path}");
        }

        // Check that a scout-instance.json does not already exist
        $instanceFile = "{$path}/scout-instance.json";
        if (realpath($instanceFile)) {
            throw new \Exception("scout-instance.json already exists in {$path}");
        }

        // TODO Create a git repo if one does not exist


        // Create a scout-project.json file
        $twig = $this->getContainer()->get('twig');
        $template = $twig->load('InitialiseProject.twig');
        $this->commands[] = $template->render([
            'path' => $path,
            'name' => $name,
            'full_name' => $input->getOption('full-name'),
        ]);
    }
}
