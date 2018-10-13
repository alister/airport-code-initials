<?php

namespace App\Command;

use App\GetIataCodes;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GetIataCodesCommand extends Command
{
    protected static $defaultName = 'app:get-iata-codes';

    /** @var GetIataCodes */
    private $iataCodes;

    public function __construct(GetIataCodes $iataCodes)
    {
        parent::__construct(self::$defaultName);

        $this->iataCodes = $iataCodes;
    }

    protected function configure()
    {
        $this
            ->setDescription('Process the iata codes to CSV & Javascript data-lookup-module')
            ->addOption('latest', null, InputOption::VALUE_NONE, 'Download the latest IATA list')
            ->addOption('process', null, InputOption::VALUE_OPTIONAL, 'Process the named (or newly downloaded) IATA list')
            ->addOption('output', null, InputOption::VALUE_OPTIONAL, 'Output filename basename (data. [csv|js])', 'data')
            #->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $filename = $this->getFileNameToProcess($input);
        
        if (!$filename) {
            $io->error('No file to process');
            
            return 1;
        }

        $io->success("processing '{$filename}'");

        $outputFileBase = 'data';
        if ($input->getOption('output')) {
            $outputFileBase = $input->getOption('output');
        }

        $this->iataCodes->writeSummaryToCsv($filename, $outputFileBase . '.csv');
        $this->iataCodes->writeSummaryToJsModuleArray($filename, $outputFileBase . '.js');

        return 0;
    }

    protected function getFileNameToProcess(InputInterface $input): ?string
    {
        $filename = null;
        if ($input->getOption('latest')) {
            $filename = $this->iataCodes->downloadLatestIataCodesList();
        }

        $processFilename = $input->getOption('process');
        if ($processFilename === null && $filename) {
            $processFilename = $filename;
        }
        
        return $processFilename;
    }
}
