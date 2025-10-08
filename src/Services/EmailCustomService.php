<?php
namespace App\Services;

use App\Entity\Mail;
use App\Entity\Shop;
use App\Entity\User;
use App\UIBuilder\Coaching\CustomerUI;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;

class EmailCustomService {

    public function __construct(private readonly UtilsService $utilsService, private readonly EntityManagerInterface $entityManager, Environment $templating){
        $this->templating = $templating;
    }

    public function setEmailFromUI($email, $emailDatas)
    {
        $content = $this->utilsService->htmlEncodeBeforeSave($emailDatas['content']);
        $email->setName($emailDatas['name']);
        $email->setVariables($emailDatas['variables']);
        $email->setSubject($emailDatas['subject']);
        $email->setContent($content);
    }


    public function getMailContent($mailkey, ?User $userTo, $variables, $actionURL = false, $actionText = '')
    {
        $mailInfos = $this->entityManager->getRepository(Mail::class)->findOneBy([
            'mailkey' => $mailkey
        ]);
        if(!is_null($mailInfos)) {
            $content = $this->utilsService->htmlDecode($mailInfos->getContent());
            $subject = $mailInfos->getSubject();
            if(!is_null($userTo)) {
                $variables['prenom'] =  $userTo->getFirstname();
                $variables['nom'] = $userTo->getLastname();
            }
            foreach ($variables as $strKey => $value) {
                $key ='{'.$strKey.'}';
                $content = str_replace($key, $value, $content);
                $subject = str_replace($key, $value, $subject);
            }
            //replace varibales
            $shortDesc = $this->utilsService->htmlCut($content);
            $shortDesc = strip_tags($shortDesc);
            $appURl = 'https://muslim-connect.fr/';
            if($actionURL) {
                $appURl = $actionURL;
            }
            $templateParams = array(
                'appURl' => $appURl,
                'title' => $subject,
                'shortDesc' => $shortDesc,
                'text' => $content,
                'action' => $actionURL,
                'actionText' => $actionText
            );
            return [
                'subject' => $subject,
                'body' => $this->templating->render('layout/mail-template.twig', $templateParams)
            ];
        } else {
            return null;
        }
    }

}