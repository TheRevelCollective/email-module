<?php

namespace Rbg\Email\Repository;

use Rbg\Database\Repository\MysqlRepository;
use Rbg\Email\Model\EmailSend;
use Rbg\Email\Model\EmailMessage;

class EmailRepository extends MysqlRepository
{
    public function getEmailMessageById(int $id): ?array
    {
        return $this->selectSingleRow('email_message', 'id', $id);
    }

    public function getEmailSendById(int $id): ?array
    {
        return $this->selectSingleRow('email_sends', 'id', $id);
    }

    public function saveEmailSend(EmailSend $send): void
    {
        $data = [
            'email_message_id' => $send->getMessage()->getId(),
            'message_id' => $send->getMessageId(),
            'to_address' => $send->getToAddress(),
            'to_name' => $send->getToName(),
            'date_sent' => $send->getDateSent()?->format('Y-m-d H:i:s'),
            'status' => $send->getStatus(),
            'error_message' => $send->getErrorMessage(),
            'complained' => $send->isComplained() ? 1 : 0,
            'tracking_key' => $send->getTrackingKey()
        ];
        $id = $this->insertOrUpdate('email_sends', 'id', $send->getId(), $data);
        if ($id) {
            $send->setId($id);
        }
    }

    public function saveMessage(EmailMessage $message): void
    {
        $data = [
            'from_address' => $message->getFromAddress(),
            'from_name' => $message->getFromName(),
            'subject' => $message->getSubject(),
            'date_added' => $message->getDateAdded()->format('Y-m-d H:i:s'),
            'text_message' => $message->getTextMessage(),
            'html_message' => $message->getHtmlMessage()
        ];
        $id = $this->insertOrUpdate('email_message', 'id', $message->getId(), $data);
        if ($id) {
            $message->setId($id);
        }
    }

    public function getSendsForEmail(EmailMessage $message): array
    {
        return $this->selectRowsByValues('email_sends', ['email_message_id' => $message->getId()]);
    }
}
