<?php

namespace App\Command;

use App\Factory\UserFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Creates an admin user',
)]
class CreateAdminCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Creating admin user');

        $email = $io->ask('Enter email');
        $firstName = $io->ask('Enter first name');
        $lastName = $io->ask('Enter last name');
        $password = $io->askHidden('Enter password');
        $passwordConfirm = $io->askHidden('Confirm password');

        if ($password !== $passwordConfirm) {
            $io->error('Passwords do not match');
            return Command::FAILURE;
        }

        UserFactory::new()
            ->withAdminRole()
            ->create([
                'email' => $email,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'plainPassword' => $password,
            ]);

        $io->success('Admin user created.');

        return Command::SUCCESS;
    }
}
