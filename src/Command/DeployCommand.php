<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeployCommand extends Command
{
    protected static $defaultName = 'app:deploy';

    public function __construct() {
        parent::__construct();
    }

    protected function configure() {
        // ...
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        //cache : php bin/console cache:pool:clear cache.global_clearer
        $command = $this->getApplication()->find('cache:pool:clear');
        $arguments = [
            'pools' => ['cache.global_clearer']
        ];
        $greetInput = new ArrayInput($arguments);
        $command->run($greetInput, $output);

        //CLEAR CACHE APCU CACHE, apcu_clear_cache ne peut pas être executé à partir de CLi !
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL,$this->domaine."/priv-clear-cache-ghu");
//        curl_setopt($ch, CURLOPT_POST, 1); //POST
//        $parameters = [
//            'secret' => 'Hdsjre1qmd56Mpofp',
//        ];
//        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        $server_output = curl_exec($ch);
//        $output->writeln($server_output);
//        curl_close ($ch);

        //do a sudo apache2ctl graceful to be sure to clear all cache ...

        //upd database
        $command = $this->getApplication()->find('doctrine:migrations:migrate');
        $arguments = [
            '--no-interaction' => true
        ];
        $greetInput = new ArrayInput($arguments);
        $command->run($greetInput, $output);

        return Command::SUCCESS;
    }
}