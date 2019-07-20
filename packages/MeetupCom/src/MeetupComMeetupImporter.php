<?php declare(strict_types=1);

namespace Fop\MeetupCom;

use Fop\Contract\MeetupImporterInterface;
use Fop\Entity\Meetup;
use Fop\MeetupCom\Api\MeetupComApi;
use Fop\MeetupCom\Meetup\MeetupComMeetupFactory;
use Fop\Repository\GroupRepository;

final class MeetupComMeetupImporter implements MeetupImporterInterface
{
    /**
     * @var GroupRepository
     */
    private $groupRepository;

    /**
     * @var MeetupComMeetupFactory
     */
    private $meetupComMeetupFactory;

    /**
     * @var MeetupComApi
     */
    private $meetupComApi;

    public function __construct(
        GroupRepository $userGroupRepository,
        MeetupComMeetupFactory $meetupComMeetupFactory,
        MeetupComApi $meetupComApi
    ) {
        $this->groupRepository = $userGroupRepository;
        $this->meetupComMeetupFactory = $meetupComMeetupFactory;
        $this->meetupComApi = $meetupComApi;
    }

    /**
     * @return Meetup[]
     */
    public function getMeetups(): array
    {
        $groupSlugs = $this->groupRepository->fetchGroupSlugs();

        $meetups = [];

        $meetupsData = $this->meetupComApi->getMeetupsByGroupSlugs($groupSlugs);
        foreach ($meetupsData as $meetupData) {
            $meetup = $this->meetupComMeetupFactory->createFromData($meetupData);
            if ($meetup === null) {
                continue;
            }

            $meetups[] = $meetup;
        }

        return $meetups;
    }

    public function getKey(): string
    {
        return 'meetup-com';
    }
}
