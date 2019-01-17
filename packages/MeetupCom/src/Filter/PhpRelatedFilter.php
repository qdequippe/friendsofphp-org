<?php declare(strict_types=1);

namespace Fop\MeetupCom\Filter;

use Fop\Entity\Group;
use Nette\Utils\Strings;

final class PhpRelatedFilter
{
    /**
     * @var string[]
     */
    private $yesKeywords = [
        'php',
        'PrestaShop',
        'Presta Shop',
        'PHPPers',
        'PHP Pers',
        'Wordpress',
        'Drupal',
        'Laravel',
        'Symfony',
        'Zend',
        'Nette',
        'CodeIgniter',
        'Code Igniter',
        'CakePHP',
        'Cake PHP',
        'Magento',
        'Mautic',
        'SilverStripe',
        'Silver Stripe',
        'craft cms',
        'craftCms',
        'eZ',
        // misc
        'StripeGirls',
    ];

    /**
     * @param mixed[] $groups
     * @return mixed[]
     */
    public function filterGroups(array $groups): array
    {
        return array_filter($groups, function ($group) {
            return $this->isNameMatch((string) $group[Group::NAME]);
        });
    }

    private function isNameMatch(string $name): bool
    {
        if (Strings::match($name, '#\b(' . implode('|', $this->yesKeywords) . ')\b#i')) {
            return true;
        }

        return Strings::startsWith($name, 'PHP') || Strings::endsWith($name, 'PHP');
    }
}
