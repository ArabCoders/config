<?php
/**
 * This file is part of {@see arabcoders\config} Package.
 *
 * (c) 2013-2016 Abdul.Mohsen B. A. A.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace arabcoders\config;

use arabcoders\config\
{
    Exceptions\ConfigException,
    Interfaces\Config as ConfigInterface
};

/**
 * Config Manager.
 *
 * @author Abdul.Mohsen B. A. A. <admin@arabcoders.org>
 */
class Config implements ConfigInterface
{
    /**
     * @var bool Whether modifications to configuration data are allowed.
     */
    protected $allowModifications;

    /**
     * @var int Number of elements in configuration data.
     */
    protected $count;

    /**
     * @var array Data within the configuration.
     */
    protected $data = [ ];

    /**
     * @var bool Used when unsetting values during iteration to ensure we do not skip the next element.
     */
    protected $skipNextIteration;

    public function __construct( array $data, bool $allowModifications = false, array $options = [ ] )
    {
        $this->allowModifications = (bool) $allowModifications;

        foreach ( $data as $key => $value )
        {
            $this->data[$key] = $value;
            $this->count++;
        }
    }

    public function get( string $key, $default = false, array $options = [ ] )
    {
        return ( array_key_exists( $key, $this->data ) ) ? $this->data[$key] : $default;
    }

    public function getAll( array $options = [ ] )
    {
        return $this->data;
    }

    /**
     * Magic function so that $obj->value will work.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get( $key )
    {
        return $this->get( $key );
    }

    /**
     * Set a value in the config.
     *
     * Only allow setting of a property if $allowModifications  was set to true
     * on construction. Otherwise, throw an exception.
     *
     * @param  string $key
     * @param  mixed  $value
     *
     * @throws ConfigException
     */
    public function __set( $key, $value )
    {
        if ( !$this->allowModifications )
        {
            throw new ConfigException( 'Config is read only' );
        }

        if ( null === $key )
        {
            $this->data[] = $value;
        }
        else
        {
            $this->data[$key] = $value;
        }

        $this->count++;
    }

    /**
     * isset() overloading
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset( $name )
    {
        return isset( $this->data[$name] );
    }

    /**
     * unset() overloading
     *
     * @param  string $name
     *
     * @throws ConfigException
     */
    public function __unset( $name )
    {
        if ( !$this->allowModifications )
        {
            throw new ConfigException( 'Config is read only' );
        }

        if ( isset( $this->data[$name] ) )
        {
            unset( $this->data[$name] );
            $this->count--;
            $this->skipNextIteration = true;
        }
    }

    public function count()
    {
        return $this->count;
    }

    public function current()
    {
        $this->skipNextIteration = false;

        return current( $this->data );
    }

    public function key()
    {
        return key( $this->data );
    }

    public function next()
    {
        if ( $this->skipNextIteration )
        {
            $this->skipNextIteration = false;

            return;
        }

        next( $this->data );
    }

    public function rewind()
    {
        $this->skipNextIteration = false;
        reset( $this->data );
    }

    public function valid()
    {
        return $this->key() !== null;
    }

    public function offsetExists( $offset )
    {
        return $this->__isset( $offset );
    }

    public function offsetGet( $offset )
    {
        return $this->get( $offset );
    }

    public function offsetSet( $offset, $value )
    {
        $this->__set( $offset, $value );
    }

    public function offsetUnset( $offset )
    {
        $this->__unset( $offset );
    }
}