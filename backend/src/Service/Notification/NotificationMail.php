<?php

namespace App\Service\Notification;

use App\Entity\User\User;
use App\Event\EventRecord;
use App\Service\Common\ClockInterface;
use App\Service\Common\DefaultMailer;

readonly class NotificationMail
{
    public function __construct(
        private DefaultMailer $mailer,
        private ClockInterface $clock
    ) {
    }


    /**
     * @param EventRecord[] $eventRecords
     * @param User $user
     * @return void
     */
    public function send(array $eventRecords, User $user): void
    {
        $email = $this->mailer->createTemplatedEmail();

        $email
            ->to($user->getEmail())
            ->subject('Your notifications - ' . $this->clock->now()->toFormattedDayDateString())
            ->htmlTemplate('emails/notification/notification.html.twig')
            ->context([
                'user' => $user,
                'eventRecords' => $eventRecords
            ]);

        $this->mailer->sendTemplated($email);
    }
}
