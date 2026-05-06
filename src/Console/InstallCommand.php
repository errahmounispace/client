<?php

namespace Laraowl\Client\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    protected $signature = 'laraowl:install';

    protected $description = 'Install and configure the Laraowl client';

    public function handle()
    {
        $this->info('Installing Laraowl Client...');

        $this->publishConfiguration();

        $this->askForConfiguration();

        $this->info('Laraowl installed successfully.');
    }

    protected function publishConfiguration()
    {
        $this->call('vendor:publish', [
            '--tag' => 'laraowl-config',
            '--force' => true,
        ]);
    }

    protected function askForConfiguration()
    {
        if (!$this->confirm('Do you want to configure your credentials now?', true)) {
            return;
        }

        $url = $this->ask('Laraowl Server URL', 'https://laraowl.laraowl.com');
        $token = $this->ask('Project Token');

        $this->updateEnv($url, $token);
    }

    protected function updateEnv($url, $token)
    {
        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            return;
        }

        $content = File::get($envPath);

        if (!str_contains($content, 'LARAOWL_SERVER_URL')) {
            $content .= "\nLARAOWL_SERVER_URL={$url}";
        } else {
            $content = preg_replace('/LARAOWL_SERVER_URL=.*/', "LARAOWL_SERVER_URL={$url}", $content);
        }

        if (!str_contains($content, 'LARAOWL_TOKEN')) {
            $content .= "\nLARAOWL_TOKEN={$token}";
        } else {
            $content = preg_replace('/LARAOWL_TOKEN=.*/', "LARAOWL_TOKEN={$token}", $content);
        }

        File::put($envPath, $content);
        
        $this->info('Environment variables updated.');
    }
}
