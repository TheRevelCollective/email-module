<?php

namespace Rbg\Email;

use Rbg\Email\Repository\EmailAddressRepository;
use Rbg\Hydrator\Hydrator;
use Rbg\Email\Model\EmailAddress;

class EmailAddresses
{
    public const VALID_RECHECK_INTERVAL = '30 day';
    public const INVALID_RECHECK_INTERVAL = '1 hour';
    private EmailAddressRepository $repository;
    private Hydrator $hydrator;

    public function __construct(EmailAddressRepository $repository, Hydrator $hydrator)
    {
        $this->repository = $repository;
        $this->hydrator = $hydrator;
    }

    public function validateEmailAddress(string $address, ?\DateTimeImmutable $date = null): bool
    {
        $address = trim($address);
        if ($date === null) {
            $date = new \DateTimeImmutable();
        }
        $email = $this->getEmailByAddress($address);
        if ($email === null) {
            $email = new EmailAddress();
            $email->setEmail($address);
            $email->setLastChecked($date->modify('-10 year'));
            $email->setValid(false);
        }
        if ($email->needsRevalidating()) {
            if (filter_var($address, FILTER_VALIDATE_EMAIL) !== $address) {
                $email->setInvalidReason('Invalid e-mail address');
                $email->setValid(false);
                $email->setLastChecked($date);
                $this->saveEmailAddress($email);
                return false;
            }
            [, $domain] = explode('@', $address);
            $host = $this->getMxRecord($domain);
            if (!$host) {
                $email->setInvalidReason('No MX Record set for domain');
                $email->setLastChecked($date);
                $email->setValid(false);
                $this->saveEmailAddress($email);
                return false;
            }

            $email->setLastChecked($date);
            $email->setValid(true);
            $email->setInvalidReason(null);
            $this->saveEmailAddress($email);
        }
        return $email->isValid();
    }

    public function getEmailByAddress(string $address): ?EmailAddress
    {
        return $this->hydrator->hydrate(EmailAddress::class, $this->repository->getEmailAddressByEmail($address));
    }

    public function saveEmailAddress(EmailAddress $address): void
    {
        $this->repository->saveEmailAddress($address);
    }

    protected function getMxRecord(string $hostname): ?string
    {
        if ($hostname === 'revolutionbarsgroup.com') {
            return 'cluster5.eu.messagelabs.com';
        }
        $hostname = trim($hostname);
        $records = dns_get_record($hostname, DNS_MX);
        usort($records, function ($record1, $record2) {
            return $record1['pri'] > $record2['pri'] ? 1 : -1;
        });

        if (empty($records)) {
            return null;
        }

        return $records[0]['target'];
    }
}
