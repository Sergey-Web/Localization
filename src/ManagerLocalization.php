<?php

declare(strict_types=1);

namespace Yarmoshuk\Localization;

use Yarmoshuk\Localization\Exceptions\KeyDoesAlreadyExistsException;
use Yarmoshuk\Localization\Exceptions\KeyDoesNotExistException;
use Yarmoshuk\Localization\Exceptions\LangAlreadyExistsException;
use Yarmoshuk\Localization\Exceptions\LangDoesNotExistException;
use Yarmoshuk\Localization\Exceptions\LocalDoesNotExistException;
use Yarmoshuk\Localization\Exceptions\SectionAlreadyExistsException;
use Yarmoshuk\Localization\Exceptions\SectionDoesNotExistException;

class ManagerLocalization
{
    public const FILE_EXTENSION = '.php';

    /**
     * @var array<int, string>
     */
    private array $languages;

    /**
     * @var array<int, string>
     */
    private array $sections;

    /**
     * @throws LocalDoesNotExistException
     */
    public function __construct(
        private readonly string $pathLocalization,
    ) {
        if (is_dir($pathLocalization) === false) {
            throw new LocalDoesNotExistException();
        }

        $this->languages = HelperLocalization::getLanguages($this->pathLocalization);
        $this->sections = HelperLocalization::getSections($this->pathLocalization);
    }

    /**
     * @return array <int, string>
     */
    public function getLanguages(): array
    {
        return $this->languages;
    }

    /**
     * @return array <int, string>
     */
    public function getSections(): array
    {
        return $this->sections;
    }

    /**
     * @throws KeyDoesAlreadyExistsException
     * @throws LangAlreadyExistsException
     * @throws LangDoesNotExistException
     * @throws LocalDoesNotExistException
     * @throws SectionAlreadyExistsException
     * @throws SectionDoesNotExistException
     */
    public function createLang(string $lang): bool
    {
        $languages = $this->languages;
        (new LangLocalization($this->pathLocalization, $this->languages))
            ->create($lang);

        $this->languages[] = $lang;

        if (isset($this->sections[0]) && isset($languages[0])) {
            $sections = $this->getKeys($languages[0]);

            foreach ($sections as $sectionName => $keys) {
                $pathSection = HelperLocalization::generatePathSection(
                    $this->pathLocalization,
                    $sectionName,
                    $lang
                );

                (new SectionLocalization($pathSection))->create();

                foreach ($keys as $key => $val) {
                    (new KeyLocalization($pathSection))->create($key);
                }
            }
        }

        return true;
    }

    /**
     * @throws LangAlreadyExistsException
     * @throws LangDoesNotExistException
     * @throws LocalDoesNotExistException
     */
    public function renameLang(string $langNew, string $langOld): bool
    {
        return (new LangLocalization($this->pathLocalization, $this->languages))
            ->rename($langNew, $langOld);
    }

    /**
     * @throws LangDoesNotExistException
     * @throws LocalDoesNotExistException
     */
    public function deleteLang(string $lang): bool
    {
        return (new LangLocalization($this->pathLocalization, $this->languages))
            ->delete($lang);
    }

    /**
     * @throws LangDoesNotExistException|SectionAlreadyExistsException
     */
    public function createSection(string $sectionName): bool
    {
        if (!isset($this->languages[0])) {
            throw new LangDoesNotExistException();
        }

        foreach ($this->languages as $lang) {
            $pathSection = HelperLocalization::generatePathSection(
                $this->pathLocalization,
                $sectionName,
                $lang
            );

            (new SectionLocalization($pathSection))
                ->create();
        }

        return true;
    }

    /**
     * @throws SectionDoesNotExistException
     */
    public function deleteSection(string $sectionName): bool
    {
        foreach ($this->languages as $lang) {
            $pathSection = HelperLocalization::generatePathSection(
                $this->pathLocalization,
                $sectionName,
                $lang
            );

            (new SectionLocalization($pathSection))->delete();
        }

        return true;
    }

    /**
     * @throws SectionAlreadyExistsException
     * @throws SectionDoesNotExistException
     */
    public function renameSection(string $newSectionName, string $oldSectionName): bool
    {
        foreach ($this->languages as $lang) {
            $pathSectionOld = HelperLocalization::generatePathSection(
                $this->pathLocalization,
                $oldSectionName,
                $lang
            );

            $pathSectionNew = HelperLocalization::generatePathSection(
                $this->pathLocalization,
                $newSectionName,
                $lang
            );

            (new SectionLocalization($pathSectionOld))
                ->rename($pathSectionNew);
        }

        return true;
    }

    /**
     * @throws KeyDoesNotExistException
     * @throws LangDoesNotExistException
     * @throws SectionDoesNotExistException
     */
    public function setValueForKey(
        string $key,
        string $value,
        string $section,
        string $lang
    ): bool {
        $this->checkExistLang($lang);
        $this->checkExistSection($section);

        $pathSection = HelperLocalization::generatePathSection(
            $this->pathLocalization,
            $section,
            $lang
        );

        (new KeyLocalization($pathSection))->setValue($key, $value);

        return true;
    }

    /**
     * @return array <mixed>
     * @throws LangDoesNotExistException
     * @throws SectionDoesNotExistException
     */
    public function getKeys(string $lang): array
    {
        $this->checkExistLang($lang);

        $keys = [];
        foreach ($this->sections as $section) {
            $pathSection = HelperLocalization::generatePathSection(
                $this->pathLocalization,
                $section,
                $lang
            );

            $keys[$section] = (new SectionLocalization($pathSection))->getKeys();
        }

        return $keys;
    }

    /**
     * @return array <string, string>
     */
    public function getKey(string $key, string $section): array
    {
        $this->checkExistSection($section);

        $keyData = [];
        foreach ($this->languages as $lang) {
            $pathSection = HelperLocalization::generatePathSection(
                $this->pathLocalization,
                $section,
                $lang
            );

            $keyData[$lang] = (new KeyLocalization($pathSection))->getValue($key);
        }

        return $keyData;
    }

    /**
     * @throws KeyDoesAlreadyExistsException
     * @throws SectionDoesNotExistException
     */
    public function createKey(string $key, string $section): bool
    {
        $this->checkExistSection($section);

        foreach ($this->languages as $lang) {
            $pathSection = HelperLocalization::generatePathSection(
                $this->pathLocalization,
                $section,
                $lang
            );

            (new KeyLocalization($pathSection))->create($key);
        }

        return true;
    }

    /**
     * @throws KeyDoesAlreadyExistsException
     * @throws KeyDoesNotExistException
     * @throws SectionDoesNotExistException
     */
    public function renameKey(string $newKey, string $oldKey, string $section): bool
    {
        $this->checkExistSection($section);

        foreach ($this->languages as $lang) {
            $pathSection = HelperLocalization::generatePathSection(
                $this->pathLocalization,
                $section,
                $lang
            );

            (new KeyLocalization($pathSection))->rename($newKey, $oldKey);
        }

        return true;
    }

    /**
     * @throws KeyDoesNotExistException
     * @throws SectionDoesNotExistException
     */
    public function deleteKey(string $key, string $section): bool
    {
        $this->checkExistSection($section);

        foreach ($this->languages as $lang) {
            $pathSection = HelperLocalization::generatePathSection(
                $this->pathLocalization,
                $section,
                $lang
            );

            (new KeyLocalization($pathSection))->delete($key);
        }

        return true;
    }

    /**
     * @throws SectionDoesNotExistException
     */
    private function checkExistSection(string $section): void
    {
        if (!in_array($section, $this->sections)) {
            throw new SectionDoesNotExistException();
        }
    }

    /**
     * @throws LangDoesNotExistException
     */
    private function checkExistLang(string $lang): void
    {
        if (!in_array($lang, $this->languages)) {
            throw new LangDoesNotExistException();
        }
    }
}
