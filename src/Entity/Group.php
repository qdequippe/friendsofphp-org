<?php declare(strict_types=1);

namespace Fop\Entity;

final class Group
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $meetupComId;

    /**
     * @var string
     */
    private $meetupComUrl;

    /**
     * @var string|null
     */
    private $country;

    public function __construct(string $name, int $meetupComId, string $meetupComUrl, ?string $country)
    {
        $this->name = $name;
        $this->meetupComId = $meetupComId;
        $this->meetupComUrl = $meetupComUrl;
        $this->country = $country;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMeetupComId(): int
    {
        return $this->meetupComId;
    }

    public function getMeetupComUrl(): string
    {
        return $this->meetupComUrl;
    }

    public function getstring(): ?string
    {
        return $this->country;
    }
}
