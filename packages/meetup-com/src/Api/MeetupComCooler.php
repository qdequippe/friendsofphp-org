<?php declare(strict_types=1);

namespace Fop\MeetupCom\Api;

use Symfony\Component\Console\Style\SymfonyStyle;

final class MeetupComCooler
{
    /**
     * @var int
     */
    private const REMAINING_REQUEST_LOWER_LIMIT = 10;

    /**
     * @var int
     */
    private const COOLDOWN_IN_SECONDS = 6;

    /**
     * @var MeetupComApi
     */
    private $meetupComApi;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(MeetupComApi $meetupComApi, SymfonyStyle $symfonyStyle)
    {
        $this->meetupComApi = $meetupComApi;
        $this->symfonyStyle = $symfonyStyle;
    }

    public function coolDownIfNeeded(): void
    {
        $reaminingRequestCount = $this->meetupComApi->getRemainingRequestCount();
        if ($reaminingRequestCount >= self::REMAINING_REQUEST_LOWER_LIMIT) {
            return;
        }

        $this->symfonyStyle->warning(sprintf(
            'Remaining request count %d is under %d. Cooling down for %d seconds not to throtle meetup.com API',
            $reaminingRequestCount,
            self::REMAINING_REQUEST_LOWER_LIMIT,
            self::COOLDOWN_IN_SECONDS
        ));

        sleep(self::COOLDOWN_IN_SECONDS);
    }
}
