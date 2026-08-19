<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class KdpBackup extends Command
{
    protected $signature = 'kdp:backup {--output= : Custom output directory}';

    protected $description = 'Create a full backup of the database and storage files';

    public function handle(): int
    {
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $backupDir = $this->option('output') ?? storage_path('backups');

        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $dbFile = $backupDir."/backup_{$timestamp}.sql";
        $storageDir = storage_path('app/public');
        $archiveFile = "{$backupDir}/backup_{$timestamp}.tar.gz";

        $connection = config('database.default');
        $this->info("Backing up database connection: {$connection}");

        $dumpCommand = $this->buildDumpCommand($connection, $dbFile);

        $this->info('Dumping database...');
        $process = Process::fromShellCommandline($dumpCommand);
        $process->setTimeout(null);
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if (! $process->isSuccessful()) {
            $this->error('Database dump failed: '.$process->getErrorOutput());

            return self::FAILURE;
        }

        $this->info('Database dumped successfully to: '.$dbFile);

        $this->info('Archiving files...');
        $archiveProcess = new Process([
            'tar', '-czf', $archiveFile,
            '-C', dirname($dbFile), basename($dbFile),
            '-C', dirname($storageDir), basename($storageDir),
        ]);
        $archiveProcess->run();

        if (! $archiveProcess->isSuccessful()) {
            $this->error('Archive creation failed');

            return self::FAILURE;
        }

        unlink($dbFile);

        $this->info('Backup completed: '.$archiveFile);
        $this->info('Size: '.$this->formatBytes(filesize($archiveFile)));

        return self::SUCCESS;
    }

    protected function buildDumpCommand(string $connection, string $outputFile): string
    {
        $config = config("database.connections.{$connection}");

        return match ($connection) {
            'mysql' => sprintf(
                'mysqldump -h %s -u %s -p%s %s > %s',
                $config['host'],
                $config['username'],
                $config['password'] ?? '',
                $config['database'],
                $outputFile
            ),
            'pgsql' => sprintf(
                'pg_dump -h %s -U %s -d %s -f %s',
                $config['host'],
                $config['username'],
                $config['database'],
                $outputFile
            ),
            'sqlite' => sprintf(
                'cp %s %s',
                $config['database'],
                $outputFile
            ),
            default => throw new \InvalidArgumentException("Unsupported database driver: {$connection}")
        };
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2).' '.$units[$pow];
    }
}
