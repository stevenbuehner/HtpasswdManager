<?php

namespace HtpasswdManager\Service;


class HtpasswdServiceTest extends \PHPUnit_Framework_TestCase {

    protected $testDataDir;

    public function setUp() {
        $this->testDataDir = __DIR__ . '/TestData';

        parent::setUp();
    }


    public function testCreateFileIfNotExistant() {
        $testFile = $this->testDataDir . '/notExistant';

        if (true === file_exists($testFile)) {
            unlink($testFile);
        }

        $this->assertFileNotExists($testFile);

        new HtpasswdService($testFile);
        $this->assertFileExists($testFile);

        // Cleanup
        unlink($testFile);
    }

    public function testGetUserList_Empty() {
        $testFile = $this->testDataDir . '/htaccess_UserServiceTest_empty';

        if (true === file_exists($testFile)) {
            unlink($testFile);
        }
        $this->assertFileNotExists($testFile);
        touch($testFile);
        $this->assertFileExists($testFile);

        $service = new HtpasswdService($testFile);

        $userList = $service->getUserList();
        $this->assertEquals($expected = [ ], $userList, "UserList");

        // Cleanup
        unlink($testFile);
    }

    public function testGetUserList_Filled() {
        $testFile = $this->testDataDir . '/htaccess_UserServiceTest';
        $this->assertFileExists($testFile);

        $service = new HtpasswdService($testFile);

        $userList = $service->getUserList();
        $this->assertEquals(
            $expected = [ 'steven' => '$apr1$UF1l/6iv$tT3IJUH.QL82HAraXetNo0',
                          'blub'   => '$apr1$jVosh5nC$q6KJ5EZNU6thOPETMQw8O/'
            ], $userList, "UserList");
    }

    public function testAddUser() {
        $testFileTemplate = $this->testDataDir . '/htaccess_UserServiceTest';
        $testFile         = $this->testDataDir . '/htaccess_UserServiceTest_addUser';
        $this->assertFileExists($testFileTemplate);

        if (true === file_exists($testFile)) {
            unlink($testFile);
        }
        $this->assertFileNotExists($testFile);

        copy($testFileTemplate, $testFile);
        $this->assertFileExists($testFile);

        $service  = new HtpasswdService($testFile);
        $userList = $service->getUserList();
        $this->assertCount(2, $userList);
        $this->assertEquals(2, $this->getNumberOfLines($testFile), "Number of Lines");


        $service->addUser('test', 'test');

        $userList = $service->getUserList();
        $this->assertCount(3, $userList);
        $this->assertEquals($expected = [ 'steven', 'blub', 'test' ],
            array_keys($userList));
        $this->assertEquals(3, $this->getNumberOfLines($testFile), "Number of Lines");


        // Cleanup
        unlink($testFile);
    }


    public function testdeleteUser() {
        $testFileTemplate = $this->testDataDir . '/htaccess_UserServiceTest';
        $testFile         = $this->testDataDir . '/htaccess_UserServiceTest_addUser';
        $this->assertFileExists($testFileTemplate);

        if (true === file_exists($testFile)) {
            unlink($testFile);
        }
        $this->assertFileNotExists($testFile);

        copy($testFileTemplate, $testFile);
        $this->assertFileExists($testFile);

        $service  = new HtpasswdService($testFile);
        $userList = $service->getUserList();
        $this->assertCount(2, $userList);
        $this->assertEquals(2, $this->getNumberOfLines($testFile), "Number of Lines");


        $service->deleteUser('steven');
        $userList = $service->getUserList();
        $this->assertCount(1, $userList);
        $this->assertEquals($expected = [ 'blub' ],
            array_keys($userList));
        $this->assertEquals(1, $this->getNumberOfLines($testFile), "Number of Lines");


        // Cleanup
        unlink($testFile);
    }

    public function testUserExists() {
        $testFile = $this->testDataDir . '/htaccess_UserServiceTest';
        $this->assertFileExists($testFile);

        $service  = new HtpasswdService($testFile);
        $service->getUserList();

        $this->assertTrue($service->userExists('steven'), 'User should exist');
        $this->assertTrue($service->userExists('blub'), 'User should exist');
        $this->assertFalse($service->userExists(''), "User '' should not exist");
        $this->assertFalse($service->userExists('notExistant'), "User 'notExistant' should not exist");
        $this->assertFalse($service->userExists('not Existant'), "User 'not Existant' should not exist");
    }

    protected function getNumberOfLines($filename) {
        $cont  = file_get_contents($filename);
        $lines = explode("\n", $cont);

        // Remove emtpy lines
        foreach ($lines as $i => $line) {
            if (empty(trim($line))) {
                unset($lines[$i]);
            }
        }

        return count($lines);
    }

}