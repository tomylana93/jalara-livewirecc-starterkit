<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Fortify\Features;
use Livewire\Livewire;

function formValidationXPath(string $html): DOMXPath
{
    $document = new DOMDocument;
    $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);

    return new DOMXPath($document);
}

test('authentication forms defer validation to the server', function (string $routeName): void {
    $parameters = $routeName === 'password.reset' ? ['token' => 'test-token', 'email' => 'test@example.com'] : [];
    $response = $this->get(route($routeName, $parameters))->assertOk();
    $xpath = formValidationXPath($response->getContent());

    expect($xpath->evaluate('count(//form)'))->toBeGreaterThan(0.0)
        ->and($xpath->evaluate('count(//form[not(@novalidate)])'))->toBe(0.0)
        ->and($xpath->evaluate('count(//input[@required or @pattern or @type="email"])'))->toBe(0.0)
        ->and($xpath->evaluate('count(//input[@name="email" and @type="text" and @inputmode="email"])'))->toBe(1.0);
})->with(['login', 'register', 'password.request', 'password.reset']);

test('two factor challenge renders server errors on the corresponding inputs', function (string $field, string $value): void {
    $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());
    $user = User::factory()->withTwoFactor()->create(['two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP')]);
    $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect(route('two-factor.login'));
    $response = $this->from(route('two-factor.login'))->followingRedirects()
        ->post(route('two-factor.login.store'), [$field => $value])->assertOk();
    $xpath = formValidationXPath($response->getContent());

    expect($xpath->evaluate('count(//form[not(@novalidate)])'))->toBe(0.0)
        ->and($xpath->evaluate('count(//input[@required or @pattern])'))->toBe(0.0)
        ->and($xpath->evaluate('count(//input[@data-flux-otp-input])'))->toBe(6.0);

    $selector = $field === 'code'
        ? '//input[@data-flux-otp-input and @aria-invalid="true" and @data-invalid]'
        : '//input[@name="recovery_code" and @aria-invalid="true" and @data-invalid]';

    expect($xpath->evaluate('count('.$selector.')'))->toBe($field === 'code' ? 6.0 : 1.0);
})->with([['code', '12'], ['recovery_code', 'invalid-recovery-code']]);

test('settings forms defer validation to the server', function (string $routeName): void {
    $this->actingAs(User::factory()->create());
    $response = $this->withSession(['auth.password_confirmed_at' => time()])
        ->get(route($routeName))->assertOk();
    $xpath = formValidationXPath($response->getContent());

    expect($xpath->evaluate('count(//form)'))->toBeGreaterThan(0.0)
        ->and($xpath->evaluate('count(//form[not(@novalidate)])'))->toBe(0.0)
        ->and($xpath->evaluate('count(//input[@required or @pattern or @type="email"])'))->toBe(0.0);
})->with(['profile.edit', 'security.edit']);

test('profile email field shows and clears its server validation state', function (): void {
    $this->actingAs(User::factory()->create());
    $component = Livewire::test('pages::settings.profile')
        ->set('email', 'invalid-email')
        ->call('updateProfileInformation')
        ->assertHasErrors(['email' => 'email']);
    $xpath = formValidationXPath($component->html());

    expect($xpath->evaluate('count(//input[@name="email" and @aria-invalid="true" and @data-invalid])'))->toBe(1.0)
        ->and($xpath->evaluate('count(//input[@name="name" and @aria-invalid="true"])'))->toBe(0.0);

    $component->set('email', 'updated@example.com')->call('updateProfileInformation')->assertHasNoErrors();
    $xpath = formValidationXPath($component->html());

    expect($xpath->evaluate('count(//input[@name="email" and (@aria-invalid="true" or @data-invalid)])'))->toBe(0.0);
});

test('two factor verification marks all six slots invalid and clears the state on reset', function (): void {
    $this->actingAs(User::factory()->create());
    $component = Livewire::test('pages::settings.two-factor-setup-modal', ['requiresConfirmation' => true])
        ->set('showVerificationStep', true)
        ->set('code', '12')
        ->call('confirmTwoFactor')
        ->assertHasErrors(['code' => 'size']);
    $xpath = formValidationXPath($component->html());

    expect($xpath->evaluate('count(//input[@data-flux-otp-input])'))->toBe(6.0)
        ->and($xpath->evaluate('count(//input[@data-flux-otp-input and @aria-invalid="true" and @data-invalid])'))->toBe(6.0)
        ->and($xpath->evaluate('count(//ui-otp[@aria-invalid="true"])'))->toBe(1.0);

    $component->call('resetVerification')->set('showVerificationStep', true)->assertHasNoErrors();
    $xpath = formValidationXPath($component->html());

    expect($xpath->evaluate('count(//input[@data-flux-otp-input and (@aria-invalid="true" or @data-invalid)])'))->toBe(0.0);
});
