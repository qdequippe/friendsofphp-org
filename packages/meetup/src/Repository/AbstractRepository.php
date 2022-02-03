<?php

declare(strict_types=1);

namespace Fop\Meetup\Repository;

use Fop\Meetup\Contract\Repository\RepositoryInterface;
use Jajo\JSONDB;
use Symfony\Contracts\Service\Attribute\Required;

abstract class AbstractRepository implements RepositoryInterface
{
    #[Required]
    protected JSONDB $jsonDb;

    /**
     * @return array<string, mixed>
     */
    public function fetchAll(): array
    {
        return $this->jsonDb->from($this->getTable())
            ->get();
    }

    /**
     * @param array<string, $meetups mixed>
     */
    public function saveMany(array $meetups): void
    {
        $this->jsonDb->insert($this->getTable(), $meetups);
    }
}
