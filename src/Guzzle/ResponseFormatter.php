<?php declare(strict_types=1);

namespace Fop\Guzzle;

use Fop\Exception\ApiException;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Psr\Http\Message\ResponseInterface;

final class ResponseFormatter
{
    /**
     * @return mixed[]
     */
    public function formatResponseToJson(ResponseInterface $response, string $originalUrl): array
    {
        if ($response->getStatusCode() !== 200) {
            throw new ApiException(sprintf(
                'Response to "%s" failed: "%s"',
                $originalUrl,
                $response->getReasonPhrase()
            ));
        }

        try {
            return Json::decode((string) $response->getBody(), Json::FORCE_ARRAY);
        } catch (JsonException $jsonException) {
            throw new JsonException('Syntax error while decoding:' . (string) $response->getBody());
        }
    }
}
