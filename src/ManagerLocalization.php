<?php

declare(strict_types=1);

namespace Yarmoshuk\Localization;

use DirectoryIterator;
use Exception;

class ManagerLocalization
{
    public const FILE_EXTENSION = '.php';

    /**
     * @var array<int, string>
     */
    private array $languages;

    /**
     * @var array <int, string>
     */
    private array $sections;

    /**
     * @throws Exception
     */
    public function __construct(
        private readonly string $pathLocalization,
    ) {
        if (is_dir($pathLocalization) === false) {
            throw new Exception('Localization directory does not exist');
        }

        $this->languages = $this->setLocalizationLang();
        $this->sections = $this->setLocalizationSection();
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
     * @throws Exception
     */
    public function createLocal(string $lang): bool
    {
        $languages = $this->languages;
        (new LangLocalization($this->pathLocalization, $this->languages))
            ->create($lang);

        $this->languages[] = $lang;

        if (isset($this->sections[0]) && isset($languages[0])) {
            $sections = $this->getKeys($languages[0]);

            foreach ($sections as $sectionName => $keys) {
                (new SectionLocalization($this->generatePathSection($sectionName, $lang)))->create();

                foreach ($keys as $key => $val) {
                    (new KeyLocalization(
                        $this->generatePathSection($sectionName, $lang)
                    ))->create($key);
                }
            }
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function renameLang(string $newLang, string $oldLang): bool
    {
        (new LangLocalization($this->pathLocalization, $this->languages))
            ->rename($newLang, $oldLang);

        return true;
    }

    /**
     * @throws Exception
     */
    public function deleteLang(string $lang): bool
    {
        (new LangLocalization($this->pathLocalization, $this->languages))
            ->delete($lang);

        return true;
    }

    /**
     * @throws Exception
     */
    public function createSection(string $sectionName): bool
    {
        if (!isset($this->languages[0])) {
            throw new Exception('You can\'t create a section without localizations', 400);
        }

        foreach ($this->languages as $lang) {
            (new SectionLocalization($this->generatePathSection($sectionName, $lang)))
                ->create();
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function deleteSection(string $sectionName): bool
    {
        foreach ($this->languages as $lang) {
            (new SectionLocalization($this->generatePathSection($sectionName, $lang)))
                ->delete();
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function renameSection(string $newSectionName, string $oldSectionName): bool
    {
        foreach ($this->languages as $lang) {
            (new SectionLocalization(
                $this->generatePathSection($oldSectionName, $lang)
            ))->rename(
                pathNewSection: $this->generatePathSection($newSectionName, $lang)
            );
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function setValueForKey(string $key, string $value, string $section, string $lang): bool
    {
        $this->checkExistLang($lang);
        $this->checkExistSection($section);

        (new KeyLocalization(
            $this->generatePathSection($section, $lang)
        ))->setValue($key, $value);

        return true;
    }

    /**
     * @return array <mixed>
     * @throws Exception
     */
    public function getKeys(string $lang): array
    {
        $this->checkExistLang($lang);

        $keys = [];
        foreach ($this->sections as $section) {
            $keys[$section] = (new SectionLocalization($this->generatePathSection($section, $lang)))
                ->getKeys();
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
            $keyData[$lang] = (new KeyLocalization(
                $this->generatePathSection($section, $lang)
            ))->getKey($key);
        }

        return $keyData;
    }

    /**
     * @throws Exception
     */
    public function createKey(string $key, string $section): bool
    {
        $this->checkExistSection($section);

        foreach ($this->languages as $lang) {
            (new KeyLocalization(
                $this->generatePathSection($section, $lang)
            ))->create($key);
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function renameKey(string $newKey, string $oldKey, string $section): bool
    {
        $this->checkExistSection($section);

        foreach ($this->languages as $lang) {
            (new KeyLocalization($this->generatePathSection($section, $lang)))
                ->rename($newKey, $oldKey);
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function deleteKey(string $key, string $section): bool
    {
        $this->checkExistSection($section);

        foreach ($this->languages as $lang) {
            (new KeyLocalization($this->generatePathSection($section, $lang)))->delete($key);
        }

        return true;
    }

    /**
     * @return array <int, string>
     * @throws Exception
     */
    private function setLocalizationLang(): array
    {
        $languages = [];

        /** @var DirectoryIterator $fileInfo */
        foreach (new DirectoryIterator($this->pathLocalization) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            $languages[] = $fileInfo->getFilename();
        }

        return $languages;
    }

    /**
     * @return array <int, string>
     */
    private function setLocalizationSection(): array
    {
        $sections = [];
        if (!empty($this->languages)) {
            $path = $this->pathLocalization . DIRECTORY_SEPARATOR . $this->languages[0];

            /** @var DirectoryIterator $item */
            foreach (new DirectoryIterator($path) as $item) {
                if ($item->isDot()) {
                    continue;
                }

                $sections[] = str_replace(static::FILE_EXTENSION, '', $item->getFilename());
            }
        }

        return $sections;
    }

    private function generatePathSection(string $fileName, string $lang): string
    {
        return $this->pathLocalization . DIRECTORY_SEPARATOR
            . $lang . DIRECTORY_SEPARATOR
            . $fileName . static::FILE_EXTENSION;
    }

    private function checkExistSection(string $section): void
    {
        if (!in_array($section, $this->sections)) {
            throw new Exception(
                'The "' . $section . '" section does not exist',
                400
            );
        }
    }

    /**
     * @throws Exception
     */
    private function checkExistLang(string $lang): void
    {
        if (!in_array($lang, $this->languages)) {
            throw new Exception('The "' . $lang . '" language does not exist', 400);
        }
    }
}
