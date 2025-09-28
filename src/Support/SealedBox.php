<?php

namespace Iperamuna\FilamentSecret\Support;

class SealedBox
{
    /**
     * Decrypt a sodium sealed-box ciphertext (URLSAFE_NO_PADDING base64).
     * @throws \SodiumException
     */
    public static function decryptBase64(string $ciphertextB64): string
    {
        $pkEnv = config('filament-secret.sealed_box_public_key');
        $skEnv = config('filament-secret.sealed_box_secret_key');

        if (empty($pkEnv) || empty($skEnv)) {
            throw new \RuntimeException('SEALED_BOX keys are not configured.');
        }

        $pk = sodium_base642bin($pkEnv, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
        $sk = sodium_base642bin($skEnv, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
        $kp = sodium_crypto_box_keypair_from_secretkey_and_publickey($sk, $pk);

        $cipher = sodium_base642bin($ciphertextB64, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);
        $plain = sodium_crypto_box_seal_open($cipher, $kp);
        if ($plain === false) {
            throw new \RuntimeException('Invalid sealed box payload.');
        }
        return $plain;
    }

    /**
     * Encrypt plain text using sodium sealed-box and return as URLSAFE_NO_PADDING base64.
     * @throws \SodiumException
     */
    public static function encryptPlainText(string $plainText): string
    {
        $pkEnv = config('filament-secret.sealed_box_public_key');

        if (empty($pkEnv)) {
            throw new \RuntimeException('SEALED_BOX public key is not configured.');
        }

        $pk = sodium_base642bin($pkEnv, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);

        $cipher = sodium_crypto_box_seal($plainText, $pk);
        $ciphertextB64 = sodium_bin2base64($cipher, SODIUM_BASE64_VARIANT_URLSAFE_NO_PADDING);

        return $ciphertextB64;
    }
}
