<?php

declare(strict_types=1);

namespace Fop\Meetup\Repository;

use Fop\Meetup\Contract\Repository\RepositoryInterface;
use Jajo\JSONDB;
use Nette\Utils\FileSystem;
use Symfony\Contracts\Service\Attribute\Required;
use Symplify\SmartFileSystem\SmartFileSystem;

abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * @var string
     */
    private const JSON_DATABASE_DIRECTORY = __DIR__ . '/../../../../json-database';

    #[Required]
    public JSONDB $jsonDb;

    #[Required]
    public SmartFileSystem $smartFileSystem;

    /**
     * Must be called before first method calls
     */
    #[Required]
    public function boot(): void
    {
        if (! file_exists(self::JSON_DATABASE_DIRECTORY)) {
            FileSystem::createDir(self::JSON_DATABASE_DIRECTORY);
        }

        // create empty storage file if not exists
        $storageFile = self::JSON_DATABASE_DIRECTORY . '/' . $this->getTable();
        if (! file_exists($storageFile)) {
            $this->smartFileSystem->dumpFile($storageFile, '[]');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchAll(): array
    {
        return $this->jsonDb->from($this->getTable())
            ->get();
    }

    /**
     * @param array<string, mixed> $item
     */
    public function insert(array $item): void
    {
        $this->jsonDb->insert($this->getTable(), $item);
    }

    public function deleteAll(): void
    {
        $storageFile = self::JSON_DATABASE_DIRECTORY . '/' . $this->getTable();
        $this->smartFileSystem->dumpFile($storageFile, '[]');
    }
}
