<?php

namespace App\Service\Common;


use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

readonly class DefaultMailer
{

    public function __construct(
        private MailerInterface $mailer,
        private string $fromEmail,
        private string $fromEmailName
    ) {
    }

    public function createTemplatedEmail(): TemplatedEmail
    {
        $templatedEmail = new TemplatedEmail();

        $templatedEmail->from(new Address($this->fromEmail, $this->fromEmailName));

        return $templatedEmail;
    }

    public function sendTemplated(TemplatedEmail $templatedEmail): void
    {
        $this->mailer->send($templatedEmail);
    }

}
