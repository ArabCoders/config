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
 * Adapter: YAML
 *
 * @author Abdul.Mohsen B. A. A. <admin@arabcoders.org>
 */
class YAML implements AdapterInterface
{
    /**
     * @var callable YAML decoder callback
     */
    protected $yamlDecoder;

    /**
     * Constructor
     *
     * @param callable $yamlDecoder
     */
    public function __construct( callable $yamlDecoder = null )
    {
        if ( $yamlDecoder !== null )
        {
            $this->setYamlDecoder( $yamlDecoder );
        }
        else
        {
            if ( function_exists( 'yaml_parse' ) )
            {
                $this->setYamlDecoder( 'yaml_parse' );
            }
        }
    }

    /**
     * Set callback for decoding YAML
     *
     * @param  string|callable $yamlDecoder the decoder to set
     *
     * @throws AdapterException if yamlDecoder is not callable.
     *
     * @return YAML
     */
    public function setYamlDecoder( $yamlDecoder )
    {
        if ( !is_callable( $yamlDecoder ) )
        {
            throw new AdapterException( 'Invalid parameter to setYamlDecoder() - must be callable' );
        }

        $this->yamlDecoder = $yamlDecoder;

        return $this;
    }

    /**
     * Get callback for YAML Decoding.
     *
     * @throws AdapterException if yamlDecoder is not callable.
     *
     * @return callable
     */
    public function getYamlDecoder()
    {
        if ( !is_callable( $this->yamlDecoder ) )
        {
            throw new AdapterException( 'Invalid parameter to setYamlDecoder() - must be callable' );
        }

        return $this->yamlDecoder;
    }

    public function fromFile( string $filename, array $options = [ ] ): array
    {
        if ( !is_file( $filename ) || !is_readable( $filename ) )
        {
            throw new AdapterException( sprintf( "File '%s' doesn't exist or not readable", $filename ) );
        }

        if ( null === $this->getYamlDecoder() )
        {
            throw new AdapterException( "You didn't specify a Yaml decoder callback" );
        }

        $yaml = $this->getYamlDecoder();

        $config = $yaml( file_get_contents( $filename ) );

        if ( null === $config )
        {
            throw new AdapterException( "Error parsing YAML data" );
        }

        return $this->process( $config );
    }

    public function fromString( string $string, array $options = [ ] ): array
    {
        if ( empty( $string ) )
        {
            throw new AdapterException( 'String was empty.' );
        }

        if ( null === $this->getYamlDecoder() )
        {
            throw new AdapterException( "You didn't specify a Yaml decoder callback" );
        }

        $yaml = $this->getYamlDecoder();

        $config = $yaml( $string );

        if ( null === $config )
        {
            throw new AdapterException( "Error parsing YAML data" );
        }

        return $this->process( $config );
    }

    /**
     * Process array,
     *
     * @param array $data
     *
     * @return array
     */
    protected function process( array $data )
    {
        foreach ( $data as $key => $value )
        {
            if ( is_array( $value ) )
            {
                $data[$key] = $this->process( $value );
            }
        }

        return $data;
    }
}