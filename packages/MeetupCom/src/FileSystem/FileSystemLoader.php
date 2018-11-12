<?php declare(strict_types=1);

namespace Fop\MeetupCom\FileSystem;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Symfony\Component\Yaml\Yaml;

final class FileSystemLoader
{
    /**
     * @return mixed[]
     */
    public function loadFileToItems(string $filePath): array
    {
        $data = FileSystem::read($filePath);

        if (Strings::endsWith($filePath, '.txt')) {
            $data = explode(PHP_EOL, $data);

            // remove empty lines
            return array_filter($data);
        }

        if (Strings::match($data, '#\*.(yml|yaml)$#')) {
            return Yaml::parse($data);
        }

        return [];
    }
}
