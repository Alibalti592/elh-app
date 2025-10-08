<?php
namespace App\Services;


use Symfony\Contracts\Cache\CacheInterface;

class CacheService {

    public function __construct(CacheInterface $cache) {
        $this->cache = $cache;
    }

    //Keys
    public function getKeyName($baseKey, $params) {
        return $baseKey."-".implode('-', $params);
    }

    /********** CHAT *********/
    public function chatNbNotificationKey($user) {
        return $this->getKeyName('chat-notification-key', [$user->getId()]);
    }
    public function onChatNotificationUpdate($user) {
        $this->cache->delete($this->chatNbNotificationKey($user));
    }

    /********** Notification *********/
    public function notificationsKey($user) {
        return $this->getKeyName('notifications-key', [$user->getId()]);
    }
    public function onNotificationsUpdate($user) {
        $this->cache->delete($this->notificationsKey($user));
    }

}