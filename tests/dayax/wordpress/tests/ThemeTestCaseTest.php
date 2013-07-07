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

use \dayax\wordpress\ThemeTestCase;

/**
 * Description of ThemeTestCase
 *
 * @author Anthonius Munthi <me@itstoni.com>
 */
class ThemeTestCaseTest extends ThemeTestCase
{
    /**
     * @expectedException PHPUnit_Framework_ExpectationFailedException
     * @expectedExceptionMessage actual status code is "200"
     */
    public function testAssertResponseStatus()
    {
        $this->open('/');
        $this->assertResponseStatus(200);
        $this->assertResponseStatus(500);
    }
    
    /**
     * @expectedException PHPUnit_Framework_ExpectationFailedException
     * @expectedExceptionMessage was NOT "200"
     */
    public function testAssertNotResponseStatus()
    {
        $this->open('/');
        $this->assertNotResponseStatus(500);
        $this->assertNotResponseStatus(200);
    }
    
    /**
     * @covers \dayax\wordpress\ThemeTestCase::assertHasResponseHeader
     * @covers \dayax\wordpress\ThemeTestCase::getResponseHeader
     */
    public function testAssertHasResponseHeader()
    {
        $this->open('/');
        $this->assertHasResponseHeader('Content-Type');

        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException');
        $this->assertHasResponseHeader('Unknown-Header');
    }

    public function testAssertNotHasResponseHeader()
    {
        $this->open('/');

        $this->assertNotHasResponseHeader('Unknown-Header');

        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException');
        $this->assertNotHasResponseHeader('Content-Type');
    }

    public function testAssertResponseHeaderContains()
    {
        $this->open('/');
        $this->assertResponseHeaderContains('Content-Type', 'text/html; charset=UTF-8');
        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException', 'Failed asserting that response header for "Content-Type" contains "text/json". Actual content is "text/html; charset=UTF-8"'
        );

        $this->assertResponseHeaderContains('Content-Type', 'text/json');
    }

    public function testAssertNotResponseHeaderContains()
    {
        $this->open('/');
        $this->assertNotResponseHeaderContains('Content-Type', 'text/json');
        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException', 'Failed asserting response header "Content-Type" does not contain "text/html; charset=UTF-8"'
        );

        $this->assertNotResponseHeaderContains('Content-Type', 'text/html; charset=UTF-8');
    }

    public function testAssertResponseHeaderRegex()
    {
        $this->open('/');
        $this->assertResponseHeaderRegex('Content-Type', '#charset#');
        $this->assertResponseHeaderRegex('Content-Type', '#text#');
        $this->assertResponseHeaderRegex('Content-Type', '#html#');

        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException', 'actual content is "text/html; charset=UTF-8"'
        );
        $this->assertResponseHeaderRegex('Content-Type', '#json#');
    }

    public function testAssertNotResponseHeaderRegex()
    {
        $this->open('/');
        $this->assertNotResponseHeaderRegex('Content-Type', '#json#');

        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException', 'Failed asserting response header "Content-Type" does not match regex "#html#'
        );
        $this->assertNotResponseHeaderRegex('Content-Type', '#html#');
    }

    /**
     * @dataProvider getTestShouldThrowException
     * @expectedException \PHPUnit_Framework_ExpectationFailedException     
     */
    public function testShouldThrowWhenResponseHeaderNotExist($method, $header)
    {
        $this->open('/');
        $this->$method($header, null, null);
    }

    public function getTestShouldThrowException()
    {
        return array(
            array('assertResponseHeaderContains', 'foo-bar'),
            array('assertNOtResponseHeaderContains', 'foo-bar'),
            array('assertResponseHeaderRegex', 'foo-bar'),
            array('assertNotResponseHeaderRegex', 'foo-bar'),
        );
    }
    
    /**
     * @expectedException PHPUnit_Framework_ExpectationFailedException
     * @expectedExceptionMessage Failed asserting that element with ".non-existent-css-class" selector is exist.
     */
    public function testAssertHasElement()
    {
        $this->open('/');
        $this->assertHasElement('article#post-1241');
        $this->assertHasElement('h1.entry-title');        

        $this->assertHasElement('.non-existent-css-class');
    }

    public function testAssertNotHasElement()
    {
        $this->open('/');
        $this->assertNotHasElement('h1.foo');
        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException');
        $this->assertNotHasElement('h1');
    }

    public function testAssertElementCount()
    {
        $this->open('/');
        $this->assertElementCount('article#post-1241', 1);

        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException',
                'Failed asserting that current response contain "article#post-1241" element, with "200" count. Actual element count is "1"'
        );
        $this->assertElementCount('article#post-1241', 200);
    }

    public function testAssertNotElementCount()
    {
        $this->open('/');
        $this->assertNotElementCount('foo', 1);
        $this->assertNotElementCount('h1', 2);

        $this->setExpectedException(
            'PHPUnit_Framework_ExpectationFailedException',
            'Failed asserting node DENOTED BY "h1" DOES NOT OCCUR EXACTLY "6" times'
        );
        $this->assertNotElementCount('h1', 6);
    }

    public function testAssertElementContains()
    {
        $this->open('/');
        $this->assertElementContains('h1', 'Sticky');
        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException');
        $this->assertElementContains('h1', 'header foo');
    }

    /**
     * @expectedException \PHPUnit_Framework_ExpectationFailedException
     * @expectedExceptionMessage Failed asserting node DENOTED BY "foo" EXISTS
     */
    public function testShouldThrowOnAssertElementContainsWhenElementNotExist()
    {
        $this->open('/');
        $this->assertElementContains('foo', 'bar');
    }

    public function testAssertNotElementContains()
    {
        $this->open('/');
        $this->assertNotElementContains('h1', 'Foo');
        $this->setExpectedException('PHPUnit_Framework_ExpectationFailedException');
        $this->assertNotElementContains('h1', 'Sticky');
    }

    /**
     * @expectedException \PHPUnit_Framework_ExpectationFailedException
     * @expectedExceptionMessage Failed asserting node DENOTED BY "foo" EXISTS
     */
    public function testShouldThrowOnAssertNotElementContainsWhenElementNotExist()
    {
        $this->open('/');
        $this->assertNotElementContains('foo', 'bar');
    }

    public function testAssertElementContentRegex()
    {
        $this->open('/');
        $this->assertElementContentRegex('', '#Sticky#');

        $this->setExpectedException(
                'PHPUnit_Framework_ExpectationFailedException', 'actual content is "Sticky"'
        );
        $this->assertElementContentRegex('h1', '#foo#');
    }

    /**
     * @expectedException \PHPUnit_Framework_ExpectationFailedException
     * @expectedExceptionMessage Failed asserting node DENOTED BY "foo" EXISTS
     */
    public function testShouldThrowOnAssertElementContentRegexWhenElementNotExist()
    {
        $this->open('/');
        $this->assertElementContentRegex('foo', 'bar');
    }

    public function testAssertNotElementContentRegex()
    {
        $this->open('/');
        $this->assertNotElementContentRegex('h1', '#foo#');

        $this->setExpectedException(
                'PHPUnit_Framework_ExpectationFailedException', 'DOES NOT CONTAIN content MATCHING "#Sticky#"'
        );
        $this->assertNotElementContentRegex('h1', '#Sticky#');
    }

    /**
     * @expectedException \PHPUnit_Framework_ExpectationFailedException
     * @expectedExceptionMessage Failed asserting node DENOTED BY "foo" EXISTS
     */
    public function testShouldThrowOnAssertNotElementContentRegexWhenElementNotExist()
    {
        $this->open('/');
        $this->assertNotElementContentRegex('foo', 'bar');
    }
}
