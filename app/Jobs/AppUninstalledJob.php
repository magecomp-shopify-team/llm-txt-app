<?php

namespace App\Jobs;

use App\Models\LlmSetting;
use Illuminate\Support\Facades\Storage;
use Osiset\ShopifyApp\Actions\CancelCurrentPlan;
use Osiset\ShopifyApp\Contracts\Commands\Shop as IShopCommand;
use Osiset\ShopifyApp\Contracts\Queries\Shop as IShopQuery;
use Osiset\ShopifyApp\Messaging\Events\AppUninstalledEvent;
use Osiset\ShopifyApp\Objects\Values\ShopDomain;
use Osiset\ShopifyApp\Util;

class AppUninstalledJob extends \Osiset\ShopifyApp\Messaging\Jobs\AppUninstalledJob
{
    /**
     * Execute the job.
     *
     * @param IShopCommand      $shopCommand             The commands for shops.
     * @param IShopQuery        $shopQuery               The querier for shops.
     * @param CancelCurrentPlan $cancelCurrentPlanAction The action for cancelling the current plan.
     *
     * @return bool
     */
    public function handle(
        IShopCommand $shopCommand,
        IShopQuery $shopQuery,
        CancelCurrentPlan $cancelCurrentPlanAction
    ): bool {
        $domainString = $this->domain;
        // Convert the domain
        $this->domain = ShopDomain::fromNative($this->domain);

        // Get the shop
        $shop = $shopQuery->getByDomain($this->domain);
        if (!$shop) {
            return true;
        }
        $shopId = $shop->getId();

        // Cancel the current plan
        $cancelCurrentPlanAction($shopId);

        // Purge shop of token, plan, etc.
        $shopCommand->clean($shopId);

        LlmSetting::where('user_id', $shopId->toNative())->delete();
        Storage::deleteDirectory($domainString);

        // Check freemium mode
        if (Util::getShopifyConfig('billing_freemium_enabled') === true) {
            // Add the freemium flag to the shop
            $shopCommand->setAsFreemium($shopId);
        }

        // Soft delete the shop.
        $shopCommand->softDelete($shopId);

        event(new AppUninstalledEvent($shop));

        return true;
    }
}
