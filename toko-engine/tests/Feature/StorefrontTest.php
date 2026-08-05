<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_storefront_example_is_publicly_accessible(): void
    {
        $response = $this->get(route('home'));

        $response
            ->assertOk()
            ->assertSee('Toko Senja')
            ->assertSee('Produk unggulan')
            ->assertSee('Teko Tanah Sore');
    }
}
