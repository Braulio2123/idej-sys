<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = Usuario::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = Usuario::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'nombre' => 'Usuario Actualizado',
                'email' => 'actualizado@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Usuario Actualizado', $user->nombre);
        $this->assertSame('actualizado@example.com', $user->email);
    }

    public function test_user_can_not_delete_their_own_account_from_profile(): void
    {
        $user = Usuario::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response->assertNotFound();
        $this->assertNotNull($user->fresh());
    }
}
