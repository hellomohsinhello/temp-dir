<?php

namespace Hellomohsinhello\TempDir\Test;

use FilesystemIterator;
use Hellomohsinhello\TempDir\Exceptions\InvalidDirectoryName;
use Hellomohsinhello\TempDir\Exceptions\PathAlreadyExists;
use Hellomohsinhello\TempDir\tempDir;
use PHPUnit\Framework\TestCase;

class TempDirTest extends TestCase
{
    protected string $tempDir = 'temp_dir';

    protected string $testingDirectory = __DIR__.DIRECTORY_SEPARATOR.'temp';

    protected string $tempDirFullPath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDirFullPath = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$this->tempDir;

        $this->deleteDir($this->testingDirectory);
        $this->deleteDir($this->tempDirFullPath);
    }

    protected function deleteDir(string $path): bool
    {
        if (is_link($path)) {
            return unlink($path);
        }

        if (! file_exists($path)) {
            return true;
        }

        if (! is_dir($path)) {
            return unlink($path);
        }

        foreach (new FilesystemIterator($path) as $item) {
            if (! $this->deleteDir($item)) {
                return false;
            }
        }

        return rmdir($path);
    }

    public function testTempDir()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_create_a_temporary_directory()
    {
        $tempDir = (new TempDir())->create();

        $this->assertDirectoryExists($tempDir->path());
    }

    /** @test */
    public function it_can_create_a_temporary_directory_with_shorthand_make()
    {
        $tempDir = tempDir::make();

        $this->assertDirectoryExists($tempDir->path());
    }

    /** @test */
    public function it_can_create_a_temporary_directory_with_a_name()
    {

        $tempDir = (new TempDir())
            ->name($this->tempDir)
            ->create();

        $this->assertDirectoryExists($tempDir->path());
        $this->assertDirectoryExists($this->tempDirFullPath);
    }

    /** @test */
    public function it_does_not_generate_spaces_in_directory_path()
    {
        $tempDir = (new TempDir())->create();

        $this->assertEquals(0, substr_count($tempDir->path(), ' '));
    }

    /** @test */
    public function it_can_create_a_temporary_directory_in_a_custom_location()
    {
        $tempDir = (new TempDir())
            ->location($this->testingDirectory)
            ->name($this->tempDir)
            ->create();

        $this->assertDirectoryExists($tempDir->path());
        $this->assertDirectoryExists($this->testingDirectory.DIRECTORY_SEPARATOR.$this->tempDir);
    }

    /** @test */
    public function it_can_create_a_temporary_directory_in_a_custom_location_through_the_constructor()
    {
        $tempDir = (new tempDir($this->testingDirectory))
            ->name($this->tempDir)
            ->create();

        $this->assertDirectoryExists($tempDir->path());
        $this->assertDirectoryExists($this->testingDirectory.DIRECTORY_SEPARATOR.$this->tempDir);
    }

    /** @test */
    public function it_strips_trailing_slashes_from_a_path()
    {
        $tempDir = (new TempDir())
            ->name($this->tempDir)
            ->create();

        $testingPath = $tempDir->path('testing'.DIRECTORY_SEPARATOR);
        $this->assertStringEndsNotWith(DIRECTORY_SEPARATOR, $testingPath);
    }

    /** @test */
    public function it_strips_trailing_slashes_from_a_location()
    {
        $tempDir = (new tempDir($this->testingDirectory.DIRECTORY_SEPARATOR))
            ->create();

        $this->assertStringEndsNotWith(DIRECTORY_SEPARATOR, $tempDir->path());

        $tempDir = (new TempDir())
            ->location($this->testingDirectory.DIRECTORY_SEPARATOR)
            ->create();

        $this->assertStringEndsNotWith(DIRECTORY_SEPARATOR, $tempDir->path());
    }

    /** @test */
    public function by_default_it_will_not_overwrite_an_existing_directory()
    {
        mkdir($this->tempDirFullPath);

        $this->expectException(PathAlreadyExists::class);

        (new TempDir())
            ->name($this->tempDir)
            ->create();
    }

    /** @test */
    public function it_will_overwrite_an_existing_directory_when_using_force_create()
    {
        mkdir($this->tempDirFullPath);

        $testFile = $this->tempDirFullPath.DIRECTORY_SEPARATOR.'test.txt';

        touch($testFile);

        $this->assertFileExists($testFile);

        (new TempDir())
            ->force()
            ->name($this->tempDir)
            ->create();

        $this->assertDirectoryExists($this->tempDirFullPath);
        $this->assertFileDoesNotExist($testFile);
    }

    /** @test */
    public function it_provides_chainable_create_methods()
    {
        $tempDir = (new TempDir())
            ->name($this->tempDir)
            ->create();

        $this->assertInstanceOf(TempDir::class, $tempDir);

        $tempDir = (new TempDir())
            ->name($this->tempDir)
            ->force()
            ->create();

        $this->assertInstanceOf(TempDir::class, $tempDir);
    }

    /** @test */
    public function it_can_create_a_subdirectory_in_the_temporary_directory()
    {
        $tempDir = (new TempDir())
            ->name($this->tempDir)
            ->create();

        $subdirectory = 'abc';
        $subdirectoryPath = $tempDir->path($subdirectory);

        $this->assertDirectoryExists($subdirectoryPath);
        $this->assertDirectoryExists("{$this->tempDirFullPath}/{$subdirectory}");
    }

    /** @test */
    public function it_can_create_a_multiple_subdirectories_in_the_temporary_directory()
    {
        $tempDir = (new TempDir())
            ->name($this->tempDir)
            ->create();

        $subdirectories = 'abc/123/xyz';
        $subdirectoryPath = $tempDir->path($subdirectories);

        $this->assertDirectoryExists($subdirectoryPath);
        $this->assertDirectoryExists("{$this->tempDirFullPath}/{$subdirectories}");
    }

    /** @test */
    public function it_can_create_a_path_to_a_file_in_the_temporary_directory()
    {
        $tempDir = (new TempDir())
            ->name($this->tempDir)
            ->create();

        $subdirectoriesWithFile = 'abc/123/xyz/test.txt';
        $subdirectoryFilePath = $tempDir->path($subdirectoriesWithFile);
        touch($subdirectoryFilePath);

        $this->assertFileExists($subdirectoryFilePath);
        $this->assertFileExists("{$this->tempDirFullPath}/{$subdirectoriesWithFile}");
    }

    /** @test */
    public function it_can_delete_a_temporary_directory_containing_files()
    {
        $tempDir = (new TempDir())
            ->name($this->tempDir)
            ->create();

        $subdirectoriesWithFile = 'abc/123/xyz/test.txt';
        $subdirectoryPath = $tempDir->path($subdirectoriesWithFile);
        touch($subdirectoryPath);
        $tempDir->delete();

        $this->assertDirectoryDoesNotExist($this->tempDirFullPath);
    }

    /** @test */
    public function it_can_delete_a_temporary_directory_containing_no_content()
    {
        $tempDir = (new TempDir())
            ->name($this->tempDir)
            ->create();

        $tempDir->delete();

        $this->assertDirectoryDoesNotExist($this->tempDirFullPath);
    }

    /** @test */
    public function it_can_delete_a_temporary_directory_containing_broken_symlink()
    {
        $tempDir = (new TempDir())
            ->name($this->tempDir)
            ->create();

        symlink(
            $tempDir->path().DIRECTORY_SEPARATOR.'target',
            $tempDir->path().DIRECTORY_SEPARATOR.'link'
        );

        $tempDir->delete();

        $this->assertDirectoryDoesNotExist($this->tempDirFullPath);
    }

    /** @test */
    public function it_can_empty_a_temporary_directory()
    {
        $tempDir = (new TempDir())
            ->name($this->tempDir)
            ->create();

        $subdirectoriesWithFile = 'abc/123/xyz/test.txt';
        $subdirectoryPath = $tempDir->path($subdirectoriesWithFile);
        touch($subdirectoryPath);
        $tempDir->empty();

        $this->assertFileDoesNotExist($this->tempDirFullPath.DIRECTORY_SEPARATOR.$subdirectoriesWithFile);
        $this->assertDirectoryExists($this->tempDirFullPath);
    }

    /** @test */
    public function it_throws_exception_on_invalid_name()
    {
        $this->expectException(InvalidDirectoryName::class);
        $this->expectExceptionMessage('The directory name `/` contains invalid characters.');
        $tempDir = (new TempDir())
            ->name('/');
    }

    /** @test */
    public function it_should_return_true_on_deleted_file_is_not_existed()
    {
        $tempDir = (new TempDir())
            ->delete();

        $this->assertTrue($tempDir);
    }

    /** @test */
    public function it_exists_function_should_tell_if_directory_exists()
    {
        $tempDir = (new TempDir())
            ->name($this->tempDir);

        $this->assertFalse($tempDir->exists());

        $tempDir->create();

        $this->assertTrue($tempDir->exists());
    }

    /** @test */
    public function it_can_delete_when_object_is_destroyed()
    {
        $tempDir = (new TempDir())
            ->name($this->tempDir)
            ->deleteWhenDestroyed()
            ->create();

        $fullPath = $tempDir->path();

        $this->assertDirectoryExists($fullPath);

        unset($tempDir);
        $this->assertDirectoryDoesNotExist($fullPath);
    }
}