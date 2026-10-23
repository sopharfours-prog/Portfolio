<?php

namespace Tests\Feature;

use App\Models\Message;
use App\Models\Setting;
use App\Models\User;
use App\Support\MailSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MailSettingsTest extends TestCase
{
    use RefreshDatabase;

    private array $gmail = [
        'tab' => 'mail', 'mode' => 'smtp', 'host' => 'smtp.gmail.com', 'port' => 587, 'encryption' => 'tls',
        'username' => 'me@gmail.com', 'password' => 'abcd efgh ijkl mnop', 'from_address' => 'me@gmail.com', 'from_name' => 'Sophar',
    ];

    public function test_smtp_settings_are_saved_with_an_encrypted_password_and_applied(): void
    {
        $this->actingAs($this->admin());
        $this->get('/admin/settings?tab=mail')->assertOk()->assertSee(__('Use Gmail settings'));

        $this->put('/admin/settings', $this->gmail)->assertSessionHasNoErrors();

        $stored = Setting::get('mail');
        $this->assertNotSame('abcd efgh ijkl mnop', $stored['password']);                  // never stored in plain text
        $this->assertSame('abcd efgh ijkl mnop', Crypt::decryptString($stored['password']));
        $this->get('/admin/settings?tab=mail')->assertDontSee('abcd efgh ijkl mnop');     // never sent back to the browser

        MailSettings::apply();
        $this->assertSame(['smtp', 'smtp.gmail.com', 587, 'abcd efgh ijkl mnop', 'me@gmail.com'], [
            config('mail.default'), config('mail.mailers.smtp.host'), config('mail.mailers.smtp.port'),
            config('mail.mailers.smtp.password'), config('mail.from.address'),
        ]);
        $this->assertFalse(MailSettings::isTestMode());
    }

    public function test_an_empty_password_field_keeps_the_saved_password(): void
    {
        $this->actingAs($this->admin());
        $this->put('/admin/settings', $this->gmail);
        $this->put('/admin/settings', [...$this->gmail, 'password' => '', 'from_name' => 'New name'])->assertSessionHasNoErrors();

        $this->assertSame('abcd efgh ijkl mnop', Crypt::decryptString(Setting::get('mail.password')));
        $this->assertSame('New name', Setting::get('mail.from_name'));
    }

    public function test_invalid_settings_are_rejected(): void
    {
        $this->actingAs($this->admin());

        $this->put('/admin/settings', ['tab' => 'mail', 'mode' => 'smtp', 'encryption' => 'tls'])->assertSessionHasErrors(['host', 'port', 'from_address']);
        $this->put('/admin/settings', [...$this->gmail, 'host' => 'smtp.gmail.com; rm -rf /'])->assertSessionHasErrors('host');
        $this->put('/admin/settings', ['tab' => 'mail', 'mode' => 'log', 'encryption' => 'tls'])->assertSessionHasNoErrors();   // test mode needs nothing else
    }

    public function test_test_email_is_sent_to_the_admin(): void
    {
        Mail::fake();
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/settings/mail-test')
            ->assertRedirect(route('admin.settings.edit', ['tab' => 'mail']))
            ->assertSessionHas('toast', fn (array $toast) => $toast[0] === 'success' && str_contains($toast[1], $admin->email));
    }

    public function test_messages_warning_disappears_once_real_sending_is_set_up(): void
    {
        $this->actingAs($this->admin());
        $message = Message::create(['name' => 'Bee', 'email' => 'bee@example.com', 'subject' => 'Hi', 'body' => 'A message long enough.']);
        $setUpLink = 'href="'.route('admin.settings.edit', ['tab' => 'mail']).'"';   // only part of the test-mode warning

        $this->get("/admin/messages?message={$message->id}")->assertSee($setUpLink, false);

        $this->put('/admin/settings', $this->gmail);
        MailSettings::apply();
        $this->get("/admin/messages?message={$message->id}")->assertDontSee($setUpLink, false);
    }

    public function test_editors_cannot_see_or_change_email_settings(): void
    {
        $this->actingAs($this->admin(User::ROLE_EDITOR));

        $this->get('/admin/settings?tab=mail')->assertForbidden();
        $this->put('/admin/settings', $this->gmail)->assertForbidden();
        $this->post('/admin/settings/mail-test')->assertForbidden();
    }
}
