<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use DateTime;
use App\Entity\Correction;
use Doctrine\ORM\EntityManagerInterface;

ini_set('memory_limit', -1);

class UpdateSortCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:update-sort';
    protected $entityManager;

    function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('editions', InputOption::VALUE_REQUIRED, 'e', 'comma separated list of editions whose corrections need an update, such as --editions=2,45,67'); // --editions=2,45,67
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach($input->getOption('editions') as $editionId){
          echo ' = = = = = = = = = = = = = = = = '  . "\n";
          echo '' . $editionId . "\n";
        }



        //echo str_pad($row, 6, ' ', STR_PAD_LEFT) . ' Zeilen bearbeitet'  . "\n";

	    return Command::SUCCESS;
        // return Command::FAILURE;
        // return Command::INVALID;
    }

}