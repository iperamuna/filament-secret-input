<?php

namespace Iperamuna\FilamentSecret\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Database\Eloquent\SerializesCastableAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Iperamuna\FilamentSecret\Support\SealedBox;

class AsEncryptedMaskedString implements CastsAttributes, SerializesCastableAttributes
{
    /**
     * @throws \SodiumException
     */
    public function get(Model $model, string $key, $value, array $attributes): ?string
    {
        if (! isset($attributes[$key]) || $attributes[$key] === null) {
            return null;
        }

        return Crypt::decryptString($attributes[$key]);
    }

    public function set(Model $model, string $key, $value, array $attributes): ?array
    {
        if ($value === null) {
            return [$key => null];
        }

        $sodiumDecryptOne = SealedBox::decryptBase64($value);
        $sodiumDecryptOne = SealedBox::decryptBase64($sodiumDecryptOne);

        return [$key => Crypt::encryptString((string) $sodiumDecryptOne)];
    }

    public function serialize(Model $model, string $key, $value, array $attributes): mixed
    {
        if (config('filament-secret.mask_encrypted_in_arrays', true)) {
            // constant mask (don’t leak length)
            return '******';
        }

        // expose plaintext when masking disabled
        return $this->get($model, $key, $attributes[$key] ?? null, $attributes);
    }
}
