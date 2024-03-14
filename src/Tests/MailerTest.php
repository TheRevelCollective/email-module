<?php

namespace Rbg\Email\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\TransportInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Rbg\Email\Repository\EmailRepository;
use Rbg\Hydrator\Hydrator;
use Twig\Environment;
use Rbg\Email\Email;
use Rbg\Email\Model\MessageGenerator;
use Rbg\Email\Tests\Fixtures\DummyBrand;
use Rbg\Email\EmailAddresses;

class MailerTest extends TestCase
{
    private Mailer $mailer;
    private MockObject|TransportInterface $transport;
    private EmailRepository|MockObject $repository;
    private Hydrator|MockObject $hydrator;
    private Environment|MockObject $twig;
    private EmailAddresses|MockObject $addresses;

    public function setUp(): void
    {
        $this->transport = $this->getMockBuilder(TransportInterface::class)->disableOriginalConstructor()->getMock();
        $this->mailer = new Mailer($this->transport);
        $this->repository = $this->getMockBuilder(EmailRepository::class)->disableOriginalConstructor()->getMock();
        $this->hydrator = $this->getMockBuilder(Hydrator::class)->disableOriginalConstructor()->getMock();
        $this->twig = $this->getMockBuilder(Environment::class)->disableOriginalConstructor()->getMock();
        $this->addresses = $this->getMockBuilder(EmailAddresses::class)->disableOriginalConstructor()->getMock();
    }

    public function testRenderEmail()
    {
        $this->twig->expects($this->once())
            ->method('render')
            ->with('test.twig', ['test' => 1])
            ->willReturn('<p>test</p>');
        $generator = (new MessageGenerator($this->getMailer()))
            ->template('test.twig')
            ->setVariables(['test' => 1]);
        $this->getMailer()->renderEmail($generator);

        $this->assertEquals('<p>test</p>', $generator->getRenderedHtml());
        $this->assertEquals('test', $generator->getRenderedText());
    }

    public function testSendQuickInkyEmail()
    {
        $this->addresses->expects($this->once())
            ->method('validateEmailAddress')
            ->willReturn(true);
        $this->twig->expects($this->atMost(1))
            ->method('render')
            ->with('email/inky-template.twig', ['content' => '<row>test</row>', 'test' => 1])
            ->willReturn('<p>test</p>');
        $this->transport->expects($this->atMost(1))
            ->method('send');
        $mailer = $this->getMailer();
        $message = $mailer->sendInkyTemplate(new DummyBrand(), 'test@test.com', 'test name', '<row>test</row>', ['test' => 1]);

        $this->assertEquals('<p>test</p>', $message->getHtmlMessage());
        $this->assertEquals('test', $message->getTextMessage());
        $send = $message->getSends()[0];
        $this->assertEquals('test@test.com', $send->getToAddress());
        $this->assertEquals('test name', $send->getToName());
    }

    public function testSendEmail()
    {
        $this->addresses->expects($this->once())
            ->method('validateEmailAddress')
            ->willReturn(true);
        $generator = (new MessageGenerator($this->getMailer()))
            ->to('test@test.com')
            ->setBrand(new DummyBrand())
            ->template('test.twig')
            ->subject('Test Subject');

        $this->transport->expects($this->once())
            ->method('send');

        $this->getMailer()->sendEmail($generator);

        $this->assertEquals('Test Subject', $generator->getMessage()->getSubject());
        $this->assertEquals(1, count($generator->getMessage()->getSends()));

        $send = $generator->getMessage()->getSends()[0];
        $this->assertEquals('sent', $send->getStatus());
        $this->assertEquals('test@test.com', $send->getToAddress());
    }

    private function getMailer(): Email
    {
        return new Email($this->mailer, $this->repository, $this->hydrator, $this->twig, $this->addresses);
    }
}