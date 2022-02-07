<?php

declare(strict_types=1);

namespace Fop\MeetupCom;

use Fop\Meetup\Contract\MeetupImporterInterface;
use Fop\Meetup\Repository\GroupRepository;
use Fop\Meetup\ValueObject\Meetup;
use Fop\MeetupCom\Api\MeetupComApi;
use Fop\MeetupCom\Api\MeetupComCooler;
use Fop\MeetupCom\Meetup\MeetupComMeetupFactory;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\Console\Style\SymfonyStyle;

final class MeetupComMeetupImporter implements MeetupImporterInterface
{
    public function __construct(
        private readonly GroupRepository $groupRepository,
        private readonly MeetupComMeetupFactory $meetupComMeetupFactory,
        private readonly MeetupComApi $meetupComApi,
        private readonly SymfonyStyle $symfonyStyle,
        private readonly MeetupComCooler $meetupComCooler
    ) {
    }

    /**
     * @return Meetup[]
     */
    public function getMeetups(): array
    {
        $errors = [];
        $meetups = [];

        foreach ($this->groupRepository->fetchAll() as $group) {
            try {
                $groupSlug = $group->getMeetupComSlug();

                $message = sprintf('Loading meetups for "%s" group', $groupSlug);
                $this->symfonyStyle->note($message);

                $meetupsData = $this->meetupComApi->getMeetupsByGroupSlug($groupSlug);

                $this->meetupComCooler->coolDownIfNeeded();

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

        return $meetups;
    }

    public function getKey(): string
    {
        return 'meetup-com';
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
