<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Review;
use App\Model\Entity\Tag;
use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use App\Rating\CalculateAverageRating;
use App\Rating\CountRatingsPerValue;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

use function array_fill_callback;

final class VideoGameFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly Generator              $faker,
        private readonly CalculateAverageRating $calculateAverageRating,
        private readonly CountRatingsPerValue   $countRatingsPerValue
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        $tags = $manager->getRepository(Tag::class)->findAll();
        $users = $manager->getRepository(User::class)->findAll();

        /** @var array<int, VideoGame> $videoGames */
        $videoGames = array_fill_callback(0, 50, function (int $index): VideoGame {
            $videoGame = new VideoGame;
            $description = $this->faker->paragraphs(10, true);
            $test = $this->faker->paragraphs(6, true);
            $videoGame->setTitle(sprintf('Jeu vidéo %d', $index));
            $videoGame->setDescription(is_string($description) === true ? $description : '');
            $videoGame->setReleaseDate(new DateTimeImmutable());
            $videoGame->setTest(is_string($test) === true ? $test : '');
            $videoGame->setRating(($index % 5) + 1);
            $videoGame->setImageName(sprintf('video_game_%d.png', $index));
            $videoGame->setImageSize(2_098_872);
            return $videoGame;
        }
        );


        // TODO : Ajouter les tags aux vidéos
        for ($i = 0; $i < count($videoGames); $i++) {

            for ($j = 0; $j < 5; $j++) {

                $videoGames[$i]->getTags()->add($tags[($i + $j) % count($tags)]);
            }


            $manager->persist($videoGames[$i]);
        }

        $manager->flush();

        // TODO : Ajouter des reviews aux vidéos
        for ($i = 0; $i < count($videoGames); $i++) {
            for ($j = 1; $j < count($users); $j++) {
                $comment = $this->faker->paragraphs(3, true);
                $review = (new Review)
                    ->setRating($i % 5)
                    ->setComment(is_string($comment) === true ? $comment : '')
                    ->setUser($users[$j])
                    ->setVideoGame($videoGames[$i]);
                $videoGames[$i]->getReviews()->add($review);
                $manager->persist($review);
                $this->calculateAverageRating->calculateAverage($videoGames[$i]);
                $this->countRatingsPerValue->countRatingsPerValue($videoGames[$i]);
            }
        }
        $manager->flush();

    }

    public function getDependencies(): array
    {
        return [UserFixtures::class];
    }
}
