<?php declare(strict_types=1);

// quick way to load huge file with links to groups, e.g.
// https://www.meetup.com/PHPSP-Santos/
// and show they details

use Nette\Utils\FileSystem;
use Symfony\Component\Process\Process;

require_once __DIR__ . '/vendor/autoload.php';

$fileContent = FileSystem::read(__DIR__ . '/input.txt');
$groupUrls = explode(PHP_EOL, $fileContent);

// remove empty
$groupUrls = array_filter($groupUrls);

$groupDetails = [];
foreach ($groupUrls as $groupUrl) {
    $process = new Process(sprintf('bin/console show-meetup-detail %s', $groupUrl));
    $process->run();

    if ($process->isSuccessful()) {
        $groupDetails[] = $process->getOutput();
    }
}



dump($groupDetails);

