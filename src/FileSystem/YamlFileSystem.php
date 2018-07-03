<?php declare(strict_types=1);

namespace Fop\FileSystem;

use Symfony\Component\Yaml\Yaml;

final class YamlFileSystem
{
    /**
     * @param mixed[] $data
     */
    public function saveArrayToFile(array $data, string $file): void
    {
        $yamlDump = Yaml::dump($data, 10, 4);
        file_put_contents($file, $yamlDump);
    }
}
