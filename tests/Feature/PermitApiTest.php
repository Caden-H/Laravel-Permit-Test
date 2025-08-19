<?php

namespace Tests\Feature;

use App\Models\Permit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermitApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_permit_with_valid_data()
    {
        $payload = [
            'number' => 'PRM-2001',
            'applicant' => 'Jane Developer',
            'status' => 'pending',
        ];

        $res = $this->postJson('/api/permits', $payload);

        $res->assertCreated()
            ->assertJsonFragment(['number' => 'PRM-2001']);

        $this->assertDatabaseHas('permits', ['number' => 'PRM-2001']);
    }

    /** @test */
    public function it_rejects_invalid_payload_with_422()
    {
        $payload = [
            'number' => '',
            'applicant' => '',
            'status' => 'banana',
        ];

        $res = $this->postJson('/api/permits', $payload);

        $res->assertStatus(422)
            ->assertJsonValidationErrors(['number','applicant','status']);
    }
}
