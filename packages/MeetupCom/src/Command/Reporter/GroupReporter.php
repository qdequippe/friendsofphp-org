<?php declare(strict_types=1);

namespace Fop\MeetupCom\Command\Reporter;

use Fop\MeetupCom\Group\GroupDetailResolver;
use Symfony\Component\Console\Style\SymfonyStyle;

final class GroupReporter
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var GroupDetailResolver
     */
    private $groupDetailResolver;

    public function __construct(SymfonyStyle $symfonyStyle, GroupDetailResolver $groupDetailResolver)
    {
        $this->symfonyStyle = $symfonyStyle;
        $this->groupDetailResolver = $groupDetailResolver;
    }

    /**
     * @param mixed[] $group
     */
    public function printGroup(array $group): void
    {
        $groupSlug = $this->groupDetailResolver->resolveSlugFromUrl($group['link']);

        $this->symfonyStyle->writeln(sprintf("        -   name: '%s'", str_replace("'", '"', $group['name'])));
        $this->symfonyStyle->writeln(sprintf("            meetup_com_slug: '%s'", $groupSlug));
        $this->symfonyStyle->writeln(sprintf("            country: '%s'", $group['country']));
        $this->symfonyStyle->newLine();
    }
}
