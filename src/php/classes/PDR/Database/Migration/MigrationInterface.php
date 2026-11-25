<?php

namespace PDR\Database\Migration;

interface MigrationInterface {

    public function getVersion(): string;

    public function getDescription(): string;

    public function up(): bool;
}
