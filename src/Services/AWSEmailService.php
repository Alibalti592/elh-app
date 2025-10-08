<?php
namespace App\Services;

use AsyncAws\Ses\Input\SendEmailRequest;
use AsyncAws\Ses\SesClient;
use AsyncAws\Ses\ValueObject\Body;
use AsyncAws\Ses\ValueObject\Content;
use AsyncAws\Ses\ValueObject\Destination;
use AsyncAws\Ses\ValueObject\EmailContent;
use AsyncAws\Sqs\Input\ChangeMessageVisibilityRequest;
use AsyncAws\Sqs\Input\DeleteMessageRequest;
use AsyncAws\Sqs\Input\ReceiveMessageRequest;
use AsyncAws\Sqs\Input\SendMessageRequest;
use AsyncAws\Sqs\SqsClient;
use AsyncAws\Sqs\ValueObject\Message;
use Psr\Log\LoggerInterface;

class AWSEmailService {
    private SqsClient $sqsClient;
    private SesClient $sesClient;
    private $logger;
    private $env;
    CONST QUEUE_URL = 'https://sqs.eu-west-3.amazonaws.com/975050212617/mail-muslimconnect';

    public function __construct($awsID, $awsSecret, LoggerInterface $logger, $env) {
        $this->sqsClient = new SqsClient([
            'region' => 'eu-west-3',
            'accessKeyId' => $awsID,
            'accessKeySecret' => $awsSecret,
        ]);
        $this->sesClient = new SesClient([
            'region' => 'eu-west-3',
            'accessKeyId' => $awsID,
            'accessKeySecret' => $awsSecret,
        ]);
        $this->logger = $logger;
        $this->env = $env;
    }

    public function addEmailToQueue($from, $to, $replyTo, $subject, $body, $type = 'Text') {
        if(is_null($from)) {
            $from = 'Muslim Connect <noreply@muslim-connect.fr>';
        }
        if(is_null($replyTo)) {
            $replyTo = 'contact@muslim-connect.fr';
        }
        $to = $this->env == 'dev' ? 'elheidiapp@gmail.com' : $to;
        $to = filter_var($to, FILTER_SANITIZE_EMAIL);
        if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->sendEmailWitSNS($from, $to, $replyTo, $subject, $body);
//            $this->sqsClient->sendMessage(new SendMessageRequest([
//                'QueueUrl' => self::QUEUE_URL,
//                'MessageBody' => $body,
//                'MessageAttributes' => [
//                    'from' => [
//                        'DataType' => 'String',
//                        'StringValue' => $from,
//                    ],
//                    'to' => [
//                        'DataType' => 'String',
//                        'StringValue' => $to,
//                    ],
//                    'replyTo' => [
//                        'DataType' => 'String',
//                        'StringValue' => $replyTo,
//                    ],
//                    'subject' => [
//                        'DataType' => 'String',
//                        'StringValue' => $subject,
//                    ],
//                    'type' => [
//                        'DataType' => 'String',
//                        'StringValue' => $type,
//                    ],
//                ]
//            ]));
        }
    }

    public function retrieveFromQueueAndSendEmails() {
        $result = $this->sqsClient->receiveMessage(new ReceiveMessageRequest([
            'QueueUrl' => self::QUEUE_URL,
            'WaitTimeSeconds' => 20, //temps d'attente au cas où il n'y a pas de message
            'MaxNumberOfMessages' => 10,
            'MessageAttributeNames' => ['All']
        ]));
        foreach ($result->getMessages() as $message) {
            try {
                //ENVOYER LE MESSAGE
                $hasSend = $this->sendEmailFromSQSMessage($message);
                // When finished, delete the message
                if($hasSend) {
                    $this->sqsClient->deleteMessage(new DeleteMessageRequest([
                        'QueueUrl' => self::QUEUE_URL,
                        'ReceiptHandle' => $message->getReceiptHandle(),
                    ]));
                }
            } catch (\Exception $e) {
                $this->logger->error('retrieveFromQueueAndSendEmails : '.$e->getCode().' | '.$e->getMessage().' ');
                if($e->getCode() != 400) {
                    // Optional : Set the visibility to 0 to be instantaneously requeued:  quand le message est  pull il est invisible pendant 30s par d'autres pull éventuels
                    $this->sqsClient->changeMessageVisibility(new ChangeMessageVisibilityRequest([
                        'QueueUrl' => self::QUEUE_URL,
                        'ReceiptHandle' => $message->getReceiptHandle(),
                        'VisibilityTimeout' => 0,
                    ]));
                }
            }
        }
    }

    public function sendEmailWitSNS($from, $to, $replyTo, $subject, $body) {
        $type = 'Html';
        try {
            $this->sesClient->sendEmail(new SendEmailRequest([
                'FromEmailAddress' => $from,
                'ReplyToAddresses' => [$replyTo],
                'Content' => new EmailContent([
                    'Simple' => new \AsyncAws\Ses\ValueObject\Message([
                        'Subject' => new Content(['Data' => $subject]),
                        'Body' => new Body([
                            $type => new Content(['Data' => $body]), //Html or Text
                        ]),
                    ]),
                ]),
                'Destination' => new Destination([
                    'ToAddresses' => [$to]
                ]),
            ]));
        } catch (\Exception $e) {
            $this->logger->error('Error send SES : '.$e->getCode().' | '.$e->getMessage().' '.$to);
            return false;
        }
        return true;
    }

    public function sendEmailFromSQSMessage(Message $message) {
        $body = $message->getBody();
        $attributes = $message->getMessageAttributes();
        $from = $attributes['from']->getStringValue();
        $replyTo = $attributes['replyTo']->getStringValue() ?? $attributes['from']->getStringValue();
        $to = $this->env == 'dev' ? 'elheidiapp@gmail.com' : $attributes['to']->getStringValue();
        $subject = $attributes['subject']->getStringValue();
        $type = $attributes['type']->getStringValue() ?? 'Text';
        try {
            $this->sesClient->sendEmail(new SendEmailRequest([
                'FromEmailAddress' => $from,
                'ReplyToAddresses' => [$replyTo],
                'Content' => new EmailContent([
                    'Simple' => new \AsyncAws\Ses\ValueObject\Message([
                        'Subject' => new Content(['Data' => $subject]),
                        'Body' => new Body([
                            $type => new Content(['Data' => $body]), //Html or Text
                        ]),
                    ]),
                ]),
                'Destination' => new Destination([
                    'ToAddresses' => [$to]
                ]),
            ]));
        } catch (\Exception $e) {
            $this->logger->error('Error send SES : '.$e->getCode().' | '.$e->getMessage().' '.$to);
            if($e->getCode() == 400) {
                $this->sqsClient->deleteMessage(new DeleteMessageRequest([
                    'QueueUrl' => self::QUEUE_URL,
                    'ReceiptHandle' => $message->getReceiptHandle(),
                ]));
            }
            return false;
        }
        return true;
    }


}