<?php

/*
 * This file is part of the wordpress functional test package.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dayax\wordpress\tests;

use \dayax\wordpress\Client;

/**
 * Description of ClientTest
 *
 * @author Anthonius Munthi <me@itstoni.com>
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function testShouldCreateRequest()
    {        
        $c = new Client();
        $c->request('GET', 'http://localhost:9000/');
        $this->assertContains('WordPress Test',$c->getCrawler()->filter('body')->text());
        
        $c->request('GET', 'http://localhost:9000/hello-world');        
        $this->assertContains('Hello world',$c->getCrawler()->filter('body')->text());
        
        $this->assertTrue(is_single());
        
        $c->request('GET', 'http://localhost:9000/topics/uncategorized');
        $this->assertTrue(is_category());
    }
    
    public function testShouldCreate404Status()
    {
        $c = new Client();        
        $c->request('GET', 'http://localhost:9000/foo');
        $this->assertTrue(is_404());        
        $this->assertEquals(404,$c->getResponse()->getStatus());        
        $this->assertContains('This is somewhat embarrassing, isn’t it?', $c->getCrawler()->filter('body')->text());
    }
}
