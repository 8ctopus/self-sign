<?php

declare(strict_types=1);

namespace Oct8pus\SelfSign;

use Exception;
use Symfony\Component\Console\Style\SymfonyStyle;

class Helper
{
    /**
     * Check if command is installed
     *
     * @param string $command
     *
     * @return bool true if installed, otherwise false
     */
    public static function commandExists(string $command) : bool
    {
        $return = shell_exec(sprintf('which %s', escapeshellarg($command)));
        return !empty($return);
    }

    /**
     * Run command
     *
     * @param string $command
     * @param string $stdout
     * @param string $stderr
     *
     * @return void
     */
    public static function runCommand(string $command, string &$stdout, string &$stderr) : void
    {
        $descriptorSpec = [
            // stdin
            0 => ['pipe', 'r'],
            // stdout
            1 => ['pipe', 'w'],
            // stderr
            2 => ['pipe', 'w'], //["file", "/tmp/error-output.txt", "a"],
        ];

        $process = proc_open($command, $descriptorSpec, $pipes, null, null, null);

        if (!is_resource($process)) {
            throw new Exception('open openssl process');
        }

        // write input data
        //fwrite($pipes[0], 'test');
        fclose($pipes[0]);

        while (1) {
            $status = proc_get_status($process);

            if (!$status['running']) {
                break;
            }

            sleep(10);
        }

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        // it is important to close all pipes before calling proc_close in order to avoid a deadlock
        $exitCode = proc_close($process);

        if ($exitCode !== 0) {
            throw new Exception("process exit code {$exitCode}");
        }
    }

    public static function log(SymfonyStyle $io, string $stdout, string $stderr) : void
    {
        if (!empty($stdout)) {
            $io->info($stdout);
        }

        if (!empty($error)) {
            $io->error($stderr);
        }
    }

    /**
     * Check if running in Windows
     *
     * @return bool
     */
    public static function isWindows() : bool
    {
        return strtoupper(substr(php_uname('s'), 0, 3)) === 'WIN';
    }
}
