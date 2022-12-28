<?php

declare(strict_types=1);

namespace Fop\Tests\MeetupCom;

use Fop\MeetupCom\MeetupComCrawler;
use Goutte\Client;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class MeetupComCrawlerTest extends TestCase
{
    public function testGetMeetupsByGroupSlug(): void
    {
        // Arrange
        $mockResponse = new MockResponse(file_get_contents(__DIR__ . '/fixtures/meetup_events.html'));
        $httpClient = new MockHttpClient([$mockResponse]);
        $goutteClient = new Client($httpClient);
        $meetupComCrawler = new MeetupComCrawler($goutteClient);

        // Act
        $meetups = $meetupComCrawler->getMeetupsByGroupSlug('group-slug');

        // Assert
        self::assertCount(1, $meetups);
        $meetup = $meetups[0];
        self::assertSame('"Xdebug gonna give it to ya" & "Upgrade your Symfony app with Rector"', $meetup['name']);
    }
}
