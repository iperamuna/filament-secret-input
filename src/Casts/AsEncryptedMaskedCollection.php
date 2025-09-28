<?php

namespace Iperamuna\FilamentSecret\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;

class AsEncryptedMaskedCollection implements CastsAttributes, SerializesCastableAttributes
{
    /** @param class-string<Collection> $collectionClass */
    public function __construct(protected string $collectionClass = Collection::class) {}

    public function get(Model $model, string $key, $value, array $attributes): ?Collection
    {
        if (! isset($attributes[$key]) || $attributes[$key] === null) {
            return null;
        }

        $decoded = json_decode(Crypt::decryptString($attributes[$key]), true) ?? [];
        $class = $this->collectionClass;

        return new $class($decoded);
    }

    public function set(Model $model, string $key, $value, array $attributes): ?array
    {
        if ($value === null) {
            return [$key => null];
        }

        $arr = $value instanceof Collection ? $value->toArray() : (array) $value;

        return [$key => Crypt::encryptString(json_encode($arr))];
    }

    public function serialize(Model $model, string $key, $value, array $attributes): mixed
    {
        if (config('filament-secret.mask_encrypted_in_arrays', true)) {
            return '******';
        }

        // expose plaintext when masking disabled
        return $this->get($model, $key, $attributes[$key] ?? null, $attributes)?->toArray();
    }
}
