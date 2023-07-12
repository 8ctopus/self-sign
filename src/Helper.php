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
        $process = proc_open($command, [
            0 => ['pipe', 'r'], // stdin
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w'], // stderr
        ], $pipes, null);

        if (!is_resource($process)) {
            throw new Exception('open process');
        }

        /*
        while (1) {
            $status = proc_get_status($process);

            if (!$status['running']) {
                break;
            }

            sleep(10);
        }
        */

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $status = proc_close($process);

        // REM $status = $status['exitcode'];

        if ($status !== 0) {
            throw new Exception("command exit code - {$status} - {$command}");
        }
    }

    /**
     * Run command alternate
     *
     * @param string $command
     * @param string &$stdout
     * @param string &$stderr
     *
     * @return void
     */
    public static function runCommandAlternate(string $command, string &$stdout, string &$stderr) : void
    {
        $stderr = '';

        $result = exec($command, $output, $status);

        $stdout = implode(PHP_EOL, $output);

        // check command exit code
        if ($result === false || $status !== 0) {
            throw new Exception("command exit code - {$status} - {$command}");
        }
    }

    public static function log(SymfonyStyle $io, string $stdout, string $stderr) : void
    {
        if (!empty($stdout)) {
            $io->info($stdout);
        }

        if (!empty($stderr)) {
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
