<?php
namespace Qiniu\Tests;

use Qiniu\Storage\BucketManager;

class BucketTest extends \PHPUnit_Framework_TestCase
{
    protected $bucketManager;
    protected $dummyBucketManager;
    protected $bucketName;
    protected $key;
    protected function setUp()
    {
        global $bucketName;
        global $key;
        $this->bucketName = $bucketName;
        $this->key = $key;

        global $testAuth;
        $this->bucketManager = new BucketManager($testAuth);

        global $dummyAuth;
        $this->dummyBucketManager = new BucketManager($dummyAuth);
    }

    public function testBuckets()
    {

        list($list, $error) = $this->bucketManager->buckets();
        $this->assertTrue(in_array($this->bucketName, $list));
        $this->assertNull($error);

        list($list2, $error) = $this->dummyBucketManager->buckets();
        $this->assertEquals(401, $error->code());
        $this->assertNull($list2);
        $this->assertNotNull($error->message());
    }

    public function testList()
    {
        list($items, $marker, $error) = $this->bucketManager->listFiles($this->bucketName, null, null, 2);
        $this->assertNotNull($items[0]);
        $this->assertNotNull($marker);
    }

    public function testStat()
    {
        list($stat, $error) = $this->bucketManager->stat($this->bucketName, $this->key);
        $this->assertNotNull($stat);
        $this->assertNull($error);
        $this->assertNotNull($stat['hash']);

        list($stat, $error) = $this->bucketManager->stat($this->bucketName, 'nofile');
        $this->assertNull($stat);
        $this->assertEquals(612, $error->code());
        $this->assertNotNull($error->message());

        list($stat, $error) = $this->bucketManager->stat('nobucket', 'nofile');
        $this->assertNull($stat);
        $this->assertEquals(631, $error->code());
        $this->assertNotNull($error->message());
    }

    public function testDelete()
    {
        $error = $this->bucketManager->delete($this->bucketName, 'del');
        $this->assertEquals(612, $error->code());
    }


    public function testRename()
    {
        $key = 'renamefrom' . rand();
        $this->bucketManager->copy($this->bucketName, $this->key, $this->bucketName, $key);
        $key2 = 'renameto' . $key;
        $error = $this->bucketManager->rename($this->bucketName, $key, $key2);
        $this->assertNull($error);
        $error = $this->bucketManager->delete($this->bucketName, $key2);
        $this->assertNull($error);
    }


    public function testCopy()
    {
        $key = 'copyto' . rand();
        $error = $this->bucketManager->copy(
            $this->bucketName,
            $this->key,
            $this->bucketName,
            $key
        );
        $this->assertNull($error);
        $error = $this->bucketManager->delete($this->bucketName, $key);
        $this->assertNull($error);
    }


    public function testChangeMime()
    {
        $error = $this->bucketManager->changeMime(
            $this->bucketName,
            'php-sdk.html',
            'text/html'
        );
        $this->assertNull($error);
    }

    public function testPrefetch()
    {
        $error = $this->bucketManager->prefetch(
            $this->bucketName,
            'php-sdk.html'
        );
        $this->assertNull($error);
    }

    public function testFetch()
    {
        list($ret, $error) = $this->bucketManager->fetch(
            'http://developer.qiniu.com/docs/v6/sdk/php-sdk.html',
            $this->bucketName,
            'fetch.html'
        );
        $this->assertArrayHasKey('hash', $ret);
        $this->assertNull($error);

        list($ret, $error) = $this->bucketManager->fetch(
            'http://developer.qiniu.com/docs/v6/sdk/php-sdk.html',
            $this->bucketName,
            ''
        );
        $this->assertArrayHasKey('key', $ret);
        $this->assertNull($error);
 
        list($ret, $error) = $this->bucketManager->fetch(
            'http://developer.qiniu.com/docs/v6/sdk/php-sdk.html',
            $this->bucketName
        );
        $this->assertArrayHasKey('key', $ret);
        $this->assertNull($error);
    }

    public function testBatchCopy()
    {
        $key = 'copyto' . rand();
        $ops = BucketManager::buildBatchCopy(
            $this->bucketName,
            array($this->key => $key),
            $this->bucketName
        );
        list($ret, $error) = $this->bucketManager->batch($ops);
        $this->assertEquals(200, $ret[0]['code']);
        $ops = BucketManager::buildBatchDelete($this->bucketName, array($key));
        list($ret, $error) = $this->bucketManager->batch($ops);
        $this->assertEquals(200, $ret[0]['code']);
    }

    public function testBatchMove()
    {
        $key = 'movefrom'. rand();
        $this->bucketManager->copy($this->bucketName, $this->key, $this->bucketName, $key);
        $key2 = $key . 'to';
        $ops = BucketManager::buildBatchMove(
            $this->bucketName,
            array($key => $key2),
            $this->bucketName
        );
        list($ret, $error) = $this->bucketManager->batch($ops);
        $this->assertEquals(200, $ret[0]['code']);
        $error = $this->bucketManager->delete($this->bucketName, $key2);
        $this->assertNull($error);
    }

    public function testBatchRename()
    {
        $key = 'rename' . rand();
        $this->bucketManager->copy($this->bucketName, $this->key, $this->bucketName, $key);
        $key2 = $key . 'to';
        $ops = BucketManager::buildBatchRename($this->bucketName, array($key => $key2));
        list($ret, $error) = $this->bucketManager->batch($ops);
        $this->assertEquals(200, $ret[0]['code']);
        $error = $this->bucketManager->delete($this->bucketName, $key2);
        $this->assertNull($error);
    }

    public function testBatchStat()
    {
        $ops = BucketManager::buildBatchStat($this->bucketName, array('php-sdk.html'));
        list($ret, $error) = $this->bucketManager->batch($ops);
        $this->assertEquals(200, $ret[0]['code']);
    }
}
