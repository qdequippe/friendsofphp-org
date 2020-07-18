<?php declare(strict_types=1);

use PhpCsFixer\Fixer\Operator\UnaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\CodingStandard\Fixer\ArrayNotation\StandaloneLineInMultilineArrayFixer;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\EasyCodingStandard\Configuration\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(LineLengthFixer::class);

    $services->set(StandaloneLineInMultilineArrayFixer::class);

    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PATHS, [
        __DIR__ . '/bin',
        __DIR__ . '/config',
        __DIR__ . '/src',
        __DIR__ . '/tests',
        __DIR__ . '/packages',
        __DIR__ . '/ecs.php',
    ]);

    $parameters->set(Option::SETS, ['php70', 'php71', 'clean-code', 'dead-code', 'symplify', 'common', 'psr12']);

    $parameters->set(
        Option::SKIP,
        [
            UnaryOperatorSpacesFixer::class => null,
            BlankLineAfterOpeningTagFixer::class => null,
        ]
    );
};
