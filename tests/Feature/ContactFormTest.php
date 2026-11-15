<?php

namespace Tests\Feature;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Support\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    protected array $valid = [
        'name' => 'عميل تجريبي', 'phone' => '0555000000', 'email' => 'client@example.com',
        'subject' => 'wholesale', 'message' => 'أرغب بالطلب بالجملة',
    ];

    public function test_message_is_stored_and_emailed(): void
    {
        Mail::fake();
        Content::put('settings', ['notify_email' => 'sales@example.com']);

        $this->post('/contact', $this->valid)->assertRedirect()->assertSessionHas('contact_sent');

        $this->assertDatabaseHas('contact_messages', ['email' => 'client@example.com', 'subject' => 'wholesale']);
        Mail::assertSent(ContactMessageReceived::class, fn ($m) => $m->hasTo('sales@example.com'));
    }

    public function test_validation_errors(): void
    {
        $this->from('/contact')->post('/contact', ['email' => 'nope'])
            ->assertSessionHasErrors(['name', 'phone', 'email', 'message']);
        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_honeypot_is_silently_dropped(): void
    {
        $this->post('/contact', $this->valid + ['website' => 'http://spam.example'])->assertSessionHas('contact_sent');
        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_submissions_are_throttled(): void
    {
        Mail::fake();
        for ($i = 0; $i < 5; $i++) {
            $this->post('/contact', $this->valid);
        }
        $this->from('/contact')->post('/contact', $this->valid)->assertSessionHasErrors('throttle');
        $this->assertSame(5, ContactMessage::count());
    }
}
