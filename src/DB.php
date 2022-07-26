<?php

namespace Corviz\Database;

use ClanCats\Hydrahon\Query\Expression;
use ClanCats\Hydrahon\Query\Sql\Func;
use Exception;
use ClanCats\Hydrahon\Query\Sql\Exception as SqlException;
use ClanCats\Hydrahon\Query\Sql\Table;

/**
 * @method static bool beginTransaction()
 * @method static int execute(string $query, array $bindings = [])
 * @method static bool commit()
 * @method static bool rollback()
 * @method static array select(string $query, array $bindings = [])
 */
abstract class DB
{
    /**
     * @var string|null
     */
    private static ?string $defaultConnection = null;

    /**
     * @var Connection[]
     */
    private static array $connections = [];

    /**
     * Fetch a connection
     *
     * @param string|null $connectionName
     *
     * @return Connection
     * @throws Exception
     */
    public static function connection(?string $connectionName = null): Connection
    {
        if (!$connectionName) {
            $connectionName = self::$defaultConnection;

            if (!$connectionName) {
                throw new Exception('Default connection is not set');
            }
        }

        if (!isset(self::$connections[$connectionName])) {
            throw new Exception("Connection not found: $connectionName");
        }

        return self::$connections[$connectionName];
    }

    /**
     * @param string $connectionName
     * @param Connection $connection
     *
     * @return void
     */
    public static function addConnection(string $connectionName, Connection $connection)
    {
        self::$connections[$connectionName] = $connection;

        if (is_null(self::$defaultConnection)) {
            self::$defaultConnection = $connectionName;
        }
    }

    /**
     * @param mixed $value
     * @return Expression
     */
    public static function raw(mixed $value): Expression
    {
        return new Expression($value);
    }

    /**
     * @param $functionName
     * @param ...$args
     *
     * @return Func
     * @throws SqlException
     */
    public static function function($functionName, ...$args): Func
    {
        return new Func($functionName, ...$args);
    }

    /**
     * @param string $tableName
     *
     * @return Table
     * @throws Exception
     */
    public static function table(string $tableName): Table
    {
        return self::connection()->fetchBuilder()->table($tableName);
    }

    /**
     * @param string $name
     * @param array $arguments
     *
     * @return mixed
     * @throws Exception
     */
    public static function __callStatic(string $name, array $arguments)
    {
        $connection = self::connection();

        if (method_exists($connection, $name)) {
            return $connection->$name(...$arguments);
        }

        return null;
    }
}
