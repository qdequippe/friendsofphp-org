<?php

declare(strict_types=1);

namespace Fop\Meetup\ValueObject;

use Fop\Meetup\Contract\ArrayableInterface;

final class Group implements ArrayableInterface
{
    public function __construct(
        private string $name,
        private string $meetupComSlug,
        private string $country
    ) {
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

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'meetup_com_slug' => $this->getMeetupComSlug(),
            'country' => $this->country,
        ];
    }
}
