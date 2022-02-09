<?php

declare(strict_types=1);

use Fop\Core\ValueObject\Option;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\PackageBuilder\Strings\StringFormatConverter;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $services->load('Fop\MeetupCom\\', __DIR__ . '/../src')
        ->exclude([__DIR__ . '/../src/ValueObject']);

    $services->set(Client::class);
    $services->set(StringFormatConverter::class);

    $parameters = $containerConfigurator->parameters();

    # https://secure.meetup.com/meetup_api/oauth_consumers/
    $parameters->set(Option::MEETUP_COM_OAUTH_KEY, '%env(MEETUP_COM_OAUTH_KEY)%');
    $parameters->set(Option::MEETUP_COM_OAUTH_SECRET, '%env(MEETUP_COM_OAUTH_SECRET)%');

    $parameters->set(
        Option::USA_STATES,
        [
            'al' => 'Alabama',
            'ak' => 'Alaska',
            'as' => 'American Samoa',
            'az' => 'Arizona',
            'ar' => 'Arkansas',
            'ca' => 'California',
            'co' => 'Colorado',
            'ct' => 'Connecticut',
            'de' => 'Delaware',
            'dc' => 'District Of Columbia',
            'fm' => 'Federated States Of Micronesia',
            'fl' => 'Florida',
            'ga' => 'Georgia',
            'gu' => 'Guam',
            'hi' => 'Hawaii',
            'id' => 'Idaho',
            'il' => 'Illinois',
            'in' => 'Indiana',
            'ia' => 'Iowa',
            'ks' => 'Kansas',
            'ky' => 'Kentucky',
            'la' => 'Louisiana',
            'me' => 'Maine',
            'mh' => 'Marshall Islands',
            'md' => 'Maryland',
            'ma' => 'Massachusetts',
            'mi' => 'Michigan',
            'mn' => 'Minnesota',
            'ms' => 'Mississippi',
            'mo' => 'Missouri',
            'mt' => 'Montana',
            'ne' => 'Nebraska',
            'nv' => 'Nevada',
            'nh' => 'New Hampshire',
            'nj' => 'New Jersey',
            'nm' => 'New Mexico',
            'ny' => 'New York',
            'nc' => 'North Carolina',
            'nd' => 'North Dakota',
            'mp' => 'Northern Mariana Islands',
            'oh' => 'Ohio',
            'ok' => 'Oklahoma',
            'or' => 'Oregon',
            'pw' => 'Palau',
            'pa' => 'Pennsylvania',
            'pr' => 'Puerto Rico',
            'ri' => 'Rhode Island',
            'sc' => 'South Carolina',
            'sd' => 'South Dakota',
            'tn' => 'Tennessee',
            'tx' => 'Texas',
            'ut' => 'Utah',
            'vt' => 'Vermont',
            'vi' => 'Virgin Islands',
            'va' => 'Virginia',
            'wa' => 'Washington',
            'wv' => 'West Virginia',
            'wi' => 'Wisconsin',
            'wy' => 'Wyoming',
        ]
    );
};
