<?php

namespace DbalUtil\Connection\Ramsey\Uuid;

use DbalUtil\Connection\ConnectionAbstractTrait;
use Ramsey\Uuid\Uuid;


trait QueryTrait
{
    use ConnectionAbstractTrait;

    public function insert_uuid4($table, array $insert) { // TODO: (SECURITY) assert $insert is an array DONE
        // TODO: This name shoud be hidden from high level api and replaced by just "insert" to hide implementation details
        //^ Humpf Default should may be return (uu)id
        $insert['uuid'] = Uuid::uuid4();
        $this->getConnection()->insert($table, $insert);
        // The construct with the array triggers a prepared statement
        // dbal, not in dbo but easy
    }

    public function namespace_insert($table, array $insert, $namespace, $text, $uuid_field=NULL) { // TODO: (SECURITY) assert $insert is an array DONE
        if (NULL == $uuid_field):
            $uuid_field='uuid';
        endif;
        $conn = $this->getConnection();
        $qb = $conn->createQueryBuilder();
        // $conn->insert($table, $insert);
        // The construct with the array triggers a prepared statement
        $conn->executeUpdate('INSERT INTO ' . $table . ' (' . $uuid_field . ', ' . implode(',', array_keys($insert)) . ') ' .
            // "VALUES (uuid_generate_v5(" . $qb->createPositionalParameter($namespace) . "::uuid, " . $qb->createPositionalParameter($text) . ")," . 
            'VALUES (?,' . 
                implode(',', array_map([$qb, 'createPositionalParameter'], array_values($insert))) . ')',
            array_merge([Uuid::uuid5($namespace, $text)], array_values($insert))
        ); // TODO finger crossed everything keeps in the same-right order
        //^ The second argument of uuid_generate_v5 cannot be of uuid type, could it be of a binary type of the same size as uuid?
        //^ $conn->getDatabasePlatform()->quoteStringLiteral($namespace) => Notice: Undefined property: Doctrine\DBAL\Connection::$getDatabasePlatform
        //^ postgres$ psql --dbname=...
        //^ # CREATE EXTENSION "uuid-ossp";
        //^ https://packages.debian.org/en/postgresql-contrib
        // SECURITY TODO: Prepared statement DONE
        // TODO: be sure array_keys and array_values are in the same order.
        // TODOUUID: DONE
        // dbal, doable in dbo
    }

    public function insert_returning_uuid($table, array $insert, $uuidKey='uuid') {
        $uuidValue = Uuid::uuid4();
        $insert[$uuidKey] = $uuidValue;
        $this->getConnection()->insert($table, $insert);
        return [$uuidKey => $uuidValue]; // to return the same thing as PostgreSQL "RETURNING"
    }

    public function insert_url_returning_uuid($table, array $insert, $uuidKey='uuid') {
        // "url" name could be a parameter
        $uuidValue = Uuid::uuid5('6ba7b811-9dad-11d1-80b4-00c04fd430c8', $insert['url']);
        $insert[$uuidKey] = $uuidValue;
        $this->getConnection()->insert($table, $insert);
        return [$uuidKey => $uuidValue]; // to return the same thing as PostgreSQL "RETURNING"
    }

    public function insert_default_values_returning_uuid($table, $uuidKey='uuid') { /// TODO: id in parameter
        $insert = [$uuidKey => Uuid::uuid4()];
        $this->getConnection()->insert($table, $insert);
        return $insert; // to return the same thing as PostgreSQL "RETURNING"
    }
}

// TODO: id in connection or table object to be done
