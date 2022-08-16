<?php

declare(strict_types=1);

namespace Yarmoshuk\Localization\Tests;

use PHPUnit\Framework\TestCase;
use Yarmoshuk\Localization\Exceptions\KeyDoesAlreadyExistsException;
use Yarmoshuk\Localization\Exceptions\KeyDoesNotExistException;
use Yarmoshuk\Localization\Exceptions\LangDoesNotExistException;
use Yarmoshuk\Localization\Exceptions\SectionAlreadyExistsException;
use Yarmoshuk\Localization\Exceptions\SectionDoesNotExistException;
use Yarmoshuk\Localization\HelperLocalization;
use Yarmoshuk\Localization\KeyLocalization;
use Yarmoshuk\Localization\ManagerLocalization;

class KeyLocalizationTest extends TestCase
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

    /**
     * @dataProvider setValueProvider
     */
    public function testSetValue(
        array $languages,
        string $dir,
        array $sections,
        string $lang,
        string $keyDef,
        string $key,
        string $value,
    ): void
    {
        $this->checkIsDir($dir);
        $this->generateData(
            dir: $dir,
            languages: $languages,
            sections: $sections,
            keys: [$keyDef => '']
        );

        $pathSection = $dir . DIRECTORY_SEPARATOR
            . $lang . DIRECTORY_SEPARATOR . $sections[0] . ManagerLocalization::FILE_EXTENSION;

        $this->checkSectionIsExists($pathSection);
        $keyLocalization = new KeyLocalization($pathSection);
        $this->checkKeyExists($keyLocalization, $key);
        $this->assertIsInt($keyLocalization->setValue($key, $value));
    }

    public function testCreateDuplicateKey(): void
    {
        $languages = ['en', 'uk'];
        $dir = static::PATH_DIR_LOCAL;
        $sections = ['messages'];
        $lang = 'en';
        $key = 'hello';

        $this->checkIsDir($dir);
        $this->generateData(
            dir: $dir,
            languages: $languages,
            sections: $sections,
            keys: [$key => '']
        );

        $pathSection = $dir . DIRECTORY_SEPARATOR
            . $lang . DIRECTORY_SEPARATOR . $sections[0] . ManagerLocalization::FILE_EXTENSION;

        $keyLocalization = new KeyLocalization($pathSection);
        $this->checkKeyDuplicate($keyLocalization, $key);
        $keyLocalization->create($key);
    }

    /**
     * @dataProvider getValueProvider
     */
    public function testGetValue(
        array $languages,
        string $dir,
        array $sections,
        string $lang,
        string $keyDef,
        string $key,
        string $value,
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

        $keyLocalization = new KeyLocalization($pathSection);
        $keyLocalization->setValue($key, $value);
        $this->checkKeyExists($keyLocalization, $key);
        $this->assertSame($value, $keyLocalization->getValue($key));
    }

    /**
     * @dataProvider deleteProvider
     */
    public function testDelete(
        array $languages,
        string $dir,
        array $sections,
        string $lang,
        string $keyDef,
        string $key,
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

        $keyLocalization = new KeyLocalization($pathSection);
        $this->checkKeyExists($keyLocalization, $key);
        $this->assertIsInt($keyLocalization->delete($key));
    }

    /**
     * @dataProvider renameProvider
     */
    public function testRename(
        array $languages,
        string $dir,
        array $sections,
        string $lang,
        string $keyDef,
        string $keyNew,
        string $keyOld,
    ): void
    {
        $this->checkIsDir($dir);
        $this->generateData(
            dir: $dir,
            languages: $languages,
            sections: $sections,
            keys: [$keyDef => '']
        );

        $pathSection = $dir . DIRECTORY_SEPARATOR
            . $lang . DIRECTORY_SEPARATOR . $sections[0] . ManagerLocalization::FILE_EXTENSION;

        $keyLocalization = new KeyLocalization($pathSection);
        $this->checkKeyExists($keyLocalization, $keyOld);
        $this->checkKeyDuplicate($keyLocalization, $keyNew);
        $this->assertIsInt($keyLocalization->rename($keyNew, $keyOld));
    }

    protected function deleteProvider(): array
    {
        return [
            'keyDoesNotExist' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'lang' => 'en',
                'keyDef' => 'hello',
                'key' => 'test',
            ],
            'correctData' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'lang' => 'en',
                'keyDef' => 'hello',
                'key' => 'test',
            ],
        ];
    }

    protected function renameProvider(): array
    {
        return [
            'keyDoesNotExist' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'lang' => 'en',
                'keyDef' => 'hello',
                'keyNew' => 'new_key',
                'keyOld' => 'old_key',
            ],
            'keyAlreadyExists' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'lang' => 'en',
                'keyDef' => 'hello',
                'keyNew' => 'hello',
                'keyOld' => 'hello',
            ],
            'correctData' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'lang' => 'en',
                'keyDef' => 'hello',
                'keyNew' => 'new_key',
                'keyOld' => 'hello',
            ],
        ];
    }

    protected function getValueProvider(): array
    {
        return [
            'keyDoesNotExists' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'lang' => 'en',
                'keyDef' => 'hello',
                'key' => 'test',
                'value' => 'Welcome',
            ],
            'correctData' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'lang' => 'en',
                'keyDef' => 'hello',
                'key' => 'hello',
                'value' => 'Welcome',
            ],
        ];
    }

    protected function setValueProvider(): array
    {
        return [
            'sectionDoesNotExist' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'lang' => 'pl',
                'keyDef' => 'hello',
                'key' => 'hello',
                'value' => 'Welcome',
            ],
            'correctData' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'lang' => 'en',
                'keyDef' => 'hello',
                'key' => 'hello',
                'value' => 'Welcome',
                'keyNew' => 'newKey',
            ],
            'KeyExists' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'lang' => 'en',
                'keyDef' => 'hello',
                'key' => 'test',
                'value' => 'Welcome',
                'keyNew' => 'newKey',
            ],
        ];
    }

    private function checkSectionIsAlreadyExists(string $pathSection)
    {
        if (file_exists($pathSection)) {
            $this->expectException(SectionAlreadyExistsException::class);
            $this->expectExceptionMessage('Section already exists');
        }
    }

    private function checkIsDir(string $dir): void
    {
        if (is_dir($dir) === false) {
            $this->expectException(LangDoesNotExistException::class);
            $this->expectExceptionMessage('The language directory does not exist');
        }
    }

    private function checkSectionIsExists(string $pathSection): void
    {
        if (!file_exists($pathSection)) {
            $this->expectException(SectionDoesNotExistException::class);
            $this->expectExceptionMessage('Section does not exist');
        }
    }

    private function checkKeyExists(KeyLocalization $keyLocalization, string $key): void
    {
        if ($keyLocalization->existsKey($key) === false) {
            $this->expectException(KeyDoesNotExistException::class);
            $this->expectExceptionMessage('The key is not in the section');
        }
    }

    private function checkKeyDuplicate(KeyLocalization $keyLocalization, string $key): void
    {
        if ($keyLocalization->existsKey($key)) {
            $this->expectException(KeyDoesAlreadyExistsException::class);
            $this->expectExceptionMessage('Key already exists in the section');
        }
    }
}
