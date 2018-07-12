<?php namespace IXP\Services\Grapher\Graph;

/*
 * Copyright (C) 2009-2016 Internet Neutral Exchange Association Company Limited By Guarantee.
 * All Rights Reserved.
 *
 * This file is part of IXP Manager.
 *
 * IXP Manager is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation, version v2.0 of the License.
 *
 * IXP Manager is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License v2.0
 * along with IXP Manager.  If not, see:
 *
 * http://www.gnu.org/licenses/gpl-2.0.html
 */

use IXP\Services\Grapher;
use IXP\Services\Grapher\{Graph};

use Entities\User as UserEntity;

use Auth;

/**
 * Grapher -> Abstract Graph
 *
 * @author     Barry O'Donovan <barry@islandbridgenetworks.ie>
 * @category   Grapher
 * @package    IXP\Services\Grapher
 * @copyright  Copyright (C) 2009-2016 Internet Neutral Exchange Association Company Limited By Guarantee
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU GPL V2.0
 */
class IXP extends Graph {


    /**
     * Constructor
     * @param Grapher $grapher
     */
    public function __construct( Grapher $grapher ) {
        parent::__construct( $grapher );
    }

    /**
     * Set parameters in bulk from associative array
     *
     * {@inheritDoc}
     *
     * @param array $params
     * @return \IXP\Services\Grapher Fluid interface
     */
    public function setParamsFromArray( array $params ): Graph {
        parent::setParamsFromArray( $params );

        return $this;
    }

    /**
     * The name of a graph (e.g. member name, IXP name, etc)
     * @return string
     */
    public function name(): string {
        return "INEX";
    }

    /**
     * A unique identifier for this 'graph type'
     *
     * E.g. for an IXP, it might be ixpxxx where xxx is the database id
     * @return string
     */
    public function identifier(): string {
        return sprintf( "ixp%03d", 1 );
    }


    /**
     * This function controls access to the graph.
     *
     * {@inheritDoc}
     *
     * For IXP aggregate graphs we pretty much allow complete access.
     *
     * @return bool
     */
    public function authorise(): bool {
        if( Auth::check() && Auth::user()->isSuperUser() ) {
            return $this->allow();
        }

        if( in_array( $this->category(), [ self::CATEGORY_ERRORS, self::CATEGORY_DISCARDS ] ) ) {
            $this->deny();
            return false;
        }

        if( is_numeric( config( 'grapher.access.ixp' ) ) && config( 'grapher.access.ixp' ) == UserEntity::AUTH_PUBLIC ) {
            return $this->allow();
        }

        if( Auth::check() && is_numeric( config( 'grapher.access.ixp' ) ) && Auth::user()->getPrivs() >= config( 'grapher.access.ixp' ) ) {
            return $this->allow();
        }

        $this->deny();
        return false;
    }

    /**
     * Generate a URL to get this graphs 'file' of a given type
     *
     * @param array $overrides Allow standard parameters to be overridden (e.g. category)
     * @return string
     */
    public function url( array $overrides = [] ): string {
        return parent::url( $overrides ) . sprintf("&id=%d",
            isset( $overrides['id']   ) ? $overrides['id']   : 1
        );
    }

    /**
     * Get parameters in bulk as associative array
     *
     * Extends base function
     *
     * @return array $params
     */
    public function getParamsAsArray(): array {
        $p = parent::getParamsAsArray();
        $p['id'] = 1;
        return $p;
    }


}
