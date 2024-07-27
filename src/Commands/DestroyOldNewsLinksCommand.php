<?php

namespace NickKlein\News\Commands;

use App\Models\SourceLinks;
use App\Models\SourcesFavourites;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DestroyOldNewsLinksCommand extends Command
{
    const DAYS = 7;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:destroyOldNewsLinks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Destroy old news links';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $sourceFavouritesArray = SourcesFavourites::all()->pluck('source_link_id')->toArray();
        SourceLinks::where('created_at', '<', Carbon::now()->subDay(self::DAYS))->whereNotIn('source_link_id', $sourceFavouritesArray)->delete();
    }
}
