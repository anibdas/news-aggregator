<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Author;
use App\Models\Category;
use App\Models\Source;
use Illuminate\Support\Facades\Log;

class NewsArticleAggregatorService
{
    public function __construct()
    {
        //
    }

    public function aggregate($sources): void
    {
        foreach ($sources as $sourceImplementation) {
            $sourceName = $sourceImplementation->getSourceName();
            Log::info("Starting aggregation from source: " . $sourceName);

            try {
                $articlesData = $sourceImplementation->fetchArticles();
                $sourceModel = Source::firstOrCreate(["name" => $sourceName]);

                $newArticlesCount = 0;

                foreach ($articlesData as $data) {
                    $categoryModel = null;
                    if (!empty($data["category_name"])) {
                        $categoryModel = Category::firstOrCreate(["name" => $data["category_name"]]);
                    }

                    $authorModel = null;
                    if (!empty($data["author_name"])) {
                        $authorStr = is_string($data["author_name"]) ? substr($data["author_name"], 0, 255) : "Unknown";
                        $authorModel = Author::firstOrCreate(["name" => $authorStr]);
                    }

                    $existingArticle = Article::where("url", $data["url"])->first();

                    if (!$existingArticle) {
                        Article::create([
                            "title" => substr($data["title"], 0, 255),
                            "description" => $data["description"],
                            "content" => $data["content"],
                            "url" => substr($data["url"], 0, 255),
                            "image_url" => isset($data["image_url"]) ? substr($data["image_url"], 0, 255) : null,
                            "published_at" => $data["published_at"],
                            "source_id" => $sourceModel->id,
                            "category_id" => $categoryModel?->id,
                            "author_id" => $authorModel?->id,
                        ]);
                        $newArticlesCount++;
                    }
                }

                Log::info("Finished aggregation from: " . $sourceName . ". Inserted " . $newArticlesCount . " new articles.");
            } catch (\Exception $e) {
                Log::error("Error aggregating from " . $sourceName . ": " . $e->getMessage());
            }
        }
    }
}

