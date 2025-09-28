
<?php

use Iperamuna\FilamentSecret\Tests\Models\SecretThing;
use Iperamuna\FilamentSecret\Tests\CreatesDatabase;

uses(CreatesDatabase::class);

beforeEach(function () {
    $this->setUpDatabase();
});

it('masks encrypted value in arrays', function () {
    $m = new SecretThing();
    $m->value = 'plain secret';
    $m->save();

    $arr = $m->fresh()->toArray();
    expect($arr['value'])->toBe('******');
});

it('stores ciphertext in database, not plaintext', function () {
    $m = new SecretThing();
    $m->value = 'plain secret';
    $m->save();

    $raw = \DB::table('secret_things')->where('id', $m->id)->value('value');
    expect($raw)->not()->toBe('plain secret');
    expect($raw)->toBeString();
    expect(strlen($raw))->toBeGreaterThan(10);
});
