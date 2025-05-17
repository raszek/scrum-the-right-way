<?php

namespace App\Service\Site;

use App\Entity\User\User;
use App\Service\Common\DefaultMailer;

readonly class ForgotPasswordMail
{
    public function __construct(
        private DefaultMailer $mailer
    ) {
    }


    public function send(User $user): void
    {
        $email = $this->mailer->createTemplatedEmail();

        $email
            ->to($user->getEmail())
            ->subject('Here is your reset password link')
            ->htmlTemplate('emails/site/forgot_password_mail.html.twig')
            ->context([
                'user' => $user,
            ]);

        $this->mailer->sendTemplated($email);
    }
}
