<?php

namespace App\DataFixtures;

use App\Entity\Code;
use App\Entity\Game;
use App\Entity\Opinion;
use App\Entity\Platform;
use App\Entity\Profile;
use App\Entity\Tag;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Constraints\Date;

class AppFixtures extends Fixture
{
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        function generateRandomString($length = 4) {
            return substr(
                str_shuffle(
                    str_repeat($x='0123456789ABCDEFGHIJKLMN0PQRSTUVWXYZ', 
                    ceil($length/strlen($x)) )
                ),1,$length);
        }

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
        $games = [];
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
            array_push($games, $game);
            $manager->persist($game);
        }

        // Generate Codes
        for ($i=0; $i < 30; $i++) { 
            $code = new Code();
            $code->setCode(
                generateRandomString() . '-' . generateRandomString() . '-' . generateRandomString() . '-' . generateRandomString()
            );
            $code->setGame($games[rand(0, 9)]);
            $code->setUsed(false);
            $manager->persist($code);
        }

        // Create Admin
        $admin = new User();
        $admin->setEmail('admin@test.com');
        $password = $this->encoder->encodePassword($admin, 'Admin');
        $admin->setPassword($password);
        $admin->setRoles(['ROLE_ADMIN']);
        $profile = new Profile();
        $profile->setFirstname('Admin');
        $profile->setLastname('Istrateur');
        $date = new DateTime();
        $profile->setBirthday($date);
        $profile->setRegisterDate($date);
        $profile->setBalance(rand(0,150));
        $profile->setUser($admin);
        $manager->persist($admin);
        $manager->persist($profile);

        // Create Users & Profiles
        $users = [];
        for ($i = 1; $i <= 3; $i++) {
            $user = new User();
            $user->setEmail('user' . $i . '@test.com');
            $password = $this->encoder->encodePassword($user, 'User' . $i);
            $user->setPassword($password);
            $profile = new Profile();
            $profile->setFirstname('User');
            $profile->setLastname($i);
            $date = new DateTime();
            $profile->setBirthday($date);
            $profile->setRegisterDate($date);
            $profile->setBalance(rand(0,150));
            $profile->setUser($user);
            array_push($users, $profile);
            $manager->persist($user);
            $manager->persist($profile);
        }

        // Generate Opinions
        for ($i=0; $i < 13; $i++) { 
            $opinion = new Opinion();
            $opinion->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit. ');
            $opinion->setNote(rand(1,5));
            $date = new DateTime();
            $opinion->setPostedOn($date);
            $opinion->setGame($games[rand(0,9)]);
            $opinion->setUser($users[rand(0,2)]);
            $manager->persist($opinion);
        }

        $manager->flush();
    }
}
