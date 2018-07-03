<?php declare(strict_types=1);

namespace Fop\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symplify\PackageBuilder\Console\Command\CommandNaming;

final class ImportGroupsFromPhpUgCommand extends Command
{
    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        dump('EEE');
        die;
    }
}
