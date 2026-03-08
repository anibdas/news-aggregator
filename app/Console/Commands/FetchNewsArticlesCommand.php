<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NewsArticleAggregatorService;
use App\Models\Setting;
use Carbon\Carbon;
class FetchNewsArticlesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-news-articles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $news_sync_date = $page = '';
        $settings_data = Setting::whereIn('parameter', ['news_sync_date','page'])->get();
        foreach ($settings_data as $key => $data) {
            if($data['parameter'] == 'news_sync_date')
                $news_sync_date = $data['value'];
            if($data['parameter'] == 'page')
                $page = $data['value'];
        }
        if(empty($news_sync_date) || empty($page)){
            $this->info('Sync date or page doesnt exist! kindly run SettingsSeeder');
            return false;
        }

        if(Carbon::now()->toDateString() != $news_sync_date){
            Setting::where("parameter","news_sync_date")->update(['value' => Carbon::now()->toDateString()]);
            Setting::where('parameter','page')->update(['value' => 1]);
            $news_sync_date = Carbon::now()->toDateString();
            $page = 1;
        }

        $this->info('Starting to fetch news articles...');
        $aggregator = new NewsArticleAggregatorService();
        // Register all news sources
        $sources = [
            new \App\Services\NewsSources\NewsApiSource($news_sync_date, $page),
            // new \App\Services\NewsSources\TheGuardianSource($news_sync_date, $page),
            // new \App\Services\NewsSources\NewYorkTimesSource($news_sync_date, $page),
        ];

        $aggregator->aggregate($sources);
        Setting::where('parameter','page')->increment('value', 1);
        $this->info('Finished fetching news articles.');
        return Command::SUCCESS;
    }
}
