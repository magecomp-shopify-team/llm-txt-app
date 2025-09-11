<?php

namespace App\Console\Commands;

use App\Helpers\LlmFileGenerator;
use App\Models\LlmSetting;
use App\Models\User;
use Illuminate\Console\Command;

class SyncResourcesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync-resources';

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
        $llmSettings = LlmSetting::with('user')->whereBetween('next_synced_at', [now(), now()->addDay()])->get();
        foreach ($llmSettings as $setting) {
            $user = $setting->user;
            if ($user) {
                $helper = new LlmFileGenerator($user);
                $helper->generate();
            }
        }
    }
}
