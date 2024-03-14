<?php

namespace Rbg\Email\Model;

use Rbg\Email\EmailAddresses;

class EmailAddress
{
    private ?int $id = null;
    private string $email;
    private bool $valid;
    private \DateTimeImmutable $lastChecked;
    private ?string $invalidReason;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): EmailAddress
    {
        $this->id = $id;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): EmailAddress
    {
        $this->email = $email;
        return $this;
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function setValid(bool $valid): EmailAddress
    {
        $this->valid = $valid;
        return $this;
    }

    public function getLastChecked(): \DateTimeImmutable
    {
        return $this->lastChecked;
    }

    public function setLastChecked(\DateTimeImmutable $lastChecked): EmailAddress
    {
        $this->lastChecked = $lastChecked;
        return $this;
    }

    public function getInvalidReason(): ?string
    {
        return $this->invalidReason;
    }

    public function setInvalidReason(?string $invalidReason): EmailAddress
    {
        $this->invalidReason = $invalidReason;
        return $this;
    }

    public function needsRevalidating(): bool
    {
        if ($this->isValid() === false) {
            $cutoff = new \DateTimeImmutable('-' . EmailAddresses::INVALID_RECHECK_INTERVAL);
            return $cutoff >= $this->getLastChecked();
        }
        $cutoff = new \DateTimeImmutable('-' . EmailAddresses::VALID_RECHECK_INTERVAL);
        return $cutoff >= $this->getLastChecked();
    }
}
