<?php

namespace TeamEric\JournalSarah\Command;

use Rvdv\Guzzle\Twitter\TwitterClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;

class ExportTweetsPdfCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('export:pdf')
            ->setDescription("Creer un fichier PDF d'après les tweets")
            ->addArgument(
                'filename',
                InputArgument::REQUIRED
            )
            ->addOption(
                'with-background',
                null,
                InputOption::VALUE_NONE
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('filename');

        $command = $this->getApplication()->find('export:latex');

        $rootDir = __DIR__ . '/../../../../';
        $tmpDir = $rootDir . '/tmp';

        $arguments = array(
          'command'  => 'export:latex',
          'filename' => $tmpDir . DIRECTORY_SEPARATOR . '/output.latex',
          '--with-background' => $input->getOption('with-background'),
        );

        $input = new ArrayInput($arguments);
        $returnCode = $command->run($input, $output);

        $cmd = sprintf('rm -rf %s', escapeshellarg($tmpDir . '/*'));
        $output->writeln($cmd);
        exec($cmd);

        $cmd = sprintf('cp -r %s %s', escapeshellarg($rootDir . '/Ressources/images'), escapeshellarg($tmpDir));
        $output->writeln($cmd);
        exec($cmd);

        passthru(sprintf("cd %s && pdflatex output.latex", escapeshellarg($tmpDir)));
        rename($tmpDir . "/output.pdf", $filename);

        $output->writeln(sprintf('Write %s', $filename));
    }
}
