@props([
    'alpineDisabled' => null,
    'alpineValid' => null,
    'disabled' => false,
    'inlinePrefix' => false,
    'inlineSuffix' => false,
    'prefix' => null,
    'prefixActions' => [],
    'prefixIcon' => null,
    'prefixIconColor' => 'gray',
    'prefixIconAlias' => null,
    'suffix' => null,
    'suffixActions' => [],
    'suffixIcon' => null,
    'suffixIconColor' => 'gray',
    'suffixIconAlias' => null,
    'valid' => true,
])

@php
    use Filament\Support\View\Components\InputComponent\WrapperComponent\IconComponent;
    use Illuminate\View\ComponentAttributeBag;

    $prefixActions = array_filter(
        $prefixActions,
        fn (\Filament\Actions\Action $prefixAction): bool => $prefixAction->isVisible(),
    );

    $suffixActions = array_filter(
        $suffixActions,
        fn (\Filament\Actions\Action $suffixAction): bool => $suffixAction->isVisible(),
    );

    $hasPrefix = count($prefixActions) || $prefixIcon || filled($prefix);
    $hasSuffix = count($suffixActions) || $suffixIcon || filled($suffix);

    $hasAlpineDisabledClasses = filled($alpineDisabled);
    $hasAlpineValidClasses = filled($alpineValid);
    $hasAlpineClasses = $hasAlpineDisabledClasses || $hasAlpineValidClasses;

    $wireTarget = $attributes->whereStartsWith(['wire:target'])->first();

    $hasLoadingIndicator = filled($wireTarget);

    if ($hasLoadingIndicator) {
        $loadingIndicatorTarget = html_entity_decode($wireTarget, ENT_QUOTES);
    }

    $mask = ($attributes['data-secret-mask'] ?? '1') === '1';
    $ro   = ($attributes['data-secret-readonly'] ?? '1') === '1';
    $cpy  = ($attributes['data-secret-copy'] ?? '1') === '1';
    $enc  = ($attributes['data-secret-client-encrypt'] ?? '1') === '1';
    $pk   = $attributes['data-secret-public-key'] ?? '';
@endphp

<div
    x-data="secretField({
                isMaskedRevealed: false,
                masked: {{ $mask ? 'true' : 'false' }},
                readonlyWhenMasked: {{ $ro ? 'true' : 'false' }},
                enableCopy: {{ $cpy ? 'true' : 'false' }},
                clientEncryptOnSubmit: {{ $enc ? 'true' : 'false' }},
                publicKeyB64: @js($pk)
            })"
    x-init="init()"
    @if ($hasAlpineClasses)
        x-bind:class="{
            {{ $hasAlpineDisabledClasses ? "'fi-disabled': {$alpineDisabled}," : null }}
            {{ $hasAlpineValidClasses ? "'fi-invalid': ! ({$alpineValid})," : null }}
        }"
    @endif
    {{
        $attributes
            ->except(['wire:target', 'tabindex'])
            ->class([
                'fi-input-wrp',
                'fi-disabled' => (! $hasAlpineClasses) && $disabled,
                'fi-invalid' => (! $hasAlpineClasses) && (! $valid),
            ])
    }}
>
    @if ($hasPrefix || $hasLoadingIndicator)
        <div
            @if (! $hasPrefix)
                wire:loading.delay.{{ config('filament.livewire_loading_delay', 'default') }}.flex
            wire:target="{{ $loadingIndicatorTarget }}"
            wire:key="{{ \Illuminate\Support\Str::random() }}" {{-- Makes sure the loading indicator gets hidden again. --}}
            @endif
            @class([
                'fi-input-wrp-prefix',
                'fi-input-wrp-prefix-has-content' => $hasPrefix,
                'fi-inline' => $inlinePrefix,
                'fi-input-wrp-prefix-has-label' => filled($prefix),
            ])
        >
            @if (count($prefixActions))
                <div class="fi-input-wrp-actions">
                    @foreach ($prefixActions as $prefixAction)
                        {{ $prefixAction }}
                    @endforeach
                </div>
            @endif

            {{
                \Filament\Support\generate_icon_html($prefixIcon, $prefixIconAlias, (new \Illuminate\View\ComponentAttributeBag)
                    ->merge([
                        'wire:loading.remove.delay.' . config('filament.livewire_loading_delay', 'default') => $hasLoadingIndicator,
                        'wire:target' => $hasLoadingIndicator ? $loadingIndicatorTarget : false,
                    ], escape: false)
                    ->color(IconComponent::class, $prefixIconColor))
            }}

            @if ($hasLoadingIndicator)
                {{
                    \Filament\Support\generate_loading_indicator_html((new \Illuminate\View\ComponentAttributeBag([
                        'wire:loading.delay.' . config('filament.livewire_loading_delay', 'default') => $hasPrefix,
                        'wire:target' => $hasPrefix ? $loadingIndicatorTarget : null,
                    ]))->color(IconComponent::class, 'gray'))
                }}
            @endif

            @if (filled($prefix))
                <span class="fi-input-wrp-label">
                    {{ $prefix }}
                </span>
            @endif
        </div>
    @endif

    <div
        @if ($hasLoadingIndicator && (! $hasPrefix))
            @if ($inlinePrefix)
                wire:loading.delay.{{ config('filament.livewire_loading_delay', 'default') }}.class.remove="ps-3"
        @endif

        wire:target="{{ $loadingIndicatorTarget }}"
        @endif
        @class([
            'fi-input-wrp-content-ctn',
            'fi-input-wrp-content-ctn-ps' => $hasLoadingIndicator && (! $hasPrefix) && $inlinePrefix,
        ])
    >
        <div class="space-y-2">
            {{ $slot }}
        </div>
    </div>

    @if ($hasSuffix)
        <div
            @class([
                'fi-input-wrp-suffix',
                'fi-inline' => $inlineSuffix,
                'fi-input-wrp-suffix-has-label' => filled($suffix),
            ])
        >
            @if (filled($suffix))
                <span class="fi-input-wrp-label">
                    {{ $suffix }}
                </span>
            @endif

            {{
                \Filament\Support\generate_icon_html($suffixIcon, $suffixIconAlias, (new \Illuminate\View\ComponentAttributeBag)
                    ->merge([
                        'wire:loading.remove.delay.' . config('filament.livewire_loading_delay', 'default') => $hasLoadingIndicator,
                        'wire:target' => $hasLoadingIndicator ? $loadingIndicatorTarget : false,
                    ], escape: false)
                    ->color(IconComponent::class, $suffixIconColor))
            }}

            @if (count($suffixActions))
                <div class="fi-input-wrp-actions">
                    @foreach ($suffixActions as $suffixAction)
                        {{ $suffixAction }}
                    @endforeach
                </div>
            @endif
        </div>
    @endif
        <script>
            document.addEventListener('alpine:init', () => {
                console.log('alpine:init');
                function notify(message, status = 'success') {
                    window.dispatchEvent(new CustomEvent('filament-notify', { detail: { status, message } }))
                }

                let secretField;
                let _s = window.sodium;

                Alpine.data('secretField', (opts = {}) => ({
                    masked: !!opts.masked,
                    isMaskedRevealed: opts.isMaskedRevealed || false,
                    readonly: !!opts.masked && !!opts.readonlyWhenMasked,
                    enableCopy: !!opts.enableCopy,
                    clientEncryptOnSubmit: true,
                    publicKeyB64: opts.publicKeyB64 || '',

                    init() {
                        this.isMaskedRevealed = false;

                        if (this.masked) this.$refs.ta?.classList.add('secret-mask')

                        const form = this.$root.closest('form')
                        if (form) {
                            form.addEventListener('submit', async () => {
                                await this.sealBeforeSubmit()
                            })
                        }
                    },

                    async ensureSodium() {
                        console.log('ensureSodium', window.sodium, _s);
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
                        const s = await this.ensureSodium();
                        if (!publicKeyB64) throw new Error('Public key missing');
                        console.log('urlnopad', this.b64UrlNoPad(s));
                        const v = this.b64UrlNoPad(s);
                        const pk = s.from_base64(publicKeyB64, v);
                        const sealed = s.crypto_box_seal(s.from_string(plaintext ?? ''), pk);
                        return s.to_base64(sealed, v);
                    },

                    async decryptSeal(publicKeyB64, secretKeyB64, ciphertextB64) {
                        const s = await ensureSodium();
                        const v = b64UrlNoPad(s);

                        const pk = s.from_base64(publicKeyB64, v);
                        const sk = s.from_base64(secretKeyB64, v);
                        const kp = s.crypto_box_keypair_from_secretkey_and_publickey(sk, pk);

                        const cipher = s.from_base64(ciphertextB64, v);
                        const plain = s.crypto_box_seal_open(cipher, kp);
                        if (!plain) throw new Error('Invalid sealed payload');
                        return s.to_string(plain);
                    },

                    async sealBeforeSubmit() {
                        if (!this.clientEncryptOnSubmit || !this.publicKeyB64) return
                        const txt = this.$refs.ta?.value ?? ''
                        if (!txt.trim()) return

                        const b64 = await this.encryptSeal(this.publicKeyB64, txt)
                        console.log('sealed (before submit):', b64)
                        this.$wire.set('data.value', b64);
                        //if (this.$refs.hidden) this.$refs.hidden.value = b64

                        // clear plaintext for safety
                        //if (this.$refs.ta) this.$refs.ta.value = b64;
                        console.log('sealed (before submit):', b64)

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
                        console.log('showEdit');
                        this.$nextTick(() => {
                            Livewire.dispatch('reveal-secret');
                        });
                        this.isMaskedRevealed = true
                        this.masked = false
                        this.readonly = false
                        this.$refs.ta?.classList.remove('secret-mask')
                        this.$nextTick(() => this.$refs.ta?.focus())
                    },

                    hide() {
                        this.isMaskedRevealed = false
                        this.masked = true
                        this.readonly = true
                        this.$refs.ta?.classList.add('secret-mask')
                        this.$nextTick(() => {});
                    },
                }))
            })
        </script>
</div>
