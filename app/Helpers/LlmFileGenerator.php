<?php

namespace App\Helpers;

use App\Models\LlmSetting;
use App\Models\LlmSyncHistory;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LlmFileGenerator
{
    protected $shop;

    public function __construct(User $shop)
    {
        $this->shop = $shop;
    }

    /**
     * Generate the llms.txt file with shop data
     */
    public function generate(): string
    {
        if(!$this->shop->plan_id && !$this->shop->shopify_freemium) {
            return '';
        }

        $settings = $this->shop->llmSetting;

        $shopInfo = $this->getShopInfo();
        $domain = $shopInfo['primaryDomain']['url'] ?? ('https://' . $shopInfo['myshopifyDomain']);

        $content = "# Shop Information\n";
        $content .= "- **Name:** {$shopInfo['name']}\n";
        $content .= "- **Description:** " . ($shopInfo['description'] ?? '-') . "\n";
        $content .= "- **Domain:** {$domain}\n";
        $content .= "\n---\n\n";

        $newCounts = [
            'products' => 0,
            'collections' => 0,
            'blogs' => 0,
            'pages' => 0,
        ];

        // Products
        if ($settings?->include_products) {
            $products = $this->getProducts();
            $newCounts['products'] = count($products);

            $content .= "# Products\n";
            foreach ($products as $i => $edge) {
                $p = $edge['node'];
                $url = "{$domain}/products/{$p['handle']}";
                $desc = !empty($p['description']) ? " — {$p['description']}" : "";
                $content .= ($i + 1) . ". [{$p['title']}]({$url}){$desc}\n";
            }
            $content .= "\n---\n\n";
        }

        // Collections
        if ($settings?->include_collections) {
            $collections = $this->getCollections();
            $newCounts['collections'] = count($collections);

            $content .= "# Collections\n";
            foreach ($collections as $i => $edge) {
                $c = $edge['node'];
                $url = "{$domain}/collections/{$c['handle']}";
                $desc = !empty($c['description']) ? " — {$c['description']}" : "";
                $content .= ($i + 1) . ". [{$c['title']}]({$url}){$desc}\n";
            }
            $content .= "\n---\n\n";
        }

        // Blogs
        if ($settings?->include_blogs) {
            $blogs = $this->getBlogs();
            $newCounts['blogs'] = count($blogs);

            $content .= "# Blogs\n";
            foreach ($blogs as $i => $edge) {
                $b = $edge['node'];
                $url = "{$domain}/blogs/{$b['handle']}";
                $desc = !empty($b['seo']['description']) ? " — {$b['seo']['description']}" : "";
                $content .= ($i + 1) . ". [{$b['title']}]({$url}){$desc}\n";
            }
            $content .= "\n---\n\n";
        }

        // Pages
        if ($settings?->include_pages) {
            $pages = $this->getPages();
            $newCounts['pages'] = count($pages);

            $content .= "# Pages\n";
            foreach ($pages as $i => $edge) {
                $pg = $edge['node'];
                $url = "{$domain}/pages/{$pg['handle']}";
                $desc = !empty($pg['bodySummary']) ? " — {$pg['bodySummary']}" : "";
                $content .= ($i + 1) . ". [{$pg['title']}]({$url}){$desc}\n";
            }
        }

        // Save file
        $folderName = $this->shop->name;
        $filePath = $folderName . '/llms.txt';
        Storage::disk('public')->put($filePath, $content);

        // Ensure redirect
        $urlRedirect = config('mc-config.urlRedirects', []);
        $urlRedirect = $urlRedirect[0] ?? ['path' => '/llms.txt', 'target' => '/a/llms'];
        $this->shop->ensureRedirect($urlRedirect['path'], $urlRedirect['target']);

        // Sync history
        $this->sync($this->shop, $newCounts);

        return Storage::disk('public')->path($filePath);
    }

    protected function getShopInfo()
    {
        $query = <<<'GRAPHQL'
        {
          shop {
            name
            description
            myshopifyDomain
            primaryDomain {
              url
            }
          }
        }
        GRAPHQL;

        $response = $this->shop->api()->graph($query);
        return $response['body']['data']['shop'] ?? [];
    }

    protected function getLimit(string $resource): int|string
    {
        $planId = $this->shop->plan_id ?? 1;
        $planConfig = config('mc-config.plan_config.' . $planId, []);

        return $planConfig[$resource] ?? 0;
    }

    protected function fetchAll(string $resource, string $nodeFields): array
    {
        $limit = $this->getLimit($resource);
        if ($limit === 0) {
            return [];
        }

        $edges = [];
        $hasNextPage = true;
        $cursor = null;

        // Shopify max per query = 250
        $perPage = ($limit === 'ALL') ? 250 : min(250, (int) $limit);

        while ($hasNextPage && ($limit === 'ALL' || count($edges) < $limit)) {
            // GraphQL query with variables
            $query = <<<GRAPHQL
                query fetchResource(\$cursor: String) {
                  {$resource}(first: {$perPage}, after: \$cursor) {
                    edges {
                    cursor
                    node {
                        {$nodeFields}
                      }
                    }
                    pageInfo {
                      hasNextPage
                    }
                  }
                }
                GRAPHQL;

            $variables = ['cursor' => $cursor];

            $response = $this->shop->api()->graph($query, $variables);
            $data = $response['body']['data'][$resource] ?? [];

            if (!empty($data['edges'])) {
                foreach ($data['edges'] as $edge) {
                    $edges[] = $edge;
                    if ($limit !== 'ALL' && count($edges) >= $limit) {
                        break 2; // stop if limit reached
                    }
                }
                $cursor = end($data['edges'])['cursor'] ?? null;
            }

            $hasNextPage = $data['pageInfo']['hasNextPage'] ?? false;
        }

        return $edges;
    }


    protected function getProducts()
    {
        return $this->fetchAll('products', 'title handle description');
    }

    protected function getCollections()
    {
        return $this->fetchAll('collections', 'title handle description');
    }

    protected function getBlogs()
    {
        return $this->fetchAll('blogs', 'title handle seo { title description }');
    }

    protected function getPages()
    {
        return $this->fetchAll('pages', 'title handle bodySummary');
    }

    public function sync(User $shop, array $newCounts)
    {
        DB::transaction(function () use ($shop, $newCounts) {
            $settings = LlmSetting::firstOrCreate(
                ['user_id' => $shop->id],
                ['sync_frequency' => 'weekly']
            );

            $lastHistory = LlmSyncHistory::where('user_id', $shop->id)
                ->latest('id')
                ->first();

            $oldCounts = [
                'products' => $lastHistory->products_new ?? 0,
                'collections' => $lastHistory->collections_new ?? 0,
                'pages' => $lastHistory->pages_new ?? 0,
                'blogs' => $lastHistory->blogs_new ?? 0,
            ];

            $diff = [
                'products_added' => max(0, $newCounts['products'] - $oldCounts['products']),
                'products_removed' => max(0, $oldCounts['products'] - $newCounts['products']),
                'collections_added' => max(0, $newCounts['collections'] - $oldCounts['collections']),
                'collections_removed' => max(0, $oldCounts['collections'] - $newCounts['collections']),
                'pages_added' => max(0, $newCounts['pages'] - $oldCounts['pages']),
                'pages_removed' => max(0, $oldCounts['pages'] - $newCounts['pages']),
                'blogs_added' => max(0, $newCounts['blogs'] - $oldCounts['blogs']),
                'blogs_removed' => max(0, $oldCounts['blogs'] - $newCounts['blogs']),
            ];

            LlmSyncHistory::create([
                'user_id' => $shop->id,
                'synced_from' => $settings->last_synced_at,
                'synced_to' => now(),

                'products_old' => $oldCounts['products'],
                'products_new' => $newCounts['products'],
                'products_added' => $diff['products_added'],
                'products_removed' => $diff['products_removed'],

                'collections_old' => $oldCounts['collections'],
                'collections_new' => $newCounts['collections'],
                'collections_added' => $diff['collections_added'],
                'collections_removed' => $diff['collections_removed'],

                'pages_old' => $oldCounts['pages'],
                'pages_new' => $newCounts['pages'],
                'pages_added' => $diff['pages_added'],
                'pages_removed' => $diff['pages_removed'],

                'blogs_old' => $oldCounts['blogs'],
                'blogs_new' => $newCounts['blogs'],
                'blogs_added' => $diff['blogs_added'],
                'blogs_removed' => $diff['blogs_removed'],
            ]);

            $settings->last_synced_at = now();
            $settings->next_synced_at = $this->calculateNextSync($settings->sync_frequency);
            $settings->save();
        });
    }

    /**
     * Calculate next sync time based on frequency
     */
    protected function calculateNextSync(string $frequency): Carbon
    {
        return match ($frequency) {
            'hourly' => now()->addHour(),
            'daily' => now()->addDay(),
            'weekly' => now()->addWeek(),
            'monthly' => now()->addMonth(),
            default => now()->addDay(),
        };
    }

}
