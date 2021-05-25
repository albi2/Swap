<?php

namespace app\core;

// Every model must have these
/**
 * findOne(condition)
 * findAll(condition)
 * updateOne()
 * updateAll()
 */
abstract class DbModel extends Model
{
    abstract public  static function tableName(): string;

    abstract public static function attributes(): array;

    abstract public static function primaryKey(): string;

    public function save()
    {
        $tableName = $this->tableName();
        $attributes = $this->attributes();
        $params = array_map(fn($attr) => ":$attr", $attributes);
        $statement = self::prepare("INSERT INTO $tableName(".implode(',', $attributes).") 
                VALUES(".implode(',', $params).")");
        
        foreach($attributes as $attribute){
            $statement->bindValue(":$attribute", $this->{$attribute});            
        }
       
        $statement->execute();
        return true;
    }

    public static function findOne($where) {
        $tableName = static::tableName();
        $attributes = array_keys($where);
        $sql = implode("AND ",array_map(fn($attr) => "$attr = :$attr", $attributes));
        // SELECT * FROM $tableName WHERE email = :email AND firstname = :firstname
        $statement = self::prepare("SELECT * from $tableName WHERE $sql LIMIT 1");
        foreach($where as $key => $item) {
            $statement->bindValue(":$key", $item);
        }
        $statement->execute();

        // Kthe objekt sipas tipit te klases
        return $statement->fetchObject(static::class);
    }

    public static function prepare($sql)
    {
        return Application::$app->db->pdo->prepare($sql);
    }
}