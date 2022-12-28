<?php

declare(strict_types=1);

namespace Fop\MeetupCom\Api;

use Goutte\Client;

final class MeetupComCrawler
{
    private const BASE_URI = 'https://meetup.com';

    public function __construct(
        private readonly Client $client
    ) {
    }

    public function getMeetupsByGroupSlug(string $groupSlug): array
    {
        $uri = sprintf('%s/%s/events', self::BASE_URI, $groupSlug);

        $crawler = $this->client->request('GET', $uri);

        $data = [];
        foreach ($crawler->filter('script[type="application/ld+json"]') as $domElement) {
            $schemas = json_decode($domElement->textContent, true, 512, \JSON_THROW_ON_ERROR);

            foreach ($schemas as $schema) {
                if (isset($schema['@type']) && $schema['@type'] === 'Event') {
                    $data[] = $schema;
                }
            }
        }

        return $data;
    }
}
