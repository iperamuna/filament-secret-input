
<?php

namespace Iperamuna\FilamentSecret\Tests;

use Iperamuna\FilamentSecret\FilamentSecretInputServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            FilamentSecretInputServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'base64:6vK1KQJc2rjzv2Sp3ZCk1s0O2aXo5H2HfH4lJ7G2sVY=');
        $app['config']->set('filament-secret.mask_encrypted_in_arrays', true);
    }
}
