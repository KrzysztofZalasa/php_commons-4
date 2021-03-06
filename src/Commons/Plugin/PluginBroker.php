<?php

/**
 * =============================================================================
 * @file       Commons/Plugin/PluginBroker.php
 * @author     Lukasz Cepowski <lukasz@cepowski.com>
 * 
 * @copyright  PHP Commons
 *             Copyright (C) 2009-2013 PHP Commons Contributors
 *             All rights reserved.
 *             www.phpcommons.com
 * =============================================================================
 */

namespace Commons\Plugin;

use Commons\Callback\Callback;
use Commons\Http\Request;

class PluginBroker
{
    
    protected $_namespaces = array();
    protected $_instances = array();
    
    /**
     * Add namespace.
     * @param string $namespace
     * @return \Commons\Plugin\PluginBroker
     */
    public function addNamespace($namespace)
    {
        $namespace = trim($namespace, '\\');
        $this->_namespaces[$namespace] = $namespace;
        return $this;
    }
    
    /**
     * Has namespace.
     * @param string $namespace
     * @return boolean
     */
    public function hasNamespace($namespace)
    {
        return isset($this->_namespaces[trim($namespace, '\\')]);
    }
    
    /**
     * Remove namespace.
     * @param string $namespace
     * @return \Commons\Plugin\PluginBroker
     */
    public function removeNamespace($namespace)
    {
        unset($this->_namespaces[trim($namespace, '\\')]);
        return $this;
    }
    
    /**
     * Set namespaces.
     * @param array<string> $namespaces
     * @return \Commons\Plugin\PluginBroker
     */
    public function setNamespaces(array $namespaces)
    {
        $this->_namespaces = array();
        foreach ($namespaces as $namespace) {
            $this->addNamespace($namespace);
        }
        return $this;
    }
    
    /**
     * Get namespaces.
     * @return array<string>
     */
    public function getNamespaces() 
    {
        return $this->_namespaces;
    }
    
    /**
     * Add plugin instance.
     * @param string $name
     * @param PluginInterface $instance
     * @return \Commons\Plugin\PluginBroker
     */
    public function addPlugin($name, PluginInterface $instance)
    {
        $this->_instances[$name] = $instance;
        return $this;
    }
    
    /**
     * Has plugin instance?
     * @param string $name
     * @return boolean
     */
    public function hasPlugin($name)
    {
        return isset($this->_instances[$name]);
    }
    
    /**
     * Get or create plugin instance.
     * @param string $name
     * @throws Exception
     * @return \Commons\Plugin\PluginInterface
     */
    public function getPlugin($name)
    {
        if (!$this->hasPlugin($name)) {
            foreach ($this->getNamespaces() as $namespace) {
                $class = '\\'.$namespace.'\\'.ucwords($name).'Plugin';
                if (class_exists($class)) {
                    $this->addPlugin($name, new $class);
                }
            }
        }
        if (!$this->hasPlugin($name)) {
            throw new Exception("Cannot find plugin '{$name}'");
        }
        return $this->_instances[$name];
    }
    
    /**
     * Remove plugin instance.
     * @param string $name
     * @return \Commons\Plugin\PluginBroker
     */
    public function removePlugin($name)
    {
        unset($this->_instances[$name]);
        return $this;
    }

    /**
     * Invoke plugin.
     * @param PluginAwareInterface $invoker
     * @param string $name
     * @param array $args
     * @return mixed
     */
    public function invoke(PluginAwareInterface $invoker, $name, array $args = array())
    {
        $plugin = $this->getPlugin($name);
        $plugin->setInvoker($invoker);
        $callback = new Callback($plugin, $name);
        return $callback->callArray($args);
    }
    
}

