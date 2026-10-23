<?php

namespace Tests\Feature;

use App\Mail\MessageReplyMail;
use App\Models\Message;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Tests\TestCase;

class MessageReplyTest extends TestCase
{
    use RefreshDatabase;

    private function message(): Message
    {
        return Message::create(['name' => 'Bee', 'email' => 'bee@example.com', 'subject' => 'Website project', 'body' => 'Can you build a website for my shop?']);
    }

    public function test_reply_is_emailed_from_the_admin_and_kept_in_the_history(): void
    {
        Mail::fake();
        Setting::put('contact', ['email' => 'owner@example.com']);
        $admin = $this->admin();
        $message = $this->message();

        $this->actingAs($admin)->get("/admin/messages?message={$message->id}")->assertSee('Re: Website project')->assertSee(route('admin.messages.reply', $message), false);

        $this->post("/admin/messages/{$message->id}/reply", ['subject' => 'Re: Website project', 'body' => 'Yes, happy to help!'])
            ->assertRedirect(route('admin.messages.index', ['message' => $message->id]));

        Mail::assertSent(MessageReplyMail::class, fn (MessageReplyMail $mail) => $mail->hasTo('bee@example.com')
            && $mail->hasReplyTo('owner@example.com')
            && $mail->replySubject === 'Re: Website project'
            && str_contains($mail->render(), 'Yes, happy to help!')
            && str_contains($mail->render(), 'Can you build a website for my shop?'));

        $this->assertNotNull($message->fresh()->replied_at);
        $this->assertDatabaseHas('message_replies', ['message_id' => $message->id, 'user_id' => $admin->id, 'body' => 'Yes, happy to help!']);
        $this->get("/admin/messages?message={$message->id}")->assertSee('Yes, happy to help!');
    }

    public function test_failed_sending_keeps_the_text_and_does_not_mark_as_replied(): void
    {
        Mail::shouldReceive('to->send')->andThrow(new RuntimeException('SMTP down'));
        $message = $this->message();

        $this->actingAs($this->admin())
            ->from("/admin/messages?message={$message->id}")
            ->post("/admin/messages/{$message->id}/reply", ['subject' => 'Re: x', 'body' => 'My reply'])
            ->assertRedirect("/admin/messages?message={$message->id}")
            ->assertSessionHasInput('body', 'My reply');

        $this->assertNull($message->fresh()->replied_at);
        $this->assertDatabaseCount('message_replies', 0);
    }

    public function test_reply_requires_a_body_and_sign_in(): void
    {
        Mail::fake();
        $message = $this->message();

        $this->post("/admin/messages/{$message->id}/reply", ['subject' => 'x', 'body' => 'y'])->assertRedirect(route('admin.login'));
        $this->actingAs($this->admin())->post("/admin/messages/{$message->id}/reply", ['subject' => 'Re: x', 'body' => ''])->assertSessionHasErrors('body');
        Mail::assertNothingSent();
    }
}
