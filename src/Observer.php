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

namespace eArc\ObserverTree;

use eArc\ObserverTree\Interfaces\EventListenerFactoryInterface;
use eArc\Tree\Node;
use Psr\Container\ContainerInterface;

/**
 * Observer defines the listenable nature of the composite classes
 */
class Observer extends Node
{
    const CALL_LISTENER_BREAK = 1;
    const CALL_LISTENER_CONTINUE = 2;

    /** @var array */
    protected $initialisedListener = [];

    /** @var array */
    protected $listener = [];

    /** @var array */
    protected $type = [];

    /**
     * Calls all registered listeners that match the type sorted by their
     * patience until either all are called or the filter returns a
     * Observer::CALL_LISTENER_BREAK.
     *
     * @param $payload
     * @param int|null $type
     * @param callable|null $preInitFilter
     * @param callable|null $preCallFilter
     * @param callable|null $postCallFilter
     * @param ContainerInterface|null $container
     */
    public function callListeners(
        $payload,
        ?int $type = null,
        ?callable $preInitFilter = null,
        ?callable $preCallFilter = null,
        ?callable $postCallFilter = null,
        ?ContainerInterface $container = null
    ): void
    {
        asort($this->listener, SORT_NUMERIC);

        foreach($this->listener as $FQN => $patience)
        {
            if (null !== $type && 0 === ($this->type[$FQN] & $type)) {
                continue;
            }

            if ($preInitFilter && $return = $preInitFilter($FQN, $this->type[$FQN], $patience))
            {
                if ($return === self::CALL_LISTENER_BREAK) {
                    break;
                }

                if ($return === self::CALL_LISTENER_CONTINUE) {
                    continue;
                }
            }

            $listener = $this->getListener($container, $FQN);

            if ($preCallFilter && $return = $preCallFilter($listener))
            {
                if ($return === self::CALL_LISTENER_BREAK) {
                    break;
                }

                if ($return === self::CALL_LISTENER_CONTINUE) {
                    continue;
                }
            }

            if (method_exists($listener, 'process')) {
                $result = $listener->process($payload);

                if ($postCallFilter && $return = $postCallFilter($result, $listener))
                {
                    if ($return === self::CALL_LISTENER_BREAK) {
                        break;
                    }
                }
            }
        }
    }

    /**
     * Get the listener from the container or if it fails try to build the
     * class. (A class not part of the container is saved to a stack and thus
     * never build a second time by this function.)
     *
     * @param null|ContainerInterface $container
     * @param string $FQN
     *
     * @return EventListenerFactoryInterface
     */
    protected function getListener(?ContainerInterface $container, string $FQN): EventListenerFactoryInterface
    {
        if ($container && $container->has($FQN))
        {
            return $container->get($FQN);
        }

        if (!isset($this->initialisedListener[$FQN]))
        {
            $this->initialisedListener[$FQN] = new $FQN();
        }

        return $this->initialisedListener[$FQN];
    }

    /**
     * Registers a listener by its fully qualified class name or its container
     * name.
     *
     * @param string $FQN
     * @param int $type
     * @param float $patience
     */
    public function registerListener(string $FQN, int $type = 0, float $patience = 0): void
    {
        $this->listener[$FQN] = $patience;
        $this->type[$FQN] = $type;
    }

    /**
     * Unregisters a listener by its fully qualified class name or its container
     * name. (It must be the same name the listener was registered.)
     *
     * @param string $FQN
     */
    public function unregisterListener(string $FQN): void
    {
        unset($this->initialisedListener[$FQN]);
        unset($this->listener[$FQN]);
        unset($this->type[$FQN]);
    }

    /**
     * @inheritdoc
     */
    public function toString(): string
    {
        return $this->root->nodeToString();
    }

    /**
     * @inheritdoc
     */
    protected function nodeToString($indent = ''): string
    {
        $str = $indent . "--{$this->name}--\n";
        $str .= $this->listenersToString($indent . '  ');

        foreach ($this->children as $child)
        {
            /** @var Observer $child */
            $str .= $child->nodeToString($indent . '  ');
        }

        return $str;
    }

    /**
     * Transforms the attached listeners into a string representation.
     *
     * @param string $indent
     *
     * @return string
     */
    protected function listenersToString($indent = '  '): string
    {
        $str = '';

        foreach ($this->listener as $FQN => $patience)
        {
            $str .= $indent . '  ' . $FQN . ': '
                . "{ patience: $patience, type: {$this->type[$FQN]} }\n";
        }

        return $str;
    }
}
