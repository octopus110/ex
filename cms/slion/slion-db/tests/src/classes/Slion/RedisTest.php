<?php

namespace Slion;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-07-26 at 15:21:07.
 */
class RedisTest extends \PHPUnit_Framework_TestCase {

    /**
     * @var Redis
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new Redis;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {

    }

    /**
     * @covers Slion\Redis::instance
     * @todo   Implement testInstance().
     */
    public function testInstance() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Slion\Redis::selectClient
     * @todo   Implement testSelectClient().
     */
    public function testSelectClient() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    /**
     * @covers Slion\Redis::__callStatic
     * @todo   Implement test__callStatic().
     */
    public function test__callStatic() {
        Redis::set('aaa', '222');
        du(Redis::get('aaa'));
    }

}
