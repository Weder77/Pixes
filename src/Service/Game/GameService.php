<?php

namespace App\Service\Game;

class GameService
{
    public function generateSlug(string $name): String 
    {
        return str_replace(" ", "-", strtolower($name));
    }
}