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

/**
 * Interface a class must implement to become an EventListener.
 */
interface EventListenerInterface extends EventListenerFactoryInterface
{
    /**
     * Method which is called by the Observer the EventListener is attached to.
     *
     * @param mixed $payload
     *
     * @return mixed|void
     */
    public function process($payload);
}
