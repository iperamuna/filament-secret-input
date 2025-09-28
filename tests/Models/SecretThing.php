
<?php

namespace Iperamuna\FilamentSecret\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Iperamuna\FilamentSecret\Casts\AsEncryptedMaskedString;

class SecretThing extends Model
{
    protected $table = 'secret_things';
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = [
        'value' => AsEncryptedMaskedString::class,
    ];
}
