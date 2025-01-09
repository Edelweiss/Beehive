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
        $this->addOption('editions', 'i', InputOption::VALUE_REQUIRED, 'comma separated list of editions whose corrections need an update, such as --editions=2,45,67 OR »all«, but be warned »all« means 90 000 data sets');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
      echo ' = = = = = = = = = = = = = = = = '  . "\n";
      $flushCounter = 1;
      foreach($this->getCorrections($input->getOption('editions')) as $correction){
        echo str_pad($flushCounter, 8, ' ', STR_PAD_LEFT) . ': ' . $correction->getEditionId() . '/' . $correction->getId() . "\n";
        $correction->setSortValues();
        $this->entityManager->persist($correction);

        if(!($flushCounter++ % 500)){
          $this->entityManager->flush();
          echo '________________________________ F L U S H ________________________________' . "\n";
        }

      }

      $this->entityManager->flush();
      echo '________________________________ F L U S H ________________________________' . "\n";

	  return Command::SUCCESS;
      // return Command::FAILURE;
      // return Command::INVALID;
    }

    private function getCorrections($editions){
      $repository = $this->entityManager->getRepository(Correction::class);
      if($editions == 'all'){
        return $repository->findAll();
      } else if (preg_match('/^\d+(,\d+)*$/', $editions)) {
        
        return $repository->findBy(['edition' => explode(',', $editions));
      }
      return [];
    }

}
