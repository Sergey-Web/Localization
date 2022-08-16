<?php

declare(strict_types=1);

namespace Yarmoshuk\Localization;

use Exception;
use Yarmoshuk\Localization\Exceptions\SectionAlreadyExistsException;
use Yarmoshuk\Localization\Exceptions\SectionDoesNotExistException;

class SectionLocalization
{
    public function __construct(
        private readonly string $pathSection
    ) {
    }

    /**
     * @throws SectionDoesNotExistException
     * @return array <int, string>
     */
    public function getKeys(): array
    {
        if (!file_exists($this->pathSection)) {
            throw new SectionDoesNotExistException();
        }

        return include $this->pathSection;
    }

    /**
     * @throws SectionDoesNotExistException
     */
    public function delete(): bool
    {
        if (!file_exists($this->pathSection)) {
            throw new SectionDoesNotExistException();
        }

        return unlink($this->pathSection);
    }

    /**
     * @throws SectionAlreadyExistsException
     * @throws SectionDoesNotExistException
     */
    public function rename(string $pathNewSection): bool
    {
        if (file_exists($pathNewSection)) {
            throw new SectionAlreadyExistsException();
        }

        if (!file_exists($this->pathSection)) {
            throw new SectionDoesNotExistException();
        }

        return rename($this->pathSection, $pathNewSection);
    }

    /**
     * @throws SectionAlreadyExistsException
     */
    public function create(): int|false
    {
        if (file_exists($this->pathSection)) {
            throw new SectionAlreadyExistsException();
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
