<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Finder\Adapter;

/**
 * Interface for finder engine implementations.
 *
 * @author Jean-François Simon <contact@jfsimon.fr>
 */
abstract class AbstractAdapter implements AdapterInterface
{
    protected $followLinks = false;
    protected $mode        = 0;
    protected $minDepth    = 0;
    protected $maxDepth    = PHP_INT_MAX;
    protected $exclude     = array();
    protected $names       = array();
    protected $notNames    = array();
    protected $contains    = array();
    protected $notContains = array();
    protected $sizes       = array();
    protected $dates       = array();
    protected $filters     = array();
    protected $sort        = false;
    protected $paths       = array();
    protected $notPaths    = array();

    private static $areSupportedAdapters = array();
    private static $areSupportedPaths    = array();

    /**
     * {@inheritDoc}
     *
     * This generic implementation should not need to be overridden in the derived
     * class as it provides a cache layer. The 2 following methods should rather
     * be implemented:
     *
     * @see canBeUsed
     * @see canBeUsedOnPath
     */
    public function isSupported($path)
    {
        $name = $this->getName();
        $canonicalPath = realpath($path);
        $canonicalPath = false === $canonicalPath ? (string) $path : $canonicalPath;

        if (!isset(self::$areSupportedAdapters[$name])) {
            self::$areSupportedAdapters[$name] = $this->canBeUsed();
        }

        if (!self::$areSupportedAdapters[$name]) {
            return false;
        }

        if (!isset(self::$areSupportedPaths[$canonicalPath][$name])) {
            self::$areSupportedPaths[$canonicalPath][$name] = $this->canBeUsedOnPath($canonicalPath);
        }

        return self::$areSupportedPaths[$canonicalPath][$name];
    }

    /**
     * {@inheritdoc}
     */
    public function setFollowLinks($followLinks)
    {
        $this->followLinks = $followLinks;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setDepths(array $depths)
    {
        $this->minDepth = 0;
        $this->maxDepth = PHP_INT_MAX;

        foreach ($depths as $comparator) {
            switch ($comparator->getOperator()) {
                case '>':
                    $this->minDepth = $comparator->getTarget() + 1;
                    break;
                case '>=':
                    $this->minDepth = $comparator->getTarget();
                    break;
                case '<':
                    $this->maxDepth = $comparator->getTarget() - 1;
                    break;
                case '<=':
                    $this->maxDepth = $comparator->getTarget();
                    break;
                default:
                    $this->minDepth = $this->maxDepth = $comparator->getTarget();
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setExclude(array $exclude)
    {
        $this->exclude = $exclude;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setNames(array $names)
    {
        $this->names = $names;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setNotNames(array $notNames)
    {
        $this->notNames = $notNames;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setContains(array $contains)
    {
        $this->contains = $contains;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setNotContains(array $notContains)
    {
        $this->notContains = $notContains;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSizes(array $sizes)
    {
        $this->sizes = $sizes;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setDates(array $dates)
    {
        $this->dates = $dates;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setFilters(array $filters)
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSort($sort)
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPath(array $paths)
    {
        $this->paths = $paths;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setNotPath(array $notPaths)
    {
        $this->notPaths = $notPaths;

        return $this;
    }

    /**
     * Returns whether the adapter is supported in the current environment.
     *
     * @see isSupported
     *
     * @return Boolean Whether the adapter is supported
     */
    abstract protected function canBeUsed();

    /**
     * Returns whether the adapter supports a given path.
     *
     * Some adapters only support resolvable absolute paths like the GNU/BSD
     * find binary adapter. Others adapters might support PHP streams like "ftp://"
     * or "phar://" like the PHP adapter.
     *
     * @param string Path to check
     *
     * @see isSupported
     *
     * @return Boolean Whether the adapter supports the given path
     */
    abstract protected function canBeUsedOnPath($path);
}
