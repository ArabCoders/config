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
 * Adapter: INI
 *
 * @author Abdul.Mohsen B. A. A. <admin@arabcoders.org>
 */
class INI implements AdapterInterface
{
    /**
     * @var string Separator for nesting levels of configuration data identifiers.
     */
    protected $nestSeparator = '.';

    /**
     * Set nest separator.
     *
     * @param string $separator
     *
     * @return INI
     */
    public function setNestSeparator( string $separator ): INI
    {
        $this->nestSeparator = $separator;

        return $this;
    }

    /**
     * Get nest separator.
     *
     * @return string
     */
    public function getNestSeparator(): string
    {
        return $this->nestSeparator;
    }

    public function fromFile( string $filename, array $options = [ ] ): array
    {
        if ( !is_file( $filename ) || !is_readable( $filename ) )
        {
            throw new AdapterException( sprintf( "INI File '%s' doesn't exist and/or not readable", $filename ) );
        }

        set_error_handler( function ( $error, $message ) use ( $filename )
        {
            throw new AdapterException( sprintf( "Error reading INI file '%s' : %s", $filename, $message ), $error );
        }, E_WARNING );

        $ini = parse_ini_file( $filename, true );

        restore_error_handler();

        return $this->process( $ini );
    }

    public function fromString( string $string, array $options = [ ] ): array
    {
        if ( empty( $string ) )
        {
            return [ ];
        }

        set_error_handler( function ( $error, $message )
        {
            throw new AdapterException( sprintf( 'Error reading INI string: %s', $message ), $error );
        }, E_WARNING );

        $ini = parse_ini_string( $string, true );

        restore_error_handler();

        return $this->process( $ini );
    }

    /**
     * Process data from the parsed ini file.
     *
     * @param  array $data
     *
     * @return array
     */
    protected function process( array $data ): array
    {
        $config = [ ];

        foreach ( $data as $section => $value )
        {
            if ( is_array( $value ) )
            {
                if ( strpos( $section, $this->nestSeparator ) !== false )
                {
                    $sections = explode( $this->nestSeparator, $section );

                    $config = array_merge_recursive( $config, $this->buildNestedSection( $sections, $value ) );
                }
                else
                {
                    $config[$section] = $this->processSection( $value );
                }
            }
            else
            {
                $this->processKey( $section, $value, $config );
            }
        }

        return $config;
    }

    /**
     * Process a nested section
     *
     * @param array $sections
     * @param mixed $value
     *
     * @return array
     */
    private function buildNestedSection( $sections, $value ): array
    {
        if ( 0 === count( $sections ) )
        {
            return $this->processSection( $value );
        }

        $nestedSection = [ ];

        $first = array_shift( $sections );

        $nestedSection[$first] = $this->buildNestedSection( $sections, $value );

        return $nestedSection;
    }

    /**
     * Process a section.
     *
     * @param  array $section
     *
     * @return array
     */
    protected function processSection( array $section ): array
    {
        $config = [ ];

        foreach ( $section as $key => $value )
        {
            $this->processKey( $key, $value, $config );
        }

        return $config;
    }

    /**
     * Process a key.
     *
     * @param string $key
     * @param string $value
     * @param array  $config
     *
     * @throws AdapterException
     *
     * @return array
     */
    protected function processKey( $key, $value, array &$config ): array
    {
        if ( false !== strpos( $key, $this->nestSeparator ) )
        {
            $pieces = explode( $this->nestSeparator, $key, 2 );

            if ( !strlen( $pieces[0] ) || !strlen( $pieces[1] ) )
            {
                throw new AdapterException( sprintf( 'Invalid key "%s"', $key ) );
            }
            elseif ( !isset( $config[$pieces[0]] ) )
            {
                if ( $pieces[0] === '0' && !empty( $config ) )
                {
                    $config = [ $pieces[0] => $config ];
                }
                else
                {
                    $config[$pieces[0]] = [ ];
                }
            }
            elseif ( !is_array( $config[$pieces[0]] ) )
            {
                throw new AdapterException( sprintf( 'Cannot create sub-key for "%s", as key already exists', $pieces[0] ) );
            }
            $this->processKey( $pieces[1], $value, $config[$pieces[0]] );
        }
        else
        {
            $config[$key] = $value;
        }
    }
}
