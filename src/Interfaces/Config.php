<?php
/**
 * This file is part of {@see arabcoders\config} Package.
 *
 * (c) 2013-2016 Abdul.Mohsen B. A. A.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace arabcoders\config\Interfaces;

/**
 * Config Interface.
 *
 * @author Abdul.Mohsen B. A. A. <admin@arabcoders.org>
 */
interface Config extends \Countable, \Iterator, \ArrayAccess
{
    /**
     * Constructor.
     *
     * Data is read-only unless $allowModifications is set to true
     *
     * @param array $data
     * @param bool  $allowModification
     * @param array $options
     */
    public function __construct( array $data, bool $allowModification = false, array $options = [ ] );

    /**
     * Get default/modifed Key value
     *
     * @param string $key
     * @param mixed  $default
     * @param array  $options
     *
     * @return mixed
     */
    public function get( string $key, $default = null, array $options = [ ] );

    /**
     * Get All Data as key/value pair.
     *
     * @param array $options
     *
     * @return array
     */
    public function getAll( array $options = [ ] );
}