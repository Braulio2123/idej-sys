<?php

namespace Tests;

use App\Models\Usuario;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function actingAs(UserContract $user, $guard = null)
    {
        parent::actingAs($user, $guard);

        if ($user instanceof Usuario) {
            $this->withSession([
                'auth.version' => $user->versionAutenticacion(),
                'auth.password_changed_at' => $user->password_changed_at?->timestamp,
            ]);
        }

        return $this;
    }
}
