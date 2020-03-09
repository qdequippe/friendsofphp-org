<?php

declare(strict_types=1);

namespace Fop\MeetupCom\ValueObject;

final class RateLimits
{
    /**
     * @var int
     */
    private $requestLimit;

    /**
     * @var int
     */
    private $remainingRequests;

    /**
     * @var int
     */
    private $requestToReset;

    public function __construct(int $requestLimit, int $remainingRequests, int $requestToReset)
    {
        $this->requestLimit = $requestLimit;
        $this->remainingRequests = $remainingRequests;
        $this->requestToReset = $requestToReset;
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
