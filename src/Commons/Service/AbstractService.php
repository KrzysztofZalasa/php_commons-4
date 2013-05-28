<?php

/**
 * =============================================================================
 * @file        Commons/Service/AbstractService.php
 * @author     Lukasz Cepowski <lukasz@cepowski.com>
 *
 * @copyright  PHP Commons
 *              Copyright (C) 2009-2012 HellWorx Software
 *              All rights reserved.
 *              www.hellworx.com
 * =============================================================================
 */

namespace Commons\Service;

use Commons\Container\TraversableContainer;

abstract class AbstractService implements ServiceInterface
{
    
    protected $_config;
    
    /**
     * Set service config.
     * @see \Commons\Service\ServiceInterface::setConfig()
     */
    public function setConfig(TraversableContainer $config)
    {
        $this->_config = $config;
        return $this;
    }
    
    /**
     * Get service config.
     * @see \Commons\Service\ServiceInterface::getConfig()
     */
    public function getConfig()
    {
        return $this->_config;
    }
    
}