<?php
namespace App\Services;

use App\Entity\ChatMessage;
use App\Entity\FcmToken;
use App\Entity\User;
use App\Repository\FcmTokenRepository;
use App\UIBuilder\UserUI;
use App\Entity\Coaching\ChatBubble;
use App\Entity\Act\Activity;

class FcmNotificationService {

    private $fcmTokenRepository;
    private $userUI;
    private $firebaseFcmk;
    const PROJECT_ID = "elhapp-78deb";

    public function __construct($kernelProjectDir, FcmTokenRepository $fcmTokenRepository, UserUI $userUI, String $firebaseFcmk) {
        $this->fcmTokenRepository = $fcmTokenRepository;
        $this->kernelProjectDir = $kernelProjectDir;
        $this->userUI = $userUI;
        $this->firebaseFcmk = $firebaseFcmk;
    }

    public function sendFcmChatNotification($fcmTokens, ChatMessage $bubble) {
        $createdBy = $bubble->getCreatedBy();
        $name = $createdBy->getFirstname() .' '. $createdBy->getLastname();
        $title = 'Nouveau message de ' .$name;
        $message = strlen($bubble->getContent()) > 50 ? mb_substr($bubble->getContent(), 0, 50).'...' : $bubble->getContent();
        $userUi  = $this->userUI->getUserProfilUI($createdBy);
        $datas = [
            'view' => 'chatview',
            'userId' => $createdBy->getId(),
            'userUI' => $userUi,
            'image' => $userUi['photo']
        ];
        foreach ($fcmTokens as $fcmToken) {
            if($fcmToken instanceof FcmToken) {
                $this->sendMessageV2($fcmToken->getFcmToken(), $title, $message, $datas);
            } elseif(is_string($fcmToken)) {
                $this->sendMessageV2($fcmToken, $title, $message, $datas);
            }
        }
    }


       public function sendFcmDefaultNotification($user, $title, $message, $data = null) {
        //FCM token
        $fcmTokens = $this->fcmTokenRepository->findBy([
            'user' => $user
        ]);
        //https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages?hl=fr#AndroidConfig
        if(!empty($fcmTokens)) {
            $sentTokens = [];
            foreach ($fcmTokens as $fcmToken) {
                $token = $fcmToken->getFcmToken();
                if (isset($sentTokens[$token])) {
                    continue;
                }
                $sentTokens[$token] = true;
                $this->sendMessageV2($token, $title, $message, null);
            }
        }
    }


    private function getGoogleAccessToken(){
        $client = new \Google_Client();
        $client->setAuthConfig(json_decode($this->firebaseFcmk, true)); //array or file
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();
        //normalement valable une heure cache ??
        return $token['access_token'] ?? false;
    }

    public function sendMessageV2($userToken, $title, $message, $data = null) {
        $projectId = self::PROJECT_ID;
        $apiurl = 'https://fcm.googleapis.com/v1/projects/'.$projectId.'/messages:send';
        $fireBaseToken = $this->getGoogleAccessToken(); //valide pour plusieurs request / cache it ?!
        if(!$fireBaseToken) {
            return;
        }
        $headers = [
            'Authorization: Bearer ' . $fireBaseToken,
            'Content-Type: application/json'
        ];
        $notification_tray = [
            'title'             => $title,
            'body'              => $message,
        ];
        $message = [
            'message' => [
                'notification'     => $notification_tray,
                "android" => [
                    "notification" => [
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'sound' => 'default'
                    ]
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default'  // 🔊 pour un bip standard sur iOS
                        ]
                    ]
                ],
                "token" => $userToken
            ],
        ];
        if(!is_null($data)) {
            try {
                $data = $this->array_map_recursive('strval', $data); //force all values to be string !
                $message['message']['data'] = $data;
            } catch (\Throwable $t) {}
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiurl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            //log it ??!!
        }
        //DELETE les tokens périmées
        $resArr = json_decode($result, true);
        if(is_array($resArr) && isset($resArr['error']["code"]) && isset($resArr['error']["status"])) {
            if($resArr['error']["code"] == 404 && $resArr['error']["status"] == "NOT_FOUND") {
                $this->fcmTokenRepository->deleteToken($userToken);
            }
        }
        curl_close($ch);
    }


    /**
     * FCM accepte only un niveau de array ! => decode côté App !!
     * @param $callback
     * @param $array
     * @return array|false[]|string[]
     */
    public function array_map_recursive($callback, $array)
    {
        $func = function ($item) use (&$func, &$callback) {
            return is_array($item) ? json_encode($item) : call_user_func($callback, $item);
        };
        return array_map($func, $array);
    }
}
