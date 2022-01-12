<?php

namespace Cosmic\ORM\Databases\SQL;

use Cosmic\Utilities\Collection;
use Cosmic\ORM\Bootstrap\Database;
use Cosmic\ORM\Bootstrap\Driver;
use Cosmic\ORM\Common\ConnectionString;

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
    public function exists(string $modelClass, array $where)
    {
        $tableName = (new $modelClass())->getTableName();
        $array = $this->createParametersBinds($where);
        $where = implode(" AND ", $array[0]);

        $this->addQueryToNextBatch("SELECT 1 FROM `$tableName` WHERE $where", $array[1]);

        $queryResults = $this->commit();
        $rows = $queryResults[0]->all();

        if ($rows == []) {
            return false;
        }

        return true;
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
    public function findAll(string $modelClass, array $where = [], string $append = __EMPTY__): array
    {
        $models = [];
        $tableName = (new $modelClass())->getTableName();

        if ($where == []) {

            $this->addQueryToNextBatch("SELECT * FROM `$tableName` $append");
        } else {

            $array = $this->createParametersBinds($where);
            $where = implode(" AND ", $array[0]);
            $this->addQueryToNextBatch("SELECT * FROM `$tableName` WHERE $where $append", $array[1]);
        }

        $queryResults = $this->commit();
        $rows = $queryResults[0]->all();

        foreach ($rows as $row) {

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
    public function save($models, bool $forceInsert = false): void
    {
        $models = Collection::normalize($models);

        foreach ($models as $model) {

            $tableName = $model->getTableName();
            $id = $model->getId();

            $shouldInsert = !($this->exists($model->getClassName(), ["id" => $id]));

            if ($id == 0) {
                $values = $model->getAttributesValues(true);
                $placeholders = $model->getAttributesPlaceholders(true);
            } else {
                $values = $model->getAttributesValues();
                $placeholders = $model->getAttributesPlaceholders();
            }

            if ($shouldInsert || $forceInsert) {
                $this->addQueryToNextBatch("INSERT INTO `$tableName` SET $placeholders", $values);
            } else {
                $this->addQueryToNextBatch("UPDATE `$tableName` SET $placeholders WHERE id = :id", $values);
            }

        }
    }

    /**
     * @inheritdoc
     */
    public function delete($models): void
    {
        $models = Collection::normalize($models);

        foreach ($models as $model) {

            $tableName = $model->getTableName();
            $id = $model->getId();

            if ($id == 0) {

                $this->addQueryToNextBatch("DELETE FROM `$tableName` WHERE id = :id", [":id" => $model->getId()]);
            }
        }
    }
}
