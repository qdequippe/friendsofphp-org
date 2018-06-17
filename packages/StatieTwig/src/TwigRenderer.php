<?php declare(strict_types=1);

namespace Statie\StatieTwig;

use Symplify\Statie\Renderable\File\AbstractFile;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\LoaderInterface;

final class TwigRenderer
{
    /**
     * @var Environment
     */
    private $twigEnvironment;

    /**
     * @var ArrayLoader
     */
    private $twigArrayLoader;

    public function __construct(Environment $twigEnvironment, ArrayLoader $twigArrayLoader)
    {
        $this->twigEnvironment = $twigEnvironment;
        $this->twigArrayLoader = $twigArrayLoader;
    }

    /**
     * @param string[] $parameters
     */
    public function render(AbstractFile $file, array $parameters = []): string
    {
        $this->twigArrayLoader->setTemplate($file->getFilePath(), $file->getContent());

        // layout?

        return $this->twigEnvironment->render($file->getFilePath(), $parameters);
    }
}
