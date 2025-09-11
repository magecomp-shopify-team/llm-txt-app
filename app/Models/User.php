<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Osiset\ShopifyApp\Contracts\ShopModel as IShopModel;
use Osiset\ShopifyApp\Traits\ShopModel;

class User extends Authenticatable implements IShopModel
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, ShopModel, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            // 'password' => 'hashed',
        ];
    }

    /**
     * Get the LLM settings for the user/shop
     */
    public function llmSetting()
    {
        return $this->hasOne(LlmSetting::class, 'user_id', 'id');
    }

    public function ensureRedirect(string $path = '/llms.txt', string $target = '/a/llms'): bool
    {
        $checkQuery = <<<'GRAPHQL'
        query ($query: String!) {
          urlRedirects(first: 1, query: $query) {
            edges {
              node {
                id
                path
                target
              }
            }
          }
        }
        GRAPHQL;

        $checkResponse = $this->api()->graph($checkQuery, [
            'query' => "path:$path",
        ]);

        $existing = $checkResponse['body']['data']['urlRedirects']['edges'][0]['node'] ?? null;

        if ($existing) {
            if ($existing['target'] === $target) {
                return true;
            }

            $updateMutation = <<<'GRAPHQL'
            mutation UrlRedirectUpdate($id: ID!, $urlRedirect: UrlRedirectInput!) {
              urlRedirectUpdate(id: $id, urlRedirect: $urlRedirect) {
                userErrors {
                  field
                  message
                }
              }
            }
            GRAPHQL;

            $variables = [
                'id' => $existing['id'],
                'urlRedirect' => [
                    'path' => $path,
                    'target' => $target,
                ],
            ];

            $updateResponse = $this->api()->graph($updateMutation, $variables);
            $errors = $updateResponse['body']['data']['urlRedirectUpdate']['userErrors'] ?? [];

            return empty($errors);
        }

        $createMutation = <<<'GRAPHQL'
        mutation UrlRedirectCreate($urlRedirect: UrlRedirectInput!) {
          urlRedirectCreate(urlRedirect: $urlRedirect) {
            userErrors {
              field
              message
            }
          }
        }
        GRAPHQL;

        $variables = [
            'urlRedirect' => [
                'path' => $path,
                'target' => $target,
            ],
        ];

        $createResponse = $this->api()->graph($createMutation, $variables);
        $errors = $createResponse['body']['data']['urlRedirectCreate']['userErrors'] ?? [];

        return count($errors) > 0 ? false : true;
    }
}
