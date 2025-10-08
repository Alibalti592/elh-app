<?php
namespace App\Command;

use App\Services\AWSEmailService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SqsSendEmails extends Command
{
    //run it every hour ... 0 * * * *
    protected static $defaultName = 'app:sqssend:emails';
    private $AWSEmailService;

    public function __construct(AWSEmailService $AWSEmailService) {
        $this->AWSEmailService = $AWSEmailService;
        parent::__construct();
    }

    protected function configure() {

    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        set_time_limit ( 3558 ); //less than one hour, inefficace à priori
        //rappel des limiation SES 14 emails seconde et 50000 / 24h
        //TOUTE LES 20s => 10 messages toute les 20s = 30 messages par minutes => 1800 messages par heur au max ! (ok for now ..)
        // => 43200 messages toute les 24h max !
        //ne pas avoir trop de message par minutes permet de caler des messages prioritaires hors SQS !
        $startGlobalJob = time();
        while((time() - $startGlobalJob) < 3538) { //plus les 20s dans la boucle => 1h
            $startTime = time();
            $this->AWSEmailService->retrieveFromQueueAndSendEmails(); //envoi 10 messages max, temps max de la fonction 20 seconde + envoies
            $spendTime = time() - $startTime;
            if ($spendTime < 20) {
                sleep(20 - $spendTime);
            }
        }
        return Command::SUCCESS;
    }
}