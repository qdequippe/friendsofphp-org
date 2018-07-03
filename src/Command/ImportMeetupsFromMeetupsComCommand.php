<?php declare(strict_types=1);

namespace Fop\Command;

use Fop\Location;
use Fop\Meetup;
use Fop\Repository\MeetupRepository;
use Fop\Repository\UserGroupRepository;
use GuzzleHttp\Client;
use Nette\Utils\DateTime;
use Nette\Utils\Json;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class ImportMeetupsFromMeetupsComCommand extends Command
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var MeetupRepository
     */
    private $meetupRepository;
    /**
     * @var UserGroupRepository
     */
    private $userGroupRepository;

    public function __construct(Client $client, MeetupRepository $meetupRepository, UserGroupRepository $userGroupRepository)
    {
        parent::__construct();
        $this->client = $client;
        $this->meetupRepository = $meetupRepository;
        $this->userGroupRepository = $userGroupRepository;
    }

    protected function configure()
    {
        $this->setName(CommandNaming::classToName(self::class));
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $userGroups = $this->userGroupRepository->fetchAll();
        foreach ($userGroups as $userGroup) {
            dump($userGroup);
            die;
        }

        // loda from data/meetup-com-groups.yml
        $groupName = '010PHP';

        $nowDateTime = DateTime::from('now');

        $url = sprintf('http://api.meetup.com/2/events?group_urlname=%s', $groupName);
        $response = $this->client->get($url);

        $result = Json::decode($response->getBody(), Json::FORCE_ARRAY);
        $events = $result['results'];

        $meetups = [];
        foreach ($events as $event) {
            $startDateTime = DateTime::from(strtotime((string) $event['time']));

            // skip past meetups
            if ($startDateTime < $nowDateTime) {
                continue;
            }

            // draft event, not ready yet
            if (! isset($event['venue'])) {
                continue;
            }

            $venue = $event['venue'];
            $location = new Location($venue['city'], $venue['localized_country_name'], $venue['lon'], $venue['lat']);

            $meetups[] = new Meetup($event['name'], $event['group']['name'], $startDateTime, $location);
        }

        $this->meetupRepository->saveToFile($meetups);
    }
}
