<?php declare(strict_types=1);

namespace Fop\MeetupCom\Crawler;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Symfony\Component\DomCrawler\Crawler;

final class CrawlerFactory
{
    /**
     * @var string
     */
    private const XML_CONDOM = '<?xml version="1.0" encoding="utf-8"?>';

    public function createFromUrl(string $url): ?Crawler
    {
        if (file_exists($url) === false) {
            return null;
        }

        $remoteContent = trim(FileSystem::read($url));
        $remoteContent = $this->removeXmlCondom($remoteContent);
        return new Crawler($remoteContent);
    }

    /**
     * Sometimes the xml condom is provided to disallow parsing of content.
     * Let's remove it.
     */
    private function removeXmlCondom(string $content): string
    {
        if (! Strings::startsWith($content, self::XML_CONDOM)) {
            return $content;
        }

        $content = Strings::substring($content, strlen(self::XML_CONDOM));
        return trim($content);
    }
}
