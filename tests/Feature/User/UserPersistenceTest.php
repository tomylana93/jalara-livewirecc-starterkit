<?php

declare(strict_types=1);

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

test('users receive unique UUID primary keys and active status by default', function (): void {
    $user = User::factory()->make();
    unset($user->status);
    $user->save();
    $anotherUser = User::factory()->create();

    $user->refresh();

    expect($user->id)->toBeUuid()->not->toBe($anotherUser->id)
        ->and($anotherUser->id)->toBeUuid()
        ->and($user->status)->toBe(UserStatus::Active);
});

test('user factories provide active, disabled, and suspend statuses', function (): void {
    $activeUser = User::factory()->make();
    $disabledUser = User::factory()->disabled()->create();
    $suspendedUser = User::factory()->suspend()->create();

    $disabledUser->refresh();
    $suspendedUser->refresh();

    expect($activeUser->status)->toBe(UserStatus::Active)
        ->and($disabledUser->status)->toBe(UserStatus::Disabled)
        ->and($suspendedUser->status)->toBe(UserStatus::Suspend);
});

test('user statuses persist as strings and serialize to their enum values', function (UserStatus $status, string $value): void {
    $user = User::factory()->create();

    $user->status = $status;
    $user->save();
    $user->refresh();

    expect($user->status)->toBe($status)
        ->and($user->toArray()['status'])->toBe($value);
    $this->assertDatabaseHas('users', ['id' => $user->id, 'status' => $value]);
})->with([
    'active' => [UserStatus::Active, 'active'],
    'disabled' => [UserStatus::Disabled, 'disabled'],
    'suspend' => [UserStatus::Suspend, 'suspend'],
]);

test('passkeys belong to users with UUID primary keys and are deleted with their owner', function (): void {
    $user = User::factory()->create();
    DB::table('passkeys')->insert([
        'user_id' => $user->id,
        'name' => 'Test passkey',
        'credential_id' => 'test-credential',
        'credential' => '{}',
    ]);

    $passkey = $user->passkeys()->firstOrFail();

    expect($passkey->user_id)->toBe($user->id)
        ->and($passkey->id)->toBeInt();

    $user->delete();

    $this->assertDatabaseMissing('passkeys', ['id' => $passkey->id]);
});

test('database sessions store and retrieve sessions for users with UUID primary keys', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
    $handler = new DatabaseSessionHandler(DB::connection(), 'sessions', 120, app());
    $sessionId = Str::random(40);

    $handler->write($sessionId, 'session-payload');

    $this->assertDatabaseHas('sessions', ['id' => $sessionId, 'user_id' => $user->id]);
    expect($handler->read($sessionId))->toBe('session-payload');
});
