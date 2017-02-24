<?php

namespace MrBill\Persistence;

use Generator;

class DataStore
{
    private const DATA_DIRECTORY = '/var/www/data';

    public function exists(string $key) : bool
    {
        return file_exists($this->getFileNameForKey($key));
    }

    public function get(string $key) : Generator
    {
        if ($this->exists($key) && $handle = fopen($this->getFileNameForKey($key), 'r')) {
            while (($line = fgets($handle)) !== false)
                yield substr($line, 0, -1);
            fclose($handle);
        }
    }

    public function append(string $key, string $item) : void
    {
        // Newlines are silently lost!
        $item = str_replace("\n", '', $item);
        file_put_contents($this->getFileNameForKey($key), $item . "\n", FILE_APPEND);
    }

    public function put(string $key, string $item) : void
    {
        // Newlines are silently lost!
        $item = str_replace("\n", '', $item);
        file_put_contents($this->getFileNameForKey($key), $item . "\n");
    }

    public function remove(string $key) : void
    {
        @unlink($this->getFileNameForKey($key));
    }

    protected function getFileNameForKey(string $key) : string
    {
        return self::DATA_DIRECTORY . '/' . $key;
    }
}
