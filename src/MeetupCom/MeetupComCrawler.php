<?php

declare(strict_types=1);

namespace Fop\MeetupCom;

use Nette\Utils\Json;
use Symfony\Component\BrowserKit\HttpBrowser;

final readonly class MeetupComCrawler
{
    private const BASE_URI = 'https://meetup.com';

    public function __construct(
        private HttpBrowser $httpBrowser
    ) {
    }

    /**
     * @return mixed[]
     */
    public function getMeetupsByGroupSlug(string $groupSlug): array
    {
        $uri = sprintf('%s/%s', self::BASE_URI, $groupSlug);

        $crawler = $this->httpBrowser->request('GET', $uri);

        $data = [];
        $structuredDataElements = $crawler->filter('script[type="application/ld+json"]');
        foreach ($structuredDataElements as $structuredDataElement) {
            $schemas = Json::decode($structuredDataElement->textContent, Json::FORCE_ARRAY);

            foreach ($schemas as $schema) {
                if (! isset($schema['@type'])) {
                    continue;
                }

                if ($schema['@type'] !== 'Event') {
                    continue;
                }

                if (! isset($schema['url'])) {
                    continue;
                }

                if (! isset($schema['organizer']['url'])) {
                    continue;
                }

                if (stripos((string) $schema['organizer']['url'], $groupSlug) === false) {
                    continue;
                }

                $crawler = $this->httpBrowser->request('GET', $schema['url']);
                $innerStructuredDataElements = $crawler->filter('script[type="application/ld+json"]');

                foreach ($innerStructuredDataElements as $innerStructuredDataElement) {
                    $innerSchema = Json::decode($innerStructuredDataElement->textContent, Json::FORCE_ARRAY);
                    if (! isset($innerSchema['@type'])) {
                        continue;
                    }

                    if ($innerSchema['@type'] !== 'Event') {
                        continue;
                    }

                    $data[] = $innerSchema;
                }
            }
        }

        return $data;
    }
}
