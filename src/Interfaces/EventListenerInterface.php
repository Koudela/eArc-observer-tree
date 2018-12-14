<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/event-tree
 * @link https://github.com/Koudela/eArc-observer-tree/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\ObserverTree\Interfaces;

/**
 * Interface a class must implement to become an EventListener.
 */
interface EventListenerInterface
{
    /**
     * Method which is called by the Observer the EventListener is attached to.
     *
     * @param mixed $payload
     *
     * @return mixed
     */
    public function process($payload);
}
