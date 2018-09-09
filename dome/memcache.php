<?php
use LSYS\Config\File;
use LSYS\Memcache\DI;
include __DIR__."/Bootstarp.php";

$r=new \LSYS\Memcache(new File("memcache.default"));
print_r($r);

$r=DI::get()->memcache()->configServers();
print_r($r->get('hi'));