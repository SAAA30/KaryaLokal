<?php

namespace Tests\Feature;

use Database\Seeders\KaryaLokalSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        Storage::fake('public');
        $this->seed(KaryaLokalSeeder::class);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Membeli karya,')
            ->assertSee('Gelang Anyaman Senja')
            ->assertSee('Cerita QR');
    }

    public function test_login_screen_uses_the_karyalokal_brand(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Masuk ke akunmu.')
            ->assertSee('KaryaLokal.id')
            ->assertDontSee('Sistem Informasi PTSL');
    }
}
