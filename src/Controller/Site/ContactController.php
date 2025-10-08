<?php
namespace App\Controller\Site;

use App\Entity\FaqCategory;
use App\Entity\Support;
use App\Form\SupportType;
use App\Services\ContactService;
use App\Model\ContactFormObject;
use App\Form\ContactSimpleType;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class ContactController extends AbstractController
{
    private $logger;
    private $contactService;
    public function __construct(LoggerInterface $logger, ContactService $contactService) {
        $this->logger = $logger;
        $this->contactService = $contactService;
    }


    #[Route('/contact', name: 'page_contact')]
    public function contactAction(Request $request) {
        //création du formulaire
        $contactObject = new ContactFormObject();
        $contactForm   = $this->createForm(ContactSimpleType::class, $contactObject);
        return $this->render('site/modules/contact/contact.twig', array(
            'form' => $contactForm->createView(),
            'captcha_key_public' => $this->getParameter('captcha_key_public')
        ));
    }



    #[Route('/contact-form-submit', name: 'contact_form_submit', methods: ['POST'])]
    public function contactSubmitAction(Request $request) {
        //création du formulaire
        $contactObject = new ContactFormObject();
        $contactForm   = $this->createForm(ContactSimpleType::class, $contactObject);
        $contactForm->handleRequest($request);
        //honeypot protection
        if(strlen($contactForm->get('phone')->getData()) > 0) {
            $this->logger->error('Tentative Spam CONTACT IP '. $request->getClientIp() .' '. $contactObject->getEmail());
            $jsonResponse =  new JsonResponse();
            $jsonResponse->setStatusCode(500);
            return $jsonResponse;
        }
        //vérification du captcha
        $verifCapatcha = $this->contactService->verifyCaptcha();
        if ($contactForm->isValid() && $verifCapatcha) {
            $data = $contactForm->getData();
            $this->contactService->sendMail(
                "Demande d'informations Muslim Connect de ".$contactObject->getName()." #". time(),
                "contact@muslim-connect.fr",
                $data->getContent(),
                $contactObject->getName(). '<contact@muslim-connect.fr>',
                $contactObject->getEmail()
            );
            //envoi des données JSON en front
            $response = new JsonResponse();
            $response->setStatusCode(200);
            return $response;
        } else {
            //form non valide
            $captchaMessage="";
            if(!$verifCapatcha) {
                $captchaMessage  = $this->contactService->getCaptchaErrorMessage($verifCapatcha);
            }
            //envoi des données d'erreurs JSON en front
            $response = new JsonResponse();
            $response->setStatusCode(500);
            $response->setData(array(
                'captchaMessage' => $captchaMessage
            ));
            return $response;
        }
    }
}
