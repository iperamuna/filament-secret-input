
<?php

use Illuminate\Support\Facades\Blade;

it('renders secret textarea with buttons', function () {
    $html = Blade::render("@component('filament-secret::components.secret-textarea', ['statePath' => 'secret_value']) @endcomponent");
    expect($html)->toContain('secret-buttons')->toContain('textarea');
});

it('renders secret text input with buttons', function () {
    $html = Blade::render("@component('filament-secret::components.secret-text-input', ['statePath' => 'api_token']) @endcomponent");
    expect($html)->toContain('secret-buttons')->toContain('input');
});
