<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ARSumQuery extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    protected $token;
    protected $headers;

    public function setUp()
    {
        parent::setUp();
        $this->token = file_get_contents('/var/www/html/larSys/tests/Unit/token.txt');
        $this->headers = ['Authorization' => 'Bearer '.$this->token, 'X-Requested-With' => 'XMLHttpRequest'];
    }

    public function testARSumQuery()
    {
        $response = $this->json('POST',"/api/arsum/query", ['name' => 'a'], $this->headers)
            ->assertStatus(200)
            ->assertJson(['status' => 'success']);



    }

    public function testARSumFilter()
    {
        $response = $this->json('POST', '/api/arsum/filter',[], $this->headers)
            ->assertStatus(200)->assertJson([]);


    }
}
