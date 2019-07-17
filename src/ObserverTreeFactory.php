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

use eArc\Observer\Exception\NoValidListenerException;
use eArc\Observer\Interfaces\ListenerInterface;
use eArc\ObserverTree\Exceptions\InvalidObserverTreeNameException;
use eArc\ObserverTree\Interfaces\ObserverTreeFactoryInterface;
use eArc\ObserverTree\Interfaces\ObserverTreeInterface;
use eArc\Tree\Exceptions\DoesNotBelongToParentException;
use eArc\Tree\Exceptions\NodeOverwriteException;
use eArc\Tree\Exceptions\NotFoundException;
use eArc\Tree\Exceptions\NotPartOfTreeException;
use Psr\Container\ContainerInterface;

/**
 * Factory building observer trees from the file system.
 */
class ObserverTreeFactory implements ObserverTreeFactoryInterface
{
    /** @var ObserverNode[] */
    protected $instances = [];

    /** @var array */
    protected $definitionPointer;

    /** @var string */
    protected $primaryDirectory;

    /** @var string[] */
    protected $ignoredListenerClassNames;

    /** @var ContainerInterface|null */
    protected $container;

    /**
     * @param string                  $absolutePathToDirectoryOfObserverTrees
     * @param string                  $namespaceOfDirectoryOfObserverTrees
     * @param array                   $extends
     * @param array                   $ignoredListenerClassNames
     * @param ContainerInterface|null $container
     */
    public function __construct(
        string $absolutePathToDirectoryOfObserverTrees,
        string $namespaceOfDirectoryOfObserverTrees,
        array $extends = [],
        array $ignoredListenerClassNames = [],
        ?ContainerInterface $container = null
    ) {
        $this->primaryDirectory = $absolutePathToDirectoryOfObserverTrees;
        $this->definitionPointer = $extends;
        $this->definitionPointer[] = [
            $absolutePathToDirectoryOfObserverTrees,
            $namespaceOfDirectoryOfObserverTrees
        ];
        $this->ignoredListenerClassNames = $ignoredListenerClassNames;
        $this->container = $container;
    }

    /**
     * @inheritdoc
     *
     * @throws NoValidListenerException
     * @throws DoesNotBelongToParentException
     * @throws NodeOverwriteException
     * @throws NotFoundException
     * @throws NotPartOfTreeException
     */
    public function get(string $treeName): ObserverTreeInterface
    {
        if (!isset($this->instances[$treeName]))
        {
            chdir($this->primaryDirectory);

            if (!is_dir($treeName))
            {
                throw new InvalidObserverTreeNameException($treeName);
            }

            $this->instances[$treeName] = $this->buildTree($treeName);
        }

        return $this->instances[$treeName];
    }

    /**
     * Build the observer tree and return the root observer.
     *
     * @param string $treeName
     *
     * @return ObserverNode
     *
     * @throws NoValidListenerException
     * @throws DoesNotBelongToParentException
     * @throws NodeOverwriteException
     * @throws NotFoundException
     * @throws NotPartOfTreeException
     */
    protected function buildTree(string $treeName): ObserverNode
    {
        $tree = new ObserverNode($this->container, null, $treeName);

        foreach($this->definitionPointer as list($rootDir, $rootNamespace))
        {
            chdir($rootDir);

            if (is_dir($treeName))
            {
                $this->processDir($rootNamespace, $treeName, $tree);
            }
        }

        return $tree;
    }

    /**
     * Add the children to the node.
     *
     * @param string $namespace
     * @param string $nodeName
     * @param ObserverNode $node
     *
     * @throws NoValidListenerException
     * @throws DoesNotBelongToParentException
     * @throws NodeOverwriteException
     * @throws NotFoundException
     * @throws NotPartOfTreeException
     */
    protected function processDir(string $namespace, string $nodeName, ObserverNode $node): void
    {
        chdir($nodeName);
        $namespace .= '\\' . $nodeName;

        foreach (scandir('.', SCANDIR_SORT_NONE) as $fileName)
        {
            if ('.' === $fileName || '..' === $fileName) {
                continue;
            }

            if (is_dir($fileName))
            {
                $this->processDir(
                    $namespace,
                    $fileName,
                    $node->hasChild($fileName) ? $node->getChild($fileName)
                        : new ObserverNode($this->container, $node, $fileName)
                );
                chdir('..');
                continue;
            }

            $className = $namespace . '\\' . substr($fileName, 0,-4);

            if (!isset($this->ignoredListenerClassNames[$className])
                && is_subclass_of($className, ListenerInterface::class))
            {
                $node->registerListener($className);
            }
        }
    }
}
