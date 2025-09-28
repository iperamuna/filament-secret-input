<?php

return [
    // Expose the public key to the client only (URLSAFE_NO_PADDING base64)
    'sealed_box_public_key' => env('SEALED_BOX_PUBLIC_KEY', ''),
    // Server-only secret key (NEVER expose to client)
    'sealed_box_secret_key' => env('SEALED_BOX_SECRET_KEY', ''),
    // If true, the AsEncryptedMaskedString cast masks the value in arrays / API.
    'mask_encrypted_in_arrays' => env('MASK_ENCRYPTED_IN_ARRAYS', true),
];
