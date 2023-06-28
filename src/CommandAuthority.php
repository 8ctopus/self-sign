<?php

declare(strict_types=1);

namespace Oct8pus\SelfSign;

use Exception;
use Oct8pus\SelfSign\Helper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CommandAuthority extends Command
{
    /**
     * Configure command options
     *
     * @return void
     */
    protected function configure() : void
    {
        $this
            ->setName('authority')
            ->setDescription('Generate certificate authority')
            ->addArgument('directory', InputArgument::REQUIRED, 'Directory to save certificate authority')
            ->addArgument('subject', InputArgument::REQUIRED, 'Certificate authority subject');
    }

    /**
     * Execute command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        // beautify input, output interface
        $io = new SymfonyStyle($input, $output);

        $dir = $input->getArgument('directory');
        $subject = $input->getArgument('subject');

        if (!file_exists($dir) && !mkdir($dir)) {
            throw new Exception('mkdir');
        }

        if (!str_ends_with($dir, DIRECTORY_SEPARATOR)) {
            $dir .= DIRECTORY_SEPARATOR;
        }

        $io->writeln('check for openssl', OutputInterface::VERBOSITY_VERBOSE);

        $exe = 'openssl';

        if (!Helper::commandExists($exe)) {
            throw new Exception("{$exe} not installed");
        }

        $io->info("generate certificate authority private key...");

        $command = "{$exe} genrsa -out {$dir}certificate_authority.key 2048";

        $io->writeln($command, OutputInterface::VERBOSITY_VERBOSE);

        $stdout = '';
        $stderr = '';

        Helper::runCommand($command, $stdout, $stderr);
        Helper::log($io, $stdout, $stderr);

        $io->info('generate certificate authority certificate...');

        // to view certificate - openssl x509 -in certificate_authority.pem -noout -text
        $command = <<<COMMAND
        {$exe} req -new -x509 -nodes -key {$dir}certificate_authority.key -sha256 -days 825 -out {$dir}certificate_authority.pem -subj "{$subject}"
        COMMAND;

        $io->writeln($command, OutputInterface::VERBOSITY_VERBOSE);

        Helper::runCommand($command, $stdout, $stderr);
        Helper::log($io, $stdout, $stderr);

        $io->info('success!');

        return 0;
    }
}
