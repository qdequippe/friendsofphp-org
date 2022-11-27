<?php

declare(strict_types=1);

namespace Fop\MeetupCom;

use Fop\Meetup\Repository\GroupRepository;
use Fop\Meetup\ValueObject\Meetup;
use Fop\MeetupCom\Api\MeetupComApi;
use Fop\MeetupCom\Meetup\MeetupComMeetupFactory;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Style\SymfonyStyle;

final class MeetupComMeetupImporter
{
    public function __construct(
        private readonly GroupRepository $groupRepository,
        private readonly MeetupComMeetupFactory $meetupComMeetupFactory,
        private readonly MeetupComApi $meetupComApi,
        private readonly SymfonyStyle $symfonyStyle,
    ) {
    }

    /**
     * @return Meetup[]
     */
    public function import(): array
    {
        $errors = [];
        $meetups = [];

        foreach ($this->groupRepository->fetchAll() as $group) {
            try {
                $groupSlug = $group->getMeetupComSlug();

                $message = sprintf('Scanning "%s" group', $groupSlug);
                $this->symfonyStyle->writeln(' * ' . $message);

                $meetupsData = $this->meetupComApi->getMeetupsByGroupSlug($groupSlug);

                $note = sprintf('Found %d meetups', count($meetupsData));
                $this->symfonyStyle->note($note);

                // @see https://www.meetup.com/api/guide/#p05-rate-limiting
                if ($meetupsData === []) {
                    continue;
                }

                $groupMeetups = $this->createMeetupsFromMeetupsData($meetupsData);
                $meetups = array_merge($meetups, $groupMeetups);
            } catch (GuzzleException $guzzleException) {
                // the group might not exists anymore, but it should not be a blocker for existing groups
                $errors[] = $guzzleException->getMessage();
            }
        }

        // report errors
        foreach ($errors as $error) {
            $this->symfonyStyle->error($error);
        }

        // sort meetups from by date
        usort(
            $meetups,
            fn (Meetup $firstMeetup, Meetup $secondMeetup): int
                => $firstMeetup->getUtcStartDateTime() <=> $secondMeetup->getUtcStartDateTime()
        );

        return $meetups;
    }

    /**
     * @param mixed[] $meetupsData
     * @return Meetup[]
     */
    private function createMeetupsFromMeetupsData(array $meetupsData): array
    {
        $meetups = [];

        foreach ($meetupsData as $meetupData) {
            $meetup = $this->meetupComMeetupFactory->createFromData($meetupData);
            if (! $meetup instanceof Meetup) {
                continue;
            }

            $meetups[] = $meetup;
        }

        return $meetups;
    }
}
