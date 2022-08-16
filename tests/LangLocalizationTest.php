<?php

declare(strict_types=1);

namespace Yarmoshuk\Localization\Tests;

use PHPUnit\Framework\TestCase;
use Yarmoshuk\Localization\Exceptions\{
    LangAlreadyExistsException,
    LangDoesNotExistException,
};
use Yarmoshuk\Localization\HelperLocalization;
use Yarmoshuk\Localization\LangLocalization;
use Yarmoshuk\Localization\SectionLocalization;

class LangLocalizationTest extends TestCase
{
    protected const PATH_DIR_LOCAL = __DIR__ . DIRECTORY_SEPARATOR . 'lang';

    protected function setUp(): void
    {
        HelperLocalization::removeTestData(static::PATH_DIR_LOCAL);
    }

    protected function tearDown(): void
    {
        HelperLocalization::removeTestData(static::PATH_DIR_LOCAL);
    }

    /**
     * @dataProvider createProvider
     */
    public function testCreate(
        array $languages,
        string $dir,
        string $lang,
        bool $result
    ): void
    {
        $langLocalization = new LangLocalization($dir, $languages);

        if (in_array($lang, $languages)) {
            $this->expectException(LangAlreadyExistsException::class);
            $this->expectExceptionMessage('Language directory already exists');
        }

        $this->assertSame($result, $langLocalization->create($lang));
    }

    /**
     * @dataProvider renameProvider
     */
    public function testRename(
        array $languages,
        string $dir,
        string $newLang,
        string $oldLang,
        bool $result
    ): void
    {
        if (!empty($languages)) {
            $langLocalization = new LangLocalization($dir, []);
            foreach ($languages as $lang) {
                $this->assertSame($result, $langLocalization->create($lang));
            }
        }

        if (in_array($newLang, $languages)) {
            $this->expectException(LangAlreadyExistsException::class);
            $this->expectExceptionMessage('Language directory already exists');
        }

        if (!in_array($oldLang, $languages)) {
            $this->expectException(LangDoesNotExistException::class);
            $this->expectExceptionMessage('The language directory does not exist');
        }

        $langLocalization = new LangLocalization($dir, $languages);
        $this->assertSame($result, $langLocalization->rename($newLang, $oldLang));
    }

    /**
     * @dataProvider deleteProvider
     */
    public function testDelete(
        array $languages,
        string $dir,
        string $lang,
        bool $result
    ): void
    {
        if (!empty($languages)) {
            $langLocalization = new LangLocalization($dir, []);
            foreach ($languages as $item) {
                $this->assertSame($result, $langLocalization->create($item));
                $sectionLocalization = new SectionLocalization(
                    $dir . DIRECTORY_SEPARATOR . $item . DIRECTORY_SEPARATOR . 'messages'
                );
                $this->assertInstanceOf(SectionLocalization::class, $sectionLocalization);
                $this->assertIsInt($sectionLocalization->create());
            }
        }

        $langLocalization = new LangLocalization($dir, $languages);

        if (!in_array($lang, $languages)) {
            $this->expectException(LangDoesNotExistException::class);
            $this->expectExceptionMessage('The language directory does not exist');
        }

        $this->assertSame($result, $langLocalization->delete($lang));
    }

    protected function deleteProvider(): array
    {
        return [
            'dataCorrect' => [
                'languages' => ['en'],
                'dir' => static::PATH_DIR_LOCAL,
                'lang' => 'en',
                'result' => true,
            ],
            'langAlreadyExists' => [
                'languages' => ['en'],
                'dir' => static::PATH_DIR_LOCAL,
                'lang' => 'uk',
                'result' => true,
            ],
        ];
    }

    protected function createProvider(): array
    {
        return [
            'dataCorrect' => [
                'languages' => ['uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'lang' => 'en',
                'result' => true,
            ],
            'langAlreadyExists' => [
                'languages' => ['uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'lang' => 'uk',
                'result' => true,
            ],
        ];
    }

    protected function renameProvider(): array
    {
        return [
            'dataCorrect' => [
                'languages' => ['en'],
                'dir' => static::PATH_DIR_LOCAL,
                'newLang' => 'uk',
                'oldLang' => 'en',
                'result' => true,
            ],
            'newLangAlreadyExists' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'newLang' => 'en',
                'oldLang' => 'uk',
                'result' => true,
            ],
            'oldLangIsNotExist' => [
                'languages' => ['en', 'uk'],
                'dir' => static::PATH_DIR_LOCAL,
                'newLang' => 'zp',
                'oldLang' => 'pl',
                'result' => true,
            ],
        ];
    }
}

