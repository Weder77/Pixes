<?php

namespace App\Service\Game;

class GameService
{
    public function generateSlug(string $name): string
    {
        return str_replace(" ", "-", strtolower($name));
    }

}