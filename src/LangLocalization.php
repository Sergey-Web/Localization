<?php

declare(strict_types=1);

namespace Yarmoshuk\Localization;

use DirectoryIterator;
use Exception;

class LangLocalization
{
    public function __construct(
        private readonly string $pathDir,
        private readonly array $languages,
    )
    {}

    public function create(string $lang): void
    {
        if (in_array($lang, $this->languages)) {
            throw new Exception('The "' . $lang . '" localization already exists', 400);
        }

        mkdir($this->pathDir . '/' . $lang);
    }

    public function rename(string $newLang, string $oldLang): void
    {
        if (in_array($newLang, $this->languages)) {
            throw new Exception('The "' . $newLang . '" localization already exists', 400);
        }

        if (!in_array($oldLang, $this->languages)) {
            throw new Exception('The "' . $oldLang . '" localization does not exist', 400);
        }

        rename($this->pathDir . '/'. $oldLang, $this->pathDir . '/'. $newLang);
    }

    /**
     * @throws Exception
     */
    public function delete(string $lang): void
    {
        if (!in_array($lang, $this->languages)) {
            throw new Exception('The "' . $lang . '" localization does not exist', 400);
        }

        /** @var DirectoryIterator $fileInfo */
        foreach (new DirectoryIterator($this->pathDir . '/' . $lang) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            unlink($fileInfo->getPathname());
        }

        rmdir($this->pathDir . '/' . $lang);
    }
}
