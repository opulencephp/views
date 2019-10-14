<?php

/**
 * Opulence
 *
 * @link      https://www.opulencephp.com
 * @copyright Copyright (C) 2019 David Young
 * @license   https://github.com/opulencephp/Opulence/blob/master/LICENSE.md
 */

declare(strict_types=1);

namespace Opulence\Views\TestsTemp\Factories\IO;

use InvalidArgumentException;
use Opulence\Views\Factories\IO\FileViewNameResolver;

/**
 * Tests the file view name resolver
 */
class FileViewNameResolverTest extends \PHPUnit\Framework\TestCase
{
    private FileViewNameResolver $resolver;

    public static function setUpBeforeClass(): void
    {
        $tmpDir = self::getTmpFilePath();
        $tmpSubDir = self::getTmpFileSubDirPath();
        mkdir($tmpDir);
        mkdir($tmpSubDir);
        $files = [
            'a.php',
            'b.php',
            'a.fortune',
            'b.fortune',
            'a.fortune.php',
            'b.fortune.php'
        ];

        foreach ($files as $file) {
            file_put_contents($tmpDir . '/' . $file, $file);
            file_put_contents($tmpSubDir . '/' . $file, $file);
        }
    }

    public static function tearDownAfterClass(): void
    {
        $files = glob(self::getTmpFilePath() . '/*');

        foreach ($files as $file) {
            if (is_dir($file)) {
                $subDirFiles = glob($file . '/*');

                foreach ($subDirFiles as $subDirFile) {
                    unlink($subDirFile);
                }

                rmdir($file);
            } else {
                unlink($file);
            }
        }

        rmdir(self::getTmpFilePath());
    }

    /**
     * Gets the path to the files in the temporary directory
     *
     * @return string The path to the files
     */
    private static function getTmpFilePath(): string
    {
        return __DIR__ . '/tmp';
    }

    /**
     * Gets the path to the files in a subdirectory of the temporary directory
     *
     * @return string The path to the files
     */
    private static function getTmpFileSubDirPath(): string
    {
        return __DIR__ . '/tmp/sub';
    }

    protected function setUp(): void
    {
        $this->resolver = new FileViewNameResolver();
    }

    public function testAppendedSlashesAreStrippedFromPaths(): void
    {
        $this->resolver->registerExtension('php');
        $this->resolver->registerPath(self::getTmpFilePath() . '/');
        $this->assertEquals(self::getTmpFilePath() . '/a.php', $this->resolver->resolve('a'));
    }

    public function testExceptionThrownWhenNoViewFound(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->resolver->registerExtension('php');
        $this->resolver->registerPath(self::getTmpFilePath());
        $this->resolver->resolve('doesNotExist');
    }

    public function testPrependedDotsAreStrippedFromExtensions(): void
    {
        $this->resolver->registerExtension('.php');
        $this->resolver->registerPath(self::getTmpFilePath());
        $this->assertEquals(self::getTmpFilePath() . '/a.php', $this->resolver->resolve('a'));
    }

    /**
     * Tests registering a non-priority extension
     */
    public function testRegisteringNonPriorityExtension(): void
    {
        $this->resolver->registerExtension('php');
        $this->resolver->registerPath(self::getTmpFilePath());
        $this->assertEquals(self::getTmpFilePath() . '/a.php', $this->resolver->resolve('a'));
        $this->resolver->registerExtension('fortune');
        $this->assertEquals(self::getTmpFilePath() . '/a.php', $this->resolver->resolve('a'));
    }

    /**
     * Tests registering a non-priority path
     */
    public function testRegisteringNonPriorityPath(): void
    {
        $this->resolver->registerExtension('php');
        $this->resolver->registerPath(self::getTmpFileSubDirPath());
        $this->assertEquals(self::getTmpFileSubDirPath() . '/a.php', $this->resolver->resolve('a'));
        $this->resolver->registerPath(self::getTmpFilePath());
        $this->assertEquals(self::getTmpFileSubDirPath() . '/a.php', $this->resolver->resolve('a'));
    }

    public function testRegisteringPriorityExtension(): void
    {
        $this->resolver->registerExtension('php', 2);
        $this->resolver->registerPath(self::getTmpFilePath());
        $this->assertEquals(self::getTmpFilePath() . '/a.php', $this->resolver->resolve('a'));
        $this->resolver->registerExtension('fortune', 1);
        $this->assertEquals(self::getTmpFilePath() . '/a.fortune', $this->resolver->resolve('a'));
    }

    public function testRegisteringPriorityPath(): void
    {
        $this->resolver->registerExtension('php');
        $this->resolver->registerPath(self::getTmpFileSubDirPath(), 2);
        $this->assertEquals(self::getTmpFileSubDirPath() . '/a.php', $this->resolver->resolve('a'));
        $this->resolver->registerPath(self::getTmpFilePath(), 1);
        $this->assertEquals(self::getTmpFilePath() . '/a.php', $this->resolver->resolve('a'));
    }

    public function testResolvingNameWithExtension(): void
    {
        $this->resolver->registerExtension('php');
        $this->resolver->registerExtension('fortune');
        $this->resolver->registerPath(self::getTmpFilePath());
        $this->assertEquals(self::getTmpFilePath() . '/a.fortune', $this->resolver->resolve('a.fortune'));
    }

    public function testResolvingWithExtensionsThatAreSubstringsOfOthers(): void
    {
        $this->resolver->registerExtension('fortune.php');
        $this->resolver->registerExtension('php');
        $this->resolver->registerPath(self::getTmpFilePath());
        $this->assertEquals(self::getTmpFilePath() . '/a.fortune.php', $this->resolver->resolve('a'));
    }
}
