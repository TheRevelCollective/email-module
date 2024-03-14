<?php

namespace Rbg\Email\Model;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

class EmailMessage
{
    private ?int $id = null;
    private string $fromAddress;
    private string $fromName;
    private string $subject;
    private \DateTimeInterface $dateAdded;
    private string $textMessage;
    private string $htmlMessage;
    private int $barId = 0;
    /**
     * @hydrator RbgEmail::getSendsForEmail
     * @var EmailSend[]
     */
    private array $sends = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getFromAddress(): string
    {
        return $this->fromAddress;
    }

    public function setFromAddress(string $fromAddress): void
    {
        $this->fromAddress = $fromAddress;
    }

    public function getFromName(): string
    {
        return $this->fromName;
    }

    public function setFromName(string $fromName): void
    {
        $this->fromName = $fromName;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function getDateAdded(): \DateTimeInterface
    {
        return $this->dateAdded;
    }

    public function setDateAdded(\DateTimeInterface $dateAdded): void
    {
        $this->dateAdded = $dateAdded;
    }

    public function getTextMessage(): string
    {
        return $this->textMessage;
    }

    public function setTextMessage(string $textMessage): void
    {
        $this->textMessage = $textMessage;
    }

    public function getHtmlMessage(): string
    {
        return $this->htmlMessage;
    }

    public function setHtmlMessage(string $htmlMessage): void
    {
        $this->htmlMessage = $htmlMessage;
    }

    public function getBarId(): int
    {
        return $this->barId;
    }

    public function setBarId(int $barId): void
    {
        $this->barId = $barId;
    }

    public function getSends(): array
    {
        return $this->sends;
    }

    public function setSends(array $sends): void
    {
        $this->sends = $sends;
    }

    public function createSymfonyMessage(): Email
    {
        return (new Email())->from(new Address($this->getFromAddress(), $this->getFromName()))
            ->subject($this->getSubject())
            ->html($this->getHtmlMessage())
            ->text($this->getTextMessage());
    }

    public function createEmailSend(string $to, ?string $toName = null): EmailSend
    {
        $send = new EmailSend();
        $send->setMessage($this);
        $send->setToAddress($to);
        $send->setTrackingKey(uniqid());
        $send->setToName($toName ?? null);
        $send->setComplained(false);
        $this->sends[] = $send;

        return $send;
    }
}
