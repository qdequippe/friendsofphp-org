<?php declare(strict_types=1);

namespace Fop\MeetupCom\Command\Reporter;

use Symfony\Component\Console\Style\SymfonyStyle;

final class GroupReporter
{
    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }

    /**
     * @param mixed[] $group
     */
    public function printGroup(array $group): void
    {
        $this->symfonyStyle->writeln(sprintf("        -   name: '%s'", str_replace("'", '"', $group['name'])));
        $this->symfonyStyle->writeln(sprintf('            meetup_com_id: %s', $group['id']));
        $this->symfonyStyle->writeln(sprintf("            meetup_com_url: '%s'", $group['link']));
        $this->symfonyStyle->writeln(sprintf("            country: '%s'", $group['country']));
        $this->symfonyStyle->newLine();
    }
}
