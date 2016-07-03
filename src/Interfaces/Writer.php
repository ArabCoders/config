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
 * Writer Interface.
 *
 * @author Abdul.Mohsen B. A. A. <admin@arabcoders.org>
 */
interface Writer
{
    /**
     * Get Config From String
     *
     * @param string $string
     * @param array  $options
     *
     * @return array
     */
    public function toString( string $string, array $options = [ ] );

    /**
     * Get Config From File.
     *
     * @param string $file
     * @param array  $options
     *
     * @return array
     */
    public function toFile( string $file, array $options = [ ] );
}