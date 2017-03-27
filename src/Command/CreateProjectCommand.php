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

        ->addArgument('name|path', InputArgument::REQUIRED, "name: Project name (when it consists of only lowercase alphanumeric and '-')\npath: A non existing path (when it starts with '/' or './').")

        ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Name of project is based on the value of <name|dir>. It can be overridden if desired.')

        // TODO allow specifying a major version 4.x, minor version 4.7, or revision 4.7.17.
        ->addOption('civicrm-version', null, InputOption::VALUE_REQUIRED, 'Version of CiviCRM you would like to use.', file_get_contents('http://latest.civicrm.org/stable.php'))
        ->addOption('full-name', null, InputOption::VALUE_REQUIRED, 'Full name for this project.', '<name>')
        ->addOption('cms', null, InputOption::VALUE_REQUIRED, 'The CMS you would like to use (currently only Drupal is supported)', 'Drupal');

      $help[] = "If <info>name|path</info> consist of only lowercase alphanumeric chararcters and '-', Scout will attempt to create a directory for the project with this name in the instance directory.";
      $help[] = "If <info>name|path</info> starts with a '/' or './', i.e it looks like a path, Scout will attempt to create a directory for the project using the path.";

      $this->setDescription("Create a new Scout project");
      $this->setHelp("\n".implode("\n\n", $help)."\n");
    }

    protected function initialize(InputInterface $input){
        if($input->getOption('full-name')=='<name>'){
            $input->setOption('full-name', '');
        }

    }

    protected function prepare(InputInterface $input, OutputInterface $output)
    {

      $fs = $this->getContainer()->get('fs');
      // If name|path is a single alphanumeric with hyphens
      if(preg_match("/^[0-9a-z\-]+$/", $input->getArgument('name|path'))){
        // create the project in the instance_dir
        $path = $this->getApplication()->config['instance_path'] . DIRECTORY_SEPARATOR . $input->getArgument('name|path');
        // with the name set to name|path
        $name = $input->getArgument('name|path');
      // If name|path looks like a path
      }elseif(preg_match("/^(\.\/|\/)/", $input->getArgument('name|path'))){
        // check the parent of the path exists
        $path = rtrim($input->getArgument('name|path'), '/');
        preg_match("/(.*)\/.*/", $path, $matches);
        $parent=$matches[1];
        if ($parent == ''){
          $parent = '/';
        }
        if(!$fs->exists($parent)){
          throw new \Exception("Cannot find parent directory {$parent}) for this project.");
        }
        // set the name to the last element of the path
        $name = array_pop(explode(DIRECTORY_SEPARATOR, $path));
      }else{
        throw new \Exception("Invalid name|path");

      }

      if($fs->exists($path)){
        throw new \Exception("Cannot create project in {$path}: path already exists.");
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
        'config' => $this->getApplication()->config
      ]);
    }

    protected function report(InputInterface $input, OutputInterface $output){

    }
}
