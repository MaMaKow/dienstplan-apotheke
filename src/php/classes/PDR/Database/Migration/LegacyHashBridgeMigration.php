<?php

namespace PDR\Database\Migration;

class LegacyHashBridgeMigration implements MigrationInterface {

    private $version;
    private $description;
    private $callback;

    public function __construct(string $version, string $description, callable $callback) {
        $this->version = $version;
        $this->description = $description;
        $this->callback = $callback;
    }

    public function getVersion(): string {
        return $this->version;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function up(): bool {
        $result = call_user_func($this->callback);
        return false !== $result;
    }
}
