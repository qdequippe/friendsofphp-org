<?php declare(strict_types=1);

namespace Fop\Core\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

final class FriendsOfPhpApplication extends Application
{
    /**
     * @param Command[] $commands
     */
    public function __construct(array $commands)
    {
        $this->addCommands($commands);

        parent::__construct();
    }
}
