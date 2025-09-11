<?php

namespace App\Http\Controllers;

use App\Helpers\LlmFileGenerator;
use App\Models\LlmSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class LlmController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $cacheKey = "llm_settings_user_{$user->id}_url_redirect";

        $hasUrlRedirect = false;
        if (!Cache::has($cacheKey) || (Cache::has($cacheKey) && Cache::get($cacheKey) != 'true')) {
            $urlRedirectRec = config('mc-config.urlRedirects', []);
            $urlRedirectRec = $urlRedirectRec[0] ?? [];
            $urlRedirect = $user->ensureRedirect($urlRedirectRec['path'], $urlRedirectRec['target']);
            if ($urlRedirect) {
                $hasUrlRedirect = true;
                Cache::put($cacheKey, 'true', now()->addMinutes(30));
            }
        } else {
            $hasUrlRedirect = true;
        }


        $settings = LlmSetting::firstOrCreate(
            ['user_id' => $user->id],
            []
        );

        $response = response()->json([
            'settings' => [
                'includeProducts' => $settings->include_products ? 1 : 0,
                'includeCollections' => $settings->include_collections ? 1 : 0,
                'includePages' => $settings->include_pages ? 1 : 0,
                'includeBlogs' => $settings->include_blogs ? 1 : 0,
                'format' => $settings->format,
                'syncFrequency' => $settings->sync_frequency,
            ],
            'lastSyncedAt' => $settings->last_synced_at,
            'nextSyncedAt' => $settings->next_synced_at,
            'hasUrlRedirect' => $hasUrlRedirect,
        ]);

        return $response;
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'includeProducts' => 'boolean',
            'includeCollections' => 'boolean',
            'includePages' => 'boolean',
            'includeBlogs' => 'boolean',
            'format' => 'string|in:human,policy,json',
            'syncFrequency' => 'string|in:daily,weekly,monthly',
        ]);

        $settings = LlmSetting::firstOrNew(['user_id' => Auth::id()]);

        $lastSyncedAt = $settings->last_synced_at ?? Carbon::now();

        $nextSyncedAt = match ($validated['syncFrequency'] ?? 'weekly') {
            'daily' => Carbon::parse($lastSyncedAt)->addDay(),
            'weekly' => Carbon::parse($lastSyncedAt)->addWeek(),
            'monthly' => Carbon::parse($lastSyncedAt)->addMonth(),
            default => Carbon::parse($lastSyncedAt)->addWeek(),
        };

        $settings->fill([
            'include_products' => $validated['includeProducts'] ?? false,
            'include_collections' => $validated['includeCollections'] ?? false,
            'include_pages' => $validated['includePages'] ?? false,
            'include_blogs' => $validated['includeBlogs'] ?? false,
            'format' => $validated['format'] ?? 'human',
            'sync_frequency' => $validated['syncFrequency'] ?? 'weekly',
            'last_synced_at' => $lastSyncedAt,
            'next_synced_at' => $nextSyncedAt,
        ])->save();

        return response()->json([
            'message' => 'Settings saved successfully',
            'settings' => $settings,
        ]);
    }

    public function counts(Request $request)
    {
        $user = Auth::user();

        $cacheKey = "shopify_counts_{$user->id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user) {
            $query = <<<'GRAPHQL'
            query {
                productsCount {
                    count
                }
                collectionsCount {
                    count
                }
                pagesCount {
                    count
                }
                blogsCount {
                    count
                }
            }
            GRAPHQL;

            $response = $user->api()->graph($query);
            $data = $response['body']['data'] ?? [];

            return [
                'products' => $data['productsCount']['count'] ?? 0,
                'collections' => $data['collectionsCount']['count'] ?? 0,
                'pages' => $data['pagesCount']['count'] ?? 0,
                'blogs' => $data['blogsCount']['count'] ?? 0,
            ];
        });
    }

    public function generate(Request $request)
    {
        $shop = Auth::user();

        $generator = new LlmFileGenerator($shop);
        $filePath = $generator->generate();

        return response()->json([
            'message' => 'llms.txt generated successfully',
            'file' => $filePath,
            'url' => asset('storage/' . $shop->name . '/llms.txt')
        ]);
    }

}
