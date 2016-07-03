<?php
/**
 * This file is part of {@see arabcoders\config} Package.
 *
 * (c) 2013-2016 Abdul.Mohsen B. A. A.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace arabcoders\config\Adapter;

use arabcoders\config\
{
    Exceptions\AdapterException,
    Interfaces\Adapter as AdapterInterface
};

/**
 * Adapter: JSON
 *
 * @author Abdul.Mohsen B. A. A. <admin@arabcoders.org>
 */
class JSON implements AdapterInterface
{
    public function fromFile( string $jsonFile, array $options = [ ] ): array
    {
        if ( empty( $jsonFile ) )
        {
            throw new AdapterException( 'No file name specified' );
        }

        if ( !is_readable( $jsonFile ) )
        {
            throw new AdapterException( sprintf( 'Json file: (%s) is not readable.', $jsonFile ) );
        }

        if ( !( $data = json_decode( file_get_contents( $jsonFile ), true ) ) )
        {
            throw new AdapterException( sprintf( 'Unable to parse json file (%s), the reason is "%s"', $jsonFile, json_last_error_msg() ) );
        }

        return $data;
    }

    public function fromString( string $string, array $options = [ ] ): array
    {
        if ( !( $data = json_decode( $string, true ) ) )
        {
            throw new \RuntimeException( sprintf( "Unable to parse json string: '%s'", json_last_error_msg() ) );
        }

        return $data;
    }
}