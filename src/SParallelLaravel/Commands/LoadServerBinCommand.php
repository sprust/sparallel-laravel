<?php

declare(strict_types=1);

namespace SParallelLaravel\Commands;

use Illuminate\Console\Command;
use SParallelLaravel\Services\ServerBinLoader;

class LoadServerBinCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sparallel:server:load';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load server binary file';

    public function handle(): int
    {
        $loader = new ServerBinLoader(
            path: config('sparallel.server.bin-path')
        );

        $this->components->info('Loading server bin...');

        $this->components->task(
            "Downloading server bin [{$loader->getVersion()}]",
            static function () use ($loader) {
                $loader->load();
            }
        );

        $this->components->info('Server bin loaded');

        return self::SUCCESS;
    }
}
