<?php

namespace Tests\Feature;

use App\Models\Permit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermitIndexFilterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_status()
    {
        Permit::create(['number'=>'P1','applicant'=>'A','status'=>'approved']);
        Permit::create(['number'=>'P2','applicant'=>'B','status'=>'pending']);

        $this->getJson('/api/permits?status=approved')
            ->assertOk()
            ->assertJsonFragment(['number'=>'P1'])
            ->assertJsonMissing(['number'=>'P2']);
    }
}
