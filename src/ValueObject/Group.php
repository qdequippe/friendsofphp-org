<?php

declare(strict_types=1);

namespace Fop\Core\ValueObject;

final class Group
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $meetupComSlug;

    /**
     * @var string
     */
    private $country;

    public function __construct(string $name, string $meetupComSlug, string $country)
    {
        $this->name = $name;
        $this->meetupComSlug = $meetupComSlug;
        $this->country = $country;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMeetupComSlug(): string
    {
        return $this->meetupComSlug;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'meetup_com_slug' => $this->getMeetupComSlug(),
            'country' => $this->country,
        ];
    }
}
