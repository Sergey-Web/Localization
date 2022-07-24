<?php

declare(strict_types=1);

namespace Yarmoshuk\Localization;

use Exception;

class KeyLocalization
{
    public function __construct(
        private readonly string $pathSection
    )
    {}

    /**
     * @throws Exception
     */
    public function setValue(string $key, string $value): bool|int
    {
        $this->checkExistKey($key);
        $data = include $this->pathSection;
        $data[$key] = $value;

        return file_put_contents($this->pathSection, $this->createContextForFile($data));
    }

    /**
     * @throws Exception
     */
    public function getKey(string $key): string
    {
        $this->checkExistKey($key);
        $data = include $this->pathSection;

        return $data[$key];
    }

    public function existsKey(string $key): bool
    {
        $data = include $this->pathSection;

        return isset($data[$key]);
    }

    /**
     * @throws Exception
     */
    public function create(string $key): bool|int
    {
        $this->checkDuplicateKey($key);
        $data = include $this->pathSection;
        $data[$key] = '';

        return file_put_contents($this->pathSection, $this->createContextForFile($data));
    }

    public function delete(string $key): bool|int
    {
        $data = include $this->pathSection;
        unset($data[$key]);

        return file_put_contents($this->pathSection, $this->createContextForFile($data));
    }

    public function rename(string $newKey, string $oldKey): bool|int
    {
        $data = include $this->pathSection;
        $data[$newKey] = $data[$oldKey];
        unset($data[$oldKey]);

        return file_put_contents($this->pathSection, $this->createContextForFile($data));
    }

    private function createContextForFile(array $data): string
    {
        $startFile = <<<START
<?php

return [
START;
        $endFile = <<<END

];

END;
        foreach ($data as $key => $val) {
            $startFile .= '
    \'' . $key . '\' => \'' . $val . '\',';
        }

        return $startFile . $endFile;
    }

    /**
     * @throws Exception
     */
    private function checkExistKey(string $key): void
    {
        if ($this->existsKey($key) === false) {
            throw new Exception(
                'The key "' . $key . '" is not in the section', 400
            );
        }
    }

    /**
     * @throws Exception
     */
    private function checkDuplicateKey(string $key): void
    {
        if ($this->existsKey($key)) {
            throw new Exception(
                'Key "' . $key . '" already exists in the section', 400
            );
        }
    }
}
