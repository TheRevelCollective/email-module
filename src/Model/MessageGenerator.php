<?php

namespace Rbg\Email\Model;

use Symfony\Component\Mime\Address;
use Rbg\Contracts\Locations\BrandInterface;
use Rbg\Email\Email;

class MessageGenerator
{
    private Email $mailer;
    private ?Address $from = null;
    private ?Address $replyTo = null;
    private ?BrandInterface $brand = null;
    private string $twigTemplateFile;
    /**
     * @var Address[]
     */
    private array $toAddresses = [];
    private ?string $subject = null;
    private array $context = [];

    private string $renderedText;
    private string $renderedHtml;

    private ?EmailMessage $message = null;

    public function __construct(Email $mailer)
    {
        $this->mailer = $mailer;
    }

    public function to(string $address, string $name = ''): self
    {
        $this->toAddresses[] = new Address($address, $name);
        return $this;
    }

    public function from(string $address, string $name = ''): self
    {
        $this->from = new Address($address, $name);
        return $this;
    }

    public function template(string $template): self
    {
        $this->twigTemplateFile = $template;
        return $this;
    }

    public function subject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    public function replyTo(string $address, string $name): self
    {
        $this->replyTo = new Address($address, $name);
        return $this;
    }

    public function setVariables(array $variables): self
    {
        $this->context = $variables;
        return $this;
    }

    public function addVariable(string $variable, mixed $value): self
    {
        $this->context[$variable] = $value;
        return $this;
    }

    public function getFrom(): ?Address
    {
        return $this->from;
    }

    public function getReplyTo(): ?Address
    {
        return $this->replyTo;
    }

    public function getTwigTemplateFile(): string
    {
        return $this->twigTemplateFile;
    }

    public function getToAddresses(): array
    {
        return $this->toAddresses;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function send(): self
    {
        $this->mailer->sendEmail($this);
        return $this;
    }

    public function setRenderedText(string $renderedText): MessageGenerator
    {
        $this->renderedText = $renderedText;
        return $this;
    }

    public function setRenderedHtml(string $renderedHtml): MessageGenerator
    {
        $this->renderedHtml = $renderedHtml;
        return $this;
    }

    public function setBrand(?BrandInterface $brand): MessageGenerator
    {
        $this->brand = $brand;
        if ($brand->getDefaultEmailFrom()) {
            $this->from($brand->getDefaultEmailFrom(), $brand->getName());
        }
        return $this;
    }

    public function getBrand(): ?BrandInterface
    {
        return $this->brand;
    }

    public function generateMessageModel(): EmailMessage
    {
        $message = new EmailMessage();
        $message->setTextMessage($this->renderedText);
        $message->setHtmlMessage($this->renderedHtml);
        $message->setSubject($this->getSubject());
        $message->setDateAdded(new \DateTime);
        $message->setFromAddress($this->getFrom()->getAddress());
        $message->setFromName($this->getFrom()->getName());
        $this->message = $message;
        return $message;
    }

    public function getMessage(): ?EmailMessage
    {
        return $this->message;
    }

    public function getRenderedText(): string
    {
        return $this->renderedText;
    }

    public function getRenderedHtml(): string
    {
        return $this->renderedHtml;
    }

    public function removeToAddress(string $email): void
    {
        $addresses = [];
        foreach ($this->getToAddresses() as $address) {
            if ($address->getAddress() !== $email) {
                $addresses[] = $address;
            }
        }
        $this->toAddresses = $addresses;
    }
}
