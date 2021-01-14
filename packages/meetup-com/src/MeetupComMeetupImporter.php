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
        private GroupRepository $groupRepository,
        private MeetupComMeetupFactory $meetupComMeetupFactory,
        private MeetupComApi $meetupComApi,
        private SymfonyStyle $symfonyStyle,
        private MeetupComCooler $meetupComCooler
    ) {
    }

    /**
     * @return Meetup[]
     */
    public function getMeetups(): array
    {
        $errors = [];
        $meetups = [];

        foreach ($this->groupRepository->getGroups() as $group) {
            try {
                $message = sprintf('Loading meetups for "%s" group', $group->getMeetupComSlug());
                $this->symfonyStyle->note($message);

                $meetupsData = $this->meetupComApi->getMeetupsByGroupSlug($group->getMeetupComSlug());
                $this->meetupComCooler->coolDownIfNeeded();

                // should help with https://github.com/TomasVotruba/friendsofphp.org/runs/492500241#step:4:32
                // @see https://www.meetup.com/meetup_api/#limits
                if ($meetupsData === []) {
                    continue;
                }

                $groupMeetups = $this->createMeetupsFromMeetupsData($meetupsData);
                $meetups = [...$meetups, ...$groupMeetups];
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
     * @return Meetup[]
     */
    private function createMeetupsFromMeetupsData(array $meetupsData): array
    {
        $meetups = [];

        foreach ($meetupsData as $meetupData) {
            $meetup = $this->meetupComMeetupFactory->createFromData($meetupData);
            if ($meetup === null) {
                continue;
            }

            $meetups[] = $meetup;
        }

        return $meetups;
    }
}
