<?php

declare(strict_types=1);

namespace Yarmoshuk\Localization;

use DirectoryIterator;
use Yarmoshuk\Localization\Exceptions\LangAlreadyExistsException;
use Yarmoshuk\Localization\Exceptions\LangDoesNotExistException;
use Yarmoshuk\Localization\Exceptions\LocalDoesNotExistException;

class LangLocalization
{
    /**
     * @param string $pathDir
     * @param array <int, string> $languages
     * @throws LocalDoesNotExistException
     */
    public function __construct(
        private readonly string $pathDir,
        private readonly array $languages,
    ) {
        if (is_dir($pathDir) === false) {
            throw new LocalDoesNotExistException();
        }
    }

    /**
     * @throws LangAlreadyExistsException
     */
    public function create(string $lang): bool
    {
        if (in_array($lang, $this->languages)) {
            throw new LangAlreadyExistsException();
        }

        return mkdir($this->pathDir . DIRECTORY_SEPARATOR . $lang);
    }

    /**
     * @throws LangAlreadyExistsException
     * @throws LangDoesNotExistException
     */
    public function rename(string $newLang, string $oldLang): bool
    {
        if (in_array($newLang, $this->languages)) {
            throw new LangAlreadyExistsException();
        }

        if (!in_array($oldLang, $this->languages)) {
            throw new LangDoesNotExistException();
        }

        return rename(
            $this->pathDir . DIRECTORY_SEPARATOR . $oldLang,
            $this->pathDir . DIRECTORY_SEPARATOR . $newLang
        );
    }

    /**
     * @throws LangDoesNotExistException
     */
    public function delete(string $lang): bool
    {
        if (!in_array($lang, $this->languages)) {
            throw new LangDoesNotExistException();
        }

        foreach (new DirectoryIterator($this->pathDir . DIRECTORY_SEPARATOR . $lang) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            unlink($fileInfo->getPathname());
        }

        return rmdir($this->pathDir . DIRECTORY_SEPARATOR . $lang);
    }
}
