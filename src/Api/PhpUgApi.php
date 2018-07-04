<?php declare(strict_types=1);

namespace Fop\Api;

use GuzzleHttp\Client;
use Nette\Utils\Json;
use function GuzzleHttp\Psr7\build_query;

final class PhpUgApi
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }
}
