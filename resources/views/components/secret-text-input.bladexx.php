@php
    $extra = $getExtraAttributes(); // ✅ correct API
    $mask = ($extra['data-secret-mask'] ?? '1') === '1';
    $ro   = ($extra['data-secret-readonly'] ?? '1') === '1';
    $cpy  = ($extra['data-secret-copy'] ?? '1') === '1';
    $enc  = ($extra['data-secret-client-encrypt'] ?? '1') === '1';
    $pk   = $extra['data-secret-public-key'] ?? '';
@endphp

<div
    x-data="secretField({
        masked: {{ $mask ? 'true' : 'false' }},
        readonlyWhenMasked: {{ $ro ? 'true' : 'false' }},
        enableCopy: {{ $cpy ? 'true' : 'false' }},
        clientEncryptOnSubmit: {{ $enc ? 'true' : 'false' }},
        publicKeyB64: @js($pk),
    })"
    x-init="init()"
    class="space-y-2"
>
    <x-filament::input
        x-ref="ta"
        {{ $attributes->merge(['class' => 'fi-fo-text-input pr-28'])->class(['secret-mask' => $mask]) }}
        x-bind:readonly="readonly"
        x-on:change.debounce.300ms="stage()"
        x-on:blur="stage()"
    />

    <input x-ref="hidden" type="hidden" name="ciphertext"/>

    <div class="secret-buttons absolute right-2 top-1.5 flex gap-1">
        <button type="button" class="btn-mini" x-on:click="showEdit()" x-show="masked">Show</button>
        <button type="button" class="btn-mini" x-on:click="hide()" x-show="!masked">Hide</button>
        <button type="button" class="btn-mini" x-on:click="copy()" x-show="enableCopy">Copy</button>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            function notify(message, status = 'success') {
                window.dispatchEvent(new CustomEvent('filament-notify', { detail: { status, message } }))
            }

            let _s;
            Alpine.data('secretField', (opts = {}) => ({
                masked: !!opts.masked,
                readonly: !!opts.masked && !!opts.readonlyWhenMasked,
                enableCopy: !!opts.enableCopy,
                clientEncryptOnSubmit: !!opts.clientEncryptOnSubmit,
                publicKeyB64: opts.publicKeyB64 || '',

                init() {
                    if (this.masked) this.$refs.ta?.classList.add('secret-mask')

                    const form = this.$root.closest('form')
                    if (form) {
                        form.addEventListener('submit', async () => {
                            await this.sealBeforeSubmit()
                        })
                    }
                },

                async ensureSodium() {
                    if (!_s) {
                        // eslint-disable-next-line no-undef
                        if (typeof sodium !== 'undefined' && sodium?.ready) _s = sodium;
                        else if (window.sodium?.ready) _s = window.sodium;
                        else throw new Error('libsodium-wrappers not found. Load it before this plugin.');
                    }
                    await _s.ready;

                    // ✅ Make sure the seal APIs exist (guards against wrong build / race)
                    if (typeof _s.crypto_box_seal !== 'function' || typeof _s.crypto_box_seal_open !== 'function') {
                        throw new Error('libsodium-wrappers loaded, but seal APIs are missing. Use libsodium-wrappers or -sumo.');
                    }

                    return _s;
                },

                // Helper to robustly get the base64 variant constant
            b64UrlNoPad(s) {
                // libsodium-wrappers exports:
                // ORIGINAL=1, ORIGINAL_NO_PADDING=3, URLSAFE=5, URLSAFE_NO_PADDING=7
                // Fall back to 7 if the constants object is missing.
                return (s.base64_variants && s.base64_variants.URLSAFE_NO_PADDING) || 7;
            },

            async encryptSeal(publicKeyB64, plaintext) {
                const s = await ensureSodium();
                if (!publicKeyB64) throw new Error('Public key missing');

                const v = b64UrlNoPad(s);
                const pk = s.from_base64(publicKeyB64, v);
                const sealed = s.crypto_box_seal(s.from_string(plaintext ?? ''), pk);
                return s.to_base64(sealed, v);
            },

            async decryptSeal(publicKeyB64, secretKeyB64, ciphertextB64) {
                const dev = (typeof import.meta !== 'undefined' && import.meta?.env?.DEV) || !!window.__DEV__;
                if (!dev) throw new Error('decryptSeal is disabled in production');

                const s = await ensureSodium();
                const v = b64UrlNoPad(s);

                const pk = s.from_base64(publicKeyB64, v);
                const sk = s.from_base64(secretKeyB64, v);
                const kp = s.crypto_box_keypair_from_secretkey_and_publickey(sk, pk);

                const cipher = s.from_base64(ciphertextB64, v);
                const plain = s.crypto_box_seal_open(cipher, kp);
                if (!plain) throw new Error('Invalid sealed payload');
                return s.to_string(plain);
            }

                async stage() {
                    if (!this.clientEncryptOnSubmit || !this.publicKeyB64) return
                    const txt = this.$refs.ta?.value ?? ''
                    if (!txt.trim()) return

                    const b64 = await this.encryptSeal(this.publicKeyB64, txt)
                    if (this.$refs.hidden) this.$refs.hidden.value = b64
                },

                async sealBeforeSubmit() {
                    if (!this.clientEncryptOnSubmit || !this.publicKeyB64) return
                    const txt = this.$refs.ta?.value ?? ''
                    if (!txt.trim()) return

                    const b64 = await this.encryptSeal(this.publicKeyB64, txt)
                    if (this.$refs.hidden) this.$refs.hidden.value = b64

                    // clear plaintext for safety
                    if (this.$refs.ta) this.$refs.ta.value = ''

                    // (optional DEV-only)
                    // try {
                    //   const plain = await Alpine.store('crypto').unseal(this.publicKeyB64, secretKeyB64, b64)
                    //   console.log('unsealed (dev):', plain)
                    // } catch (_) {}
                },

                copy() {
                    if (!this.enableCopy) return
                    const txt = this.$refs.ta?.value ?? ''
                    if (!txt) return
                    navigator.clipboard?.writeText(txt).then(() => notify('Copied to clipboard'))
                },

                showEdit() {
                    this.masked = false
                    this.readonly = false
                    this.$refs.ta?.classList.remove('secret-mask')
                    this.$nextTick(() => this.$refs.ta?.focus())
                },

                hide() {
                    this.masked = true
                    this.readonly = true
                    this.$refs.ta?.classList.add('secret-mask')
                },
            }))
        })
    </script>
</div>
