<?php

namespace App;

use Illuminate\Support\Str;

class AuthMigration implements \JsonSerializable
{
    public string $id;
    public array $tables = [];
    public static int $totalNumberOfTablesToUpdate = 7;

    public function __construct(public array $userIdToChange)
    {
        $this->id = Str::uuid()->toString();
    }

    public function addTable(string $apiName, string $tableName, int $rowCount): void
    {
        $this->tables[] = [
            "apiName" => $apiName,
            "tableName" => $tableName,
            "rowCount" => $rowCount,
            "done" => false,
            "rowsUpdated" => 0
        ];
    }

    public function tableDone(string $apiName, string $tableName, int $rowsUpdated): void
    {
        foreach ($this->tables as &$table) {
            if ($table['apiName'] == $apiName && $table['tableName'] == $tableName) {
                $table["done"] = true;
                $table["rowsUpdated"] = $rowsUpdated;
            }
        }
    }

    public function isReady(): bool
    {
        return count($this->tables) == $this::$totalNumberOfTablesToUpdate;
    }

    public function jsonSerialize()
    {
        $data = get_object_vars($this);
        $data["ready"] = $this->isReady();
        return $data;
    }
}
