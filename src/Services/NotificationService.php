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
use App\Entity\NotifToSend;
use App\Entity\Notification;
use App\Entity\Obligation;
use App\Entity\Pardon;
use App\Entity\PlanSell;
use App\Entity\Pompe;
use App\Entity\PompeNotification;
use App\Entity\ProgramEvent;
use App\Entity\Salat;
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
            $this->createSentNotif($to, $title, $message, $data['view'], 'pompe', $data);
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
                $this->createSentNotif($to, $title, $message, $data['view'], 'mosque', $data);
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
        $this->createSentNotif($userTarget, $title, $message, $data['view'], 'pardon', $data);
        $this->fcmNotificationService->sendFcmDefaultNotification($userTarget, $title, $message, $data);
    }

    public function notifForNewSalatInFavoriteMosque(Salat $salat, User $userTarget): void
    {
        $mosque = $salat->getMosque();
        if (is_null($mosque)) {
            return;
        }

        $title = "Nous appartenons à Allah et c’est vers Lui que nous retournons. 🤲";
        $message = "🕌Une salât janaza annoncée dans votre mosquée\nQu’Allah pardonne au défunt et lui accorde sa miséricorde 🤲";
        $data = [
            'view' => 'mosque_notif_view',
            'mosque' => $this->mosqueUI->getMosque($mosque),
            'salatId' => $salat->getId(),
        ];

        $now = new \DateTimeImmutable('now');
        $ceremonyAt = $salat->getCeremonyAt();
        $scheduledAt = $ceremonyAt instanceof \DateTimeImmutable ? $ceremonyAt->modify('-24 hours') : $now;

        // If ceremony is in less than 24h, notify immediately.
        if ($scheduledAt <= $now) {
            $this->createSentNotif($userTarget, $title, $message, $data['view'], 'salat_mosque', $data);
            $this->fcmNotificationService->sendFcmDefaultNotification($userTarget, $title, $message, $data);
            return;
        }

        // Otherwise, queue notification to be sent 24h before ceremony.
        $notif = new NotifToSend();
        $notif->setUser($userTarget);
        $notif->setTitle($title);
        $notif->setMessage($message);
        $notif->setSendAt(\DateTime::createFromImmutable($scheduledAt));
        $notif->setType('salat_mosque');
        $notif->setView($data['view']);
        $notif->setDatas(json_encode($data, JSON_UNESCAPED_UNICODE));
        $notif->setStatus('pending');
        // Hidden from bell until command sends it.
        $notif->setIsRead(true);
        $this->em->persist($notif);
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
        $this->createSentNotif($userTarget, $title, $message, $datas['view'], 'relation', $datas);
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
        $this->createSentNotif($userTarget, $title, $message, $data['view'], 'carte', $data);
        $this->fcmNotificationService->sendFcmDefaultNotification($userTarget, $title, $message, $data);
    }

    public function notifForNewObligation(Obligation $obligation): void
    {
        $actor = $obligation->getCreatedBy();
        if ($actor instanceof User) {
            $this->sendObligationLifecycleNotification($actor, $obligation, 'create');
        }
    }

    public function notifForUpdateObligation(User $actor, Obligation $obligation): void
    {
        $this->sendObligationLifecycleNotification($actor, $obligation, 'update');
    }

    public function notifForDeleteObligation(User $actor, Obligation $obligation): void
    {
        $this->sendObligationLifecycleNotification($actor, $obligation, 'delete');
    }

    private function sendObligationLifecycleNotification(User $actor, Obligation $obligation, string $action): void
    {
        $createdBy = $obligation->getCreatedBy();
        $relatedTo = $obligation->getRelatedTo();

        if (!($createdBy instanceof User) || !($relatedTo instanceof User)) {
            return;
        }

        if ($actor->getId() === $createdBy->getId()) {
            $sendToUser = $relatedTo;
        } elseif ($actor->getId() === $relatedTo->getId()) {
            $sendToUser = $createdBy;
        } else {
            // Safety guard: actor must be part of this obligation
            return;
        }

        $typeForRecipient = $this->getObligationTypeForRecipient(
            $obligation->getType(),
            $sendToUser,
            $createdBy,
            $relatedTo
        );

        $label = $this->getObligationLabelForNotification($typeForRecipient);
        $verb = match ($action) {
            'update' => 'vient de modifier',
            'delete' => 'vient de supprimer',
            default => 'a enregistré',
        };

        $title = match ($action) {
            'update' => $this->getUpdateObligationTitleForNotification($typeForRecipient),
            'delete' => $this->getDeleteObligationTitleForNotification($typeForRecipient),
            default => $this->getCreateObligationTitleForNotification($typeForRecipient),
        };

        $actorName = trim(($actor->getFirstname() ?? '') . ' ' . ($actor->getLastname() ?? ''));
        if ($actorName === '') {
            $actorName = 'Un membre';
        }

        $message = "🤝{$actorName} {$verb} {$label} convenu entre vous. Consulte-le !🤲";
        $data = [
            'view' => 'obligation_list_view',
            'type' => $typeForRecipient,
        ];

        try {
            $this->createSentNotif($sendToUser, $title, $message, $data['view'], "obligation_{$action}", $data);
            $this->fcmNotificationService->sendFcmDefaultNotification($sendToUser, $title, $message, $data);
        } catch (\Throwable $e) {
            // never block main flow if notif sending fails
        }
    }

    private function getObligationTypeForRecipient(string $storedType, User $recipient, User $createdBy, User $relatedTo): string
    {
        // "jed/onm" are stored from createdBy perspective; invert only when recipient is relatedTo.
        if ($recipient->getId() === $relatedTo->getId()) {
            if ($storedType === 'jed') {
                return 'onm';
            }
            if ($storedType === 'onm') {
                return 'jed';
            }
        }

        return $storedType;
    }

    private function getObligationLabelForNotification(string $type): string
    {
        return match ($type) {
            'onm' => 'un emprunt',
            'jed' => 'un prêt',
            'amana' => 'une amana',
            default => 'une obligation',
        };
    }

    private function getCreateObligationTitleForNotification(string $type): string
    {
        return match ($type) {
            'onm' => 'Nouvel emprunt',
            'jed' => 'Nouveau prêt',
            'amana' => 'Nouvelle amana',
            default => 'Nouvelle obligation',
        };
    }

    private function getUpdateObligationTitleForNotification(string $type): string
    {
        return match ($type) {
            'onm' => 'Emprunt modifié',
            'jed' => 'Prêt modifié',
            'amana' => 'Amana modifiée',
            default => 'Obligation modifiée',
        };
    }

    private function getDeleteObligationTitleForNotification(string $type): string
    {
        return match ($type) {
            'onm' => 'Emprunt supprimé',
            'jed' => 'Prêt supprimé',
            'amana' => 'Amana supprimée',
            default => 'Obligation supprimée',
        };
    }

    public function notifForObligationEchance(Obligation $obligation) {
        $createdBy = $obligation->getCreatedBy();
        if (!($createdBy instanceof User)) {
            return;
        }

        $relatedTo = $obligation->getRelatedTo();
        $type = $obligation->getType();
        $actorName = trim($createdBy->getFirstname()." ".$createdBy->getLastname());
        $otherName = trim($obligation->getFirstname()." ".$obligation->getLastname());
        if ($relatedTo instanceof User) {
            $otherName = trim($relatedTo->getFirstname()." ".$relatedTo->getLastname());
        }

        $borrowerName = trim($obligation->getFirstname()." ".$obligation->getLastname());
        $lenderName = $borrowerName;

        if ($type === 'jed') {
            $borrowerName = trim($createdBy->getFirstname()." ".$createdBy->getLastname());
            if ($relatedTo instanceof User) {
                $lenderName = trim($relatedTo->getFirstname()." ".$relatedTo->getLastname());
            }
        } elseif ($type === 'onm') {
            $lenderName = trim($createdBy->getFirstname()." ".$createdBy->getLastname());
            if ($relatedTo instanceof User) {
                $borrowerName = trim($relatedTo->getFirstname()." ".$relatedTo->getLastname());
            }
        }

        $typeForCreatedBy = $type;
        if ($relatedTo instanceof User) {
            $typeForCreatedBy = $this->getObligationTypeForRecipient($type, $createdBy, $createdBy, $relatedTo);
        }

        $titleMessage = $this->getTitleMessageForObligationType($typeForCreatedBy, $borrowerName, $lenderName, $actorName, $otherName);
        $data = [
            'view' => "obligation_list_view",
            'type' => $typeForCreatedBy,
        ];
        $this->createSentNotif($createdBy, $titleMessage['title'], $titleMessage['message'], $data['view'], 'obligation_echeance', $data);
        $this->fcmNotificationService->sendFcmDefaultNotification($createdBy, $titleMessage['title'], $titleMessage['message'], $data);

        if ($relatedTo instanceof User) {
            $typeForRelated = $this->getObligationTypeForRecipient($type, $relatedTo, $createdBy, $relatedTo);
            $data['type'] = $typeForRelated;
            $titleMessage = $this->getTitleMessageForObligationType($typeForRelated, $borrowerName, $lenderName, $actorName, $otherName);
            $this->createSentNotif($relatedTo, $titleMessage['title'], $titleMessage['message'], $data['view'], 'obligation_echeance', $data);
            $this->fcmNotificationService->sendFcmDefaultNotification($relatedTo, $titleMessage['title'], $titleMessage['message'],$data);
        }
    }

    public function getTitleMessageForObligationType($type, $borrowerName, $lenderName, $actorName = '', $otherName = '') {
        if ($type === 'jed') {
            $title = "⏰ Rappel";
            $message = "L’échéance du prêt accordé par ".$lenderName." se termine demain\nPense à honorer ta parole.";
        } elseif ($type === 'onm') {
            $title = "🔔Remboursement attendu";
            $message = $borrowerName." a été relancé(e) pour le remboursement de demain. L’Islam nous enseigne la patience et la clémence.🤲";
        } elseif ($type === 'amana') {
            $title = "La amana arrive à échéance";
            $leftName = trim((string) $actorName);
            $rightName = trim((string) $otherName);
            if ($leftName === '') {
                $leftName = $borrowerName;
            }
            if ($rightName === '') {
                $rightName = $lenderName;
            }
            $message = "La amana entre ".$leftName." et ".$rightName." arrive à échéance";
        } else {
            $title = "⏰ Rappel";
            $message = "L’échéance arrive demain\nPense à honorer ta parole.";
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
        $createdBy = $obligation->getCreatedBy();
        $relatedTo = $obligation->getRelatedTo();

        if (!($createdBy instanceof User) || !($relatedTo instanceof User)) {
            return;
        }

        if ($currentUser->getId() === $createdBy->getId()) {
            $userToNotif = $relatedTo;
        } elseif ($currentUser->getId() === $relatedTo->getId()) {
            $userToNotif = $createdBy;
        } else {
            return;
        }

        $typeForRecipient = $this->getObligationTypeForRecipient(
            $obligation->getType(),
            $userToNotif,
            $createdBy,
            $relatedTo
        );
        $label = $this->getObligationLabelForNotification($typeForRecipient);

        $actorName = trim(($currentUser->getFirstname() ?? '') . ' ' . ($currentUser->getLastname() ?? ''));
        if ($actorName === '') {
            $actorName = 'Un membre';
        }

        $title = "Remboursement partagé";
        $message = "🩶Bonne nouvelle {$actorName} vient de noter un remboursement d’{$label} convenu entre vous. Consulte-le ! 🤲";

        $data = [
            'view' => "obligation_list_view",
            'type' => $typeForRecipient,
            'tab' => 'refund',
        ];

        $this->createSentNotif($userToNotif, $title, $message, $data['view'], 'obligation_refund', $data);
        $this->fcmNotificationService->sendFcmDefaultNotification($userToNotif, $title, $message, $data);
    }

    private function createSentNotif(User $user, string $title, string $message, ?string $view = null, ?string $type = 'fcm', ?array &$data = null): void
    {
        try {
            $notif = new NotifToSend();
            $notif->setUser($user);
            $notif->setTitle($title);
            $notif->setMessage($message);
            $notif->setSendAt(new \DateTime());
            $notif->setType($type ?? 'fcm');
            $notif->setView($view);
            if (!is_null($data)) {
                $notif->setDatas(json_encode($data, JSON_UNESCAPED_UNICODE));
            }
            $notif->setStatus('sent');
            $notif->setIsRead(false);
            $this->em->persist($notif);
            $this->em->flush();
            if (!is_null($data)) {
                $data['notifId'] = (string)$notif->getId();
            }
        } catch (\Throwable $e) {
            // never block main flow if notif persistence fails
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
