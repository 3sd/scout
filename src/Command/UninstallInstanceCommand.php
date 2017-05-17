<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UninstallInstanceCommand extends ScoutCommand
{
    protected function configure()
    {
        $this
        // the name of the command (the part after "bin/console")
        ->setName('uninstall-instance')

        ->addInstanceOption()

        ->setDescription('Uninstall an installed Scout instance');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->resolveInstance($input);
    }

    protected function prepare(InputInterface $input, OutputInterface $output)
    {

        $twig = $this->getContainer()->get('twig');
        $template = $twig->load('UninstallInstance.twig');
        $this->commands[] = $template->render([
            'name' => $this->name,
            'path' => $this->path,
        ]);
    }

    function generatePassword(){
        $chars = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
        $password = '';
        while(strlen($password) < 16){
            $password .= $chars[array_rand($chars)];
        }
        return $password;
    }
}
