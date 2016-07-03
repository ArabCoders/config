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

use \XMLReader;
use arabcoders\config\
{
    Exceptions\AdapterException,
    Interfaces\Adapter as AdapterInterface
};

/**
 * Adapter: XML
 *
 * @author Abdul.Mohsen B. A. A. <admin@arabcoders.org>
 */
class XML implements AdapterInterface
{
    /**
     * @var XMLReader XML Reader instance.
     */
    protected $reader;

    /**
     * @var array Nodes to handle as plain text.
     */
    protected $textNodes = [
        XMLReader::TEXT,
        XMLReader::CDATA,
        XMLReader::WHITESPACE,
        XMLReader::SIGNIFICANT_WHITESPACE
    ];

    /**
     * Constructor.
     *
     * @param array $options
     *
     * @throws AdapterException
     */
    public function __construct( array $options = [ ] )
    {
        if ( !extension_loaded( 'libxml' ) )
        {
            throw new AdapterException( 'The XML Adapter Require the libxml extention to be loaded.' );
        }
    }

    public function fromFile( string $filename, array $options = [ ] ): array
    {
        if ( !is_file( $filename ) || !is_readable( $filename ) )
        {
            throw new AdapterException( sprintf( "File '%s' doesn't exist and/or not readable", $filename ) );
        }

        $this->reader = new XMLReader();

        $this->reader->open( $filename, null, LIBXML_XINCLUDE );

        set_error_handler( function ( $error, $message ) use ( $filename )
        {
            throw new AdapterException( sprintf( 'Error reading XML file "%s": %s', $filename, $message ), $error );
        }, E_WARNING );

        $return = $this->process();

        restore_error_handler();

        return $return;
    }

    public function fromString( string $string, array $options = [ ] ): array
    {
        if ( empty( $string ) )
        {
            return [ ];
        }

        $this->reader = new XMLReader();

        $this->reader->XML( $string, null, LIBXML_XINCLUDE );

        set_error_handler( function ( $error, $message )
        {
            throw new AdapterException( sprintf( 'Error reading XML string: %s', $message ), $error );
        }, E_WARNING );

        $return = $this->process();

        restore_error_handler();

        return $return;
    }

    /**
     * Process data from the created XMLReader.
     *
     * @return array
     */
    protected function process()
    {
        return $this->processNextElement();
    }

    /**
     * Process the next inner element.
     *
     * @return mixed
     */
    protected function processNextElement()
    {
        $children = [ ];
        $text     = '';

        while ( $this->reader->read() )
        {
            if ( $this->reader->nodeType === XMLReader::ELEMENT )
            {
                if ( 0 === $this->reader->depth )
                {
                    return $this->processNextElement();
                }

                $attributes = $this->getAttributes();
                $name       = $this->reader->name;

                if ( $this->reader->isEmptyElement )
                {
                    $child = [ ];
                }
                else
                {
                    $child = $this->processNextElement();
                }

                if ( $attributes )
                {
                    if ( !is_array( $child ) )
                    {
                        $child = [ ];
                    }

                    $child = array_merge( $child, $attributes );
                }

                if ( isset( $children[$name] ) )
                {
                    if ( !is_array( $children[$name] ) || !array_key_exists( 0, $children[$name] ) )
                    {
                        $children[$name] = [ $children[$name] ];
                    }

                    $children[$name][] = $child;
                }
                else
                {
                    $children[$name] = $child;
                }
            }
            elseif ( $this->reader->nodeType === XMLReader::END_ELEMENT )
            {
                break;
            }
            elseif ( in_array( $this->reader->nodeType, $this->textNodes ) )
            {
                $text .= $this->reader->value;
            }
        }

        return $children ? : $text;
    }

    /**
     * Get all attributes on the current node.
     *
     * @return array
     */
    protected function getAttributes()
    {
        $attributes = [ ];

        if ( !$this->reader->hasAttributes )
        {
            while ( $this->reader->moveToNextAttribute() )
            {
                $attributes[$this->reader->localName] = $this->reader->value;
            }

            $this->reader->moveToElement();
        }

        return $attributes;
    }
}