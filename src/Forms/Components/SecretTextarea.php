<?php

namespace Iperamuna\FilamentSecret\Forms\Components;

use Filament\Forms\Components\Textarea;

class SecretTextarea extends Textarea
{
    protected string $view = 'filament-secret::components.secret-textarea';

    protected bool $maskedByDefault = true;
    protected bool $readonlyWhenMasked = true;
    protected bool $copyButton = true;
    protected bool $clientEncryptOnSubmit = true;
    protected ?string $publicKey = null;

    public function masked(bool $condition = true): static
    {
        $this->maskedByDefault = $condition;
        return $this;
    }

    public function readonlyWhenMasked(bool $condition = true): static
    {
        $this->readonlyWhenMasked = $condition;
        return $this;
    }

    public function copyButton(bool $condition = true): static
    {
        $this->copyButton = $condition;
        return $this;
    }

    public function clientEncrypt(bool $enabled = true): static
    {
        $this->clientEncryptOnSubmit = $enabled;
        return $this;
    }

    public function publicKey(?string $b64): static
    {
        $this->publicKey = $b64;
        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(function () {
            // Ensure state is never leaked in plaintext to client if masking is desired.
            // We intentionally do not hydrate any plaintext here; the field is cosmetic.
        });

        $this->dehydrated(false); // don't send plaintext value as field state

        $this->extraAttributes(function () {
            $pk = $this->publicKey ?? config('filament-secret.sealed_box_public_key');
            return [
                'data-secret-mask' => $this->maskedByDefault ? '1' : '0',
                'data-secret-readonly' => $this->readonlyWhenMasked ? '1' : '0',
                'data-secret-copy' => $this->copyButton ? '1' : '0',
                'data-secret-client-encrypt' => $this->clientEncryptOnSubmit ? '1' : '0',
                'data-secret-public-key' => $pk ?? '',
            ];
        });
    }
}
