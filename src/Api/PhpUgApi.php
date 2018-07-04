<?php declare(strict_types=1);

namespace Fop\Api;

use GuzzleHttp\Client;
use Nette\Utils\Json;
use function GuzzleHttp\Psr7\build_query;

final class PhpUgApi
{
    /**
     * @var string
     */
    private const API_ALL_GROUPS_URL = 'https://php.ug/api/rest/listtype/1';

    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return mixed[]
     */
    public function getAllGroups(): array
    {
        $response = $this->client->request('get', self::API_ALL_GROUPS_URL);

        $result = Json::decode($response->getBody(), Json::FORCE_ARRAY);

        return $result['groups'] ?? [];
    }
}
