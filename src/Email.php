<?php

namespace Rbg\Email;

use Symfony\Component\Mailer\Mailer;
use Rbg\Email\Repository\EmailRepository;
use Rbg\Hydrator\Hydrator;
use Twig\Environment;
use Rbg\Email\Model\EmailSend;
use Rbg\Email\Model\EmailMessage;
use Rbg\Contracts\Locations\BrandInterface;
use Rbg\Email\Model\MessageGenerator;
use Rbg\Email\Exception\SubjectIsRequiredException;
use Rbg\Email\Exception\ToAddressRequired;
use Rbg\Email\Exception\InvalidToAddress;

class Email
{
    private Mailer $mailer;
    private EmailRepository $repository;
    private Hydrator $hydrator;
    private Environment $twig;
    private EmailAddresses $emailAddresses;

    public function __construct(Mailer $mailer, EmailRepository $repository, Hydrator $hydrator, Environment $twig, EmailAddresses $emailAddresses)
    {
        $this->mailer = $mailer;
        $this->repository = $repository;
        $this->hydrator = $hydrator;
        $this->twig = $twig;
        $this->emailAddresses = $emailAddresses;
    }

    public function getEmailSendById(int $id): ?EmailSend
    {
        return $this->hydrator->hydrate(EmailSend::class, $this->repository->getEmailSendById($id));
    }

    public function getEmailMessageById(int $id): ?EmailMessage
    {
        return $this->hydrator->hydrate(EmailMessage::class, $this->repository->getEmailMessageById($id));
    }

    /**
     * @return EmailSend[]
     */
    public function getSendsForEmail(EmailMessage $message): array
    {
        return $this->hydrator->hydrateSet(EmailSend::class, $this->repository->getSendsForEmail($message));
    }

    public function createMessage(): MessageGenerator
    {
        return new MessageGenerator($this);
    }

    public function sendInkyTemplate(BrandInterface $brand, string $toAddress, string $toName, string $inkyTemplate, array $variables = []): EmailMessage
    {
        $variables['content'] = $inkyTemplate;
        $email = $this->createMessage()
            ->subject('test')
            ->setBrand($brand)
            ->template('email/inky-template.twig')
            ->setVariables($variables)
            ->to($toAddress, $toName);

        $this->sendEmail($email);

        return $email->getMessage();
    }

    public function renderEmail(MessageGenerator $email): void
    {
        $htmlContent = $this->twig->render($email->getTwigTemplateFile(), $email->getContext());
        $email->setRenderedHtml($htmlContent);
        $textContent = $this->generatePlainText($htmlContent);
        $email->setRenderedText($textContent);
    }

    public function sendEmail(MessageGenerator $email): void
    {
        $this->validateMessageGenerator($email);
        $this->renderEmail($email);
        $message = $email->generateMessageModel();
        $this->repository->saveMessage($message);
        foreach ($email->getToAddresses() as $address) {
            $send = $message->createEmailSend($address->getAddress(), $address->getName());
            $send->setDateSent(new \DateTimeImmutable());
            $send->setStatus('created');
            $mailerSend = $send->createSymfonyModel();
            $send->setMessageId($mailerSend->generateMessageId());
            $this->repository->saveEmailSend($send);
            $mailerSend->html($this->replaceTracking($message->getHtmlMessage(), $send->getTrackingKey()));
            try {
                $mailerSend->ensureValidity();
                $this->mailer->send($mailerSend);
                $send->setStatus('sent');
            } catch (\Exception $e) {
                $send->setStatus('error');
                $send->setErrorMessage($e->getMessage());
            }
            $this->repository->saveEmailSend($send);
        }
    }

    private function replaceTracking(string $content, string $code): string
    {
        return str_replace('__TRACKING_CODE__', $code, $content);
    }

    private function validateMessageGenerator(MessageGenerator $email): void
    {
        if ($email->getSubject() === null) {
            throw new SubjectIsRequiredException('Subject is required to send an e-mail');
        }
        if (empty($email->getToAddresses())) {
            throw new ToAddressRequired('At least 1 to address is required to send an e-mail');
        }
        foreach ($email->getToAddresses() as $address) {
            $valid = $this->emailAddresses->validateEmailAddress($address->getAddress());
            if ($valid === false) {
                $email->removeToAddress($address->getAddress());
            }
        }
        if (empty($email->getToAddresses())) {
            throw new InvalidToAddress('No valid e-mail addresses to send to');
        }
    }

    public function generatePlainText(string $html): string
    {
        $html = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $html);
        $html = preg_replace("/<style\\b[^>]*>(.*?)<\\/style>/s", "", $html);
        $plainText = strip_tags($html);
        $plainText = trim($plainText);

        $lines = explode("\n", $plainText);
        $lines = array_filter($lines, function ($line) {
            return trim($line);
        });
        array_walk($lines, function (&$line) {
            $line = trim($line);
        });
        $plainText = implode("\n", $lines);

        return trim($plainText);
    }
}
