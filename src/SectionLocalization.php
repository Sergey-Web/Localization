<?php

declare(strict_types=1);

namespace Yarmoshuk\Localization;

use Exception;

class SectionLocalization
{
    public function __construct(
        private readonly string $pathSection
    ) {
    }

    public function getKeys(): array
    {
        if (!file_exists($this->pathSection)) {
            throw new Exception('Section ' . $this->pathSection . '" does not exist', 400);
        }

        return include $this->pathSection;
    }

    /**
     * @throws Exception
     */
    public function delete(): bool
    {
        if (!file_exists($this->pathSection)) {
            throw new Exception('Section "' . $this->pathSection . '" does not exist', 400);
        }

        return unlink($this->pathSection);
    }

    /**
     * @throws Exception
     */
    public function rename(string $pathNewSection): bool
    {
        if (file_exists($pathNewSection)) {
            throw new Exception('Section "' . $pathNewSection . '" already exists', 400);
        }

        if (!file_exists($this->pathSection)) {
            throw new Exception('Section "' . $this->pathSection . '" does not exist', 400);
        }

        return rename($this->pathSection, $pathNewSection);
    }

    /**
     * @throws Exception
     */
    public function create(): int|false
    {
        if (file_exists($this->pathSection)) {
            throw new Exception('Section "' . $this->pathSection . '" already exists', 400);
        }

        return file_put_contents($this->pathSection, $this->getEmptyContentFile());
    }

    private function getEmptyContentFile(): string
    {
        return <<<END
<?php

return [];

END;
    }
}
