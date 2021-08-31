<?php

namespace Pipeline\Database;

use Pipeline\Adapter\Adapter;
use Pipeline\Database\Common\ConnectionString;
use Pipeline\Database\SQL\QueryResult;
use Pipeline\Model\Model;
use Pipeline\Utilities\ArrayHelper;

class SQLDatabase extends AbstractDatabase
{
    public function __construct(Adapter $adapter, ConnectionString $connection_string, string $service_id = "SQLDatabase")
    {
        parent::__construct($adapter, $connection_string, $service_id);
    }

    public function find(string $class_name, array $where = []): QueryResult
    {
        $result = $this->findAll($class_name, $where, "LIMIT 1");
        $models = $result->get("models");
        return new QueryResult($models);
    }

    public function findAll(string $class_name, array $where = [], string $append = ""): QueryResult
    {
        $models = array();
        $table_name = (new $class_name())->getTableName();

        if ($where == []) {
            $this->addQuery("SELECT * FROM `$table_name` $append");
        } else {
            $array = ArrayHelper::placeholderCreateArray($where);
            $where = implode(" AND ", $array[0]);
            $this->addQuery("SELECT * FROM `$table_name` WHERE $where $append", $array[1]);
        }

        $internal_result = $this->execute();

        foreach ($internal_result->expose() as $row) {
            $model = new $class_name();
            $model->setValues($row);
            $model->setId($row["id"]);
            $models[] = $model;
        }

        return new QueryResult(["models" => $models]);
    }

    public function save(Model $model): void
    {
        $table_name = $model->getTableName();
        $values = $model->getAttributesValues();
        $placeholders = implode(", ", $model->getAttributesPlaceholders());
        $id = $model->getId();
        if ($id == 0) {
            $this->addQuery("INSERT INTO `$table_name` SET $placeholders", $values);
        } else {
            $values[":id"] = $id;
            $this->addQuery("UPDATE `$table_name` SET $placeholders WHERE id = :id", $values);
        }
    }

    public function delete(Model $model): void
    {
        $table_name = $model->getTableName();
        $id = $model->getId();
        if ($id == 0) {
            $this->addQuery("DELETE FROM `$table_name` WHERE id = :id", [":id" => $model->getId()]);
        }
    }

    public function saveAll(array $models): void
    {
        foreach ($models as $model) {
            $this->save($model);
        }
    }

    public function deleteAll(array $models): void
    {
        foreach ($models as $model) {
            $this->delete($model);
        }
    }
}
