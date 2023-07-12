<?php

declare(strict_types=1);

namespace Oct8pus\SelfSign;

use Exception;
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
            ->setName('certificate')
            ->setDescription('Generate self-signed SSL certificate')
            ->addArgument('destination', InputArgument::REQUIRED, 'Directory to save certificate')
            ->addArgument('domains', InputArgument::REQUIRED, 'Comma separated list of domains')
            ->addArgument('certificate_authority', InputArgument::REQUIRED, 'Certificate authority directory')
            ->addArgument('subject', InputArgument::OPTIONAL, 'Certificate subject')
            ->addUsage('test test.com,www.test.com,api.test.com test');
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
        $domains = explode(',', $input->getArgument('domains'));
        $authority = $input->getArgument('certificate_authority');
        $subject = $input->getArgument('subject');

        $style->info("generate self-signed SSL certificate for {$domains[0]}...");

        if (!str_ends_with($dir, \DIRECTORY_SEPARATOR)) {
            $dir .= \DIRECTORY_SEPARATOR;
        }

        if (!str_ends_with($authority, \DIRECTORY_SEPARATOR)) {
            $authority .= \DIRECTORY_SEPARATOR;
        }

        if (!file_exists($dir) && !mkdir($dir)) {
            throw new Exception('mkdir');
        }

        $style->writeln('check for openssl', OutputInterface::VERBOSITY_VERBOSE);

        $exe = 'openssl';

        if (!Helper::commandExists($exe)) {
            throw new Exception("{$exe} not installed");
        }

        $style->info('generate domain private key...');

        $command = "{$exe} genrsa -out {$dir}private.key 2048";

        $style->writeln($command, OutputInterface::VERBOSITY_VERBOSE);

        $stdout = '';
        $stderr = '';

        Helper::runCommand($command, $stdout, $stderr);
        Helper::log($style, $stdout, $stderr);

        $style->info('create certificate signing request...');

        if (empty($subject)) {
            $subject = "/C=RU/L=Moscow/O=8ctopus/CN={$domains[0]}";
        }

        if (Helper::isWindows()) {
            $command = <<<COMMAND
            {$exe} req -new -key {$dir}private.key -out {$dir}request.csr -subj "{$subject}"
            COMMAND;
        } else {
            $command = <<<COMMAND
            {$exe} req \\
            -new \\
            -key {$dir}private.key \\
            -out {$dir}request.csr \\
            -subj "{$subject}"
            COMMAND;
        }

        $style->writeln($command, OutputInterface::VERBOSITY_VERBOSE);

        Helper::runCommand($command, $stdout, $stderr);
        Helper::log($style, $stdout, $stderr);

        $style->info('create certificate config file...');

        $config = <<<'DATA'
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
            ++$i;
            $config .= "DNS.{$i} = {$domain}\n";
        }

        file_put_contents("{$dir}config.ext", $config);

        $style->writeln($config, OutputInterface::VERBOSITY_VERBOSE);

        $style->info('create signed certificate by certificate authority...');

        if (Helper::isWindows()) {
            $command = <<<COMMAND
            {$exe} x509 -req -in {$dir}request.csr -CA {$authority}certificate_authority.pem -CAkey {$authority}certificate_authority.key -CAcreateserial -out {$dir}certificate.pem -days 825 -sha256 -extfile {$dir}config.ext
            COMMAND;
        } else {
            $command = <<<COMMAND
            {$exe} x509 \\
            -req \\
            -in {$dir}request.csr \\
            -CA {$authority}certificate_authority.pem \\
            -CAkey {$authority}certificate_authority.key \\
            -CAcreateserial \\
            -out {$dir}certificate.pem \\
            -days 825 \\
            -sha256 \\
            -extfile {$dir}config.ext
            COMMAND;
        }

        $style->writeln($command, OutputInterface::VERBOSITY_VERBOSE);

        Helper::runCommand($command, $stdout, $stderr);
        Helper::log($style, $stdout, $stderr);

        $style->info('success!');

        return 0;
    }
}
