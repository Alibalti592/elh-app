<?php

namespace App\Controller\Admin;

use App\Entity\Mail;
use App\Services\AWSEmailService;
use App\Services\CRUDService;
use App\Services\EmailCustomService;
use App\Services\UtilsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminEmailController extends AbstractController
{

    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly UtilsService $utilsService,
                                private readonly CRUDService $CRUDService, private readonly EmailCustomService $emailCustomService, private readonly AWSEmailService $AWSEmailService) {}

    #[Route('/admin/email', name: 'admin_email_list')]
    public function index(): Response
    {
        return $this->render('admin/modules/email/list.twig', [

        ]);
    }

    #[Route('/v-load-list-emails')]
    public function loadList(Request $request): Response
    {
        $crudParams = $this->CRUDService->getListParametersFromRequest($request);
        $emails = $this->entityManager->getRepository(Mail::class)->findListFiltered($crudParams);
        $count = $this->entityManager->getRepository(Mail::class)->countListFiltered($crudParams);
        $emailUIs = [];
        /** @var Mail $email */
        foreach ($emails as $email) {
            $emailUIs[] = [
                'id' => $email->getId(),
                'mailkey' => $email->getMailkey(),
                'name' => $email->getName(),
                'subject' => $email->getSubject(),
                'variables' => !is_null($email->getVariables()) ? implode(' , ', $email->getVariables()) : null,
                'content' => $this->utilsService->htmlDecode($email->getContent()),
            ];
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'emails' => $emailUIs,
            'totalItems' => $count,
        ]);
        return $jsonResponse;
    }

    #[Route('/v-save-email', methods: ['POST'])]
    public function saveMail(Request $request): Response
    {
        $emailDatas = json_decode($request->get('email'), true);
        if(!is_null($emailDatas['id'])) {
            $email = $this->entityManager->getRepository(Mail::class)->findOneBy([
                'id' =>  $emailDatas['id']
            ]);
            if(is_null($email)) {
                throw new \ErrorException("Mail introuvable");
            }
        } else {
            $email = new Email();
        }
        $content = $this->utilsService->htmlEncodeBeforeSave($emailDatas['content']);
        $email->setSubject($emailDatas['subject']);
        $email->setContent($content);
        $this->entityManager->persist($email);
        $this->entityManager->flush();
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData();
        return $jsonResponse;
    }


    #[Route('/v-load-email-content')]
    public function preview(Request $request): Response
    {
        $maillKey = $request->get('mailkey');
        $mailDatas = $this->emailCustomService->getMailContent($maillKey, $this->getUser(), []);
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            "mailContent" => $mailDatas['body'],
            "mailTitle" => $mailDatas['subject']
        ]);
        return $jsonResponse;
    }

    #[Route('/admin-ini-email')]
    public function iniEmail(): Response
    {
        $emailKey = 'reset-password';
        $email = $this->entityManager->getRepository(Mail::class)->findOneBy([
            'mailkey' => $emailKey
        ]);
        if(is_null($email)) {
            $email = new Mail();
            $email->setContent('Vous avez fait une demande de mot de passe, voici votre de code de validation {code}');
            $email->setMailkey($emailKey);
            $email->setName('Réinitialiser mon mot de passe !');
            $email->setSubject('Réinitialiser mon mot de passe sur Muslim Connect!');
            $email->setVariables(['{nom}', '{prenom}', '{code}']);
            $this->entityManager->persist($email);
            $this->entityManager->flush();
        }
       $jsonResponse = new JsonResponse();
       $jsonResponse->setData([]);
       return $jsonResponse;

    }


    #[Route('/admin-test-envoi')]
    public function test(): Response
    {
        $to = 'contact@muslim-connect.fr';
        $mailDatas = $this->emailCustomService->getMailContent('mail-test', $this->getUser(), []);
        $this->AWSEmailService->addEmailToQueue(null, $to, null, 'test envoi ', $mailDatas['body'], 'Html');
        die('ok');
        if(!is_null($mailInfos)) {
            $body = $this->templating->render('layout/mail-template.twig', array(
                'appName' => 'Elh',
                'appURl' => false,
                'title' => $mailInfos->getSubject(),
                'shortDesc' => $shortDesc,
                'text' => $this->utilsService->htmlDecode($mailInfos->getContent()),
                'action' => false,
                'actionText' => ""
            ));
        }

    }
}
