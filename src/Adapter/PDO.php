<?php
/*
 * This file is part of {@see arabcoders\config\} Package.
 *
 * (c) 2013 ArabCoders Ltd.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace arabcoders\config\Adapter;
use arabcoders\config\Interfaces\Adapter as AdapterInterface;

/**
 * MySQL Adapter
 *
 * @package    arabcoders\config
 * @author     Abdul.Mohsen B. A. A. <admin@arabcoders.org>
 */
class MySQL implements AdapterInterface
{
    /**
     * @var array Data.
     */
    private $data;

    /**
     * @var array Table Schema.
     */
    private $schema = [
        'key'   => 'config_name',
        'value' => 'config_value',
    ];

    /**
     * Constructor.
     *
     * @param  \PDO     $pdo
     * @param  string   $table
     * @param  array    $schema
     * @param  array    $options
     * @return void
     */
    public function __construct(\PDO $pdo, $table, array $schema = [], array $options = [])
    {
        if ( !empty($schema) )
            $this->schema = $schema;

        $sql = "SELECT ".$this->schema['key']." as itemKey, ".$this->schema['value']." as itemValue FROM {$table}";

        try {
            $stmt = $pdo->query($sql);
        }catch(\PDOException $e) {
            throw new \RuntimeException( sprintf('Unable to get Data: %s', $e->getMessage() ) );
        }

        while ( $row = $stmt->fetch($pdo::FETCH_ASSOC) )
        {
            $this->data[ $row['itemKey'] ] = $row['itemValue'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fromFile($jsonFile, array $options = [])
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function fromString($string, array $options = [])
    {
        return $this->data;
    }
}