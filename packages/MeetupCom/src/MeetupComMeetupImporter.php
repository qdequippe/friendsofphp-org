<?php declare(strict_types=1);

namespace Fop\MeetupCom;

use Fop\Contract\MeetupImporterInterface;
use Fop\Entity\Meetup;
use Fop\MeetupCom\Api\MeetupComApi;
use Fop\MeetupCom\Meetup\MeetupComMeetupFactory;
use Fop\Repository\GroupRepository;
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

    public function __construct(
        GroupRepository $userGroupRepository,
        MeetupComMeetupFactory $meetupComMeetupFactory,
        MeetupComApi $meetupComApi,
        SymfonyStyle $symfonyStyle
    ) {
        $this->groupRepository = $userGroupRepository;
        $this->meetupComMeetupFactory = $meetupComMeetupFactory;
        $this->meetupComApi = $meetupComApi;
        $this->symfonyStyle = $symfonyStyle;
    }

    /**
     * @return Meetup[]
     */
    public function getMeetups(): array
    {
        $groupSlugs = $this->groupRepository->fetchGroupSlugs();

        $meetups = [];
        $errors = [];

        $progressBar = $this->symfonyStyle->createProgressBar(count($groupSlugs));

        foreach ($groupSlugs as $groupSlug) {
            try {
                $meetupsData = $this->meetupComApi->getMeetupsByGroupSlug($groupSlug);
                $progressBar->advance();

                if ($meetupsData === []) {
                    continue;
                }

                $groupMeetups = $this->createMeetupsFromMeetupsData($meetupsData);
                $meetups = array_merge($meetups, $groupMeetups);
            } catch (GuzzleException $guzzleException) {
                // the group might not exists anymore, but it should not be a blocker for existing groups
                $errors[] = $guzzleException->getMessage();
                continue;
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
