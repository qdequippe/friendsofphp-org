<?php

declare(strict_types=1);

namespace Fop\Meetup\Repository;

use Fop\Meetup\Contract\Repository\RepositoryInterface;
use Jajo\JSONDB;
use Nette\Utils\FileSystem;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractRepository implements RepositoryInterface
{
    #[Required]
    public JSONDB $jsonDb;

    /**
     * Must be called before first method calls
     */
    #[Required]
    public function boot(): void
    {
        $databaseStorageDirectory = __DIR__ . '/../../../../json-database';
        if (! file_exists($databaseStorageDirectory)) {
            FileSystem::createDir($databaseStorageDirectory);
        }

        // create empty storage file if not exists
        $storageFile = $databaseStorageDirectory . '/' . $this->getTable();
        if (! file_exists($storageFile)) {
            file_put_contents($storageFile, '[]');
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
}
