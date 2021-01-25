<?php

declare(strict_types=1);

namespace Fop\Meetup\ValueObject;

final class ParameterHolder
{
    public function __construct(
        private string $parameterName,
        private array $parameterValue
    ) {
    }

    public function getParameterName(): string
    {
        return $this->parameterName;
    }

    /**
     * @return mixed[]
     */
    public function getParameterValue(): array
    {
        return $this->parameterValue;
    }
}
