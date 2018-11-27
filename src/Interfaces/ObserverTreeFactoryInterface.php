<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/observer-tree
 * @link https://github.com/Koudela/eArc-observer-tree/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\ObserverTree\Interfaces;

use eArc\ObserverTree\Exceptions\InvalidObserverTreeNameException;
use eArc\ObserverTree\Observer;

/**
 * Factory for building and handling observer trees with a common root.
 */
interface ObserverTreeFactoryInterface
{
    /**
     * Get the observer root instance of a composite associated with the
     * $treeName identifier. Each composite is instantiated only once.
     *
     * @param string $treeName
     *
     * @return Observer
     *
     * @throws InvalidObserverTreeNameException
     */
    public function get(string $treeName): Observer;
}
