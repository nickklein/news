<?php

namespace NickKlein\News\Commands;

use NickKlein\News\Seeders\SourcesTableSeeder;
use NickKlein\News\Seeders\UserSourcesTableSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use NickKlein\News\Seeders\NewsSummaryTableSeeder;
use NickKlein\News\Seeders\SourceLinksTableSeeder;

class RunSeederCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:news-seeder';

    /**
     * The console Clean up user related things.
     *
     * @var string
     */
    protected $description = 'Runs Seeder for News';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Artisan::call('db:seed', ['--class' => UserSourcesTableSeeder::class]);
        $this->info('UserSourcesTableSeeder executed successfully.');

        Artisan::call('db:seed', ['--class' => SourcesTableSeeder::class]);
        $this->info('SourcesTableSeeder executed successfully.');

        Artisan::call('db:seed', ['--class' => SourceLinksTableSeeder::class]);
        $this->info('SourceLinksTableSeeder executed successfully.');

        Artisan::call('db:seed', ['--class' => NewsSummaryTableSeeder::class]);
        $this->info('NewsSummaryTableSeeder executed successfully.');
    }
}
