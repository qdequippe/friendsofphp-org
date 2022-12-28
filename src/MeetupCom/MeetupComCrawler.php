<?php

declare(strict_types=1);

namespace Fop\MeetupCom;

use Goutte\Client;
use Nette\Utils\Json;

final class MeetupComCrawler
{
    private const BASE_URI = 'https://meetup.com';

    public function __construct(
        private readonly Client $client
    ) {
    }

    /**
     * @return mixed[]
     */
    public function getMeetupsByGroupSlug(string $groupSlug): array
    {
        $uri = sprintf('%s/%s/events', self::BASE_URI, $groupSlug);

        $crawler = $this->client->request('GET', $uri);

        $data = [];
        $structuredDataElements = $crawler->filter('script[type="application/ld+json"]');
        foreach ($structuredDataElements as $domElement) {
            $schemas = Json::decode($domElement->textContent, Json::FORCE_ARRAY);

            foreach ($schemas as $schema) {
                if (isset($schema['@type']) && $schema['@type'] === 'Event') {
                    $data[] = $schema;
                }
            }
        }

        return $data;
    }
}
