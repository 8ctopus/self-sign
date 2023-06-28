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

class CommandCertificate extends Command
{
    /**
     * Configure command options
     *
     * @return void
     */
    protected function configure() : void
    {
        $this
            ->setName('generate')
            ->setDescription('Generate self-signed SSL certificate')
            ->addArgument('directory', InputArgument::REQUIRED, 'Directory to save certificate')
            ->addArgument('domains', InputArgument::REQUIRED, 'Comma separated list of domains')
            ->addArgument('authority', InputArgument::REQUIRED, 'Certificate authority directory');
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
        $domains = explode(',', $input->getArgument('domains'));
        $authority = $input->getArgument('authority');

        $io->info("generate self-signed SSL certificate for {$domains[0]}...");

        if (!str_ends_with($dir, DIRECTORY_SEPARATOR)) {
            $dir .= DIRECTORY_SEPARATOR;
        }

        if (!str_ends_with($authority, DIRECTORY_SEPARATOR)) {
            $authority .= DIRECTORY_SEPARATOR;
        }

        if (!file_exists($dir) && !mkdir($dir)) {
            throw new Exception('mkdir');
        }

        $io->writeln('check for openssl', OutputInterface::VERBOSITY_VERBOSE);

        $exe = 'openssl';

        if (!Helper::commandExists($exe)) {
            throw new Exception("{$exe} not installed");
        }

        $io->info('generate domain private key');

        $command = "{$exe} genrsa -out {$dir}private.key 2048";

        $io->writeln($command, OutputInterface::VERBOSITY_VERBOSE);

        $stdout = '';
        $stderr = '';

        Helper::runCommand($command, $stdout, $stderr);
        Helper::log($io, $stdout, $stderr);

        $io->info('create certificate signing request');

        $subject = "/C=RU/L=Moscow/O=8ctopus/CN={$domains[0]}";

        if (Helper::isWindows()) {
            $command = <<<COMMAND
            {$exe} req -new -key {$dir}private.key -out {$dir}request.csr -subj "{$subject}"
            COMMAND;
        } else {
            $command = <<<COMMAND
            {$exe} req \
            -new \
            -key {$dir}private.key \
            -out {$dir}request.csr \
            -subj "{$subject}"
            COMMAND;
        }

        $io->writeln($command, OutputInterface::VERBOSITY_VERBOSE);

        Helper::runCommand($command, $stdout, $stderr);
        Helper::log($io, $stdout, $stderr);

        $io->info('create certificate config file');

        $config = <<<DATA
        authorityKeyIdentifier=keyid,issuer
        basicConstraints=CA:FALSE
        keyUsage = digitalSignature, nonRepudiation, keyEncipherment, dataEncipherment
        subjectAltName = @alt_names
        [alt_names]

        DATA;

        /*
        DNS.1 = {$domains[0]} # Be sure to include the domain name here because Common Name is not so commonly honoured by itself
        DNS.2 = www.{$domains[1]} # add additional domains and subdomains if needed
        IP.1 = 192.168.0.13 # you can also add an IP address (if the connection which you have planned requires it)
        */

        foreach ($domains as $i => $domain) {
            $i++;
            $config .= "DNS.{$i} = {$domain}\n";
        }

        file_put_contents("{$dir}config.ext", $config);

        $io->writeln($config, OutputInterface::VERBOSITY_VERBOSE);

        $io->info('create signed certificate by certificate authority');

        if (Helper::isWindows()) {
            $command = <<<COMMAND
            {$exe} x509 -req -in {$dir}request.csr -CA {$authority}certificate_authority.pem -CAkey {$authority}certificate_authority.key -CAcreateserial -out {$dir}certificate.pem -days 825 -sha256 -extfile {$dir}config.ext
            COMMAND;
        } else {
            $command = <<<COMMAND
            {$exe} x509 \
            -req \
            -in {$dir}request.csr \
            -CA {$authority}certificate_authority.pem \
            -CAkey {$authority}certificate_authority.key \
            -CAcreateserial \
            -out {$dir}certificate.pem \
            -days 825 \
            -sha256 \
            -extfile {$dir}config.ext
            COMMAND;
        }

        $io->writeln($command, OutputInterface::VERBOSITY_VERBOSE);

        Helper::runCommand($command, $stdout, $stderr);
        Helper::log($io, $stdout, $stderr);

        $io->info('success!');

        return 0;
    }
}
