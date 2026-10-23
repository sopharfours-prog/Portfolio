<?php

namespace Tests\Feature;

use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as SocialUser;
use Mockery;
use Tests\TestCase;

class SocialLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.google.client_id' => 'test-google', 'services.google.client_secret' => 'secret']);
    }

    /** Makes Socialite::driver($provider)->...->user() return the given user (or throw). */
    private function fakeProvider(string $provider, SocialUser|\Throwable $result): void
    {
        $driver = Mockery::mock(Provider::class);
        foreach (['stateless', 'cookieNonce', 'scopes'] as $chain) {
            $driver->shouldReceive($chain)->andReturnSelf();
        }
        $result instanceof \Throwable
            ? $driver->shouldReceive('user')->andThrow($result)
            : $driver->shouldReceive('user')->andReturn($result);
        Socialite::shouldReceive('driver')->with($provider)->andReturn($driver);
    }

    private function socialUser(string $id, string $email, ?string $name = 'Noy Google'): SocialUser
    {
        return (new SocialUser)->map(['id' => $id, 'email' => $email, 'name' => $name]);
    }

    public function test_buttons_show_only_for_configured_providers(): void
    {
        $this->get('/login')->assertSee(route('member.social.redirect', 'google'), false)->assertDontSee('/auth/apple/redirect', false);
        $this->get('/auth/apple/redirect')->assertNotFound();

        config(['services.google.client_id' => null]);
        $this->get('/login')->assertDontSee('/auth/google/redirect', false);
    }

    public function test_google_sign_in_creates_a_visitor_account(): void
    {
        $this->fakeProvider('google', $this->socialUser('g-123', 'Noy@Example.com'));

        $this->get('/auth/google/callback')->assertRedirect(route('home'));

        $member = Member::where('google_id', 'g-123')->firstOrFail();
        $this->assertSame(['noy@example.com', 'Noy Google', null], [$member->email, $member->name, $member->password]);
        $this->assertAuthenticatedAs($member, 'member');
        $this->assertGuest('web');
    }

    public function test_sign_in_links_an_existing_email_account_and_matches_by_id_later(): void
    {
        $existing = Member::factory()->create(['email' => 'noy@example.com']);
        $this->fakeProvider('google', $this->socialUser('g-777', 'noy@example.com'));
        $this->get('/auth/google/callback');
        $this->assertSame('g-777', $existing->fresh()->google_id);
        $this->assertSame(1, Member::count());

        // Later sign-ins match the Google ID even if the Google email changed
        $this->post('/logout');
        $this->fakeProvider('google', $this->socialUser('g-777', 'new-address@example.com'));
        $this->get('/auth/google/callback');
        $this->assertAuthenticatedAs($existing->fresh(), 'member');
        $this->assertSame(1, Member::count());
    }

    public function test_apple_callback_is_a_cross_site_post_without_csrf_token(): void
    {
        config(['services.apple.client_id' => 'dev.sophar.signin']);
        $this->fakeProvider('apple', $this->socialUser('a-001', 'abc@privaterelay.appleid.com', null));

        $this->post('/auth/apple/callback', ['code' => 'x', 'state' => 'y'])->assertRedirect(route('home'));

        $member = Member::where('apple_id', 'a-001')->firstOrFail();
        $this->assertSame('abc', $member->name);   // Apple may not send a name: use the email's first part
    }

    public function test_cancelled_or_forged_sign_in_is_rejected(): void
    {
        $this->fakeProvider('google', new InvalidStateException);

        $this->get('/auth/google/callback')->assertRedirect(route('member.login'))->assertSessionHasErrors('email');
        $this->assertGuest('member');
        $this->assertSame(0, Member::count());
    }

    public function test_social_accounts_without_password_cannot_log_in_with_a_password(): void
    {
        Member::factory()->create(['email' => 'social@example.com', 'password' => null, 'google_id' => 'g-1']);

        $this->post('/login', ['email' => 'social@example.com', 'password' => ''])->assertSessionHasErrors('password');
        $this->post('/login', ['email' => 'social@example.com', 'password' => 'anything'])->assertSessionHasErrors('email');
        $this->assertGuest('member');
    }
}
