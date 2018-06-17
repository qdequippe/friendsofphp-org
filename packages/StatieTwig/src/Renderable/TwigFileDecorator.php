<?php declare(strict_types=1);

namespace Statie\StatieTwig\Renderable;

use Nette\Utils\Strings;
use Statie\StatieTwig\TwigRenderer;
use Symplify\Statie\Configuration\Configuration;
use Symplify\Statie\Contract\Renderable\FileDecoratorInterface;
use Symplify\Statie\Generator\Configuration\GeneratorElement;
use Symplify\Statie\Generator\Renderable\File\AbstractGeneratorFile;
use Symplify\Statie\Renderable\File\AbstractFile;

final class TwigFileDecorator implements FileDecoratorInterface
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var TwigRenderer
     */
    private $twigRenderer;

    public function __construct(
        Configuration $configuration,
        TwigRenderer $twigRenderer
    ) {
        $this->configuration = $configuration;
        $this->twigRenderer = $twigRenderer;
    }

    /**
     * @param AbstractFile[] $files
     * @return AbstractFile[]
     */
    public function decorateFiles(array $files): array
    {
        foreach ($files as $file) {
            if (! Strings::endsWith($file->getFilePath(), '.twig')) {
                continue;
            }

            $this->decorateFile($file);
        }

        return $files;
    }

//    /**
//     * @param AbstractFile[] $files
//     * @return AbstractFile[]
//     */
//    public function decorateFilesWithGeneratorElement(array $files, GeneratorElement $generatorElement): array
//    {
//        foreach ($files as $file) {
//            $this->decorateFileWithGeneratorElements($file, $generatorElement);
//        }
//
//        return $files;
//    }

    private function decorateFile(AbstractFile $file): void
    {
        $parameters = $file->getConfiguration() + $this->configuration->getOptions() + [
            'file' => $file,
        ];

        $htmlContent = $this->twigRenderer->render($file, $parameters);
        $file->changeContent($htmlContent);
    }

//    private function decorateFileWithGeneratorElements(AbstractFile $file, GeneratorElement $generatorElement): void
//    {
//        // prepare parameters
//        $parameters = $file->getConfiguration() + $this->configuration->getOptions() + [
//                $generatorElement->getVariable() => $file,
//                'layout' => $generatorElement->getLayout(),
//            ];
//
//        // add layout
//        $this->prependLayoutToFileContent($file, $generatorElement->getLayout());
//
//        $this->addTemplateToDynamicLatteStringLoader($file);
//
//        $htmlContent = $this->renderToString($file, $parameters);
//
//        // trim {layout %s} left over
//        $htmlContent = preg_replace('/{layout "[a-z]+"}/', '', $htmlContent);
//        $file->changeContent($htmlContent);
//    }

//    private function addTemplateToDynamicLatteStringLoader(AbstractFile $file): void
//    {
//        $this->dynamicStringLoader->changeContent($file->getBaseName(), $file->getContent());
//    }

//    private function prependLayoutToFileContent(AbstractFile $file, string $layout): void
//    {
//        $file->changeContent(sprintf('{layout "%s"}', $layout) . PHP_EOL . $file->getContent());
//    }

//    /**
//     * @param mixed[] $parameters
//     */
//    private function renderOuterWithLayout(AbstractFile $file, array $parameters): string
//    {
//        if ($file->getLayout()) {
//            $this->prependLayoutToFileContent($file, $file->getLayout());
//        }
//
//        $this->addTemplateToDynamicLatteStringLoader($file);
//
//        return $this->renderToString($file, $parameters);
//    }

//    /**
//     * @param mixed[] $parameters
//     */
//    private function renderToString(AbstractFile $file, array $parameters): string
//    {
//        try {
//            return $this->latteRenderer->renderExcludingHighlightBlocks($file->getBaseName(), $parameters);
//        } catch (\Throwable $exception) {
//            throw new InvalidLatteSyntaxException(sprintf(
//                'Invalid Latte syntax found or missing value in "%s" file: %s',
//                $file->getFilePath(),
//                $exception->getMessage()
//            ));
//        }
//    }

    /**
     * @param AbstractFile[]|AbstractGeneratorFile[] $files
     * @return AbstractFile[]|AbstractGeneratorFile[]
     */
    public function decorateFilesWithGeneratorElement(array $files, GeneratorElement $generatorElement): array
    {
        // TODO: Implement decorateFilesWithGeneratorElement() method.
    }
}
