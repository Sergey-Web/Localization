<?php

declare(strict_types=1);

namespace Yarmoshuk\Localization;

use Yarmoshuk\Localization\Exceptions\KeyDoesAlreadyExistsException;
use Yarmoshuk\Localization\Exceptions\KeyDoesNotExistException;
use Yarmoshuk\Localization\Exceptions\SectionDoesNotExistException;

class KeyLocalization
{
    /**
     * @throws SectionDoesNotExistException
     */
    public function __construct(
        private readonly string $pathSection
    ) {
        if (!file_exists($this->pathSection)) {
            throw new SectionDoesNotExistException();
        }
    }

    /**
     * @psalm-suppress UnresolvableInclude
     * @throws KeyDoesNotExistException
     */
    public function setValue(string $key, string $value): int|bool
    {
        $this->checkKeyExists($key);
        /** @var array $data <mixed> $data */
        $data = include $this->pathSection;
        $data[$key] = $value;

        return file_put_contents($this->pathSection, $this->createContextForFile($data));
    }

    /**
     * @psalm-suppress UnresolvableInclude
     * @throws KeyDoesNotExistException
     */
    public function getValue(string $key): string
    {
        $this->checkKeyExists($key);
        /** @var array $data */
        $data = include $this->pathSection;

        return (string) $data[$key];
    }

    /**
     * @psalm-suppress UnresolvableInclude
     */
    public function existsKey(string $key): bool
    {
        /** @var array $data */
        $data = include $this->pathSection;

        return isset($data[$key]);
    }

    /**
     * @psalm-suppress UnresolvableInclude
     * @throws KeyDoesAlreadyExistsException
     */
    public function create(string $key): bool|int
    {
        $this->checkDuplicateKey($key);

        /** @var array $data */
        $data = include $this->pathSection;
        $data[$key] = '';

        return file_put_contents($this->pathSection, $this->createContextForFile($data));
    }

    /**
     * @psalm-suppress UnresolvableInclude
     * @throws KeyDoesNotExistException
     */
    public function delete(string $key): bool|int
    {
        $this->checkKeyExists($key);
        /** @var array $data */
        $data = include $this->pathSection;
        unset($data[$key]);

        return file_put_contents($this->pathSection, $this->createContextForFile($data));
    }

    /**
     * @psalm-suppress UnresolvableInclude
     * @throws KeyDoesAlreadyExistsException
     * @throws KeyDoesNotExistException
     */
    public function rename(string $newKey, string $oldKey): bool|int
    {
        $this->checkKeyExists($oldKey);
        $this->checkDuplicateKey($newKey);

        /** @var array<string, string> $data */
        $data = include $this->pathSection;
        $data[$newKey] = $data[$oldKey];
        unset($data[$oldKey]);

        return file_put_contents($this->pathSection, $this->createContextForFile($data));
    }

    /**
     * @param array <int, string> $data
     */
    private function createContextForFile(array $data): string
    {
        $startFile = <<<START
<?php

return [
START;
        $endFile = <<<END

];

END;
        /** @var string $val */
        foreach ($data as $key => $val) {
            $startFile .= '
    \'' . $key . '\' => \'' . $val . '\',';
        }

        return $startFile . $endFile;
    }

    /**
     * @throws KeyDoesNotExistException
     */
    private function checkKeyExists(string $key): void
    {
        if ($this->existsKey($key) === false) {
            throw new KeyDoesNotExistException();
        }
    }

    /**
     * @throws KeyDoesAlreadyExistsException
     */
    private function checkDuplicateKey(string $key): void
    {
        if ($this->existsKey($key)) {
            throw new KeyDoesAlreadyExistsException();
        }
    }
}
