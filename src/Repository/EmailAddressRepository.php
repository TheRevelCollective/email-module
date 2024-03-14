<?php

namespace Rbg\Email\Repository;

use Rbg\Database\Repository\MysqlRepository;
use Rbg\Email\Model\EmailAddress;

class EmailAddressRepository extends MysqlRepository
{
    public function getEmailAddressByEmail(string $email): ?array
    {
        return $this->selectSingleRow('email_address', 'email', $email);
    }

    public function saveEmailAddress(EmailAddress $address): void
    {
        $data = [
            'email' => $address->getEmail(),
            'valid' => $address->isValid() ? 1 : 0,
            'last_checked' => $address->getLastChecked()->format('Y-m-d H:i:s'),
            'invalid_reason' => $address->getInvalidReason()
        ];
        $id = $this->insertOrUpdate('email_address', 'id', $address->getId(), $data);
        if ($id) {
            $address->setId($id);
        }
    }
}