<?php

namespace App\Command;

use App\Service\Notification\SendNotificationEmails;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'notification:send-emails',
    description: 'Send notification emails to user which did not read the notification',
)]
class NotificationSendEmailsCommand extends Command
{
    public function __construct(
        private readonly SendNotificationEmails $notificationService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->notificationService->execute();

        $io = new SymfonyStyle($input, $output);
        $io->success('Successfully sent emails');

        return Command::SUCCESS;
    }
}
