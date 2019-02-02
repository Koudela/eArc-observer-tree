<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 * observer composite component
 *
 * @package earc/observer-tree
 * @link https://github.com/Koudela/eArc-observer-tree/
 * @copyright Copyright (c) 2018-2019 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\ObserverTree\Interfaces;

use eArc\Observer\Interfaces\ObserverInterface;
use eArc\Tree\Interfaces\NodeInterface;

interface ObserverTreeInterface extends NodeInterface, ObserverInterface
{
}
