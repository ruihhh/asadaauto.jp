<?php

namespace Tests\Feature;

use App\Mail\ContactInquiry;
use App\Models\Car;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_is_accessible(): void
    {
        $this->get(route('contact.index'))->assertOk();
    }

    public function test_contact_page_shows_car_info_when_stock_no_provided(): void
    {
        $car = Car::factory()->create([
            'stock_no' => 'AA1234',
            'make' => 'ホンダ',
            'model' => 'フィット',
            'status' => 'available',
        ]);

        $response = $this->get(route('contact.index', ['stock_no' => $car->stock_no]));

        $response->assertOk();
        $response->assertSee('AA1234');
    }

    public function test_contact_form_sends_email_and_redirects_to_thanks(): void
    {
        Mail::fake();

        $response = $this->post(route('contact.send'), [
            'name' => '山田 太郎',
            'email' => 'yamada@example.com',
            'phone' => '090-1234-5678',
            'message' => '在庫の確認をお願いします。',
        ]);

        $response->assertRedirect(route('contact.thanks'));
        Mail::assertSent(ContactInquiry::class);
    }

    public function test_contact_form_with_stock_no_sends_email(): void
    {
        Mail::fake();

        $car = Car::factory()->create(['stock_no' => 'BB9999', 'status' => 'available']);

        $this->post(route('contact.send'), [
            'name' => '鈴木 花子',
            'email' => 'suzuki@example.com',
            'stock_no' => 'BB9999',
            'message' => 'この車両について詳しく教えてください。',
        ]);

        Mail::assertSent(ContactInquiry::class);
    }

    public function test_contact_form_requires_name_email_and_message(): void
    {
        $response = $this->post(route('contact.send'), []);

        $response->assertSessionHasErrors(['name', 'email', 'message']);
    }

    public function test_contact_form_rejects_invalid_stock_no(): void
    {
        $response = $this->post(route('contact.send'), [
            'name' => '田中 一郎',
            'email' => 'tanaka@example.com',
            'stock_no' => 'NONEXISTENT',
            'message' => 'テスト',
        ]);

        $response->assertSessionHasErrors(['stock_no']);
    }

    public function test_thanks_page_is_accessible(): void
    {
        $this->get(route('contact.thanks'))->assertOk();
    }
}
