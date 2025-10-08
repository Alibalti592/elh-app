<?php
namespace App\Services;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ContactService
{
    private $mailer;
    private $captchaKey;
    private $logger;

    const GOOGLE_URL = "https://www.google.com/recaptcha/api/siteverify";

    /**
     * @param MailerInterface $mailer
     */
    public function __construct( MailerInterface $mailer, $captchaKey, LoggerInterface $logger, AWSEmailService $AWSEmailService)
    {
        $this->mailer = $mailer;
        $this->captchaKey = $captchaKey;
        $this->logger = $logger;
        $this->AWSEmailService = $AWSEmailService;
    }

    /**
     * Envoi du mail
     * @param $subject
     * @param $addressDest
     * @param $body
     * @param $from
     */
    public function sendMail($subject, $addressDest, $body, $from, $replyto) {
        try {
            $this->AWSEmailService->sendEmailWitSNS($from, $addressDest, $replyto, $subject, $body);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Canot send mail '.$e->getMessage());
        }

    }


    /**
     * @param $verifCapatcha
     * @return string
     */
    public function getCaptchaErrorMessage($verifCapatcha) {
        switch ($verifCapatcha) {
            case null:
                return 'Merci de valider le captcha';
                break;
            case false:
                return 'Le captcha est invalide';
                break;
            case true:
                return 'Merci de re-valider le captcha';
                break;
        }
    }

    /**
     * test du captcha return true or flase or null si non coché
     * @return bool or null
     */
    public function verifyCaptcha() {
        if (isset($_POST['g-recaptcha-response'])) {
            $captcha = $_POST['g-recaptcha-response'];
        } else {
            return null;
        }
        $url             = self::GOOGLE_URL . "?secret=" . $this->captchaKey . "&response=" . $captcha;
        $responseCaptcha = file_get_contents($url);
        $responseCaptcha = json_decode($responseCaptcha);
        return $responseCaptcha->success;
    }
}