<?php

declare(strict_types=1);

namespace Fop\Meetup\ValueObject;

use Fop\Meetup\Contract\ArrayableInterface;

final readonly class Group implements ArrayableInterface
{
    public function __construct(
        private string $name,
        private string $meetupComSlug,
        private string $country
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data['name'], $data['meetup_com_slug'], $data['country']);
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
     * @return array{name: string, meetup_com_slug: string, country: string}
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'meetup_com_slug' => $this->meetupComSlug,
            'country' => $this->country,
        ];
    }
}
