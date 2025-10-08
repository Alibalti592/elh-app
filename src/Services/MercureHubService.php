<?php
namespace App\Services;

use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MercureHubService {

//    public function __construct($domaine, $hubUrl, HubInterface $hub) {
//        $this->domaine = $domaine;
//        $this->hubUrl = $hubUrl;
//        $this->hub = $hub;
//    }
//
//    public function getHttpResponseCode(string $url): int{
//        try {
//            $context = stream_context_create(array('http' => array(
//                'method' => 'HEAD',
//                'timeout' => 0.5
//            ))); //si timeout on va dans le catch !!
//            $headers = get_headers($url, false, $context);
//            return substr($headers[0], 9, 3);
//        } catch (\ErrorException $e) {
//            return 500;
//        }
//    }
//
//    public function getHubSubscribeUrl($topicKey) {
//        $url = $this->hubUrl.'?'.http_build_query([
//            'topic' => $this->getTopic($topicKey)
//        ]);
//        if($this->getHttpResponseCode($url) == 200) {
//            return $url;
//        }
//        return false;
//    }
//
//    public function getTopic($key) {
//        return $this->domaine."/".$key;
//    }
//
//    //subscribe to another topic (non testé)
//    public function addTopicToUrl($url, $topic) {
//        $url .= $url.(parse_url($url, PHP_URL_QUERY) ? '&' : '?').http_build_query([
//            'topic' => $this->getTopic($topic)
//        ]);
//        return $url;
//    }
//
//    public function sendNotification($topicKey, $data) {
//        if($this->getHubSubscribeUrl($topicKey) != false) {
//            try {
//                set_time_limit(1);
//                $topic = $this->getTopic($topicKey);
//                $update = new Update($topic, json_encode($data), false);
//                $this->hub->publish($update);
//            } catch (\Throwable $t) {
//            }
//        }
//    }
}