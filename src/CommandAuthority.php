<?php

declare(strict_types=1);

namespace Oct8pus\SelfSign;

use Exception;
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
            ->addArgument('destination', InputArgument::REQUIRED, 'Directory to save certificate authority')
            ->addArgument('subject', InputArgument::OPTIONAL, 'Certificate authority subject')
            ->addUsage('test /CN=RU/O=8ctopus');
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
        $style = new SymfonyStyle($input, $output);

        $dir = $input->getArgument('destination');
        $subject = $input->getArgument('subject');

        if (empty($subject)) {
            $subject = '/CN=RU/O=8ctopus';
        }

        if (!file_exists($dir) && !mkdir($dir)) {
            throw new Exception('mkdir');
        }

        if (!str_ends_with($dir, \DIRECTORY_SEPARATOR)) {
            $dir .= \DIRECTORY_SEPARATOR;
        }

        $style->writeln('check for openssl', OutputInterface::VERBOSITY_VERBOSE);

        $exe = 'openssl';

        if (!Helper::commandExists($exe)) {
            throw new Exception("{$exe} not installed");
        }

        $style->info('generate certificate authority private key...');

        $command = "{$exe} genrsa -out {$dir}certificate_authority.key 2048";

        $style->writeln($command, OutputInterface::VERBOSITY_VERBOSE);

        Helper::runCommand($command, $style);

        $style->info('generate certificate authority certificate...');

        // to view certificate - openssl x509 -in certificate_authority.pem -noout -text
        $command = <<<COMMAND
        {$exe} req -new -x509 -nodes -key {$dir}certificate_authority.key -sha256 -days 825 -out {$dir}certificate_authority.pem -subj "{$subject}"
        COMMAND;

        $style->writeln($command, OutputInterface::VERBOSITY_VERBOSE);

        Helper::runCommand($command, $style);

        $style->info('success!');

        return 0;
    }
}
