<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Tests\Functional\FunctionalTestCase;

final class FilterTest extends FunctionalTestCase
{
    /* Code déjà présent */
    public function testShouldListTenVideoGames(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');
        $this->client->clickLink('2');
        self::assertResponseIsSuccessful();
    }

    public function testShouldFilterVideoGamesBySearch(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');
        $this->client->submitForm('Filtrer', ['filter[search]' => 'Jeu vidéo 49'], 'GET');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(1, 'article.game-card');
    }

    /* Fin du code déjà présent */

    /**
     * @param array{
     *     page: int,
     *     limit: int,
     *     count: int,
     *     sort: string,
     *     direction: string,
     *     filter: array<string, mixed>
     * } $queryParams
     * @param string $resultSentenceExpected
     * @param array<string> $expectedTitles
     * @dataProvider tagsListProvider
     */
    public function testShouldFilteredVideoGamesByTags(array $queryParams, string $resultSentenceExpected, array $expectedTitles): void
    {
        $this->get('/', $queryParams);
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(count($expectedTitles), 'article.game-card');
        self::assertSelectorTextContains('main .fw-bold', $resultSentenceExpected);
        $crawler = $this->client->getCrawler();
        $titles = $crawler->filter('.game-card-title');
        self::assertCount(count($expectedTitles), $titles);
        foreach ($expectedTitles as $key => $expectedTitle) {
            self::assertSame($expectedTitle, $titles->eq($key)->text());
        }

    }

    /**
     * @uses testShouldFilteredVideoGamesByTags
     * @return array<string, array{array<string, mixed>, string, array<string>}>
     *
     */
    public static function tagsListProvider(): array
    {
        return [
            'without tags' => [
                FilterTest::createQueryParams(1, []),
                'Affiche 10 jeux vidéo de 1 à 10 sur les 50 jeux vidéo',
                ['Jeu vidéo 0', 'Jeu vidéo 1', 'Jeu vidéo 2', 'Jeu vidéo 3', 'Jeu vidéo 4', 'Jeu vidéo 5', 'Jeu vidéo 6', 'Jeu vidéo 7', 'Jeu vidéo 8', 'Jeu vidéo 9']
            ],
            'page 2 without tags ' => [
                FilterTest::createQueryParams(2, []),
                'Affiche 10 jeux vidéo de 11 à 20 sur les 50 jeux vidéo',
                ['Jeu vidéo 10', 'Jeu vidéo 11', 'Jeu vidéo 12', 'Jeu vidéo 13', 'Jeu vidéo 14', 'Jeu vidéo 15', 'Jeu vidéo 16', 'Jeu vidéo 17', 'Jeu vidéo 18', 'Jeu vidéo 19']
            ],
            'with one tag' => [
                FilterTest::createQueryParams(1, [
                    'tags' => [1]
                ]),
                'Affiche 10 jeux vidéo de 1 à 10 sur les 25 jeux vidéo',
                ['Jeu vidéo 0', 'Jeu vidéo 6', 'Jeu vidéo 7', 'Jeu vidéo 8', 'Jeu vidéo 9', 'Jeu vidéo 10', 'Jeu vidéo 16', 'Jeu vidéo 17', 'Jeu vidéo 18', 'Jeu vidéo 19']
            ],
            'with many tags' => [
                FilterTest::createQueryParams(1, [
                    'tags' => [1, 2, 3]
                ]),
                'Affiche 10 jeux vidéo de 1 à 10 sur les 15 jeux vidéo',
                ['Jeu vidéo 0', 'Jeu vidéo 8', 'Jeu vidéo 9', 'Jeu vidéo 10', 'Jeu vidéo 18', 'Jeu vidéo 19', 'Jeu vidéo 20', 'Jeu vidéo 28', 'Jeu vidéo 29', 'Jeu vidéo 30']
            ],
            'with too much tags' => [
                FilterTest::createQueryParams(1, [
                    'tags' => [1, 3, 7]
                ]),
                'Affiche 0 jeux vidéo de 1 à 0 sur les 0 jeux vidéo',
                [],
            ],
            'with missing tags' => [
                FilterTest::createQueryParams(1, [
                    'tags' => [-1]
                ]),
                'Affiche 10 jeux vidéo de 1 à 10 sur les 50 jeux vidéo',
                ['Jeu vidéo 0', 'Jeu vidéo 1', 'Jeu vidéo 2', 'Jeu vidéo 3', 'Jeu vidéo 4', 'Jeu vidéo 5', 'Jeu vidéo 6', 'Jeu vidéo 7', 'Jeu vidéo 8', 'Jeu vidéo 9']
            ],
        ];
    }

    /**
     * @param int $page : The current page
     * @param array<string,mixed> $filter : Tags list and search string
     * @return array{
     *      page: int,
     *      limit: int,
     *      count: int,
     *      sort: string,
     *      direction: string,
     *      filter: array<string, mixed>
     * }
     */
    public static function createQueryParams(int $page, array $filter): array
    {
        return [
            'page' => $page,
            'limit' => 10,
            'count' => 10,
            'sort' => 'Title',
            'direction' => 'Ascending',
            'filter' => $filter
        ];
    }


}
