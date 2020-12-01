<?php

declare(strict_types=1);

namespace Fop\Group\ValueObject;

final class Group
{
    public function __construct(private string $name, private string $meetupComSlug, private string $country)
    {
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
