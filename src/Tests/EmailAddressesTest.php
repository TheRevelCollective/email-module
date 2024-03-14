<?php

namespace Rbg\Email\Tests;

use PHPUnit\Framework\TestCase;
use Rbg\Email\Repository\EmailAddressRepository;
use Rbg\Hydrator\Hydrator;
use PHPUnit\Framework\MockObject\MockObject;
use Rbg\Email\EmailAddresses;
use Rbg\Email\Model\EmailAddress;

class EmailAddressesTest extends TestCase
{
    private EmailAddressRepository|MockObject $repository;
    private Hydrator|MockObject $hydrator;

    public function setUp(): void
    {
        $this->repository = $this->getMockBuilder(EmailAddressRepository::class)->disableOriginalConstructor()->getMock();
        $this->hydrator = $this->getMockBuilder(Hydrator::class)->disableOriginalConstructor()->getMock();
    }

    public function testValidateEmailNoMx()
    {
        $date = new \DateTimeImmutable();
        $mock = $this->getMockWithMethods(['getMxRecord', 'getEmailByAddress']);
        $mock->expects($this->once())
            ->method('getEmailByAddress')
            ->willReturn(null);
        $mock->expects($this->once())
            ->method('getMxRecord')
            ->willReturn(null);

        $mockEmail = new EmailAddress();
        $mockEmail->setEmail('test@test.com');
        $mockEmail->setValid(false);
        $mockEmail->setLastChecked($date);
        $mockEmail->setInvalidReason('No MX Record set for domain');
        $this->repository->expects($this->once())
            ->method('saveEmailAddress')
            ->with($mockEmail);

        $this->assertEquals(false, $mock->validateEmailAddress('test@test.com', $date));
    }

    public function testValidateEmailInvalidEmail()
    {
        $date = new \DateTimeImmutable();
        $mock = $this->getMockWithMethods(['getMxRecord', 'getEmailByAddress']);
        $mock->expects($this->once())
            ->method('getEmailByAddress')
            ->willReturn(null);
        $mock->expects($this->never())
            ->method('getMxRecord')
            ->willReturn(null);

        $mockEmail = new EmailAddress();
        $mockEmail->setEmail('test@@@test');
        $mockEmail->setValid(false);
        $mockEmail->setLastChecked($date);
        $mockEmail->setInvalidReason('Invalid e-mail address');
        $this->repository->expects($this->once())
            ->method('saveEmailAddress')
            ->with($mockEmail);

        $this->assertEquals(false, $mock->validateEmailAddress('test@@@test', $date));
    }

    public function testValidateEmailOK()
    {
        $date = new \DateTimeImmutable();
        $mock = $this->getMockWithMethods(['getMxRecord', 'getEmailByAddress']);
        $mock->expects($this->once())
            ->method('getEmailByAddress')
            ->willReturn(null);
        $mock->expects($this->once())
            ->method('getMxRecord')
            ->willReturn('test.com');

        $mockEmail = new EmailAddress();
        $mockEmail->setEmail('test@test.com');
        $mockEmail->setValid(true);
        $mockEmail->setLastChecked($date);
        $this->repository->expects($this->once())
            ->method('saveEmailAddress')
            ->with($mockEmail);

        $this->assertEquals(true, $mock->validateEmailAddress('test@test.com', $date));
    }

    private function getClass(): EmailAddresses
    {
        return new EmailAddresses($this->repository, $this->hydrator);
    }

    private function getMockWithMethods(array $methods): MockObject|EmailAddresses
    {
        return $this->getMockBuilder(EmailAddresses::class)->setConstructorArgs([$this->repository, $this->hydrator])->onlyMethods($methods)->getMock();
    }
}
