<?php

declare(strict_types=1);

namespace Yarmoshuk\Localization\Tests;

use Yarmoshuk\Localization\Exceptions\KeyDoesAlreadyExistsException;
use Yarmoshuk\Localization\Exceptions\LangAlreadyExistsException;
use Yarmoshuk\Localization\Exceptions\LocalDoesNotExistException;
use Yarmoshuk\Localization\Exceptions\SectionAlreadyExistsException;
use Yarmoshuk\Localization\Exceptions\SectionDoesNotExistException;
use Yarmoshuk\Localization\KeyLocalization;
use Yarmoshuk\Localization\LangLocalization;
use Yarmoshuk\Localization\ManagerLocalization;
use Yarmoshuk\Localization\SectionLocalization;

trait GeneratorDataLocalization
{
    /**
     * @throws KeyDoesAlreadyExistsException
     * @throws LangAlreadyExistsException
     * @throws SectionDoesNotExistException
     * @throws LocalDoesNotExistException
     * @throws SectionAlreadyExistsException
     */
    protected function generateData(string $dir, array $languages, array $sections, array $keys): void
    {
        foreach ($languages as $item) {
            $langLocalization = new LangLocalization($dir, []);

            $this->assertTrue($langLocalization->create($item));

            foreach ($sections as $itemSection) {
                $pathSectionTest = $dir . DIRECTORY_SEPARATOR
                    . $item . DIRECTORY_SEPARATOR
                    . $itemSection . ManagerLocalization::FILE_EXTENSION;

                $sectionLocalization = new SectionLocalization($pathSectionTest);
                $this->assertInstanceOf(SectionLocalization::class, $sectionLocalization);
                $this->assertIsInt($sectionLocalization->create());

                foreach ($keys as $key => $val) {
                    $keyLocalization = new KeyLocalization($pathSectionTest);
                    $this->assertInstanceOf(KeyLocalization::class, $keyLocalization);

                    $this->assertIsInt($keyLocalization->create($key));
                    $this->assertIsInt($keyLocalization->setValue($key, $val));
                }
            }
        }
    }
}