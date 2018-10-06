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
        'Wordpress',
        'Drupal',
        'Laravel',
        'Symfony',
        'Zend',
        'CakePHP',
        'Magento',
        'Mautic',
        'SilverStripe',
        'craft cms',
        'eZ',
        // misc
        'StripeGirls',
        'Elastic',
    ];

    /**
     * @param mixed[] $groups
     * @return mixed[]
     */
    public function filterGroups(array $groups): array
    {
        $matchedGroups = [];
        foreach ($groups as $group) {
            if ($this->isNameMatch((string) $group[Group::NAME])) {
                $matchedGroups[] = $group;
            }
        }

        return $matchedGroups;
    }

    private function isNameMatch(string $name): bool
    {
        if (Strings::match($name, '#\b(' . implode('|', $this->yesKeywords) . ')\b#i')) {
            return true;
        }

        return Strings::endsWith($name, 'PHP');
    }
}
