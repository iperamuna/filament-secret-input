
<?php

namespace Iperamuna\FilamentSecret\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CreatesDatabase
{
    protected function setUpDatabase(): void
    {
        Schema::create('secret_things', function (Blueprint $table) {
            $table->id();
            $table->text('value')->nullable();
        });
    }
}
