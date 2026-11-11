<?php

namespace Tests\Feature;

use Tests\TestCase;

class GameTest extends TestCase
{
    public function test_the_pilah_game_is_available_without_authentication(): void
    {
        $response = $this->get(route('game.pilah'));

        $response->assertOk()
            ->assertSee('Misi Pilah')
            ->assertSee('Mulai Misi')
            ->assertSee('Limbah B3');
    }
}
