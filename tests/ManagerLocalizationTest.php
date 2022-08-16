<?php

declare(strict_types=1);

namespace Yarmoshuk\Localization\Tests;

use PHPUnit\Framework\TestCase;
use Yarmoshuk\Localization\Exceptions\KeyDoesAlreadyExistsException;
use Yarmoshuk\Localization\Exceptions\KeyDoesNotExistException;
use Yarmoshuk\Localization\Exceptions\LangAlreadyExistsException;
use Yarmoshuk\Localization\Exceptions\LangDoesNotExistException;
use Yarmoshuk\Localization\Exceptions\LocalDoesNotExistException;
use Yarmoshuk\Localization\Exceptions\SectionAlreadyExistsException;
use Yarmoshuk\Localization\Exceptions\SectionDoesNotExistException;
use Yarmoshuk\Localization\HelperLocalization;
use Yarmoshuk\Localization\KeyLocalization;
use Yarmoshuk\Localization\ManagerLocalization;

/**
 * @codeCoverageIgnore
 */
class ManagerLocalizationTest extends TestCase
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
     * @dataProvider getLanguagesSectionsSectionsProvider
     */
    public function testGetLanguagesSections(
        array $languages,
        string $dir,
        array $sections,
        string $key,
    ): void
    {
        $this->checkLocalDoesNotExist($dir);
        $this->generateData(
            dir: $dir,
            languages: $languages,
            sections: $sections,
            keys: [$key => '']
        );

        $managerLocalization = new ManagerLocalization($dir);
        $this->assertSame($languages, $managerLocalization->getLanguages());
        $this->assertSame($sections, $managerLocalization->getSections());
    }

    /**
     * @dataProvider renameLangProvider
     */
    public function testRenameLang(
        array $languages,
        string $dir,
        array $sections,
        string $key,
        string $langNew,
        string $langOld,
    ): void
    {
        $this->generateData(
            dir: $dir,
            languages: $languages,
            sections: $sections,
            keys: [$key => '']
        );

        $managerLocalization = new ManagerLocalization($dir);
        $this->checkLangAlreadyExists($managerLocalization, $langNew);
        $this->checkLangDoesNotExist($managerLocalization, $langOld);
        $this->assertTrue($managerLocalization->renameLang($langNew, $langOld));
    }

    /**
     * @dataProvider createLangProvider
     */
    public function testCreateLang(
        array $languages,
        string $dir,
        array $sections,
        string $key,
        string $lang
    ): void
    {
        $this->generateData(
            dir: $dir,
            languages: $languages,
            sections: $sections,
            keys: [$key => '']
        );

        $managerLocalization = new ManagerLocalization($dir);
        $this->checkLangAlreadyExists($managerLocalization, $lang);
        $this->assertTrue($managerLocalization->createLang($lang));
    }

    /**
     * @dataProvider deleteLangProvider
     */
    public function testDeleteLang(
        array $languages,
        string $dir,
        array $sections,
        string $key,
        string $lang
    ): void
    {
        $this->generateData(
            dir: $dir,
            languages: $languages,
            sections: $sections,
            keys: [$key => '']
        );

        $managerLocalization = new ManagerLocalization($dir);
        $this->checkLangDoesNotExist($managerLocalization, $lang);
        $this->assertTrue($managerLocalization->deleteLang($lang));
    }

    /**
     * @dataProvider createSectionProvider
     */
    public function testCreateSection(
        array $languages,
        string $dir,
        array $sections,
        string $key,
        string $section,
    ): void
    {
        $this->generateData(
            dir: $dir,
            languages: $languages,
            sections: $sections,
            keys: [$key => '']
        );

        $managerLocalization = new ManagerLocalization($dir);
        $this->checkLangDoesNotExist($managerLocalization, $languages[0] ?? '');
        $this->checkSectionAlreadyExists(
            HelperLocalization::generatePathSection($dir, $section, $languages[0] ?? '')
        );
        $this->assertTrue($managerLocalization->createSection($section));
    }

    /**
     * @dataProvider deleteSectionProvider
     */
    public function testDeleteSection(
        array $languages,
        string $dir,
        array $sections,
        string $key,
        string $section,
    ): void
    {
        $this->generateData(
            dir: $dir,
            languages: $languages,
            sections: $sections,
            keys: [$key => '']
        );

        $managerLocalization = new ManagerLocalization($dir);
        $this->checkLangDoesNotExist($managerLocalization, $languages[0] ?? '');

        $this->checkSectionDoesNotExist(
            $managerLocalization,
            $section
        );

        $this->assertTrue($managerLocalization->deleteSection($section));
    }

    /**
     * @dataProvider renameSectionProvider
     */
    public function testRenameSection(
        array $languages,
        string $dir,
        array $sections,
        string $key,
        string $sectionOld,
        string $sectionNew,
    ): void
    {
        $this->generateData(
            dir: $dir,
            languages: $languages,
            sections: $sections,
            keys: [$key => '']
        );

        $managerLocalization = new ManagerLocalization($dir);
        $this->checkLangDoesNotExist($managerLocalization, $languages[0] ?? '');

        $this->checkSectionDoesNotExist(
            $managerLocalization,
            $sectionOld
        );

        $this->checkSectionAlreadyExists(
            HelperLocalization::generatePathSection($dir, $sectionNew, $languages[0] ?? '')
        );

        $this->assertTrue($managerLocalization->renameSection($sectionNew, $sectionOld));
    }

    /**
     * @dataProvider setValueForKeyProvider
     */
    public function testSetValueForKey(
        array $languages,
        string $dir,
        array $sections,
        string $key,
        string $value,
        string $section,
        string $lang
    ): void
    {
        $this->generateData(
            dir: $dir,
            languages: $languages,
            sections: $sections,
            keys: [$key => '']
        );

        $managerLocalization = new ManagerLocalization($dir);
        $this->checkLangDoesNotExist($managerLocalization, $lang);
        $this->checkSectionDoesNotExist($managerLocalization, $section);
        $managerLocalization->setValueForKey($key, $value, $section, $lang);
    }

    /**
     * @dataProvider getKeyProvider
     */
    public function testGetKey(
        array $languages,
        string $dir,
        array $sections,
        array $keys,
        string $key,
        string $section,
        array $result
    ): void
    {
        $this->generateData(
            dir: $dir,
            languages: $languages,
            sections: $sections,
            keys: $keys
        );

        $managerLocalization = new ManagerLocalization($dir);
        $this->checkSectionDoesNotExist($managerLocalization, $section);
        $this->checkKeyDoesNotExist($key, $section, $languages[0], $dir);
        $keyData = $managerLocalization->getKey($key, $section);
        $this->assertSame($result, $keyData);
    }

    /**
     * @dataProvider createKeyProvider
     */
    public function testCreateKey(
        array $languages,
        string $dir,
        array $sections,
        array $keys,
        string $key,
        string $section
    ): void
    {
        $this->generateData(
            dir: $dir,
            languages: $languages,
            sections: $sections,
            keys: $keys
        );

        $managerLocalization = new ManagerLocalization($dir);
        $this->checkSectionDoesNotExist($managerLocalization, $section);
        $this->checkKeyAlreadyExists($key, $section, $languages[0], $dir);
        $this->assertTrue($managerLocalization->createKey($key, $section));
    }

    /**
     * @dataProvider renameKeyProvider
     */
    public function testRenameKey(
        array $languages,
        string $dir,
        array $sections,
        string $section,
        array $keys,
        string $keyOld,
        string $keyNew,
    ): void
    {
        $this->generateData(
            dir: $dir,
            languages: $languages,
            sections: $sections,
            keys: $keys
        );

        $managerLocalization = new ManagerLocalization($dir);
        $this->checkSectionDoesNotExist($managerLocalization, $section);
        $this->checkKeyAlreadyExists($keyNew, $section, $languages[0], $dir);
        $this->checkKeyDoesNotExist($keyOld, $section, $languages[0], $dir);
        $this->assertTrue($managerLocalization->renameKey($keyNew, $keyOld, $section));
    }

    /**
     * @dataProvider deleteKeyProvider
     */
    public function testDeleteKey(
        array $languages,
        string $dir,
        array $sections,
        string $section,
        array $keys,
        string $key,
    ): void
    {
        $this->generateData(
            dir: $dir,
            languages: $languages,
            sections: $sections,
            keys: $keys
        );

        $managerLocalization = new ManagerLocalization($dir);
        $this->checkSectionDoesNotExist($managerLocalization, $section);
        $this->checkKeyDoesNotExist($key, $section, $languages[0], $dir);
        $this->assertTrue($managerLocalization->deleteKey($key, $section));
    }

    public function testInstanceIsNotDirectoryException(): void
    {
        $dir = 'is_not_dir';
        $this->checkLocalDoesNotExist($dir);
        new ManagerLocalization($dir);
    }

    protected function deleteKeyProvider(): array
    {
        return [
            'dataCorrect' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages', 'auth'],
                'section' => 'messages',
                'keys' => ['key1' => 'val1', 'key2' => 'val2'],
                'key' => 'key1',
            ],
            'keyDoesNotExist' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages', 'auth'],
                'section' => 'messages',
                'keys' => ['key1' => 'val1', 'key2' => 'val2'],
                'key' => 'key3',
            ],
            'sectionDoesNotExist' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages', 'auth'],
                'section' => 'test',
                'keys' => ['key1' => 'val1', 'key2' => 'val2'],
                'key' => 'key1',
            ],
        ];
    }

    protected function createKeyProvider(): array
    {
        return [
            'dataCorrect' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages', 'auth'],
                'keys' => ['key1' => 'val1', 'key2' => 'val2'],
                'key' => 'key3',
                'section' => 'messages',
            ],
            'sectionDoesNotExist' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages', 'auth'],
                'keys' => ['key1' => 'val1', 'key2' => 'val2'],
                'key' => 'key2',
                'section' => 'test',
            ],
            'keyAlreadyExists' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages', 'auth'],
                'keys' => ['key1' => 'val1', 'key2' => 'val2'],
                'key' => 'key2',
                'section' => 'messages',
            ],
        ];
    }

    protected function renameKeyProvider(): array
    {
        return [
            'dataCorrect' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages', 'auth'],
                'section' => 'messages',
                'keys' => ['key1' => 'val1', 'key2' => 'val2'],
                'keyOld' => 'key1',
                'keyNew' => 'keyNew',
            ],
            'keyOldDoesNotExist' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages', 'auth'],
                'section' => 'messages',
                'keys' => ['key1' => 'val1', 'key2' => 'val2'],
                'keyOld' => 'keyOld',
                'keyNew' => 'keyNew',
            ],
            'keyNewAlreadyExists' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages', 'auth'],
                'section' => 'messages',
                'keys' => ['key1' => 'val1', 'key2' => 'val2'],
                'keyOld' => 'keyOld',
                'keyNew' => 'key2',
            ],
            'sectionDoesNotExist' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages', 'auth'],
                'section' => 'test',
                'keys' => ['key1' => 'val1', 'key2' => 'val2'],
                'keyOld' => 'key1',
                'keyNew' => 'keyNew',
            ],
        ];
    }

    protected function getKeyProvider(): array
    {
        return [
            'dataCorrect' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages', 'auth'],
                'keys' => ['key1' => 'val1', 'key2' => 'val2'],
                'key' => 'key1',
                'section' => 'messages',
                'result' => [
                    'en' => 'val1',
                    'uk' => 'val1',
                ],
            ],
            'sectionDoesNotExist' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages', 'auth'],
                'keys' => ['key1' => 'val1', 'key2' => 'val2'],
                'key' => 'key1',
                'section' => 'test',
                'result' => [
                    'en' => 'val1',
                    'uk' => 'val1',
                ],
            ],
            'keyNotFound' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages', 'auth'],
                'keys' => ['key1' => 'val1', 'key2' => 'val2'],
                'key' => 'key3',
                'section' => 'messages',
                'result' => [
                    'en' => 'val1',
                    'uk' => 'val1',
                ],
            ],
        ];
    }

    protected function renameSectionProvider(): array
    {
        return [
            'dataCorrect' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages', 'auth'],
                'key' => 'hello',
                'sectionOld' => 'messages',
                'sectionNew' => 'test',
            ],
            'sectionDoesNotExist' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages', 'auth'],
                'key' => 'hello',
                'sectionOld' => 'old_section',
                'sectionNew' => 'new_section',
            ],
            'sectionAlreadyExists' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages', 'auth'],
                'key' => 'hello',
                'sectionOld' => 'test',
                'sectionNew' => 'messages',
            ],
        ];
    }

    protected function setValueForKeyProvider(): array
    {
        return [
            'dataCorrect' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages', 'auth'],
                'key' => 'hello',
                'value' => 'Welcome',
                'section' => 'messages',
                'lang' => 'en',
            ],
            'isNotLang' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages', 'auth'],
                'key' => 'hello',
                'value' => 'Welcome',
                'section' => 'messages',
                'lang' => 'pl',
            ],
            'isNotSection' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages', 'auth'],
                'key' => 'hello',
                'value' => 'Welcome',
                'section' => 'test',
                'lang' => 'en',
            ],
        ];
    }

    protected function deleteSectionProvider(): array
    {
        return [
            'dataCorrect' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'key' => 'hello',
                'section' => 'messages',
            ],
            'sectionDoesNotExist' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'key' => 'hello',
                'section' => 'auth',
            ],
        ];
    }

    protected function renameLangProvider(): array
    {
        return [
            'dataCorrect' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'key' => 'hello',
                'langNew' => 'pl',
                'langOld' => 'en',
            ],
            'langAlreadyExists' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'key' => 'hello',
                'langNew' => 'en',
                'langOld' => 'en',
            ],
            'langDoesNotExist' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'key' => 'hello',
                'langNew' => 'pl',
                'langOld' => 'en',
            ],
        ];
    }

    protected function getLanguagesSectionsSectionsProvider(): array
    {
        return [
            'dataCorrect' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'key' => 'hello',
            ],
            'isNotDir' => [
                'languages' => ['en', 'uk'],
                'dir' => 'is_not_dir',
                'sections' => ['messages'],
                'key' => 'hello',
            ],
        ];
    }

    protected function createLangProvider(): array
    {
        return [
            'dataCorrect' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'key' => 'hello',
                'lang' => 'pl',
            ],
            'langDuplicate' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'key' => 'hello',
                'lang' => 'en',
            ],
        ];
    }

    protected function deleteLangProvider(): array
    {
        return [
            'dataCorrect' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'key' => 'hello',
                'lang' => 'en',
            ],
            'langDoesNotExist' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'key' => 'hello',
                'lang' => 'pl',
            ],
        ];
    }

    protected function createSectionProvider(): array
    {
        return [
            'dataCorrect' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'key' => 'hello',
                'section' => 'auth',
            ],
            'langDoesNotExist' => [
                'languages' => [],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'key' => 'hello',
                'section' => 'auth',
            ],
            'sectionAlreadyExists' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'sections' => ['messages'],
                'key' => 'hello',
                'section' => 'messages',
            ],
        ];
    }

    private function checkLocalDoesNotExist(string $dir): void
    {
        if (is_dir($dir) === false) {
            $this->expectException(LocalDoesNotExistException::class);
            $this->expectExceptionMessage('The localization directory does not exist');
        }
    }

    private function checkLangAlreadyExists(ManagerLocalization $managerLocalization, string $lang): void
    {
        if (in_array($lang, $managerLocalization->getLanguages())) {
            $this->expectException(LangAlreadyExistsException::class);
            $this->expectExceptionMessage('Language directory already exists');
        }
    }

    private function checkLangDoesNotExist(ManagerLocalization $managerLocalization, string $lang): void
    {
        if (!in_array($lang, $managerLocalization->getLanguages())) {
            $this->expectException(LangDoesNotExistException::class);
            $this->expectExceptionMessage('The language directory does not exist');
        }
    }

    private function checkSectionAlreadyExists(string $sectionPath): void
    {
        if (file_exists($sectionPath)) {
            $this->expectException(SectionAlreadyExistsException::class);
            $this->expectExceptionMessage('Section already exists');
        }
    }

    private function checkSectionDoesNotExist(
        ManagerLocalization $managerLocalization,
        string $section
    ): void
    {
        if (!in_array($section, $managerLocalization->getSections())) {
            $this->expectException(SectionDoesNotExistException::class);
            $this->expectExceptionMessage('Section does not exist');
        }
    }

    private function checkKeyDoesNotExist(
        string $key,
        string $section,
        string $language,
        string $dir
    ): void
    {
        $pathSection = HelperLocalization::generatePathSection($dir, $section, $language);
        $keyLocalization = new KeyLocalization($pathSection);
        if ($keyLocalization->existsKey($key) === false) {
            $this->expectException(KeyDoesNotExistException::class);
            $this->expectExceptionMessage('The key is not in the section');
        }
    }

    private function checkKeyAlreadyExists(
        string $key,
        string $section,
        string $language,
        string $dir
    ): void
    {
        $pathSection = HelperLocalization::generatePathSection($dir, $section, $language);
        $keyLocalization = new KeyLocalization($pathSection);
        if ($keyLocalization->existsKey($key)) {
            $this->expectException(KeyDoesAlreadyExistsException::class);
            $this->expectExceptionMessage('Key already exists in the section');
        }
    }
}
