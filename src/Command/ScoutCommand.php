<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class ScoutCommand extends Command
{
    public $commands = [];

    protected function addProjectOption()
    {
        $this->addOption('project-path', 'i', InputOption::VALUE_REQUIRED, 'Path to project. The current directory and its parents are searched when this option is not specified.');

        return $this;
    }

    // TODO: Why do we have resolveproject and resolveinstance
    protected function resolveProject(InputInterface $input)
    {
        $originalPath = $input->getOption('project-path');
        if (!$originalPath = realpath($originalPath)) {
            throw new \Exception("Path '{$originalPath}' does not exist.");
        }

        // Search up the directory tree until we find a scout-project.json
        $pathElements = explode('/', $originalPath);
        do {
            $path = implode('/', $pathElements);
            $projectFile = "{$path}/scout-project.json";
            if (is_file($projectFile)) {
                break;
            }
            if (count($pathElements) == 1) {
                throw new \Exception("Could not find a scout project in '{$originalPath}' or any of its parent directories.");
            }
        } while (array_pop($pathElements));

        $this->path = $path;
        $this->name = end($pathElements);
        // create db name by replacing - with _ (debatable whether this is
        // better than just removing the dashes
        $this->dbName = str_replace('-', '_', $this->name);
        $this->project = json_decode(file_get_contents($projectFile));
    }

    protected function addInstanceOption()
    {
        $this->addOption('instance-path', 'i', InputOption::VALUE_REQUIRED, 'Path to instance. The current directory and its parents are searched when this option is not specified.');

        return $this;
    }
    protected function resolveInstance(InputInterface $input)
    {
        $originalPath = $input->getOption('instance-path');
        if (!$originalPath = realpath($originalPath)) {
            throw new \Exception("Path '{$originalPath}' does not exist.");
        }

        // Search up the directory tree until we find a scout-project.json
        $pathElements = explode('/', $originalPath);
        do {
            $path = implode('/', $pathElements);
            $instanceJsonFile = "{$path}/scout-instance.json";
            if (is_file($instanceJsonFile)) {
                break;
            }
            if (count($pathElements) == 1) {
                throw new \Exception("Could not find a scout instance in '{$originalPath}' or any of its parent directories.");
            }
        } while (array_pop($pathElements));

        $this->path = $path;
        $this->name = end($pathElements);
        // create db name by replacing - with _ (debatable whether this is
        // better than just removing the dashes
        $this->dbName = str_replace('-', '_', $this->name);
        $this->instance = json_decode(file_get_contents($instanceJsonFile));

        $this->civicrmInstalled = $this->isCiviCRMInstalled() == true;
    }

    protected function getContainer()
    {
        return $this->getApplication()->container;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->prepare($input, $output);

        foreach ($this->commands as $commands) {
            if ($input->getOption('dry-run')) {
                echo $commands;
            } else {
                // Creating and executing a temporary file with bash is one way
                // of avoiding the weirdness that happens when PHP tries to
                // execute external scripts (details of weirdness lost to the
                // sands of time).
                $tempFile = tempnam('/tmp', 'scout_');
                file_put_contents($tempFile, $commands);
                passthru('bash '.$tempFile);
                unlink($tempFile);
            }
        }
        if (method_exists($this, 'report')) {
            $this->report($input, $output);
        }
    }

    /**
     * Prepares sets of commands ($this->commands) to be executed.
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    abstract protected function prepare(InputInterface $input, OutputInterface $output);

    protected function subCommand($command, $args, $output)
    {
        $command = $this->getApplication()->find('cache-civicrm');
        $command->run($args, $output);
    }

    protected function getLatestCiviCRMVersion()
    {
        // Cache the latest version of CiviCRM for 1 hour
        $latestCache = $this->getApplication()->config->get('cache_path').'/latest-civicrm-version.txt';
        if (time() - filemtime($latestCache) < 60 * 60) {
            return file_get_contents($latestCache);
        } else {
            // See if you can retreive the latest version from the web
            $latest = file_get_contents('https://latest.civicrm.org/stable.php');
            if (!$latest) {
              $latest = file_get_contents($latestCache);
            }
            if (!$latest) {
              throw new \Exception('Unable to determine the latest version of CiviCRM');
            }
            return $latest;
        }
    }

    protected function updateInstanceJson(InputInterface $input){
        if (!$input->getOption('dry-run')) {
          return file_put_contents("{$this->path}/scout-instance.json", json_encode($this->instance, JSON_PRETTY_PRINT));
        }else{
          echo "Would update {$this->path}/scout-instance.json to the following:\n".json_encode($this->instance, JSON_PRETTY_PRINT);
        }
    }

    protected function getOrigin($originName){
        if(!count($this->instance->origins)){
            throw new \Exception("No origins defined.");
        }
        foreach($this->instance->origins as $origin){
            if($origin->name == $originName){
                return $origin;
            }
        }
        throw new \Exception("Could not find origin '{$originName}'.");
    }

    protected function isCiviCRMInstalled(){
        return realpath("$this->path/sites/default/civicrm.settings.php");
    }
}
