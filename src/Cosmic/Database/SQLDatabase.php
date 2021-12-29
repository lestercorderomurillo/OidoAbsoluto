<?php

namespace Cosmic\Database;

use Cosmic\Core\Bootstrap\Model;
use Cosmic\Database\Bootstrap\Database;
use Cosmic\Database\Driver\Driver;
use Cosmic\Database\Common\ConnectionString;

/**
 * This class represents a SQLDatabase.
 */
class SQLDatabase extends Database
{
    /**
     * @inheritdoc
     */
    public function __construct(Driver $driver, ConnectionString $connectionString)
    {
        parent::__construct($driver, $connectionString);
    }

    /**
     * Prepare new parameter replacements for this array.
     * This function will return an array with two arrays inside:
     * One for the bindings and another one for the replacements.
     * 
     * @param array $array The input array to use.
     * 
     * @return array[] An array to use as bindings.
     */
    private function createParametersBinds(array $array): array
    {
        $prepare = [];
        $values = [];
        foreach ($array as $key => $value) {
            $prepare[$key] = "`$key` = :$key";
            $values[":$key"] = $value;
        }
        return [$prepare, $values];
    }

    /**
     * @inheritdoc
     */
    public function find(string $modelClass, array $where = [])
    {
        $models = $this->findAll($modelClass, $where, "LIMIT 1");
        if ($models == []) {
            return null;
        }
        return $models[0];
    }

    /**
     * @inheritdoc
     */
    public function findAll(string $modelClass, array $where = [], string $append = ""): array
    {
        $models = [];
        $tableName = (new $modelClass())->getTableName();

        if ($where == []) {

            $this->addQuery("SELECT * FROM `$tableName` $append");
        } else {

            $array = $this->createParametersBinds($where);
            $where = implode(" AND ", $array[0]);
            $this->addQuery("SELECT * FROM `$tableName` WHERE $where $append", $array[1]);
        }

        $internalResult = $this->commit();

        foreach ($internalResult->all() as $row) {

            $model = new $modelClass();
            $model->setValues($row);
            $model->setId($row["id"]);
            $models[] = $model;
        }

        return $models;
    }

    /**
     * @inheritdoc
     */
    public function save(Model $model): void
    {
        $tableName = $model->getTableName();
        $values = $model->getAttributesValues();
        $placeholders = implode(", ", $model->getAttributesPlaceholders());
        $id = $model->getId();

        if ($id == 0) {
            $this->addQuery("INSERT INTO `$tableName` SET $placeholders", $values);
        } else {
            $values[":id"] = $id;
            $this->addQuery("UPDATE `$tableName` SET $placeholders WHERE id = :id", $values);
        }
    }

    /**
     * @inheritdoc
     */
    public function delete(Model $model): void
    {
        $tableName = $model->getTableName();
        $id = $model->getId();

        if ($id == 0) {

            $this->addQuery("DELETE FROM `$tableName` WHERE id = :id", [":id" => $model->getId()]);
        }
    }

    /**
     * @inheritdoc
     */
    public function saveAll(array $models): void
    {
        foreach ($models as $model) {
            $this->save($model);
        }
    }

    /**
     * @inheritdoc
     */
    public function deleteAll(array $models): void
    {
        foreach ($models as $model) {
            $this->delete($model);
        }
    }
}
