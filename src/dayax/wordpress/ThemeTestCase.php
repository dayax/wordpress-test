<?php

/*
 * This file is part of the wordpress functional test package.
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
    
    private function throwExpectationFailed($message)
    {
        throw new \PHPUnit_Framework_ExpectationFailedException($message);
    }
    
    /**
     * Assert response status code
     *
     * @param  int $code
     */
    public function assertResponseStatus($code)
    {
        $actual = $this->getResponse()->getStatus();
        if ($code != $actual) {
            $this->throwExpectationFailed(sprintf(
                'Failed asserting that code "%s", actual status code is "%s"', $code, $actual
            ));
        }
        $this->assertEquals($code, $actual);
    }
    
    /**
     * Assert not response status
     *
     * @param  int $code
     */
    public function assertNotResponseStatus($code)
    {        
        $match = $this->getResponse()->getStatus();
        if ($code == $match) {
            $this->throwExpectationFailed(sprintf(
               'Failed asserting response code was NOT "%s"', $code
            ));
        }
        $this->assertNotEquals($code, $match);
    }
    
    /**
     * Get response header by key
     *
     * @param  string   $header
     * @return mixed    Header content
     */
    protected function getResponseHeader($header)
    {
        $headers = $this->getResponse()->getHeaders();
        
        return isset($headers[$header]) ? $headers[$header]:false;        
    }

    /**
     * Assert response header exists
     *
     * @param  string $header
     */
    public function assertHasResponseHeader($header)
    {        
        $response_header = $this->getResponseHeader($header);
        if (false === $this->getResponseHeader($header)) {
            throw new \PHPUnit_Framework_ExpectationFailedException(sprintf(
                    'Failed asserting response header "%s" found', $header
            ));
        }
        $this->assertNotEquals(false, $response_header);
    }

    /**
     * Assert response header does not exist
     *
     * @param  string $header
     */
    public function assertNotHasResponseHeader($header)
    {        
        $response_header = $this->getResponseHeader($header);

        if (false !== $response_header) {
            $this->throwExpectationFailed(sprintf(
                    'Failed asserting that response header "%s" was not found', $header
            ));
        }

        $this->assertFalse($response_header);
    }

    /**
     * Assert response header exists and contains the given string
     *
     * @param  string $header
     * @param  string $match
     */
    public function assertResponseHeaderContains($header, $match)
    {        
        $response_header = $this->getResponseHeader($header);
        if (!$response_header) {
            $this->throwExpectationFailed(sprintf(
                    'Failed asserting response header, header "%s" do not exists', $header
            ));
        }
        if ($match != $response_header) {
            $this->throwExpectationFailed(sprintf(
                    'Failed asserting that response header for "%s" contains "%s". Actual content is "%s"', $header, $match, $response_header
            ));
        }

        $this->assertEquals($match, $response_header);
    }

    /**
     * Assert response header exists and DOES NOT CONTAIN the given string
     *
     * @param  string $header
     * @param  string $match
     */
    public function assertNotResponseHeaderContains($header, $match)
    {        
        $response_header = $this->getResponseHeader($header);
        if (!$response_header) {
            $this->throwExpectationFailed(sprintf(
                    'Failed asserting response header, header "%s" do not exists', $header
            ));
        }
        if ($match == $response_header) {
            $this->throwExpectationFailed(sprintf(
                    'Failed asserting response header "%s" does not contain "%s"', $header, $match
            ));
        }
        $this->assertNotEquals($match, $response_header);
    }

    /**
     * Assert response header exists and matches the given pattern
     *
     * @param  string $header
     * @param  string $pattern
     */
    public function assertResponseHeaderRegex($header, $pattern)
    {
        $response_header = $this->getResponseHeader($header);
        if (!$response_header) {
            $this->throwExpectationFailed(sprintf(
                    'Failed asserting response header, header "%s" do not exists', $header
            ));
        }
        if (!preg_match($pattern, $response_header)) {
            $this->throwExpectationFailed(sprintf(
                    'Failed asserting response header "%s" exists and matches regex "%s", actual content is "%s"', $header, $pattern, $response_header
            ));
        }
        $this->assertTrue((boolean) preg_match($pattern, $response_header));
    }

    /**
     * Assert response header does not exist and/or does not match the given regex
     *
     * @param  string $header
     * @param  string $pattern
     */
    public function assertNotResponseHeaderRegex($header, $pattern)
    {        
        $response_header = $this->getResponseHeader($header);
        if (!$response_header) {
            $this->throwExpectationFailed(sprintf(
                    'Failed asserting response header, header "%s" do not exists', $header
            ));
        }
        if (preg_match($pattern, $response_header)) {
            $this->throwExpectationFailed(sprintf(
                    'Failed asserting response header "%s" does not match regex "%s"', $header, $pattern
            ));
        }
        $this->assertFalse((boolean) preg_match($pattern, $response_header));
    }
    
    /**
     * Get content with given selector
     * @param   string $selector
     * @return \Symfony\Component\DomCrawler\Crawler
     */
    protected function filter($selector)
    {
        
        $crawler = $this->getCrawler();
        $method = 'filter';
        if (substr($selector, 0, 1) === '/') {
            $method = 'filterXPath';
        }

        return $crawler->$method($selector);
    }

    /**
     * Assert that response content contains an element determined by $selector
     * @param   string $selector
     * @throws \PHPUnit_Framework_ExpectationFailedException
     */
    public function assertHasElement($selector)
    {        
        $actual = $this->filter($selector)->count();
        if ($actual <= 0) {
            throw new \PHPUnit_Framework_ExpectationFailedException(sprintf(
                    'Failed asserting that element with "%s" selector is exist.', $selector
            ));
        }
        $this->assertTrue($actual > 0);
    }

    /**
     * Assert that response content DOES NOT CONTAIN an element determined by $selector
     * @param   string $selector
     * @throws \PHPUnit_Framework_ExpectationFailedException
     */
    public function assertNotHasElement($selector)
    {
        
        $actual = $this->filter($selector)->count();
        if ($actual !== 0) {
            throw new \PHPUnit_Framework_ExpectationFailedException(sprintf(
                    'Failed asserting that element with "%s" selector is exist.', $selector
            ));
        }
        $this->assertEquals(0, $actual);
    }

    /**
     * Assert against DOM selection; should contain exact number of nodes
     *
     * @param  string $selector         CSS/XPath selector path
     * @param  string $expectedCount    Number of nodes that should match
     */
    public function assertElementCount($selector, $expectedCount)
    {
        
        $actual = $this->filter($selector)->count();
        if ($expectedCount != $actual) {
            throw new \PHPUnit_Framework_ExpectationFailedException(sprintf(
                    'Failed asserting that current response contain "%s" element, with "%s" count. Actual element count is "%s"'
                    , $selector, $expectedCount, $actual
            ));
        }
        $this->assertEquals($expectedCount, $actual);
    }

    public function assertNotElementCount($selector, $expected_count)
    {        
        $actual = $this->filter($selector)->count();
        if ($expected_count === $actual) {
            $this->throwExpectationFailed(sprintf(
                    'Failed asserting node DENOTED BY "%s" DOES NOT OCCUR EXACTLY "%d" times', $selector, $expected_count
            ));
        }
        $this->assertNotEquals($expected_count, $actual);
    }

    public function assertElementContains($selector, $match)
    {
        

        $result = $this->filter($selector);
        if ($result->count() === 0) {
            $this->throwExpectationFailed(sprintf(
                    'Failed asserting node DENOTED BY "%s" EXISTS', $selector
            ));
        }

        if (false === strpos($result->text(), $match)) {
            $this->throwExpectationFailed(sprintf(
                    'Failed asserting node denoted by "%s" CONTAINS content "%s", actual content is "%s"', $selector, $match, $result->text()
            ));
        }
        $this->assertContains($match, $result->text());
    }

    public function assertNotElementContains($selector, $match)
    {
        
        $result = $this->filter($selector);
        if ($result->count() == 0) {
            $this->throwExpectationFailed(sprintf(
                    'Failed asserting node DENOTED BY "%s" EXISTS', $selector
            ));
        }
        if (false !== strpos($result->text(), $match)) {
            $this->throwExpectationFailed(sprintf(
                    'Failed asserting node DENOTED BY %s DOES NOT CONTAIN content "%s"', $selector, $match
            ));
        }
        $this->assertNotContains($match, trim($result->text()));
    }

    /**
     * Assert against DOM selection; node should match content
     *
     * @param  string $path CSS selector path
     * @param  string $pattern Pattern that should be contained in matched nodes
     */
    public function assertElementContentRegex($path, $pattern)
    {
        
        $result = $this->filter($path);
                
        if ($result->count() == 0) {
            $this->throwExpectationFailed(sprintf(
                    'Failed asserting node DENOTED BY "%s" EXISTS', $path
            ));
        }
        $text = trim($result->text());
        if (!preg_match($pattern, $text)) {
            $this->throwExpectationFailed(sprintf(
                    'Failed asserting node denoted by "%s" CONTAINS content MATCHING "%s", actual content is "%s"',
                    $path,
                    $pattern,
                    $text
            ));
        }
        $this->assertTrue((boolean) preg_match($pattern, trim($result->text())));
    }

    /**
     * Assert against DOM selection; node should NOT match content
     *
     * @param  string $path CSS selector path
     * @param  string $pattern pattern that should NOT be contained in matched nodes
     */
    public function assertNotElementContentRegex($selector, $pattern)
    {
        

        $result = $this->filter($selector);
        if ($result->count() == 0) {
            $this->throwExpectationFailed(sprintf(
                    'Failed asserting node DENOTED BY "%s" EXISTS', $selector
            ));
        }
        
        $text = trim($result->text());
        if (preg_match($pattern, $text)) {
            $this->throwExpectationFailed(sprintf(
                    'Failed asserting node DENOTED BY "%s" DOES NOT CONTAIN content MATCHING "%s", actual content is "%s"',
                    $selector,
                    $pattern,
                    $text
            ));
        }
        $this->assertFalse((boolean) preg_match($pattern, $text));
    }
}