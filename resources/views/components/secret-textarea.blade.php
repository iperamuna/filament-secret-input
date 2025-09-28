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
    class="relative"
>
    <textarea
        x-ref="ta"
        {{ $attributes->merge(['class' => 'fi-fo-textarea-input pr-28'])->class(['secret-mask' => $mask]) }}
        x-bind:readonly="readonly"
        x-on:change.debounce.300ms="stage()"
        x-on:blur="stage()"
    ></textarea>

    <div class="secret-buttons absolute right-2 top-2 flex gap-1">
        <button type="button" class="btn-mini" x-on:click="showEdit()" x-show="masked">Show</button>
        <button type="button" class="btn-mini" x-on:click="hide()" x-show="!masked">Hide</button>
        <button type="button" class="btn-mini" x-on:click="copy()" x-show="enableCopy">Copy</button>
    </div>

    <input type="hidden" x-ref="hidden" name="{{ $getStatePath() }}_encrypted">

    <template x-if="clientEncryptOnSubmit">
        <span x-init="$root.closest('form')?.addEventListener('submit', async (e) => { await sealBeforeSubmit(); })"></span>
    </template>
</div>
