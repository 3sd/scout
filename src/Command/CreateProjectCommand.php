<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Terminal;

class CreateProjectCommand extends ScoutCommand
{
    protected function configure()
    {
      $this
        // the name of the command (the part after "bin/console")
        ->setName('create-project')

        // the short description shown while running "php bin/console list"

        ->addArgument('path', InputArgument::REQUIRED, "Path to the root of the project. Will be created. Path should NOT exist. Parent of path SHOULD exist.")
        // TODO allow specifying a major version 4.x, minor version 4.7, or revision 4.7.17.
        ->addOption('civicrm-version', null, InputOption::VALUE_REQUIRED, 'Version of CiviCRM you would like to use.')
        ->addOption('full-name', null, InputOption::VALUE_REQUIRED, 'Full name for this project (defaults to the short name)')
        // ->addOption('cms', null, InputOption::VALUE_REQUIRED, 'The CMS you would like to use.')
        ->setDescription("Create a new Scout project")
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
    }

    protected function prepare(InputInterface $input, OutputInterface $output)
    {

      $path = rtrim($input->getArgument('path'), '/');

      $pathElements = explode('/', $path);

      // Name is always the last element of the path
      $name = array_pop($pathElements);
      if(!preg_match("/^[0-9a-z\-]+$/", $name)){
        throw new \Exception("'{$name}' is not a valid project name. Only lowercase alphanumeric and dash characters are allowed.");
      }

      $parentPath = implode('/', $pathElements);

      // If there were no other elements to the path, presume the parent path
      // is the current directory.
      if(strlen($parentPath) == 0){
        $parentPath = trim(`pwd`);
      }

      $absolutePath = realpath($parentPath);
      if(!$absolutePath){
        throw new \Exception("Cannot create project '{$name}' in '{$parentPath}'. '{$parentPath}' directory does not exist.");
      }elseif(!is_dir($absolutePath)){
        throw new \Exception("Cannot create project '{$name}' in '{$parentPath}'. '{$parentPath}' is not a directory.");
      }

      $path = "{$absolutePath}/{$name}";

      if(file_exists($path)){
        throw new \Exception("Cannot create project. '{$path}' already exists.");
      }

      // Download CiviCRM source files if necessary
      $this->subCommand('cache-civicrm', new ArrayInput(array('version' => $input->getOption('civicrm-version'))), $output);

      $twig = $this->getContainer()->get('twig');
      $template = $twig->load('CreateProject.twig');
      $this->commands[] = $template->render([
        'path' => $path,
        'name' => $name,
        'civicrm_version' => $input->getOption('civicrm-version'),
        'full_name' => $input->getOption('full-name'),
        'files' => ['drupal.tar.gz', 'l10n.tar.gz'],
        'config' => $this->getApplication()->config->getAll()
      ]);
    }

    protected function report(InputInterface $input, OutputInterface $output){

    }
}
