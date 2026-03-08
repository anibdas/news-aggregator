<?php

namespace App\Services\NewsSources;

use App\Services\NewsSources\NewsSourceInterface;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class TheGuardianSource implements NewsSourceInterface
{
    protected string $apiKey;
    protected string $baseUrl = 'https://content.guardianapis.com';

    public function __construct($sync_date, $page)
    {
        $this->apiKey = env('GUARDIAN_API_KEY','');
        $this->sync_date = $sync_date;    
        $this->page = $page;
    }

    public function getSourceName(): string
    {
        return 'The Guardian';
    }

    public function fetchArticles(): array
    {
        $response = Http::get("{$this->baseUrl}/search", [
            'api-key' => $this->apiKey,
            'show-fields' => 'headline,trailText,body,thumbnail,byline',
            'page-size' => 20,
            'page' => $this->page,
            'from-date' => $this->sync_date,
        ]);

        if ($response->failed()) {
            return [];
        }

        $data = $response->json();
        $articles = [];

        foreach ($data['response']['results'] ?? [] as $article) {
            $fields = $article['fields'] ?? [];
            if (empty($article['webTitle']) || empty($article['webUrl']))
                continue;

            $articles[] = [
                'title' => $article['webTitle'],
                'description' => $fields['trailText'] ?? null,
                'content' => $fields['body'] ?? null,
                'url' => $article['webUrl'],
                'published_at' => isset($article['webPublicationDate']) ?Carbon::parse($article['webPublicationDate'])->toDateTimeString() : now(),
                'author_name' => $fields['byline'] ?? 'Unknown',
                'category_name' => $article['sectionName'] ?? 'General',
            ];
        }

        return $articles;
    }
}
