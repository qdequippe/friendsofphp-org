<?php

declare(strict_types=1);

namespace Fop\MeetupCom\ValueObject;

final class RateLimits
{
    public function __construct(
        private readonly int $requestLimit,
        private readonly int $remainingRequests,
        private readonly int $requestToReset
    ) {
    }

    public function getRequestLimit(): int
    {
        return $this->requestLimit;
    }

    public function getRemainingRequests(): int
    {
        return $this->remainingRequests;
    }

    public function getRequestToReset(): int
    {
        return $this->requestToReset;
    }
}
