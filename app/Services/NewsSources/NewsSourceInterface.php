<?php

namespace App\Services\NewsSources;

interface NewsSourceInterface
{
    /**
     * Get the unique identifier for the news source.
     */
    public function getSourceName(): string;

    /**
     * Fetch articles from the news source.
     * 
     * @return array
     */
    public function fetchArticles(): array;
}
