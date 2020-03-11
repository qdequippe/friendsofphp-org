<?php declare(strict_types=1);

namespace Fop\MeetupCom;

use Fop\Core\ValueObject\Meetup;
use Fop\Group\Repository\GroupRepository;
use Fop\Meetup\Contract\MeetupImporterInterface;
use Fop\MeetupCom\Api\MeetupComApi;
use Fop\MeetupCom\Api\MeetupComCooler;
use Fop\MeetupCom\Meetup\MeetupComMeetupFactory;
use GuzzleHttp\Exception\GuzzleException;
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
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var MeetupComCooler
     */
    private $meetupComCooler;

    public function __construct(
        GroupRepository $userGroupRepository,
        MeetupComMeetupFactory $meetupComMeetupFactory,
        MeetupComApi $meetupComApi,
        SymfonyStyle $symfonyStyle,
        MeetupComCooler $meetupComCooler
    ) {
        $this->groupRepository = $userGroupRepository;
        $this->meetupComMeetupFactory = $meetupComMeetupFactory;
        $this->meetupComApi = $meetupComApi;
        $this->symfonyStyle = $symfonyStyle;
        $this->meetupComCooler = $meetupComCooler;
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
                $this->symfonyStyle->note(sprintf('Loading meetups for %s group', $group->getMeetupComSlug()));

                $meetupsData = $this->meetupComApi->getMeetupsByGroupSlug($group->getMeetupComSlug());
                $this->meetupComCooler->coolDownIfNeeded();

                // should help with https://github.com/TomasVotruba/friendsofphp.org/runs/492500241#step:4:32
                // @see https://www.meetup.com/meetup_api/#limits
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
