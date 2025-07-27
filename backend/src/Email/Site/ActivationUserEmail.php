<?php

namespace App\Email\Site;

use App\Entity\User\User;
use App\Entity\User\UserCode;
use App\Service\Common\DefaultMailer;

class ActivationUserEmail
{

    public function __construct(
        private readonly DefaultMailer $mailer
    ) {
    }


    public function send(UserCode $userCode): void
    {
        $user = $userCode->getMainUser();

        $email = $this->mailer->createTemplatedEmail();

        $email
            ->to($user->getEmail())
            ->subject(sprintf('Account activation %s', $user->getFullName()))
            ->htmlTemplate('emails/site/activation_user.html.twig')
            ->context([
                'user' => $user,
                'userCode' => $userCode,
            ]);

        $this->mailer->sendTemplated($email);
    }
}
