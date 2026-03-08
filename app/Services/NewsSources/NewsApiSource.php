<?php

namespace App\Services\NewsSources;

use App\Services\NewsSources\NewsSourceInterface;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class NewsApiSource implements NewsSourceInterface
{
    protected string $apiKey;
    protected string $baseUrl = 'https://newsapi.org/v2';

    public function __construct($sync_date, $page)
    {
        $this->apiKey = env('NEWS_API_KEY');
        $this->sync_date = $sync_date;    
        $this->page = $page;
    }

    public function getSourceName(): string
    {
        return 'NewsAPI';
    }

    public function fetchArticles(): array
    {
        $response = Http::get("{$this->baseUrl}/everything", [
            'apiKey' => $this->apiKey,
            'language' => 'en',
            'sortBy' => 'publishedAt',
            'pageSize' => 10,
            'from' => Carbon::parse($this->sync_date)->subDays(1)->toDateString(),
            'q' => 'technology',
            'page' => $this->page
        ]);
        if ($response->failed()) {
            return [];
        }

        $data = $response->json();
        $articles = [];

        foreach ($data['articles'] ?? [] as $article) {
            if (empty($article['title']) || empty($article['url']))
                continue;

            $articles[] = [
                'title' => $article['title'],
                'description' => $article['description'] ?? null,
                'content' => $article['content'] ?? null,
                'url' => $article['url'],
                'published_at' => isset($article['publishedAt']) ? Carbon::parse($article['publishedAt'])->toDateTimeString() : now(),
                'author_name' => $article['author']?? 'Unknown',
                'category_name' => 'Technology',
            ];
        }

        return $articles;
    }
}
