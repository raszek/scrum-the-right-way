<?php

namespace App\Service\Site;

use App\Entity\User\User;
use App\Service\Common\DefaultMailer;

class ActivationUserEmail
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
            ->subject(sprintf('Account activation %s', $user->getFullName()))
            ->htmlTemplate('emails/site/activation_user.html.twig')
            ->context([
                'user' => $user,
            ]);

        $this->mailer->sendTemplated($email);
    }
}
