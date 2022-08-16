<?php

declare(strict_types=1);

namespace Yarmoshuk\Localization;

use DirectoryIterator;

class HelperLocalization
{
    public static function generatePathSection(string $path, string $fileName, string $lang): string
    {
        return $path . DIRECTORY_SEPARATOR
            . $lang . DIRECTORY_SEPARATOR
            . $fileName . ManagerLocalization::FILE_EXTENSION;
    }

    public static function removeTestData(string $pathDirLocal): void
    {
        if (is_dir($pathDirLocal)) {
            foreach (new DirectoryIterator($pathDirLocal) as $fileInfo) {
                if ($fileInfo->isDot()) {
                    continue;
                }

                if (is_dir($fileInfo->getPathname())) {
                    foreach (new DirectoryIterator($fileInfo->getPathname()) as $fileInfoInner) {
                        if ($fileInfoInner->isDot()) {
                            continue;
                        }

                        unlink($fileInfoInner->getPathname());
                    }
                    rmdir($fileInfo->getPathname());
                }
            }
        }
    }

    public static function getLanguages(string $pathLocalization): array
    {
        $languages = [];

        foreach (new DirectoryIterator($pathLocalization) as $fileInfo) {
            if ($fileInfo->isDot() || $fileInfo->isDir() === false) {
                continue;
            }

            $languages[] = $fileInfo->getFilename();
        }

        return $languages;
    }

    public static function getSections(string $pathLocalization): array
    {
        $sections = [];
        $languages = static::getLanguages($pathLocalization);
        if (!empty($languages)) {
            /** @var array<int, string> $languages */
            $path = $pathLocalization . DIRECTORY_SEPARATOR . $languages[0];

            foreach (new DirectoryIterator($path) as $item) {
                if ($item->isDot()) {
                    continue;
                }

                $sections[] = str_replace(ManagerLocalization::FILE_EXTENSION, '', $item->getFilename());
            }
        }

        return $sections;
    }
}
