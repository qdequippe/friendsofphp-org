<?php declare(strict_types=1);

namespace Fop\Repository;

use Fop\Meetup;
use Symfony\Component\Yaml\Yaml;

final class MeetupRepository
{
    /**
     * @var string
     */
    private $meetupsStorage;

    public function __construct(string $meetupsStorage)
    {
        $this->meetupsStorage = $meetupsStorage;
    }

    /**
     * @param Meetup[] $meetups
     */
    public function saveToFile(array $meetups): void
    {
        $meetupsAsArray = [];
        foreach ($meetups as $meetup) {
            // hydratation?
            $meetupsAsArray = [
                'name' => $meetup->getName(),
                'userGroup' => $meetup->getUserGroup(),
                'start' => $meetup->getStartDateTime()->format('Y-m-d H:i'),
                'city' => $meetup->getLocatoin()->getCity(),
                'country' => $meetup->getLocatoin()->getCountry(),
                'longitude' => $meetup->getLocatoin()->getLongitude(),
                'latitude' => $meetup->getLocatoin()->getLatitude(),
            ];
        }

        $meetupsYamlStructure = [
            'parameters' => [
                'meetups' => $meetupsAsArray,
            ],
        ];

        // @todo service
        $yamlDump = Yaml::dump($meetupsYamlStructure, 10, 4);
        file_put_contents($this->meetupsStorage, $yamlDump);
    }
}
