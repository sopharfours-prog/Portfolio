<?php

namespace Tests\Feature;

use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MemberAccountTest extends TestCase
{
    use RefreshDatabase;

    private function signIn(Member $member): void
    {
        $this->post('/login', ['email' => $member->email, 'password' => 'Secret-Pass-123'])->assertRedirect(route('home'));
    }

    public function test_profile_shows_registration_details(): void
    {
        $member = Member::factory()->create(['name' => 'Noy Member', 'email' => 'noy@example.com', 'password' => 'Secret-Pass-123', 'locale' => 'en', 'google_id' => 'g-9']);
        $this->signIn($member);

        $this->get('/account')->assertOk()
            ->assertSee('Noy Member')->assertSee('noy@example.com')->assertSee('Google')
            ->assertSee($member->created_at->translatedFormat('j F Y'));
        $this->get('/')->assertSee(route('member.account'), false);   // link in the account menu
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/account')->assertRedirect(route('member.login'));
    }

    public function test_member_can_update_name_language_and_password(): void
    {
        $member = Member::factory()->create(['password' => 'Secret-Pass-123']);
        $this->signIn($member);

        $this->put('/account', ['name' => 'New Name', 'locale' => 'en'])->assertRedirect(route('member.account'));
        $this->assertSame(['New Name', 'en'], [$member->fresh()->name, $member->fresh()->locale]);

        $this->put('/account/password', ['current_password' => 'wrong', 'password' => 'Another-Pass-456', 'password_confirmation' => 'Another-Pass-456'])
            ->assertSessionHasErrorsIn('password', 'current_password');
        $this->put('/account/password', ['current_password' => 'Secret-Pass-123', 'password' => 'Another-Pass-456', 'password_confirmation' => 'Another-Pass-456'])
            ->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('Another-Pass-456', $member->fresh()->password));
    }

    public function test_social_only_member_can_set_a_first_password(): void
    {
        $member = Member::factory()->create(['password' => null, 'google_id' => 'g-1']);
        $this->actingAs($member, 'member');

        $this->get('/account')->assertSee(__('site.account.setPasswordText'));
        $this->put('/account/password', ['password' => 'First-Pass-789', 'password_confirmation' => 'First-Pass-789'])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('First-Pass-789', $member->fresh()->password));
    }

    public function test_dashboard_link_is_hidden_from_members(): void
    {
        $member = Member::factory()->create(['password' => 'Secret-Pass-123']);
        $dashboard = 'href="'.route('admin.dashboard').'"';

        $this->signIn($member);
        $this->get('/account')->assertDontSee($dashboard, false);

        // Even with an admin session in the same browser, a signed-in member sees only their own menu
        $this->actingAs($this->admin());
        $this->get('/account')->assertDontSee($dashboard, false);
        $this->get('/')->assertDontSee($dashboard, false);
    }

    public function test_dashboard_link_is_shown_to_administrators(): void
    {
        $this->actingAs($this->admin());

        $this->get('/')->assertSee('href="'.route('admin.dashboard').'"', false);
    }
}
