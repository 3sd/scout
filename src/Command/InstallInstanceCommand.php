<?php

namespace Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class InstallInstanceCommand extends ScoutCommand
{
    protected function configure()
    {
        $this
        // the name of the command (the part after "bin/console")
        ->setName('install-instance')

        ->addOption('domain', null, InputOption::VALUE_REQUIRED, "Domain for this instance (defaults to http://<project-name>)")
        ->addOption('no-checks', 'N', InputOption::VALUE_NONE, "Don't check requirements. Also sets --dry-run")

        ->addProjectOption()

        ->setDescription('Install a Scout instance');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->resolveProject($input);

        if(!$input->getOption('domain')){
            $input->setOption('domain', "{$this->name}");
        }
        if($input->getOption('no-checks')){
            $input->setOption('dry-run', true);
        }
    }

    protected function prepare(InputInterface $input, OutputInterface $output)
    {

        // A variable to decide whether we should install CiviCRM
        $this->installCiviCRM = realpath("{$this->path}/sites/all/modules/civicrm");

        $dbsToCreate[] = "{$this->dbName}_drupal";
        if($this->installCiviCRM){
            $dbsToCreate[] = "{$this->dbName}_civicrm";
        }

        if(!$input->getOption('no-checks')){

            // Check that settings files do not exist
            // TODO create a scout subfolder for all scout managed sites
            $vhostFilesToCreate = ["/etc/nginx/sites/{$this->name}"];
            $existingvhostFiles = [];

            foreach($vhostFilesToCreate as $vhostFile){
                if(realpath($vhostFile)){
                    $existingvhostFiles[] = $vhostFile;
                }
            }
            if(count($existingvhostFiles) == 1){
                throw new \Exception( "Cannot proceed with install. The following vhost already exists: ".implode(', ', $existingvhostFiles).'.');
            }elseif(count($existingvhostFiles) > 1){
                throw new \Exception( "Cannot proceed with install. The following vhosts already exist: ".implode(', ', $existingvhostFiles).'.');
            }

            $settingsFilesToCreate = ["{$this->path}/sites/default/civicrm.settings.php", "{$this->path}/sites/default/settings.php"];
            $existingSettingsFiles = [];

            foreach($settingsFilesToCreate as $settingFile){
                if(realpath($settingFile)){
                    $existingSettingsFiles[] = $settingFile;
                }
            }
            if(count($existingSettingsFiles) == 1){
                throw new \Exception( "Cannot proceed with install. The following settings file already exists: ".implode(', ', $existingSettingsFiles).'.');
            }elseif(count($existingSettingsFiles) > 1){
                throw new \Exception( "Cannot proceed with install. The following settings files already exist: ".implode(', ', $existingSettingsFiles).'.');
            }

            if($this->installCiviCRM){
                $civiCRMExtensionsDir = "{$this->path}/sites/all/civicrm_extensions";
                if(!realpath($civiCRMExtensionsDir)){
                    throw new \Exception( "Cannot proceed with install. CiviCRM extensions directory ($civiCRMExtensionsDir) does not exist.");
                }
            }

            // Check that the databases do not already exist
            $existingDbs = [];
            $mysql = $this->getContainer()->get('mysql');
            $result = $mysql->query("SHOW DATABASES LIKE '{$this->dbName}_%'");
            foreach($result->fetch_all() as $db){
                if(in_array($db[0], $dbsToCreate)){
                    $existingDbs[] = $db[0];
                }
            }
            if(count($existingDbs) == 1){
                throw new \Exception( "Cannot proceed with install. The following database already exists: ".implode(', ', $existingDbs).'.');
            }elseif(count($existingDbs) > 1){
                throw new \Exception( "Cannot proceed with install. The following databases already exist: ".implode(', ', $existingDbs).'.');
            }
        }

        $twig = $this->getContainer()->get('twig');
        $template = $twig->load('InstallInstance.twig');
        $this->commands[] = $template->render([
            'path' => $this->path,
            'name' => $this->name,
            'db_name' => $this->dbName,
            'install_civicrm' => $this->installCiviCRM,
            'project' => $this->project,
            'domain' => $input->getOption('domain'),
            'mysql_password' => $this->generatePassword(),
            'user_password' => $this->generatePassword(),
            'dbs_to_create' => $dbsToCreate,
            'config' => $this->getApplication()->config->getAll(),
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
