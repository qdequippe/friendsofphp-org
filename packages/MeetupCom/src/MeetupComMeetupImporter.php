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
        $groupIds = $this->groupRepository->fetchGroupIds();

        $meetups = [];

        $groupIdsChunks = array_chunk($groupIds, 200);
        foreach ($groupIdsChunks as $groupIdsChunk) {
            foreach ($this->meetupComApi->getMeetupsByGroupsIds($groupIdsChunk) as $data) {
                $meetup = $this->meetupComMeetupFactory->createFromData($data);
                if ($meetup === null) {
                    continue;
                }

                $meetups[] = $meetup;
            }
        }

        return $meetups;
    }

    public function getKey(): string
    {
        return 'meetup-com';
    }
}
