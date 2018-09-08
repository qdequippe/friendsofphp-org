<?php declare(strict_types=1);

namespace Fop\PhpUg\Api;

use Fop\Guzzle\ResponseFormatter;
use GuzzleHttp\Client;

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

    /**
     * @var ResponseFormatter
     */
    private $responseFormatter;

    public function __construct(Client $client, ResponseFormatter $responseFormatter)
    {
        $this->client = $client;
        $this->responseFormatter = $responseFormatter;
    }

    /**
     * @return mixed[]
     */
    public function getAllGroups(): array
    {
        $response = $this->client->request('get', self::API_ALL_GROUPS_URL);

        $json = $this->responseFormatter->formatResponseToJson($response, self::API_ALL_GROUPS_URL);
        return $json['groups'] ?? [];
    }
}
