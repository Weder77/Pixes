<?php

namespace App\DataFixtures;

use App\Entity\Code;
use App\Entity\Game;
use App\Entity\Platform;
use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // Create platforms
        $platforms = ['Steam', 'PlayStation 4', 'XBox One', 'Switch'];
        $platforms_slug = ['steam', 'playstation-4', 'xbox-one', 'switch'];
        foreach ($platforms as $key => $value) {
            $platform = new Platform();
            $platform->setName($value);
            $platform->setSlug($platforms_slug[$key]);
            $manager->persist($platform);
        }

        // Create Tags
        $tags = ['Action', 'Course', 'Aventure', 'Multijoueur'];
        $tags_slug = ['action', 'course', 'aventure', 'multijoueur'];
        foreach ($tags as $key => $value) {
            $tag = new Tag();
            $tag->setName($value);
            $tag->setSlug($tags_slug[$key]);
            $manager->persist($tag);
        }

        // Create Game
        $game = new Game();
        $game->setName('Grand Theft Auto V');
        $game->setSlug('grand-theft-auto-v');
        $game->setDescription('VROOM VROOM PAN PAN');
        $game->setPrice(39.99);
        $game->setImgUrl('none');
        $game->setPegi(18);
        $game->addPlatform($platform);
        $game->addTag($tag);
        $manager->persist($game);

        $manager->flush();
    }
}
