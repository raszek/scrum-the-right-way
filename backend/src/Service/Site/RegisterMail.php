<?php

namespace App\Service\Site;

use App\Entity\User\User;
use App\Service\Common\DefaultMailer;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
class RegisterMail
{

    public function __construct(
        private readonly DefaultMailer $mailer
    ) {
    }


    public function send(User $user): void
    {
        $email = $this->mailer->createTemplatedEmail();

        $email
            ->to($user->getEmail())
            ->subject('Thanks for signing up!')
            ->htmlTemplate('emails/site/register.html.twig')
            ->context([
                'user' => $user,
            ]);

        $this->mailer->sendTemplated($email);
    }
}
