<?php

namespace App\Service\Game;

class GameService
{
    public function generateSlug(string $name): String
    {
        return str_replace(" ", "-", strtolower($name));
    }

    function generateRandomString($length = 4): String
    {
        return substr(
            str_shuffle(
                str_repeat(
                    $x = '0123456789ABCDEFGHIJKLMN0PQRSTUVWXYZ',
                    ceil($length / strlen($x))
                )
            ),
            1,
            $length
        );
    }

    public function generateCode(): String
    {
        return $this->generateRandomString() . '-' . $this->generateRandomString() . '-' . $this->generateRandomString() . '-' . $this->generateRandomString();
    }

    public function getAverageNote($opinions): float
    {
        $total = 0;
        foreach ($opinions as $opinion) {
            $total += $opinion->getNote();
        }
        if (sizeof($opinions) != 0){
            return round($total / sizeof($opinions), 1);
        } else {
            return round($total / 1, 1);
        }
    }   
}
