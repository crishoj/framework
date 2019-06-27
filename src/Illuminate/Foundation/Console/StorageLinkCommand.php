<?php

namespace Illuminate\Foundation\Console;

use Illuminate\Console\Command;

class StorageLinkCommand extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'storage:link
                    {--absolute : Use an absolute pathname in the link (default is relative)}
                    {--force : Overwrite existing link}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a symbolic link from "public/storage" to "storage/app/public"';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $publicPath = public_path('storage');
        $targetPath = storage_path('app/public');

        // For broken symlinks, `file_exists()` returns `false`, but `is_link()` returns `true`
        if (is_link($publicPath) || file_exists($publicPath)) {
            if (! $this->option('force')) {
                return $this->error('The "public/storage" directory already exists.');
            }

            if (! unlink($publicPath)) {
                return $this->error('Failed to remove existing "public/storage" directory.');
            }

            $this->warn('Removed existing "public/storage" directory.');
        }

        if (! $this->option('absolute')) {
            $targetPath = $this->relativePath($targetPath, public_path());
        }

        $this->laravel->make('files')->link($targetPath, $publicPath);

        $this->info('The [public/storage] directory has been linked.');
    }

    /**
     * Compute the relative path to a target from a given base path.
     *
     * @param string $target
     * @param string $basePath
     * @return string
     */
    protected function relativePath($target, $basePath)
    {
        // Find length of common prefix
        $i = 0;
        $limit = min(strlen($target), strlen($basePath));
        while ($i < $limit && $basePath[$i] === $target[$i]) {
            $i++;
        }

        $path = ltrim(substr($target, $i), DIRECTORY_SEPARATOR);
        $tail = substr($basePath, $i);

        if (strlen($tail)) {
            $levelsUp = substr_count($tail, DIRECTORY_SEPARATOR) + 1;

            return str_repeat('..'.DIRECTORY_SEPARATOR, $levelsUp).$path;
        }

        return $path;
    }
}
