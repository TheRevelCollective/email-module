<?php

namespace Rbg\Email\Model;

use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class EmailSend
{
    private ?int $id = null;
    private EmailMessage $message;
    private string $messageId;
    private string $toAddress;
    private string $toName;
    private ?\DateTimeImmutable $dateSent = null;
    private string $status;
    private ?string $errorMessage = null;
    private bool $complained;
    private string $trackingKey;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getMessage(): EmailMessage
    {
        return $this->message;
    }

    public function setMessage(EmailMessage $message): void
    {
        $this->message = $message;
    }

    public function getMessageId(): string
    {
        return $this->messageId;
    }

    public function setMessageId(string $messageId): void
    {
        $this->messageId = $messageId;
    }

    public function getToAddress(): string
    {
        return $this->toAddress;
    }

    public function setToAddress(string $toAddress): void
    {
        $this->toAddress = $toAddress;
    }

    public function getToName(): string
    {
        return $this->toName;
    }

    public function setToName(string $toName): void
    {
        $this->toName = $toName;
    }

    public function getDateSent(): ?\DateTimeImmutable
    {
        return $this->dateSent;
    }

    public function setDateSent(?\DateTimeImmutable $dateSent = null): void
    {
        $this->dateSent = $dateSent;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(?string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    public function isComplained(): bool
    {
        return $this->complained;
    }

    public function setComplained(bool $complained): void
    {
        $this->complained = $complained;
    }

    public function getTrackingKey(): string
    {
        return $this->trackingKey;
    }

    public function setTrackingKey(string $trackingKey): void
    {
        $this->trackingKey = $trackingKey;
    }

    public function createSymfonyModel(): Email
    {
        return $this->message->createSymfonyMessage()->addTo(new Address($this->getToAddress(), $this->getToName()));
    }
}
