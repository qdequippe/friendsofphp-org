<?php declare(strict_types=1);

namespace Fop\MeetupCom;

use Fop\Contract\MeetupImporterInterface;
use Fop\Entity\Meetup;
use Fop\MeetupCom\Api\MeetupComApi;
use Fop\MeetupCom\Meetup\MeetupComMeetupFactory;
use Fop\Repository\GroupRepository;
use Symfony\Component\Console\Style\SymfonyStyle;

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
    /**
     * @var string
     */
    private $meetupComApiKey;
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(
        GroupRepository $userGroupRepository,
        MeetupComMeetupFactory $meetupComMeetupFactory,
        MeetupComApi $meetupComApi,
        string $meetupComApiKey,
    SymfonyStyle $symfonyStyle
    ) {
        $this->groupRepository = $userGroupRepository;
        $this->meetupComMeetupFactory = $meetupComMeetupFactory;
        $this->meetupComApi = $meetupComApi;
        $this->meetupComApiKey = $meetupComApiKey;
        $this->symfonyStyle = $symfonyStyle;
    }

    /**
     * @return Meetup[]
     */
    public function getMeetups(): array
    {
        if ($this->meetupComApiKey === '') {
            $this->symfonyStyle->warning('Meetup.com data requires MEETUP_TOKEN=value for the access. Skipping.');

            return [];
        }

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
