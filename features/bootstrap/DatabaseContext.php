<?php

use Behat\Behat\Context\Context;
use Illuminate\Support\Facades\Artisan;

class DatabaseContext implements Context
{
    /**
     * @var \Illuminate\Foundation\Application
     */
    public $app;

    /**
     * @BeforeScenario
     */
    public function bootstrapLaravelAndDatabase()
    {
        // Jalankan Garbage Collector untuk membersihkan sisa objek skenario sebelumnya
        gc_collect_cycles();

        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');

        require_once __DIR__.'/../../vendor/autoload.php';

        $this->app = require __DIR__.'/../../bootstrap/app.php';
        $this->app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite' => [
                'driver'                  => 'sqlite',
                'database'                => ':memory:',
                'prefix'                  => '',
                'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            ],
        ]);

        Artisan::call('migrate:fresh');
        Artisan::call('db:seed', ['--class' => 'RoleSeeder']);
        Artisan::call('db:seed', ['--class' => 'KphSeeder']);
        Artisan::call('db:seed', ['--class' => 'KomoditasSeeder']);

        // Matikan query log untuk menghemat RAM selama pengetesan
        \Illuminate\Support\Facades\DB::connection()->disableQueryLog();
    }

    /**
     * @AfterScenario
     */
    public function cleanUpApp()
    {
        $this->app = null;
        gc_collect_cycles();
    }
}