<?php declare(strict_types=1);

namespace Statie\StatieTwig\Twig\Loader;

use Twig\Loader\FilesystemLoader;

final class FilesystemLoaderFactory
{
    /**
     * @var string
     */
    private $kernelCacheDirectory;

    public function __construct(string $kernelCacheDirectory)
    {
        $this->kernelCacheDirectory = $kernelCacheDirectory;
    }

    public function create(): FilesystemLoader
    {
        // we don't really need this here, but it's required by Twig to work
        $templatesDirectory = $this->kernelCacheDirectory . '/twig_templates';
        if (! file_exists($templatesDirectory)) {
            mkdir($templatesDirectory);
        }

        $filesystemLoader = new FilesystemLoader();
        $filesystemLoader->addPath($templatesDirectory);

        return $filesystemLoader;
    }
}
