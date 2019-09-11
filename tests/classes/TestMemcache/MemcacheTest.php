<?php
namespace TestEvent;
use PHPUnit\Framework\TestCase;
final class MemcacheTest extends TestCase
{
    public function testBase()
    {
        $redis=\LSYS\Memcache\DI::get()->memcache()->configServers();
        $redis->set("a","b");
        $this->assertEquals($redis->get("a"), "b");
    }
}