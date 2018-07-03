<?php declare(strict_types=1);

namespace Fop\Command;

use Fop\Country\CountryResolver;
use Fop\Repository\UserGroupRepository;
use GuzzleHttp\Client;
use Nette\Utils\Json;
use Nette\Utils\Strings;
use Rinvex\Country\Country;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class ImportGroupsFromPhpUgCommand extends Command
{
    /**
     * @var CountryResolver
     */
    private $countryResolver;

    /**
     * @var UserGroupRepository
     */
    private $userGroupRepository;

    /**
     * @var Client
     */
    private $client;

    public function __construct(
        CountryResolver $countryResolver,
        UserGroupRepository $userGroupRepository,
        Client $client
    ) {
        parent::__construct();
        $this->countryResolver = $countryResolver;
        $this->userGroupRepository = $userGroupRepository;
        $this->client = $client;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $response = $this->client->get('https://php.ug/api/rest/listtype/1');

        $result = Json::decode($response->getBody(), Json::FORCE_ARRAY);

        $groups = $result['groups'];

        $meetupGroups = [];
        foreach ($groups as $group) {
            // resolve meetups.com groups only
            if (! Strings::contains($group['url'], 'meetup.com')) {
                continue;
            }

            $meetupGroups[] = [
                'meetup_com_url' => $group['url'],
                'country' => $this->countryResolver->resolveFromGroup($group),
            ];
        }

        $meetupGroups = $this->sortByCountry($meetupGroups);
        $meetupGroupsByContinent = $this->groupMeetupsByContinent($meetupGroups);

        $this->userGroupRepository->saveToFile($meetupGroupsByContinent);
    }

    /**
     * @param mixed[] $meetupGroups
     * @return mixed[]
     */
    private function sortByCountry(array $meetupGroups): array
    {
        uasort($meetupGroups, function (array $firstMeetupGroup, array $secondMeetupGroup) {
            return $firstMeetupGroup['country'] > $secondMeetupGroup['country'];
        });

        return $meetupGroups;
    }

    /**
     * @param mixed[] $meetupGroups
     * @return mixed[]
     */
    private function groupMeetupsByContinent(array $meetupGroups): array
    {
        $meetupGroupsByContinent = [];

        foreach ($meetupGroups as $meetupGroup) {
            $regionKey = $this->resolveRegionKey($meetupGroup);
            $meetupGroup['country'] = $meetupGroup['country'] ? $meetupGroup['country']->getName() : 'unknown';

            $meetupGroupsByContinent[$regionKey][] = $meetupGroup;
        }

        return $meetupGroupsByContinent;
    }

    /**
     * @param mixed[] $meetupGroup
     */
    private function resolveRegionKey(array $meetupGroup): string
    {
        if ($meetupGroup['country']) {
            /** @var Country $country */
            $country = $meetupGroup['country'];

            if ($country->getRegion() === null) {
                return 'unknown';
            }

            return strtolower($country->getRegion());
        }

        return 'unknown';
    }
}
