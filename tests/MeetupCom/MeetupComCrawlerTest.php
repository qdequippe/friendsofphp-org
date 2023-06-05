<?php

declare(strict_types=1);

namespace Fop\Tests\MeetupCom;

use Fop\MeetupCom\MeetupComCrawler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symplify\SmartFileSystem\SmartFileSystem;

final class MeetupComCrawlerTest extends TestCase
{
    public function testGetMeetupsByGroupSlug(): void
    {
        // Arrange
        $smartFileSystem = new SmartFileSystem();
        $mockResponse = new MockResponse($smartFileSystem->readFile(__DIR__ . '/fixtures/meetup_events.html'));
        $mockHttpClient = new MockHttpClient([$mockResponse]);
        $httpBrowser = new HttpBrowser($mockHttpClient);
        $meetupComCrawler = new MeetupComCrawler($httpBrowser);

        // Act
        $meetups = $meetupComCrawler->getMeetupsByGroupSlug('sfugcgn');

        // Assert
        self::assertCount(1, $meetups);
        $meetup = $meetups[0];
        self::assertSame('"Xdebug gonna give it to ya" & "Upgrade your Symfony app with Rector"', $meetup['name']);
    }
}
