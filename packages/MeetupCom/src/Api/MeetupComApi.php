<?php declare(strict_types=1);

namespace Fop\MeetupCom\Api;

use Fop\Guzzle\ResponseConverter;
use Fop\MeetupCom\Guzzle\Oauth2AwareClient;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Style\SymfonyStyle;

final class MeetupComApi
{
    /**
     * @see https://www.meetup.com/meetup_api/docs/:urlname/events/#list
     * @var string
     */
    private const API_EVENTS_BY_GROUPS_URL = 'http://api.meetup.com/%s/events';

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var ResponseConverter
     */
    private $responseConverter;

    /**
     * @var Oauth2AwareClient
     */
    private $oauth2AwareClient;

    public function __construct(
        SymfonyStyle $symfonyStyle,
        ResponseConverter $responseConverter,
        Oauth2AwareClient $oauth2AwareClient
    ) {
        $this->symfonyStyle = $symfonyStyle;
        $this->responseConverter = $responseConverter;
        $this->oauth2AwareClient = $oauth2AwareClient;
    }

    /**
     * @param string[] $groupSlugs
     * @return mixed[]
     */
    public function getMeetupsByGroupSlugs(array $groupSlugs): array
    {
        $meetups = [];
        $errors = [];

        $progressBar = $this->symfonyStyle->createProgressBar(count($groupSlugs));

        foreach ($groupSlugs as $groupSlug) {
            $url = sprintf(self::API_EVENTS_BY_GROUPS_URL, $groupSlug);

            $progressBar->advance();

            try {
                $response = $this->oauth2AwareClient->request('GET', $url);
            } catch (GuzzleException $guzzleException) {
                // the group might not exists anymore, but it should not be a blocker for existing groups
                $errors[] = $guzzleException->getMessage();
                continue;
            }

            $json = $this->responseConverter->toJson($response);
            if ($json === []) {
                continue;
            }

            $meetups = array_merge($meetups, $json);
        }

        foreach ($errors as $error) {
            $this->symfonyStyle->error($error);
        }

        return $meetups;
    }
}
