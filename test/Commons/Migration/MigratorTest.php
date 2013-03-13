<?php

/**
 * =============================================================================
 * @file        Commons/Migration/MigratorTest.php
 * @author     Lukasz Cepowski <lukasz@cepowski.com>
 * 
 * @copyright  PHP Commons
 *              Copyright (C) 2009-2012 HellWorx Software
 *              All rights reserved.
 *              www.hellworx.com
 * =============================================================================
 */

namespace Commons\Migration;

use Mock\Migration\Persistence;

use Mock\Migration\Versioner;

class MigratorTest extends \PHPUnit_Framework_TestCase
{
    
    public function testSetHasGetRemoveMap()
    {
        $migrator = new Migrator();
        $this->assertFalse($migrator->hasMap('xxx'));
        $m = $migrator->setMap('xxx', new Map());
        $this->assertTrue($m instanceof Migrator);
        $this->assertTrue($migrator->hasMap('xxx'));
        $m = $migrator->removeMap('xxx');
        $this->assertTrue($m instanceof Migrator);
        $this->assertFalse($migrator->hasMap('xxx'));
    }
    
    public function testSetGetClearMaps()
    {
        $migrator = new Migrator();
        $this->assertEquals(0, count($migrator->getMaps()));
        $m = $migrator->setMaps(array('x' => new Map(), 'y' => new Map()));
        $this->assertTrue($m instanceof Migrator);
        $this->assertEquals(2, count($migrator->getMaps()));
        $m = $migrator->clearMaps();
        $this->assertTrue($m instanceof Migrator);
        $this->assertEquals(0, count($migrator->getMaps()));
    }
    
    public function testUpgrade_AllMigrations()
    {
        $migrator = new Migrator(array(
            'mock' => new Map(array(
                1 => 'Mock\\Migration\\FirstMigration',
                2 => 'Mock\\Migration\\SecondMigration',
                3 => 'Mock\\Migration\\ThirdMigration',
                4 => 'Mock\\Migration\\FourthMigration',
            ))
        ));
        $migrator->setVersioner(new Versioner());

        Persistence::$version = array();
        Persistence::$counter = 0;
        
        $m = $migrator->upgrade();
        $this->assertTrue($m instanceof Migrator);
        // Check if version of the mock map is correct.
        $this->assertEquals(4, Persistence::$version['mock']);
        // Check if all migrations incremented the counter.
        $this->assertEquals(1 + 2 + 4 + 8, Persistence::$counter);
    }
    
    public function testUpgrade_FromSecond()
    {
        $migrator = new Migrator(array(
            'mock' => new Map(array(
                1 => 'Mock\\Migration\\FirstMigration',
                2 => 'Mock\\Migration\\SecondMigration',
                3 => 'Mock\\Migration\\ThirdMigration',
                4 => 'Mock\\Migration\\FourthMigration',
            ))
        ));
        $migrator->setVersioner(new Versioner());

        Persistence::$version = array('mock' => 2);
        Persistence::$counter = 0;
        
        $m = $migrator->upgrade();
        $this->assertTrue($m instanceof Migrator);
        // Check if version of the mock map is correct.
        $this->assertEquals(4, Persistence::$version['mock']);
        // Check if all migrations incremented the counter.
        $this->assertEquals(4 + 8, Persistence::$counter);
    }
    
    public function testUpgrade_FromSecondToThird()
    {
        $migrator = new Migrator(array(
            'mock' => new Map(array(
                1 => 'Mock\\Migration\\FirstMigration',
                2 => 'Mock\\Migration\\SecondMigration',
                3 => 'Mock\\Migration\\ThirdMigration',
                4 => 'Mock\\Migration\\FourthMigration',
            ))
        ));
        $migrator->setVersioner(new Versioner());

        Persistence::$version = array('mock' => 2);
        Persistence::$counter = 0;
        
        $m = $migrator->upgrade(3);
        $this->assertTrue($m instanceof Migrator);
        // Check if version of the mock map is correct.
        $this->assertEquals(3, Persistence::$version['mock']);
        // Check if all migrations incremented the counter.
        $this->assertEquals(4, Persistence::$counter);
    }
    
    public function testUpgrade_NothingToUpgrade()
    {
        $migrator = new Migrator(array(
            'mock' => new Map(array(
                1 => 'Mock\\Migration\\FirstMigration',
                2 => 'Mock\\Migration\\SecondMigration',
                3 => 'Mock\\Migration\\ThirdMigration',
                4 => 'Mock\\Migration\\FourthMigration',
            ))
        ));
        $migrator->setVersioner(new Versioner());

        Persistence::$version = array('mock' => 4);
        Persistence::$counter = 1 + 2 + 4 + 8;
        
        $m = $migrator->upgrade();
        $this->assertTrue($m instanceof Migrator);
        // Check if version of the mock map is correct.
        $this->assertEquals(4, Persistence::$version['mock']);
        // Check if all migrations incremented the counter.
        $this->assertEquals(1 + 2 + 4 + 8, Persistence::$counter);
    }
    
    public function testDowngrade_AllMigrations()
    {
        $migrator = new Migrator(array(
            'mock' => new Map(array(
                1 => 'Mock\\Migration\\FirstMigration',
                2 => 'Mock\\Migration\\SecondMigration',
                3 => 'Mock\\Migration\\ThirdMigration',
                4 => 'Mock\\Migration\\FourthMigration',
            ))
        ));
        $migrator->setVersioner(new Versioner());

        Persistence::$version = array('mock' => 4);
        Persistence::$counter = 1 + 2 + 4 + 8;
        
        $m = $migrator->downgrade();
        $this->assertTrue($m instanceof Migrator);
        // Check if version of the mock map is correct.
        $this->assertEquals(0, Persistence::$version['mock']);
        // Check if all migrations decremented the counter.
        $this->assertEquals(0, Persistence::$counter);
    }
    
    public function testDowngrade_FromThird()
    {
        $migrator = new Migrator(array(
            'mock' => new Map(array(
                1 => 'Mock\\Migration\\FirstMigration',
                2 => 'Mock\\Migration\\SecondMigration',
                3 => 'Mock\\Migration\\ThirdMigration',
                4 => 'Mock\\Migration\\FourthMigration',
            ))
        ));
        $migrator->setVersioner(new Versioner());

        Persistence::$version = array('mock' => 3);
        Persistence::$counter = 1 + 2 + 4;
        
        $m = $migrator->downgrade();
        $this->assertTrue($m instanceof Migrator);
        // Check if version of the mock map is correct.
        $this->assertEquals(0, Persistence::$version['mock']);
        // Check if all migrations decremented the counter.
        $this->assertEquals(0, Persistence::$counter);
    }
    
    public function testDowngrade_FromThirdToSecond()
    {
        $migrator = new Migrator(array(
            'mock' => new Map(array(
                1 => 'Mock\\Migration\\FirstMigration',
                2 => 'Mock\\Migration\\SecondMigration',
                3 => 'Mock\\Migration\\ThirdMigration',
                4 => 'Mock\\Migration\\FourthMigration',
            ))
        ));
        $migrator->setVersioner(new Versioner());

        Persistence::$version = array('mock' => 3);
        Persistence::$counter = 1 + 2 + 4;
        
        $m = $migrator->downgrade(2);
        $this->assertTrue($m instanceof Migrator);
        // Check if version of the mock map is correct.
        $this->assertEquals(2, Persistence::$version['mock']);
        // Check if all migrations decremented the counter.
        $this->assertEquals(1 + 2, Persistence::$counter);
    }
    
    public function testDowngrade_NothingToUpgrade()
    {
        $migrator = new Migrator(array(
            'mock' => new Map(array(
                1 => 'Mock\\Migration\\FirstMigration',
                2 => 'Mock\\Migration\\SecondMigration',
                3 => 'Mock\\Migration\\ThirdMigration',
                4 => 'Mock\\Migration\\FourthMigration',
            ))
        ));
        $migrator->setVersioner(new Versioner());

        Persistence::$version = array();
        Persistence::$counter = 0;
        
        $m = $migrator->downgrade();
        $this->assertTrue($m instanceof Migrator);
        // Check if version of the mock map is correct.
        $this->assertEquals(0, Persistence::$version['mock']);
        // Check if all migrations decremented the counter.
        $this->assertEquals(0, Persistence::$counter);
    }
    
}