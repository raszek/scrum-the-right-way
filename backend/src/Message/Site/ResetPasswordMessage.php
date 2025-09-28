<?php

namespace App\Message\Site;

use App\Entity\User\UserCode;
use App\Service\Common\DefaultMailer;

readonly class ResetPasswordMessage
{
    public function __construct(
        private DefaultMailer $mailer
    ) {
    }

    public function send(UserCode $userCode): void
    {
        $user = $userCode->getMainUser();

        $email = $this->mailer->createTemplatedEmail();

        $email
            ->to($user->getEmail())
            ->subject('Here is your reset password link')
            ->htmlTemplate('emails/site/forgot_password_mail.html.twig')
            ->context([
                'user' => $user,
                'userCode' => $userCode,
            ]);

        $this->mailer->sendTemplated($email);
    }
}
