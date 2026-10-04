<?php

namespace PDR\Database\Migration;

interface MigrationInterface {

    public function getVersion(): int;

    public function migrate(): void;

    public function getDescription(): string;
}
