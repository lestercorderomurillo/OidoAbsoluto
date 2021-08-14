<?php

namespace VIP\Database;

use VIP\Core\InternalResult;
use VIP\Adapter\Adapter;
use VIP\Model\AbstractModel;
use VIP\Utilities\ArrayHelper;
use VIP\Database\Common\ConnectionString;

class SQLDatabase extends AbstractDatabase
{
    public function __construct(Adapter $adapter, ConnectionString $connection_string, string $service_id = "SQLDatabase")
    {
        parent::__construct($adapter, $connection_string, $service_id);
    }

    public function find(string $class_name, array $where = []): InternalResult
    {
        $result = $this->findAll($class_name, $where, "LIMIT 1");
        if ($result->getStatus() == InternalResult::FAILURE) {
            return new InternalResult([], InternalResult::FAILURE);
        }
        $models = $result->getData("models");
        return new InternalResult($models, InternalResult::SUCCESS);
    }

    public function findAll(string $class_name, array $where = [], string $append = ""): InternalResult
    {
        $models = array();
        $table_name = (new $class_name())->getTableName();

        if ($where == []) {
            $this->addQuery(trim("SELECT * FROM `$table_name` $append"));
        } else {
            $array = ArrayHelper::placeholderCreateArray($where);
            $where = implode(" AND ", $array[0]);
            $this->addQuery(trim("SELECT * FROM `$table_name` WHERE $where $append"), $array[1]);
        }

        $internal_result = $this->execute();

        if ($internal_result->getStatus() == InternalResult::FAILURE) {
            return new InternalResult([], InternalResult::FAILURE);
        }

        foreach ($internal_result->getAllData() as $row) {
            $model = new $class_name();
            $model->set($row);
            $model->setObjectID($row["id"]);
            $models[] = $model;
        }

        return new InternalResult(["models" => $models], InternalResult::SUCCESS);
    }

    public function save(AbstractModel $model): void
    {
        $table_name = $model->getTableName();
        $values = $model->getAttributesValues();
        $placeholders = implode(", ", $model->getAttributesPlaceholders());
        $id = $model->getObjectID();
        if ($id == 0) {
            $this->addQuery("INSERT INTO `$table_name` SET $placeholders", $values);
        } else {
            $values[":id"] = $id;
            $this->addQuery("UPDATE `$table_name` SET $placeholders WHERE id = :id", $values);
        }
    }

    public function delete(AbstractModel $model): void
    {
        $table_name = $model->getTableName();
        $id = $model->getObjectID();
        if ($id == 0) {
            $this->addQuery("DELETE FROM `$table_name` WHERE id = :id", [":id" => $model->getObjectID()]);
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
