<?php

declare(strict_types=1);

namespace Fop\Meetup\ValueObject;

use Fop\Meetup\Contract\ArrayableInterface;

final class Group implements ArrayableInterface
{
    public function __construct(
        private readonly string $name,
        private readonly string $meetupComSlug,
        private readonly string $country
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data['name'], $data['meetup_com_slug'], $data['country'],);
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
            'meetup_com_slug' => $this->meetupComSlug,
            'country' => $this->country,
        ];
    }
}
