<?php
namespace App\Services;

use App\Entity\Abonnement;
use App\Entity\CalEventFeedbackCoach;
use App\Entity\CalEventParticipate;
use App\Entity\CarteShare;
use App\Entity\Coaching\Customer;
use App\Entity\Coaching\CustomerManager;
use App\Entity\Invitation;
use App\Entity\Mail;
use App\Entity\MosqueNotifDece;
use App\Entity\Notification;
use App\Entity\Obligation;
use App\Entity\Pardon;
use App\Entity\PlanSell;
use App\Entity\Pompe;
use App\Entity\PompeNotification;
use App\Entity\ProgramEvent;
use App\Entity\ShopAbo;
use App\Entity\ShopOrder;
use App\Entity\Coaching\ChatBubble;
use App\Entity\SocialPost;
use App\Entity\SocialPostComment;
use App\Entity\SocialProfile;
use App\Entity\User;
use App\UIBuilder\CalEventUI;
use App\UIBuilder\CarteUI;
use App\UIBuilder\MosqueUI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class NotificationService {

    private $em;
    private $fcmNotificationService;
    private $AWSEmailService;

    CONST ADMIN_MAIL = 'elheidiapp@gmail.com';

    public function __construct(EntityManagerInterface $em, FcmNotificationService $fcmNotificationService,
                                AWSEmailService $AWSEmailService, CacheService $cacheService,
                                private readonly EmailCustomService $emailCustomService, private readonly MosqueUI $mosqueUI, private readonly CarteUI $carteUI) {
        $this->em = $em;
        $this->fcmNotificationService = $fcmNotificationService;
        $this->AWSEmailService = $AWSEmailService;
        $this->cacheService = $cacheService;
    }

    public function notifPompeFunebreNewDece(PompeNotification $pompeNotification) {
        $pompeUser = $pompeNotification->getPompe()->getManagedBy();
        if(!is_null($pompeUser)) {
            $dece = $pompeNotification->getDece();
            $fullname = $dece->getFirstname(). " ".$dece->getLastname();
            $to = $pompeUser;
            $title = "Demande de service à votre pompe funèbre";
            $message =  "Vous êtes contacté suite au décès de ".$fullname;
            $data['view'] = "pompe_noitif_view";
            $this->fcmNotificationService->sendFcmDefaultNotification($to, $title, $message, $data);
            //mail  {nom_dece} , {prenom_dece} , {lieu_dece}
            $lieu = 'lieu non défini';
            if(!is_null($dece->getLocation())) {
                $lieu = $dece->getLocation()->getLabel();
            }
            $variables = [
                'prenom_dece' => $dece->getFirstname(),
                'nom_dece' => $dece->getLastname(),
                'lieu_dece' => $lieu,
            ];
            $emailDatas = $this->emailCustomService->getMailContent('pompe_notification', $to,  $variables);
            if(!is_null($emailDatas)) {
                $this->AWSEmailService->addEmailToQueue(null, $to, null, $emailDatas['subject'], $emailDatas['body'], 'Html');
            }
        }
    }

    public function notifMosqueNewDece(MosqueNotifDece $mosqueNotifDece) {
        $mosqueUser = $mosqueNotifDece->getMosque()->getManagedBy();
        if(!is_null($mosqueUser)) {
            $dece = $mosqueNotifDece->getDece();
            $location = $dece->getLocation();
            if(!is_null($location)) {
                $fullname = $dece->getFirstname(). " ".$dece->getLastname();
                $to = $mosqueUser;
                $title = "Décès annoncé à proximité de votre mosqué";
                $lieu = $dece->getLocation()->getLabel();
                $message =  "Le décès de  ".$fullname." à ".$lieu." vient d'être annoncé";
                $data['view'] = "mosque_notif_view";
                $data['mosque'] = $this->mosqueUI->getMosque($mosqueNotifDece->getMosque());
                $this->fcmNotificationService->sendFcmDefaultNotification($to, $title, $message, $data);
                $variables = [
                    'prenom_dece' => $dece->getFirstname(),
                    'nom_dece' => $dece->getLastname(),
                    'lieu_dece' => $lieu,
                ];
                $emailDatas = $this->emailCustomService->getMailContent('mosque_notification', $to,  $variables);
                if(!is_null($emailDatas)) {
                    $this->AWSEmailService->addEmailToQueue(null, $to, null, $emailDatas['subject'], $emailDatas['body'], 'Html');
                }
            }
        }
    }

    public function notifForSharePardon(Pardon $pardon, User $userTarget) {
        $userName = $pardon->getFirstname()." ".$pardon->getLastname();
            $title = "Demande de pardon pour ".$pardon->getFirstname()." ".$pardon->getLastname();
        $message =  $userName. "vous envoie une demande de pardon";
        $data['view'] = "pardon_view";
        $this->fcmNotificationService->sendFcmDefaultNotification($userTarget, $title, $message, $data);
    }


    //reset password
    public function resetPassword(User $user, $code) {
        $to = $user->getEmail();
        $mailkey = 'reset-password';
        $variables = [
            'code' => $code
        ];
        $emailDatas = $this->emailCustomService->getMailContent($mailkey, $user,  $variables);
        if(!is_null($emailDatas)) {
            $this->AWSEmailService->addEmailToQueue(null, $to, null, $emailDatas['subject'], $emailDatas['body'], 'Html');
        }

    }

    //new relation
    public function notifForNewRelation(User $userSource, User $userTarget, $userUi, $thread) {
        $title = "Nouveau membre dans ta communauté";
        $message =  "Félicitations ! ".$userSource->getFullname()." a rejoint votre communauté.";
        $datas = [
            'view' => 'chatview',
            'threadId' => $thread->getId(),
            'userId' => $userSource->getId(),
            'userUI' => $userUi,
            'image' => $userUi['photo']
        ];
        $this->fcmNotificationService->sendFcmDefaultNotification($userTarget, $title, $message, $datas);
    }


    //notif carte death or maladie
    public function notifForCarte(CarteShare $carteShare) {
        $createdBy = $carteShare->getCarte()->getCreatedBy();
        $userTarget = $carteShare->getUser();
        $userName = $createdBy->getFirstname()." ".$createdBy->getLastname();
        $carte = $carteShare->getCarte();
        $title = "Cartes virtuelles reçues";
        $carteLabel = $this->carteUI->getTypeLabel($carte->getType());
        $message =  $userName. " vous envoie une carte de ".$carteLabel;
        $data['view'] = "carte_list_view";
        $data['carte'] = json_encode($this->carteUI->getCarte($carteShare->getCarte()));
        $this->fcmNotificationService->sendFcmDefaultNotification($userTarget, $title, $message, $data);
    }

    public function notifForNewObligation(Obligation $obligation) {
        $createdBy = $obligation->getCreatedBy();
        $userName = $createdBy->getFirstname()." ".$createdBy->getLastname();
        if(!is_null($obligation->getRelatedTo())) {
            $relatedTo = $obligation->getRelatedTo();
            $title = "Prêt partagée";
            $message =  "$userName a ajouté une dette";
            $type = $obligation->getType();
            if($type == 'onm') {
                $title = "Dette partagée";
                $message =  "$userName a ajouté un prêt";
            } elseif ($type == 'amana') {
                $title = "Amana partagée";
                $message =  "$userName a ajouté une amana";
            }
            $data['view'] = "obligation_list_view";
            if($type == 'jed') {
                $type = 'onm';
            } elseif ($type == 'onm') {
                $type = 'jed';
            }
            $data['type'] = $type;
            $this->fcmNotificationService->sendFcmDefaultNotification($relatedTo, $title, $message, $data);
        }
    }

    public function notifForObligationEchance(Obligation $obligation) {
        $createdBy = $obligation->getCreatedBy();
        $userName = $createdBy->getFirstname()." ".$createdBy->getLastname();
        $otherName = $obligation->getFirstname()." ".$obligation->getLastname();
        if(!is_null($obligation->getRelatedTo())) {
            $other = $obligation->getRelatedTo();
            $otherName = $other->getFirstname()." ".$other->getLastname();
        }
        $titleMessage = $this->getTitleMessageForObligationType($obligation->getType(), $userName, $otherName);
        $data['view'] = "obligation_list_view";
        $data['type'] = $obligation->getType();
        $this->fcmNotificationService->sendFcmDefaultNotification($createdBy, $titleMessage['title'], $titleMessage['message'], $data);
        if(!is_null($obligation->getRelatedTo())) {
            $type = $obligation->getType();
            if($type == 'onm') {
                $type = 'jed';
            } elseif ($type == 'jed') {
                $type = 'onm';
            }
            $data['type'] = $type;
            $titleMessage = $this->getTitleMessageForObligationType($type, $userName, $otherName);
            $this->fcmNotificationService->sendFcmDefaultNotification($obligation->getRelatedTo(), $titleMessage['title'], $titleMessage['message'],$data);
        }
    }

    public function getTitleMessageForObligationType($type, $userName, $otherName) {
        $title = "Le dette arrive à échéance";
        $message =  "La dette entre ".$userName. ' et '.$otherName .' arrive à échéance';
        if($type == 'onm') {
            $title = "Le prêt arrive à échéance";
            $message =  "Le prêt entre ".$userName. ' et '.$otherName .' arrive à échéance';
        } elseif ($type == 'amana') {
            $title = "La amana arrive à échéance";
            $message =  "La amana entre ".$userName. ' et '.$otherName .' arrive à échéance';
        }
        return [
            'title' => $title,
            'message' => $message,
        ];
    }


    public function notifInvitation(Invitation $invitation) {
        $fromUSer = $invitation->getCreatedBy();
        $fullname = $fromUSer->getFirstname(). " ".$fromUSer->getLastname();
        $to = $invitation->getEmail();
        //mail  {nom_dece} , {prenom_dece} , {lieu_dece}
        $variables = [
            'fromFullname' => $fullname,
            'toEmail' => $to,
        ];
        $emailDatas = $this->emailCustomService->getMailContent('invitation_mail', null,  $variables);
        if(!is_null($emailDatas)) {
            $this->AWSEmailService->addEmailToQueue(null, $to, null, $emailDatas['subject'], $emailDatas['body'], 'Html');
        }
    }


    public function notifRefundDette(User $currentUser, Obligation $obligation) {
        if(!is_null($obligation->getRelatedTo())) {
            $type = $obligation->getType();
            if($currentUser->getId() == $obligation->getRelatedTo()->getId()) {
                $userToNotif = $obligation->getCreatedBy();
            } else {
                $userToNotif = $obligation->getRelatedTo();
                if($type == 'jed') {
                    $type = 'onm';
                } elseif ($type == 'onm') {
                    $type = 'jed';
                }
            }
//            $amount = !is_null($obligation->getAmount()) ? $obligation->getAmount()."€" : null;
            $title = "Remboursement partagé";
            $message = $currentUser->getFullname(). " a notifié un remboursement";
            $data['view'] = "obligation_list_view";
            $data['type'] = $type;
            $data['tab'] = 'refund';
            $this->fcmNotificationService->sendFcmDefaultNotification($userToNotif, $title, $message, $data);
        }
    }

    public function notifNewPFAdmin(Pompe $pompe) {
        $pompeUser = $pompe->getManagedBy();
        if(!is_null($pompeUser)) {
            $variables = [
                'prenom' => $pompeUser->getFirstname(),
                'nom' => $pompeUser->getLastname(),
                'nom_pompe' => $pompe->getName(),
            ];
            $to = self::ADMIN_MAIL;
            $emailDatas = $this->emailCustomService->getMailContent('pompe_registration_admin', $pompeUser,  $variables);
            if(!is_null($emailDatas)) {
                $this->AWSEmailService->addEmailToQueue(null, $to, null, $emailDatas['subject'], $emailDatas['body'], 'Html');
            }
        }
    }

    public function notifNewPFValidationCompte(Pompe $pompe) {
        $pompeUser = $pompe->getManagedBy();
        if(!is_null($pompeUser)) {
            $variables = [
                'prenom' => $pompeUser->getFirstname(),
                'nom' => $pompeUser->getLastname(),
                'nom_pompe' => $pompe->getName(),
            ];
            $to = $pompeUser->getEmail();
            $emailDatas = $this->emailCustomService->getMailContent('pompe_registration_validation', $pompeUser,  $variables);
            if(!is_null($emailDatas)) {
                $this->AWSEmailService->addEmailToQueue(null, $to, null, $emailDatas['subject'], $emailDatas['body'], 'Html');
            }
        }
    }
}