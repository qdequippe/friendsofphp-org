<?php

declare(strict_types=1);

namespace Fop\MeetupCom;

use Exception;
use Fop\Meetup\Repository\GroupRepository;
use Fop\Meetup\ValueObject\Meetup;
use Fop\MeetupCom\Meetup\MeetupComMeetupFactory;
use Symfony\Component\Console\Style\SymfonyStyle;

final readonly class MeetupComMeetupImporter
{
    public function __construct(
        private GroupRepository $groupRepository,
        private MeetupComMeetupFactory $meetupComMeetupFactory,
        private MeetupComCrawler $meetupComCrawler
    ) {
    }

    /**
     * @return Meetup[]
     */
    public function import(SymfonyStyle $symfonyStyle): array
    {
        $errors = [];
        $meetups = [];

        $groups = $this->groupRepository->fetchAll();
        $progressBar = $symfonyStyle->createProgressBar();

        foreach ($progressBar->iterate($groups) as $group) {
            try {
                $groupSlug = $group->getMeetupComSlug();

                $meetupsData = $this->meetupComCrawler->getMeetupsByGroupSlug($groupSlug);

                if ($meetupsData === []) {
                    continue;
                }

                $groupMeetups = $this->createMeetupsFromMeetupsData($meetupsData);

                $meetups[] = $groupMeetups;
            } catch (Exception $exception) {
                $errors[] = $exception->getMessage();
            }
        }

        $meetups = array_merge(...$meetups);

        // report errors
        foreach ($errors as $error) {
            $symfonyStyle->error($error);
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
