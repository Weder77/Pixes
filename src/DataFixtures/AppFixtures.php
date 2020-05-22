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
        $platforms = [];
        for ($i = 0; $i < 4; $i++) { 
            $platform = new Platform();
            $platform->setName('Platform ' . $i);
            $platform->setSlug('platform-' . $i);
            array_push($platforms, $platform);
            $manager->persist($platform);
        }

        // Create Tags
        $tags = [];
        for ($i=0; $i < 8; $i++) { 
            $tag = new Tag();
            $tag->setName('Tag ' . $i);
            $tag->setSlug('tag-' . $i);
            array_push($tags, $tag);
            $manager->persist($tag);
        }

        // Create Games
        $pegis = [3, 7, 12, 16, 18];
        for ($i=0; $i < 10; $i++) { 
            $game = new Game();
            $game->setName('Game ' . $i);
            $game->setSlug('game-' . $i);
            $game->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec finibus ex ipsum, laoreet euismod libero posuere suscipit. Ut nec nisl quam. Curabitur ullamcorper libero turpis, in cursus tellus venenatis et. Sed venenatis euismod massa ut porttitor. Integer sollicitudin, lectus dictum lobortis iaculis, arcu nulla euismod ipsum, a tempor eros velit ut mauris. Ut id lectus at diam aliquam ultricies vel eget orci. Suspendisse imperdiet egestas odio. Aliquam pellentesque sagittis facilisis. Nunc efficitur ac neque ut luctus. Cras tincidunt turpis dignissim eros egestas ultricies. In tempus arcu vel velit pretium ultricies a quis neque. ');
            $game->setPrice(rand(1,40));
            $game->setImgUrl('default.png');
            $game->setPegi($pegis[rand(0, 4)]);
            for ($x=0; $x < rand(1, 3); $x++) { 
                $game->addPlatform($platforms[rand(0, sizeof($platforms) - 1)]);
            }
            for ($x=0; $x < rand(1, 3); $x++) { 
                $game->addTag($tags[rand(0, sizeof($tags) - 1)]);
            }
            $manager->persist($game);
        }

        $manager->flush();
    }
}
