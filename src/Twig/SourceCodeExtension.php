<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Twig;

use function Symfony\Component\String\u;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TemplateWrapper;
use Twig\TwigFunction;

/**
 * CAUTION: this is an extremely advanced Twig extension. It's used to get the
 * source code of the controller and the template used to render the current
 * page. If you are starting with Symfony, don't look at this code and consider
 * studying instead the code of the src/Twig/AppExtension.php extension.
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class SourceCodeExtension extends AbstractExtension
{
    private $controller;

    public function setController(?callable $controller): void
    {
        $this->controller = $controller;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('show_source_code', $this->showSourceCode(...), ['is_safe' => ['html'], 'needs_environment' => true]),
        ];
    }

    /**
     * @param string|TemplateWrapper|array $template
     */
    public function showSourceCode(Environment $twig, $template): string
    {
        return $twig->render('debug/source_code.html.twig', [
            'controller' => $this->getController(),
            'template' => $this->getTemplateSource($twig->resolveTemplate($template)),
        ]);
    }

    private function getController(): ?array
    {
        // this happens for example for exceptions (404 errors, etc.)
        if (null === $this->controller) {
            return null;
        }

        $method = $this->getCallableReflector($this->controller);

        if (false === $classCode = file($method->getFileName())) {
            throw new \LogicException(sprintf('There was an error while trying to read the contents of the "%s" file.', $method->getFileName()));
        }

        $startLine = $method->getStartLine() - 1;
        $endLine = $method->getEndLine();

        while ($startLine > 0) {
            $line = trim($classCode[$startLine - 1]);

            if (\in_array($line, ['{', '}', ''], true)) {
                break;
            }

            --$startLine;
        }

        $controllerCode = implode('', \array_slice($classCode, $startLine, $endLine - $startLine));

        return [
            'file_path' => $method->getFileName(),
            'starting_line' => $method->getStartLine(),
            'source_code' => $this->unindentCode($controllerCode),
        ];
    }

    /**
     * Gets a reflector for a callable.
     *
     * This logic is copied from Symfony\Component\HttpKernel\Controller\ControllerResolver::getArguments
     */
    private function getCallableReflector(callable $callable): \ReflectionFunctionAbstract
    {
        if (\is_array($callable)) {
            return new \ReflectionMethod($callable[0], $callable[1]);
        }

        if (\is_object($callable) && !$callable instanceof \Closure) {
            $r = new \ReflectionObject($callable);

            return $r->getMethod('__invoke');
        }

        return new \ReflectionFunction($callable);
    }

    private function getTemplateSource(TemplateWrapper $template): array
    {
        $templateSource = $template->getSourceContext();

        return [
            // Twig templates are not always stored in files (they can be stored
            // in a database for example). However, for the needs of the Symfony
            // Demo app, we consider that all templates are stored in files and
            // that their file paths can be obtained through the source context.
            'file_path' => $templateSource->getPath(),
            'starting_line' => 1,
            'source_code' => $templateSource->getCode(),
        ];
    }

    /**
     * Utility method that "unindents" the given $code when all its lines start
     * with a tabulation of four white spaces.
     */
    private function unindentCode(string $code): string
    {
        $codeLines = u($code)->split("\n");

        $indentedOrBlankLines = array_filter($codeLines, static fn($lineOfCode) => u($lineOfCode)->isEmpty() || u($lineOfCode)->startsWith('    '));

        $codeIsIndented = \count((array) $indentedOrBlankLines) === (is_countable($codeLines) ? \count($codeLines) : 0);
        if ($codeIsIndented) {
            $unindentedLines = array_map(static fn($lineOfCode) => u($lineOfCode)->after('    '), $codeLines);
            $code = u("\n")->join($unindentedLines)->toString();
        }

        return $code;
    }
}
