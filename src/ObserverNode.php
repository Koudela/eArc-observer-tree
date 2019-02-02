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

use eArc\ObserverTree\Interfaces\ObserverTreeInterface;
use eArc\Tree\Interfaces\NodeInterface;
use eArc\Observer\Traits\ObserverTrait;
use eArc\Tree\Traits\NodeTrait;

/**
 * ObserverNode combines the listenable nature of observer with the tree
 * composite nature of node.
 */
class ObserverNode implements ObserverTreeInterface
{
    use ObserverTrait;
    use NodeTrait;

    /**
     * @param NodeInterface|null $parent
     * @param string|null $name
     *
     * @throws \eArc\Tree\Exceptions\DoesNotBelongToParentException
     * @throws \eArc\Tree\Exceptions\NodeOverwriteException
     * @throws \eArc\Tree\Exceptions\NotPartOfTreeException
     */
    public function __construct(?NodeInterface $parent = null, ?string $name = null)
    {
        $this->initNodeTrait($parent, $name);
    }

    /**
     * Transforms the composite into a string representation.
     */
    public function __toString(): string
    {
        return $this->getRoot()->nodeToString();
    }

    /**
     * Transforms the composite into a string representation.
     *
     * @param string $indent
     *
     * @return string
     */
    protected function nodeToString(string $indent = ''): string
    {
        $str = $indent . "--{$this->getName()}--\n";
        $str .= $this->listenersToString($indent . '  ');

        foreach ($this->getChildren() as $child)
        {
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
    protected function listenersToString(string $indent = '  '): string
    {
        $str = '';

        foreach ($this->listenerPatience as $fQCN => $patience)
        {
            /** @noinspection PhpUndefinedMethodInspection */
            $str .= "$indent  $fQCN: "
                . "{ patience: $patience, type: {$fQCN::getTypes()} }\n";
        }

        return $str;
    }
}
