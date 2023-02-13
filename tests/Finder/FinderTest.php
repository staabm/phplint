<?php

declare(strict_types=1);

namespace Overtrue\PHPLint\Tests\Finder;

use Iterator;
use Overtrue\PHPLint\Command\LintCommand;
use Overtrue\PHPLint\Configuration\ConsoleOptionsResolver;
use Overtrue\PHPLint\Configuration\OptionDefinition;
use Overtrue\PHPLint\Event\EventDispatcher;
use Overtrue\PHPLint\Finder;
use Overtrue\PHPLint\Tests\TestCase;
use Symfony\Component\Console\Input\ArrayInput;

use function array_keys;
use function array_map;
use function dirname;
use function iterator_to_array;
use function str_replace;

/**
 * @author Laurent Laville
 * @since Release 9.0.0
 */
final class FinderTest extends TestCase
{
    /**
     * @covers \Overtrue\PHPLint\Finder::getFiles
     */
    public function testAllPhpFilesFoundShouldExists(): void
    {
        $dispatcher = new EventDispatcher([]);

        $basePath = dirname(__DIR__);

        $arguments = [
            OptionDefinition::PATH => [$basePath],
            '--no-configuration' => true,
            '--' . OptionDefinition::EXCLUDE => [],
            '--' . OptionDefinition::EXTENSIONS => ['php'],
        ];
        $definition = (new LintCommand($dispatcher))->getDefinition();
        $input = new ArrayInput($arguments, $definition);

        $configResolver = new ConsoleOptionsResolver($input);

        $finder = new Finder($configResolver);

        foreach ($finder->getFiles() as $file) {
            $this->assertFileExists($file->getRealPath());
        }
    }

    /**
     * @covers \Overtrue\PHPLint\Finder::getFiles
     */
    public function testSearchPhpFilesWithCondition(): void
    {
        $dispatcher = new EventDispatcher([]);

        $basePath = dirname(__DIR__);

        $arguments = [
            OptionDefinition::PATH => [$basePath],
            '--no-configuration' => true,
            '--' . OptionDefinition::EXCLUDE => ['fixtures'],
            '--' . OptionDefinition::EXTENSIONS => ['php']
        ];
        $definition = (new LintCommand($dispatcher))->getDefinition();
        $input = new ArrayInput($arguments, $definition);

        $configResolver = new ConsoleOptionsResolver($input);

        $finder = new Finder($configResolver);

        $this->assertEqualsCanonicalizing(
            [
                'Cache/CacheTest.php',
                'Configuration/ConsoleConfigTest.php',
                'Configuration/YamlConfigTest.php',
                'EndToEnd/LintCommandTest.php',
                'Finder/FinderTest.php',
                'TestCase.php',
            ],
            $this->getRelativePathFiles($finder->getFiles()->getIterator(), $basePath)
        );
    }

    private function getRelativePathFiles(Iterator $iterator, string $basePath): array
    {
        return array_map(
            function (string $filename) use ($basePath) {
                return str_replace($basePath . '/', '', $filename);
            },
            array_keys(iterator_to_array($iterator))
        );
    }
}
