<?php

namespace Tactics\DateTime\Enum\DateTimePlus;

/**
 * StorageFormat
 *
 */
enum StorageFormat: string
{
    case MYSQL_TIME = 'MYSQL_TIME';

    case MYSQL_DATE = 'MYSQL_DATE';

    case MYSQL_DATETIME = 'MYSQL_DATETIME';

    case MYSQL_YEAR = 'MYSQL_YEAR';

    case SQL_SERVER_TIME = 'SQL_SERVER_TIME';

    case SQL_SERVER_TIME_NANO = 'SQL_SERVER_TIME_NANO';

    case SQL_SERVER_DATE = 'SQL_SERVER_DATE';

    case SQL_SERVER_SMALL_DATETIME = 'SQL_SERVER_SMALL_DATETIME';

    case SQL_SERVER_DATETIME = 'SQL_SERVER_DATETIME';

    case SQL_SERVER_DATETIME_2 = 'SQL_SERVER_DATETIME_2';

    /** Preferred Sql Server storage */
    case SQL_SERVER_DATETIME_OFFSET = 'SQL_SERVER_DATETIME_OFFSET';

    case ORACLE_DATE = 'ORACLE_DATE';

    case ORACLE_TIMESTAMP = 'ORACLE_TIMESTAMP';

    case ORACLE_TIMESTAMP_WITH_TIMEZONE = 'ORACLE_TIMESTAMP_WITH_TIMEZONE';


    public function pattern(): string
    {
        return match ($this) {
            self::MYSQL_TIME,
            self::SQL_SERVER_TIME => 'H:i:s',
            self::SQL_SERVER_TIME_NANO => 'H:i:s.u',
            self::MYSQL_DATE,
            self::SQL_SERVER_DATE,
            self::ORACLE_DATE => 'Y-m-d',
            self::MYSQL_DATETIME,
            self::SQL_SERVER_SMALL_DATETIME => 'Y-m-d H:i:s',
            self::SQL_SERVER_DATETIME => 'Y-m-d H:i:s.v',
            self::SQL_SERVER_DATETIME_2,
            self::ORACLE_TIMESTAMP => 'Y-m-d H:i:s.u',
            self::SQL_SERVER_DATETIME_OFFSET => 'Y-m-d H:i:s.uP',
            self::ORACLE_TIMESTAMP_WITH_TIMEZONE => 'Y-m-d H:i:s.u P',
            self::MYSQL_YEAR => 'Y',
        };
    }
}
