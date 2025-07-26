<?php

namespace App\Email\Profile;

use App\Entity\User\UserCode;
use App\Service\Common\DefaultMailer;

readonly class ChangeEmailEmail
{

    public function __construct(
        private DefaultMailer $mailer
    ) {
    }

    public function send(UserCode $userCode): void
    {
        $user = $userCode->getUser();

        $email = $this->mailer->createTemplatedEmail();

        $email
            ->to($user->getEmail())
            ->subject(sprintf('Change email %s', $user->getFullName()))
            ->htmlTemplate('emails/profile/change_email.html.twig')
            ->context([
                'user' => $user,
                'changeEmailCode' => $userCode->getCode(),
            ]);

        $this->mailer->sendTemplated($email);
    }

}
