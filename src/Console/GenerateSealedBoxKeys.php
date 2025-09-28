<?php

namespace Iperamuna\FilamentSecret\Console;

use Illuminate\Console\Command;
use function Laravel\Prompts\{confirm, note, info, warning, intro, outro};

class GenerateSealedBoxKeys extends Command
{
    protected $signature = 'filament-secret:keys {--path= : Custom .env path (defaults to base_path/.env)}';
    protected $description = 'Generate libsodium sealed-box public/secret key pair and optionally write them to .env';

    public function handle(): int
    {
        if (! function_exists('sodium_crypto_box_keypair')) {
            warning('The sodium extension is required.');
            return self::FAILURE;
        }

        $keypair = sodium_crypto_box_keypair();
        $public  = sodium_bin2base64(sodium_crypto_box_publickey($keypair), SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
        $secret  = sodium_bin2base64(sodium_crypto_box_secretkey($keypair), SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);

        intro('🔑 Generated a new sealed-box key pair');

        note("Public Key:\n{$public}");
        note("Secret Key:\n{$secret}");

        if (! confirm(label: 'Do you want to write these keys into your .env file?', default: true)) {
            outro('Keys displayed only. Nothing written.');
            return self::SUCCESS;
        }

        $envPath = $this->option('path') ?: base_path('.env');

        if (! file_exists($envPath)) {
            warning(".env not found at {$envPath}, creating a new one.");
            file_put_contents($envPath, "\n");
        } else {
            @copy($envPath, $envPath . '.bak');
        }

        $this->writeEnv($envPath, 'SEALED_BOX_PUBLIC_KEY', $public);
        $this->writeEnv($envPath, 'SEALED_BOX_SECRET_KEY', $secret);

        outro("✅ Keys written to {$envPath}. A backup was saved to {$envPath}.bak");

        return self::SUCCESS;
    }

    protected function writeEnv(string $envPath, string $key, string $value): void
    {
        $contents = file_get_contents($envPath);

        $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';
        $line    = $key . '=' . $value;

        if (preg_match($pattern, $contents)) {
            $contents = preg_replace($pattern, $line, $contents);
        } else {
            $contents = rtrim($contents) . PHP_EOL . $line . PHP_EOL;
        }

        file_put_contents($envPath, $contents);
    }
}
