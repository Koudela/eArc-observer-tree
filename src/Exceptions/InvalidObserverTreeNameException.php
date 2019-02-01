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

namespace eArc\ObserverTree\Exceptions;

use Throwable;

/**
 * Gets thrown if a name does not belong to any observer tree.
 */
class InvalidObserverTreeNameException extends \InvalidArgumentException
{
    public function __construct(string $treeName = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct("Name `$treeName` does not point to an observer tree", $code, $previous);
    }
}
