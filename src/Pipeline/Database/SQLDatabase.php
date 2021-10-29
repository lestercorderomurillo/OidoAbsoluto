<?php

namespace Pipeline\Database;

use Pipeline\Core\Boot\ModelBase;
use Pipeline\Adapter\Adapter;
use Pipeline\Database\Common\ConnectionString;
use Pipeline\Utilities\Vector;

class SQLDatabase extends DatabaseBase
{
    public function __construct(Adapter $adapter, ConnectionString $connection_string, string $service_id = "SQLDatabase")
    {
        parent::__construct($adapter, $connection_string, $service_id);
    }

    public function find(string $model_class_name, array $where = [])
    {
        $models = $this->findAll($model_class_name, $where, "LIMIT 1");
        if ($models == []) {
            return null;
        }
        return $models[0];
    }

    public function findAll(string $model_class_name, array $where = [], string $append = ""): array
    {
        $models = [];
        $table_name = (new $model_class_name())->getTableName();

        if ($where == []) {
            $this->addQuery("SELECT * FROM `$table_name` $append");
        } else {
            $array = Vector::placeholderCreateArray($where);
            $where = implode(" AND ", $array[0]);
            $this->addQuery("SELECT * FROM `$table_name` WHERE $where $append", $array[1]);
        }

        $internal_result = $this->execute();

        foreach ($internal_result->exposeArray() as $row) {
            $model = new $model_class_name();
            $model->setValues($row);
            $model->setId($row["id"]);
            $models[] = $model;
        }

        return $models;
    }

    public function save(ModelBase $model): void
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

    public function delete(ModelBase $model): void
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
