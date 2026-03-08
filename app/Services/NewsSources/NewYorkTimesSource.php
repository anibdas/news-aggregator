<?php

namespace App\Services\NewsSources;

use App\Services\NewsSources\NewsSourceInterface;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class NewYorkTimesSource implements NewsSourceInterface
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.nytimes.com/svc/search/v2';
    public $sync_date;
    public $page;
    public function __construct($sync_date, $page)
    {
        $this->apiKey = env('NY_API_KEY','');
        $this->sync_date = $sync_date;    
        $this->page = $page;    
    }

    public function getSourceName(): string
    {
        return 'New York Times';
    }

    public function fetchArticles(): array
    {
        $response = Http::get("{$this->baseUrl}/articlesearch.json", [
            'api-key' => $this->apiKey,
            'sort' => 'newest',
            'page' => $this->page,
            'begin_date' => Carbon::createFromFormat('Y-m-d', $this->sync_date)->format("Ymd"),
        ]);
        if ($response->failed()) {
            return [];
        }

        $data = $response->json();
        $articles = [];
        foreach ($data['response']['docs'] ?? [] as $article) {
            if (empty($article['headline']['main']) || empty($article['web_url']))
                continue;

            $authorName = 'Unknown';
            if (isset($article['byline']['original'])) {
                $authorName = $article['byline']['original'];
            }

            $articles[] = [
                'title' => $article['headline']['main'],
                'description' => $article['abstract'] ?? $article['snippet'] ?? null,
                'content' => $article['lead_paragraph'] ?? null,
                'url' => $article['web_url'],
                'published_at' => isset($article['pub_date']) ? Carbon::parse($article['pub_date'])->toDateTimeString() : now(),
                'author_name' => $authorName,
                'category_name' => $article['section_name'] ?? 'General',
            ];
        }

        return $articles;
    }
}
