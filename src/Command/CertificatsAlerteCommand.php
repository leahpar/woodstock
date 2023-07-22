<?php

namespace App\Command;

use App\Entity\Certificat;
use App\Entity\Notification;
use App\Notifier\NotifierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:certificats:alerte',
    description: 'Add a short description for your command',
)]
class CertificatsAlerteCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly NotifierService $notifier,
    ) {
        parent::__construct();
    }

//    protected function configure(): void
//    {
//        $this
//            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
//            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
//        ;
//    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $certificats = $this->em->getRepository(Certificat::class)->findBy(['alerte' => false]);

        /** @var Certificat $certificat */
        foreach ($certificats as $certificat) {
            if ($certificat->isExpire()) {
                $certificat->alerte = true;
                $io->write('[  EXPIRE] ');
            }
            elseif ($certificat->dateAlerteExpiration() == null) {
                $io->write('[EN COURS] ');
            }
            elseif ($certificat->dateAlerteExpiration() > new \DateTime()) {
                $io->write('[EN COURS] ');
            }
            else {
                $msg = 'Le certificat ' . $certificat . ' arrive à expiration.';
                $this->notifier->notif('ROLE_CERTIFICAT_EDIT', $certificat, $msg);
                $certificat->alerte = true;
                $io->write('[  ALERTE] ');
            }
            $io->writeln('Certificat : ' . $certificat);
        }

        $this->em->flush();
        $io->success('OK');
        return Command::SUCCESS;
    }
}
