<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class FastTableStats
{
    /**
     * Approximate MySQL/MariaDB row count from information_schema (very fast).
     * Falls back to the exact callback when estimate is unavailable.
     */
    public static function approximateCount(string $table, callable $exactCount): int
    {
        try {
            $connection = DB::connection();
            $driver = $connection->getDriverName();

            if (in_array($driver, ['mysql', 'mariadb'], true)) {
                $dbName = $connection->getDatabaseName();
                $row = $connection->selectOne(
                    'SELECT table_rows FROM information_schema.tables WHERE table_schema = ? AND table_name = ?',
                    [$dbName, $table]
                );

                if ($row && isset($row->TABLE_ROWS) && (int) $row->TABLE_ROWS > 0) {
                    return (int) $row->TABLE_ROWS;
                }
                if ($row && isset($row->table_rows) && (int) $row->table_rows > 0) {
                    return (int) $row->table_rows;
                }
            }
        } catch (\Throwable) {
            // fall through to exact count
        }

        return (int) $exactCount();
    }
}
