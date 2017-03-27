<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ScoutCommand extends Command
{
    public $commands = [];

    protected function getContainer()
    {
        return $this->getApplication()->container;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->prepare($input, $output);

        $helper = $this->getHelper('process');

        foreach ($this->commands as $commands) {
            $tempFile = tempnam('/tmp', 'scout_');
            file_put_contents($tempFile, $commands);
            passthru('bash '.$tempFile);
            unlink($tempFile);
        }
        if (method_exists($this, 'report')) {
            $this->report($input, $output);
        }
    }

    protected function subCommand($command, $args, $output)
    {
        $command = $this->getApplication()->find('cache-civicrm');
        $command->run($args, $output);
    }
}
