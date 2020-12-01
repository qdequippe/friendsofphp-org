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

    public function __construct(private MeetupComApi $meetupComApi, private SymfonyStyle $symfonyStyle)
    {
    }

    public function coolDownIfNeeded(): void
    {
        $reaminingRequestCount = $this->meetupComApi->getRemainingRequestCount();
        if ($reaminingRequestCount >= self::REMAINING_REQUEST_LOWER_LIMIT) {
            return;
        }

        $warningMessage = sprintf(
            'Remaining request count %d is under %d. Cooling down for %d seconds not to throtle meetup.com API',
            $reaminingRequestCount,
            self::REMAINING_REQUEST_LOWER_LIMIT,
            self::COOLDOWN_IN_SECONDS
        );
        $this->symfonyStyle->warning($warningMessage);

        sleep(self::COOLDOWN_IN_SECONDS);
    }
}
