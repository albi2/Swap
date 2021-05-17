<?php

/**
    Class Database
    @package app\controllers
 */

namespace app\core;

class Database {
    
    /**
     * Database constructor
     */
    public \PDO $pdo;

     public function __construct(array $config) {
         $dsn = $config['dsn'] ?? '';
         $user = $config['user'] ?? '';
         $password = $config['password'] ?? '';
         // dsn is domain service name defines port,host, database
        $this->pdo = new \PDO($dsn, $user, $password);
        // On some problem regarding the database interface (this pdo) throws exception
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
     }

     public function applyMigrations()
     {
        $this->createMigrationsTable();
        $appliedMigrations = $this->getAppliedMigrations();                        
        $newMigrations = [];
        $files = scandir(Application::$ROOT_DIR.'/migrations');                        
        $toApplyMigrations = array_diff($files, $appliedMigrations);        
        
        foreach($toApplyMigrations as $migration)
        {
            if($migration === '.' || $migration === '..')
            {
                continue;
            }

            require_once Application::$ROOT_DIR.'/migrations/'.$migration;
            $className = pathinfo($migration, PATHINFO_FILENAME);                                    
            $instance = new $className();
            $this->log("Applying migration $migration");
            $instance->up();
            $this->log("Applied migration $migration");
            $newMigrations[] = $migration;
        }

        if(!empty($newMigrations))
        {
            $this->savedMigrations($newMigrations);            
        }
        else
        {
            $this->log("All migrations applied");
        }
     }
    
     public function createMigrationsTable()
     {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS migrations(
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE = INNODB;");
     }      

     public function getAppliedMigrations()
     {
        $statement = $this->pdo->prepare("SELECT migration FROM migrations");
        $statement->execute();
        //fetch all the migration column values as a single dimentional array
        return $statement->fetchAll(\PDO::FETCH_COLUMN);
     }     

     public function prepare($sql)
     {
         return $this->pdo->prepare($sql);
     }

     public function savedMigrations(array $migrations)
     {
        
        $str = implode(",",array_map(fn($m) => "('$m')", $migrations));        
        $statement = $this->pdo->prepare("INSERT INTO migrations (migration) VALUES
         $str
         ");
        $statement->execute();
     }

     protected function log($message)
     {
         echo '['.date('Y-m-d H:i:s').'] - ' . $message . PHP_EOL;
     }
}