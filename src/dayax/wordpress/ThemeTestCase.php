<?php

/*
 * This file is part of the {project_name}.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dayax\wordpress;

class ThemeTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \dayax\wordpress\Client
     */
    static $client;
    
    static public function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        if(!is_object(self::$client)){
            self::$client = new Client();            
        }
    }
    
    public function setUp()
    {
        
    }
        
    public function open($uri,$method="GET", array $parameters = array(), array $files = array(), array $server = array(), $content = null, $changeHistory = true)
    {
       $client = self::$client;
       $client->request($method, $uri, $parameters, $files, $server, $content, $changeHistory);
    }   
    
    /**
     * @return \dayax\wordpress\Client
     */
    public function getClient()
    {
        return self::$client;
    }
    
    /**
     * @return \Symfony\Component\BrowserKit\Response
     */
    public function getResponse()
    {
        return self::$client->getResponse();
    }
    
    /**
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    public function getCrawler()
    {
        return self::$client->getCrawler();
    }
    
    /**
     * Assert response status code
     *
     * @param  int $code
     */
    public function assertResponseStatus($code)
    {        
        $actual = self::$client->getStatusCode();
        if($code != $actual){
            $this->throwExpectationFailed(sprintf(
                'Failed asserting that code "%s", actual status code is "%s"', $code, $actual
            ));
        }
        $this->assertEquals($code, $actual);
    }
    
    private function throwExpectationFailed()
    {
        throw new \PHPUnit_Framework_ExpectationFailedException($message);
    }
}