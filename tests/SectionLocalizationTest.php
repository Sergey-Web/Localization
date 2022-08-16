<?php

declare(strict_types=1);

namespace Yarmoshuk\Localization\Tests;

use PHPUnit\Framework\TestCase;
use Yarmoshuk\Localization\Exceptions\LocalDoesNotExistException;
use Yarmoshuk\Localization\Exceptions\SectionAlreadyExistsException;
use Yarmoshuk\Localization\Exceptions\SectionDoesNotExistException;
use Yarmoshuk\Localization\HelperLocalization;
use Yarmoshuk\Localization\ManagerLocalization;
use Yarmoshuk\Localization\SectionLocalization;

/**
 * @codeCoverageIgnore
 */
class SectionLocalizationTest extends TestCase
{
    use GeneratorDataLocalization;

    protected const PATH_DIR_LOCAL = __DIR__ . DIRECTORY_SEPARATOR . 'lang';

    protected function setUp(): void
    {
        HelperLocalization::removeTestData(static::PATH_DIR_LOCAL . DIRECTORY_SEPARATOR);
    }

    protected function tearDown(): void
    {
        HelperLocalization::removeTestData(static::PATH_DIR_LOCAL . DIRECTORY_SEPARATOR);
    }

    public function testCreateSectionAlreadyExists()
    {
        $dir = static::PATH_DIR_LOCAL;
        $languages = ['en'];
        $sections = ['messages'];
        $key = 'hello';
        $lang = 'en';

        $this->generateData(
            dir: static::PATH_DIR_LOCAL,
            languages: $languages,
            sections: $sections,
            keys: [$key => '']
        );

        $pathSection = $dir . DIRECTORY_SEPARATOR
            . $lang . DIRECTORY_SEPARATOR . $sections[0] . ManagerLocalization::FILE_EXTENSION;

        $this->checkFileIsAlreadyExists($pathSection);
        $sectionLocalization = new SectionLocalization($pathSection);
        $sectionLocalization->create();
    }

    /**
     * @dataProvider getKeysProvider
     */
    public function testGetKeys(
        array $languages,
        string $dir,
        array $sections,
        string $lang,
        string $key
    ): void
    {
        $this->checkIsDir($dir);
        $this->generateData(
            dir: $dir,
            languages: $languages,
            sections: $sections,
            keys: [$key => '']
        );

        $pathSection = $dir . DIRECTORY_SEPARATOR
            . $lang . DIRECTORY_SEPARATOR . $sections[0] . ManagerLocalization::FILE_EXTENSION;

        $this->checkSectionDoesNotExist($pathSection);

        $sectionLocalization = new SectionLocalization($pathSection);

        $dataKeys = $sectionLocalization->getKeys();
        $this->assertIsArray($dataKeys);
        $this->assertArrayHasKey($key, $dataKeys);
    }

    /**
     * @dataProvider deleteProvider
     */
    public function testDelete(
        array $languages,
        string $dir,
        array $sections,
        string $lang,
        string $key
    ): void
    {
        $this->checkIsDir($dir);
        $this->generateData(
            dir: $dir,
            languages: $languages,
            sections: $sections,
            keys: [$key => '']
        );

        $pathSection = $dir . DIRECTORY_SEPARATOR
            . $lang . DIRECTORY_SEPARATOR . $sections[0] . ManagerLocalization::FILE_EXTENSION;

        $this->checkSectionDoesNotExist($pathSection);

        $sectionLocalization = new SectionLocalization($pathSection);
        $this->assertTrue($sectionLocalization->delete());
    }

    /**
     * @dataProvider renameProvider
     */
    public function testRename(
        array $languages,
        string $dir,
        array $sections,
        string $lang,
        string $key,
        string $section
    ): void
    {
        $this->checkIsDir($dir);
        $this->generateData(
            dir: $dir,
            languages: $languages,
            sections: $sections,
            keys: [$key => '']
        );

        $pathSection = HelperLocalization::generatePathSection($dir, $sections[0], $lang);
        $sectionLocalization = new SectionLocalization($pathSection);

        $pathNewSection = HelperLocalization::generatePathSection($dir, $section, $lang);
        $this->checkFileIsAlreadyExists($pathNewSection);
        $this->checkSectionDoesNotExist($pathSection);

        $this->assertTrue($sectionLocalization->rename($pathNewSection));
    }

    protected function renameProvider(): array
    {
        return [
            'dataCorrect' => [
                'languages' => ['en'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'lang' => 'en',
                'key' => 'hello',
                'section' => 'auth'
            ],
            'sectionDoesNotExist' => [
                'languages' => [],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'lang' => 'en',
                'key' => 'hello',
                'section' => 'auth'
            ],
            'sectionAlreadyExists' => [
                'languages' => ['en'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'lang' => 'en',
                'key' => 'hello',
                'section' => 'messages'
            ],
        ];
    }

    protected function deleteProvider(): array
    {
        return [
            'dataCorrect' => [
                'languages' => ['en'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'lang' => 'en',
                'key' => 'hello',
            ],
            'isNotLang' => [
                'languages' => [],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'lang' => 'en',
                'key' => 'hello',
            ],
            'isNotDir' => [
                'languages' => ['en'],
                'dir' => 'dir',
                'sections' => ['messages'],
                'lang' => 'en',
                'key' => 'hello',
            ],
        ];
    }

    protected function getKeysProvider(): array
    {
        return [
            'dataCorrect' => [
                'languages' => ['en'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'lang' => 'en',
                'key' => 'hello',
            ],
            'isNotLang' => [
                'languages' => [],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'lang' => 'en',
                'key' => 'hello',
            ],
            'isNotDir' => [
                'languages' => ['en'],
                'dir' => 'dir',
                'sections' => ['messages'],
                'lang' => 'en',
                'key' => 'hello',
            ],
        ];
    }

    private function checkIsDir(string $dir): void
    {
        if (is_dir($dir) === false) {
            $this->expectException(LocalDoesNotExistException::class);
            $this->expectExceptionMessage('The localization directory does not exist');
        }
    }

    private function checkSectionDoesNotExist(string $pathSection): void
    {
        if (!file_exists($pathSection)) {
            $this->expectException(SectionDoesNotExistException::class);
            $this->expectExceptionMessage('Section does not exist');
        }
    }

    private function checkFileIsAlreadyExists(string $pathSection)
    {
        if (file_exists($pathSection)) {
            $this->expectException(SectionAlreadyExistsException::class);
            $this->expectExceptionMessage('Section already exists');
        }
    }
}
