<?php declare(strict_types=1);

namespace Fop\PosobotaCz;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;

final class IcalParser
{
    /**
     * @return mixed[]
     */
    public function parseIcalUrlToArray(string $url): array
    {
        $content = FileSystem::read($url);
        $lines = explode(PHP_EOL, $content);

        $data = [];
        foreach ($lines as $line) {
            if (Strings::contains($line, ':') === false) {
                continue;
            }

            [$key, $value] = explode(':', $line, 2);
            $data[$key] = $value;
        }

        return $data;
    }
}
