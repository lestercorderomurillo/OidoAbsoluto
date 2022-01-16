<?php

/**
 * The Cosmic Framework 1.0 Beta
 * Quick MVC enviroment with scoped component rendering capability.
 * Supports PHP, PHPX for improved syntax suggar, javascripts callbacks, event handling and quick style embedding.

 * @author Lester Cordero Murillo <lestercorderomurillo@gmail.com>
 */

namespace Cosmic\ORM\Databases\SQL;

use Cosmic\ORM\Abstracts\Model;
use Cosmic\ORM\Abstracts\Driver;
use Cosmic\ORM\Abstracts\Repository;
use Cosmic\ORM\Common\ConnectionString;
use Cosmic\Utilities\Collections;

/**
 * This class represents a SQLDatabase.
 */
class SQLRepository extends Repository
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

        $this->addQueryToQueue("SELECT 1 FROM `$tableName` WHERE $where", $array[1]);

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

            $this->addQueryToQueue("SELECT * FROM `$tableName` $append");
        } else {

            $array = $this->createParametersBinds($where);
            $where = implode(" AND ", $array[0]);
            $this->addQueryToQueue("SELECT * FROM `$tableName` WHERE $where $append", $array[1]);
        }

        $queryResults = $this->commit();
        $rows = $queryResults[0]->all();

        foreach ($rows as $row) {

            $model = new $modelClass();
            $model->setValues($row);
            $model->id = $row["id"];
            $models[] = $model;
        }

        return $models;
    }

    /**
     * @inheritdoc
     */
    public function save($models, bool $forceInsert = false): void
    {
        $models = Collections::normalizeToList($models);

        /** @var Model $model */
        foreach ($models as $model) {

            $tableName = $model->getTableName();

            $shouldInsert = !($this->exists($model->getClassName(), ["id" => $model->id]));
            $values = $this->getAttributesValues($model->getValues(), ($model->id == 0));
            $placeholders = $this->getAttributesPlaceholders($model->getValues(),  ($model->id == 0));

            if ($shouldInsert || $forceInsert) {
                $this->addQueryToQueue("INSERT INTO `$tableName` SET $placeholders", $values);
            } else {
                $this->addQueryToQueue("UPDATE `$tableName` SET $placeholders WHERE id = :id", $values);
            }

        }
    }

    /**
     * @inheritdoc
     */
    public function delete($models): void
    {
        $models = Collections::normalizeToList($models);

        foreach ($models as $model) {

            $tableName = $model->getTableName();
            $id = $model->getId();

            if ($id == 0) {

                $this->addQueryToQueue("DELETE FROM `$tableName` WHERE id = :id", [":id" => $model->getId()]);
            }
        }
    }
}
