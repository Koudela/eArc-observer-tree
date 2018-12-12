<?php
/**
 * e-Arc Framework - the explicit Architecture Framework
 *
 * @package earc/observer-tree
 * @link https://github.com/Koudela/eArc-observer-tree/
 * @copyright Copyright (c) 2018 Thomas Koudela
 * @license http://opensource.org/licenses/MIT MIT License
 */

namespace eArc\ObserverTree;

use eArc\ObserverTree\Exceptions\InvalidObserverTreeNameException;
use eArc\ObserverTree\Interfaces\EventListenerInterface;
use eArc\ObserverTree\Interfaces\ObserverTreeFactoryInterface;

/**
 * Factory building observer trees from the file system.
 */
class ObserverTreeFactory implements ObserverTreeFactoryInterface
{
    /** @var array */
    protected $trees = [];

    /** @var array */
    protected $definitionPointer;

    /** @var string */
    protected $primaryDirectory;

    /** @var array */
    protected $ignores;

    /**
     * @param string $absolutePathToDirectoryOfObserverTrees
     * @param string $namespaceOfDirectoryOfObserverTrees
     * @param array  $extends
     * @param array  $ignores
     */
    public function __construct(
        string $absolutePathToDirectoryOfObserverTrees,
        string $namespaceOfDirectoryOfObserverTrees,
        array $extends = [],
        array $ignores = []
    ) {
        $this->primaryDirectory = $absolutePathToDirectoryOfObserverTrees;
        $this->definitionPointer = $extends;
        $this->definitionPointer[] = [
            $absolutePathToDirectoryOfObserverTrees,
            $namespaceOfDirectoryOfObserverTrees
        ];
        $this->ignores = $ignores;
    }

    /**
     * @inheritdoc
     */
    public function get(string $treeName): Observer
    {
        if (!isset($this->trees[$treeName]))
        {
            chdir($this->primaryDirectory);

            if (!is_dir($treeName))
            {
                throw new InvalidObserverTreeNameException($treeName);
            }

            $this->trees[$treeName] = $this->buildTree($treeName);
        }

        return $this->trees[$treeName];
    }

    /**
     * Build the observer tree and return the root observer.
     *
     * @param string $treeName
     *
     * @return Observer
     */
    protected function buildTree(string $treeName): Observer
    {
        $tree = new Observer(null, $treeName);

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
     * @param Observer $node
     */
    protected function processDir(string $namespace, string $nodeName, Observer $node): void
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
                    new Observer($node, $fileName)
                );
                chdir('..');
                continue;
            }

            $className = $namespace . '\\' . substr($fileName, 0,-4);

            if (isset($this->ignores[$className]))
            {
                continue;
            }

            if (is_subclass_of($className, EventListenerInterface::class))
            {
                /** @noinspection PhpUndefinedFieldInspection */
                $patience = defined($className . '::EARC_LISTENER_PATIENCE')
                    ? $className::EARC_LISTENER_PATIENCE : 0;

                /** @noinspection PhpUndefinedFieldInspection */
                $type = defined($className . '::EARC_LISTENER_TYPE')
                    ? $className::EARC_LISTENER_TYPE : 0;

                /** @noinspection PhpUndefinedFieldInspection */
                $name = defined($className . '::EARC_LISTENER_CONTAINER_ID')
                    ? $className::EARC_LISTENER_CONTAINER_ID : $className;

                $node->registerListener($name, $type, $patience);
            }
        }
    }
}
