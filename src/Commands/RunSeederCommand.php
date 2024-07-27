<?php

namespace NickKlein\News\Commands;

use NickKlein\News\Seeders\SourcesTableSeeder;
use NickKlein\News\Seeders\UserSourcesTableSeeder;
use NickKlein\News\Seeders\UserTagsTableSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use NickKlein\News\Seeders\TagsTableSeeder;

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

        Artisan::call('db:seed', ['--class' => TagsTableSeeder::class]);
        $this->info('TagsTableSeeder executed successfully.');

        Artisan::call('db:seed', ['--class' => UserSourcesTableSeeder::class]);
        $this->info('UserSourcesTableSeeder executed successfully.');

        Artisan::call('db:seed', ['--class' => UserTagsTableSeeder::class]);
        $this->info('UserTagsTableSeeder executed successfully.');

        Artisan::call('db:seed', ['--class' => SourcesTableSeeder::class]);
        $this->info('SourcesTableSeeder executed successfully.');
    }
}
