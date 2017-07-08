<?php

namespace DbalUtil\Connection\Ramsey\Uuid;

use DbalUtil\Connection\ConnectionAbstractTrait;
use Ramsey\Uuid\Uuid;


trait QueryTrait
{
    use ConnectionAbstractTrait;

    public function insert_uuid4($table, array $insert) { // TODO: (SECURITY) assert $insert is an array DONE
        // TODO: This name shoud be hidden from high level api and replaced by just "insert" to hide implementation details
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

/*
    public function insert_returning_uuid($table, array $insert) {
        // TODO: (SECURITY) assert $insert is an array DONE
        // TODO: id in parameter
        $conn = $this->getConnection();
        $qb = $conn->createQueryBuilder();
        $insert['uuid'] = Uuid::uuid4();
        //^ TODO if not set
        return $conn->executeQuery(
            'INSERT INTO ' . $table . ' (' . implode(',', array_keys($insert)) . ') ' .
                'VALUES (' . implode(',', array_map([$qb, 'createPositionalParameter'], array_values($insert))) . ') RETURNING uuid',
            array_values($insert) // TODO finger crossed that everything keeps in the same order.
        )->fetchAll()[0]; // TODO ?
        // SECURITY TODO: Prepared statement DONE
        // TODO: be sure array_keys and array_values are in the same order.
        // Postgres returning dependency could be removed
        // dbal, doable in dbo
    }
*/
/*
    public function insert_url_returning_uuid($table, array $insert) {
        // TODO: (SECURITY) assert $insert is an array DONE
        // TODO: id in parameter
        $conn = $this->getConnection();
        $qb = $conn->createQueryBuilder();
        // return $conn->executeQuery('INSERT INTO ' . $table . ' (uuid, ' . implode(',', array_keys($insert)) . ') ' .
        //     'VALUES (uuid_generate_v5(uuid_ns_url(), ' . $qb->createNamedParameter($insert['url']) . '),' . 
        //     implode(',', array_map([$qb, 'createNamedParameter'], array_values($insert))) . ') RETURNING uuid')->fetchAll()[0]; // TODO ?
        // $insert_uuid = 'uuid_generate_v5(uuid_ns_url(), ' . $qb->createNamedParameter($insert['url']) . ')'; // TODO use samed named parameter as for 'url'
        $insert_uuid = Uuid::uuid5('6ba7b811-9dad-11d1-80b4-00c04fd430c8', $insert['url']);
        $insert_parameters = array_map([$qb, 'createNamedParameter'], $insert); // to check
        $insert_parameters['uuid'] = $qb->createNamedParameter($insert_uuid);
        // dump($insert);
        // dump($insert_parameters);
        // dump($qb
        //         ->insert($table)
        //         ->values($insert_parameters)
        //         ->getSQL()
        // );
        // dump($qb->getParameters());
        return $conn->executeQuery(
            $qb
                ->insert($table)
                ->values($insert_parameters)
                ->getSQL() . ' RETURNING uuid',
            $qb->getParameters()
        )->fetchAll()[0]; // TODO ?
        //^ would works in Knex.js not DBAL
        //^ would also works with php7 https://phppackages.org/p/opulence/querybuilders
        //^ postgres$ psql --dbname=...
        //^ # CREATE EXTENSION "uuid-ossp";
        //^ https://packages.debian.org/en/postgresql-contrib
        // SECURITY TODO: Prepared statement DONE
        // TODO: be sure array_keys and array_values are in the same order.
        // TODOUUID done
        // Postgres returning dependency could be removed
        // dbal, doable in dbo
    }
*/

    public function insert_default_values_returning_uuid($table, $uuidKey='uuid') { /// TODO: id in parameter
///
        // return $conn->executeQuery('INSERT INTO ' . $table . ' DEFAULT VALUES RETURNING uuid')->fetchAll()[0]; // TODO ?
        $uuidValue = Uuid::uuid4();
        // $this->getConnection()->executeQuery('INSERT INTO ' . $table . ' (uuid) VALUES (?)', [$uuid])->fetchAll()[0]; // TODO ?
        $this->getConnection()->insert($table, [$uuidKey => $uuidValue]); // TODO: Error Management?
        return [$uuidKey => $uuidValue]; // to return the same thing as PostgreSQL "RETURNING"
///
        // https://stackoverflow.com/questions/32048634/how-to-get-the-value-of-an-update-returning-query-in-postgresql-in-doctrine
        // Postgres returning dependency could be removed
        // dbal, doable in dbo
    }
}
