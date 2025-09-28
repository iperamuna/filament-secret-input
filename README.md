
# Filament Secret Input

A Filament v4 form field for **masked secret input** with optional **client-side sealed-box encryption** and an **Eloquent encrypted masked cast**.

- Masked textarea with **Show / Hide / Copy** buttons
- Client-side encryption (browser → server) using **libsodium sealed boxes** (optional)
- At-rest encryption using Laravel's encrypted cast (with masked serialization)
- Zero build step: ships with small JS/CSS assets

## Installation

```bash
composer require iperamuna/filament-secret-input
php artisan vendor:publish --tag=filament-secret-config
```

### (Optional) Client-side encryption keys

Generate keys (once) and put into `.env`:

```php
$keypair = sodium_crypto_box_keypair();
$public  = sodium_bin2base64(sodium_crypto_box_publickey($keypair), SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
$secret  = sodium_bin2base64(sodium_crypto_box_secretkey($keypair), SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
```

```
SEALED_BOX_PUBLIC_KEY=base64_public_here
SEALED_BOX_SECRET_KEY=base64_secret_here
```

If you want browser-side encryption, also load `libsodium-wrappers` (your app asset pipeline) **or** provide a global `window.encryptSeal(pkB64, plaintext)` function. If `window.sodium` is present, the plugin will use it automatically.

## Usage

### Form fields

```php
use Iperamuna\FilamentSecret\Forms\Components\SecretTextarea;

SecretTextarea::make('secret_value') // state path base; a hidden field '<name>_encrypted' is posted
    ->label('Secret')
    ->masked()                // masked by default (stars)
    ->readonlyWhenMasked()    // lock editing while masked
    ->copyButton(true)        // show Copy button
    ->clientEncrypt(true)     // enable client-side encryption (requires public key)
    ->publicKey(config('filament-secret.sealed_box_public_key'));
```

On submit, the component will stage ciphertext into a hidden input named `secret_value_encrypted`. It does **not** dehydrate plaintext to the server.

### Saving on the server

```php
use Iperamuna\FilamentSecret\Support\SealedBox;

// $data comes from your action / form
$plaintext = SealedBox::decryptBase64($data['secret_value_encrypted']);

// Use the custom cast for at-rest encryption (see below)
$model->secret_value = $plaintext;
$model->save();
```

### Model cast

```php
use Iperamuna\FilamentSecret\Casts\AsEncryptedMaskedString;

class SecretThing extends Model
{
    protected $casts = [
        'secret_value' => AsEncryptedMaskedString::class, // masks in arrays
    ];
}
```

`AsEncryptedMaskedString` extends Laravel's encrypted cast and returns `******` when serializing (configurable via `mask_encrypted_in_arrays`).

## Security notes

- Keep TLS enabled. Client encryption is an extra layer.
- Never expose the **secret key** to the client.
- Clear plaintext ASAP; the field clears textarea content on submit.
- Encrypted columns are not searchable. Use a separate deterministic hash column if needed.

## Advanced

- You can disable client-side encryption and use only at-rest encryption; the field will still mask and provide Show/Hide/Copy UX.
- If you have your own encryption routine, expose it as `window.encryptSeal(publicKeyB64, plaintext)` and the field will use it.

## License

MIT


#### Text input variant

```php
use Iperamuna\FilamentSecret\Forms\Components\SecretTextInput;

SecretTextInput::make('api_token')
    ->label('API Token')
    ->masked()
    ->readonlyWhenMasked()
    ->copyButton(true)
    ->clientEncrypt(true)
    ->publicKey(config('filament-secret.sealed_box_public_key'));
```

## Testing

This package ships with **Pest** tests using Testbench.

Run:
```bash
composer install
composer test
```



## Key generation command

Interactive command using **Laravel Prompts**:

```bash
php artisan filament-secret:keys
```

- Shows the generated **public** and **secret** keys.
- Asks if you want to write them to `.env` (backs up to `.env.bak`).
- Optional: `--path=/custom/.env` to target a non-default env file.
