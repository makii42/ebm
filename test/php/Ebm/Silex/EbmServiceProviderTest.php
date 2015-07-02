<?php
/**
 * Created by PhpStorm.
 * User: ralfischer
 * Date: 4/1/15
 * Time: 14:50
 */

namespace Ebm\Silex;


class EbmServiceProviderTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        parent::setUp();
    }

    public function testRoutesAreEstablished()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $mockApp */
        $mockApp = $this->getMockBuilder('Silex\Application')
            ->disableOriginalConstructor()
            ->getMock();

        $foo = function() {
            return "foo";
        };
        $this->assertTrue(get_class($foo) === 'Closure');

        $mockApp->expects($this->at(1))
                ->method('get')
                ->with('/', $this->isInstanceOf('Closure'));
        $mockApp->expects($this->at(2))
                ->method('get')
                ->with('/{screen}', $this->isInstanceOf('Closure'));
        $mockApp->expects($this->at(2))
                ->method('get')
                ->with('/{screen}', $this->isInstanceOf('Closure'));

        $ebmServiceProvider = new EbmServiceProvider();
        $ebmServiceProvider->register($mockApp);

        $this->verifyMockObjects();
    }


}
