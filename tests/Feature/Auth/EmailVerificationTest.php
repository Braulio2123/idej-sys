<?php

namespace Tests\Feature\Auth;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_is_disabled(): void
    {
        $user = Usuario::factory()->create();

        $this->actingAs($user)->get('/verify-email')->assertNotFound();
    }
}
