<?php

namespace App\Command;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:client:add',
    description: 'Add a new client from the command line',
)]
class AddClientCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('firstname', InputArgument::REQUIRED, 'Client firstname')
            ->addArgument('lastname', InputArgument::REQUIRED, 'Client lastname')
            ->addArgument('email', InputArgument::REQUIRED, 'Client email')
            ->addArgument('phone', InputArgument::REQUIRED, 'Client phone number')
            ->addArgument('address', InputArgument::REQUIRED, 'Client address');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $client = new Client();
        $client->setFirstname($input->getArgument('firstname'));
        $client->setLastname($input->getArgument('lastname'));
        $client->setEmail($input->getArgument('email'));
        $client->setPhoneNumber($input->getArgument('phone'));
        $client->setAddress($input->getArgument('address'));

        $errors = $this->validator->validate($client);

        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $io->error($error->getPropertyPath() . ': ' . $error->getMessage());
            }
            return Command::FAILURE;
        }

        $this->entityManager->persist($client);
        $this->entityManager->flush();

        $io->success(sprintf('Client "%s %s" created successfully with ID %d.',
            $client->getFirstname(),
            $client->getLastname(),
            $client->getId()
        ));

        return Command::SUCCESS;
    }
}
