<?php declare(strict_types=1);

namespace Fop\Guzzle;

use Nette\Utils\Json;
use Psr\Http\Message\ResponseInterface;

final class ResponseConverter
{
    public function toJson(ResponseInterface $response): array
    {
        return Json::decode((string) $response->getBody(), Json::FORCE_ARRAY);
    }
}
