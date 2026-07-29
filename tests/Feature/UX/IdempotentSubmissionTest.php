<?php

namespace Tests\Feature\UX;

use App\Http\Middleware\PreventDuplicateSubmission;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class IdempotentSubmissionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cache.default' => 'array',
            'idej_operations.cache_store' => 'array',
            'session.driver' => 'array',
        ]);

        Cache::store('array')->flush();

        Route::post('/__tests/idempotent-operation', function () {
            Cache::store('array')->increment('test-operation-count');

            return redirect('/__tests/idempotent-result')->with('success', 'Operación completada.');
        })->middleware(['web', PreventDuplicateSubmission::class]);

        Route::post('/__tests/idempotent-validation', function () {
            $validator = Validator::make(request()->all(), [
                'nombre' => ['required', 'string'],
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            Cache::store('array')->increment('test-validation-count');

            return redirect('/__tests/idempotent-result')->with('success', 'Operación completada.');
        })->middleware(['web', PreventDuplicateSubmission::class]);
    }

    public function test_same_operation_key_is_processed_only_once(): void
    {
        $uuid = 'b3c8a0d5-4c6f-4f76-8da2-11adbd6b3261';

        $this->post('/__tests/idempotent-operation', [
            '_idempotency_key' => $uuid,
        ])->assertRedirect('/__tests/idempotent-result');

        $this->post('/__tests/idempotent-operation', [
            '_idempotency_key' => $uuid,
        ])->assertSessionHas('info');

        $this->assertSame(1, Cache::store('array')->get('test-operation-count'));
    }

    public function test_different_operation_key_allows_a_new_action(): void
    {
        $this->post('/__tests/idempotent-operation', [
            '_idempotency_key' => '4fbd7f44-14f4-4839-9468-e5df16efdfe1',
        ]);

        $this->post('/__tests/idempotent-operation', [
            '_idempotency_key' => 'e9d44821-86c2-43bf-a558-3231a836e264',
        ]);

        $this->assertSame(2, Cache::store('array')->get('test-operation-count'));
    }

    public function test_validation_failure_does_not_consume_operation_key(): void
    {
        $uuid = '79c10240-62c4-4b0b-a153-1dc3c2ecbfb2';

        $this->post('/__tests/idempotent-validation', [
            '_idempotency_key' => $uuid,
        ])->assertSessionHasErrors('nombre');

        $this->post('/__tests/idempotent-validation', [
            '_idempotency_key' => $uuid,
            'nombre' => 'Registro válido',
        ])->assertRedirect('/__tests/idempotent-result');

        $this->assertSame(1, Cache::store('array')->get('test-validation-count'));
    }

    public function test_duplicate_json_request_returns_conflict(): void
    {
        $uuid = '8e371405-5f80-4c06-a019-f8773354cb5a';

        $this->postJson('/__tests/idempotent-operation', [
            '_idempotency_key' => $uuid,
        ])->assertRedirect('/__tests/idempotent-result');

        $this->postJson('/__tests/idempotent-operation', [
            '_idempotency_key' => $uuid,
        ])->assertStatus(409)
            ->assertJson([
                'duplicate' => true,
            ]);
    }
}
