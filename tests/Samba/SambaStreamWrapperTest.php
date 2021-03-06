<?php

namespace Samba;

class SambaStreamWrapperTest extends TestCase
{
    public function testUnlinkMethod()
    {
        $url = "smb://user:password@host/base_path/to/dir/file.doc";

        $sambaMock = $this->getSambaClientMock(array('execute'));
        $parsedUrl = $sambaMock->parseUrl($url);

        $expectedExecuteCommand = 'del "to\dir\file.doc"';

        $sambaMock
            ->expects(static::once())
            ->method('execute')
            ->with($expectedExecuteCommand, $parsedUrl);

        $wrapper = new SambaStreamWrapper($sambaMock);
        $wrapper->unlink($url);
    }

    /**
     * @expectedException \Samba\SambaException
     */
    public function testUnLinkExceptionNotAPath()
    {
        $url = "smb://user:password@host/base_path";


        $sambaMock = $this->getSambaClientMock(array('execute'));

        $wrapper = new SambaStreamWrapper($sambaMock);
        $wrapper->unlink($url);
    }

    public function testRenameMethod()
    {
        $url = "smb://user:password@host/base_path/to/dir/file.doc";
        $urlNew = "smb://user:password@host/base_path/to/dir/file_new.doc";

        $sambaMock = $this->getSambaClientMock(array('execute'));

        $parsedUrlNew = $sambaMock->parseUrl($urlNew);

        $expectedExecuteCommand = 'rename "to\dir\file.doc" "to\dir\file_new.doc"';

        $sambaMock
            ->expects(static::once())
            ->method('execute')
            ->with($expectedExecuteCommand, $parsedUrlNew);

        $wrapper = new SambaStreamWrapper($sambaMock);
        $wrapper->rename($url, $urlNew);
    }

    /**
     * @expectedException \Samba\SambaException
     */
    public function testRenameExceptionOnNotOneServer()
    {
        $url = "smb://user:password@host/base_path/to/dir/file.doc";
        $urlNew = "smb://user:password@new_host/base_path/to/dir/file_new.doc";

        $sambaMock = $this->getSambaClientMock(array('execute'));

        $wrapper = new SambaStreamWrapper($sambaMock);
        $wrapper->rename($url, $urlNew);
    }

    /**
     * @expectedException \Samba\SambaException
     */
    public function testRenameExceptionNotAPath()
    {
        $url = "smb://user:password@host/base_path";
        $urlNew = "smb://user:password@host/base_path";

        $sambaMock = $this->getSambaClientMock(array('execute'));

        $wrapper = new SambaStreamWrapper($sambaMock);
        $wrapper->rename($url, $urlNew);
    }

    public function testMkDirMethod()
    {
        $url = "smb://user:password@host/base_path/to/dir";

        $sambaMock = $this->getSambaClientMock(array('execute'));
        $parsedUrl = $sambaMock->parseUrl($url);

        $expectedExecuteCommand = 'mkdir "to\dir"';

        $sambaMock
            ->expects(static::once())
            ->method('execute')
            ->with($expectedExecuteCommand, $parsedUrl);

        $wrapper = new SambaStreamWrapper($sambaMock);
        $wrapper->mkdir($url, '', '');
    }

    /**
     * @expectedException \Samba\SambaException
     */
    public function testMkDirExceptionNotAPath()
    {
        $url = "smb://user:password@host/base_path";

        $sambaMock = $this->getSambaClientMock(array('execute'));

        $wrapper = new SambaStreamWrapper($sambaMock);
        $wrapper->mkdir($url, '', '');
    }

    public function testRmDirMethod()
    {
        $url = "smb://user:password@host/base_path/to/dir";

        $sambaMock = $this->getSambaClientMock(array('execute'));
        $parsedUrl = $sambaMock->parseUrl($url);

        $expectedExecuteCommand = 'rmdir "to\dir"';

        $sambaMock
            ->expects(static::once())
            ->method('execute')
            ->with($expectedExecuteCommand, $parsedUrl);

        $wrapper = new SambaStreamWrapper($sambaMock);
        $wrapper->rmdir($url);
    }

    /**
     * @expectedException \Samba\SambaException
     */
    public function testRmDirExceptionNotAPath()
    {
        $url = "smb://user:password@host/base_path";

        $sambaMock = $this->getSambaClientMock(array('execute'));

        $wrapper = new SambaStreamWrapper($sambaMock);
        $wrapper->rmdir($url);
    }

    public function testUrlStatHost()
    {
        $urlHost = "smb://user:password@host";

        $sambaMock = $this->getSambaClientMock(array('look'));

        $lookInfo = array(
            "disk" => array("centrum"),
            "server" => array("vm6"),
            "workgroup" => array("cmag", "mygroup"),
        );

        $sambaMock
            ->method('look')
            ->willReturn($lookInfo);

        $expectedStatInfoHost = $this->createStatInfo('/tmp', 0, 0);

        $wrapper = new SambaStreamWrapper($sambaMock);
        $actualStatInfoHost = $wrapper->url_stat($urlHost);

        static::assertEquals($expectedStatInfoHost, $actualStatInfoHost);
    }

    public function testUrlStatDir()
    {
        $urlDir = "smb://user:password@host/base_path/success";

        $sambaMock = $this->getSambaClientMock(array('execute'));

        $sambaMock
            ->method('execute')
            ->willReturn($this->getExpectedDirInfo());

        $expectedStatInfoDir = $this->createStatInfo('/tmp', 0, 1380804166);

        $wrapper = new SambaStreamWrapper($sambaMock);
        $actualStatInfoDir = $wrapper->url_stat($urlDir);

        static::assertEquals($expectedStatInfoDir, $actualStatInfoDir);
    }

    public function testUrlStatFile()
    {
        $urlFile = "smb://user:password@host/base_path/catalog-goods_1378998029.xml";

        $sambaMock = $this->getSambaClientMock(array('execute'));

        $sambaMock
            ->method('execute')
            ->willReturn($this->getExpectedDirInfo());

        $expectedStatInfoFile = $this->createStatInfo('/etc/passwd', 70, 1379012430);

        $wrapper = new SambaStreamWrapper($sambaMock);
        $actualStatInfoFile = $wrapper->url_stat($urlFile);

        static::assertEquals($expectedStatInfoFile, $actualStatInfoFile);
    }

    public function testUrlStatShare()
    {
        $urlShare = "smb://user:password@host/base_path";

        $shareLookInfo = array(
            "disk" => array("base_path"),
            "server" => array("vm6"),
            "workgroup" => array("cmag", "mygroup"),
        );

        $sambaMock = $this->getSambaClientMock(array('look'));

        $sambaMock
            ->method('look')
            ->willReturn($shareLookInfo);

        $expectedStatInfoShare = $this->createStatInfo('/tmp', 0, 0);

        $wrapper = new SambaStreamWrapper($sambaMock);
        $actualStatInfoShare = $wrapper->url_stat($urlShare);

        static::assertEquals($expectedStatInfoShare, $actualStatInfoShare);
    }

    /**
     * @expectedException \Samba\SambaException
     */
    public function testUrlStatHostException()
    {
        $sambaMock = $this->getSambaClientMock(array('look'));

        $urlHost = 'smb://user:password@host';

        $wrapper = new SambaStreamWrapper($sambaMock);
        $wrapper->url_stat($urlHost);
    }

    /**
     * @expectedException \Samba\SambaException
     */
    public function testUrlStatShareException()
    {
        $shareLookInfo = array(
            "disk" => array("centrum"),
            "server" => array("vm6"),
            "workgroup" => array("cmag", "mygroup"),
        );

        $sambaMock = $this->getSambaClientMock(array('look'));

        $sambaMock
            ->method('look')
            ->willReturn($shareLookInfo);

        $urlShare = "smb://user:password@host/base_path";

        $wrapper = new SambaStreamWrapper($sambaMock);
        $wrapper->url_stat($urlShare);
    }

    /**
     * @expectedException \Samba\SambaException
     */
    public function testUrlStatPathException()
    {
        $sambaMock = $this->getSambaClientMock(array('execute'));

        $urlDir = "smb://user:password@host/base_path/success";

        $wrapper = new SambaStreamWrapper($sambaMock);
        $wrapper->url_stat($urlDir);
    }

    /**
     * @expectedException \Samba\SambaException
     */
    public function testUrlStatNotTypeUrlException()
    {
        $sambaMock = $this->getSambaClientMock(array('execute'));

        $url = "smb://";

        $wrapper = new SambaStreamWrapper($sambaMock);
        $wrapper->url_stat($url);
    }

    /**
     * @expectedException \Samba\SambaException
     */
    public function testUrlStatNotFoundPath()
    {
        $executeOutput =  <<<EOF
Anonymous login successful
Domain=[MYGROUP] OS=[Unix] Server=[Samba 3.0.33-3.39.el5_8]
NT_STATUS_NO_SUCH_FILE listing reports
EOF;

        $executeOutputStream = $this->convertStringToResource($executeOutput);

        $sambaMock = $this->getSambaClientMock(array('getProcessResource'));

        $sambaMock
            ->method('getProcessResource')
            ->willReturn($executeOutputStream);

        $urlDir = "smb://user:password@host/base_path/success";

        $wrapper = new SambaStreamWrapper($sambaMock);
        $wrapper->url_stat($urlDir);
    }

    public function testDirOpenHost()
    {
        $urlHost = "smb://user:password@host";

        $sambaMock = $this->getSambaClientMock(array('look'));

        $lookInfo = array(
            'disk' => array('centrum'),
            'server' => array('vm6'),
            'workgroup' => array('cmag', 'mygroup'),
        );

        $sambaMock
            ->expects(static::once())
            ->method('look')
            ->willReturn($lookInfo);

        $wrapper = new SambaStreamWrapper($sambaMock);
        $wrapper->dir_opendir($urlHost, 0);

        static::assertSame('centrum', $wrapper->dir_readdir());
        static::assertFalse($wrapper->dir_readdir());
    }

    public function testDirOpenDir()
    {
        $urlDir = "smb://user:password@host/base_path/to/dir";

        $sambaMock = $this->getSambaClientMock(array('execute'));

        $sambaMock
            ->expects($this->any())
            ->method('execute')
            ->willReturn($this->getExpectedDirInfo());

        $wrapper = new SambaStreamWrapper($sambaMock);
        $wrapper->dir_opendir($urlDir, 0);

        $expectedDir = array(
            'success',
            'test',
            'error',
            'tmp',
            'source',
            'catalog-goods_1234-13-09-2013_11-30-14.xml',
            'catalog-goods_1378998029.xml',
            'catalog-goods_1379058741.xml',
        );

        $dir = array();
        while (false !== ($line = $wrapper->dir_readdir())) {
            $dir[] = $line;
        }

        static::assertSame($expectedDir, $dir);
    }

    /**
     * @expectedException \Samba\SambaException
     */
    public function testDirOpenDirExceptionHostNotLook()
    {
        $urlHost = "smb://user:password@host";

        $sambaMock = $this->getSambaClientMock(array('look'));

        $wrapper = new SambaStreamWrapper($sambaMock);
        $wrapper->dir_opendir($urlHost, 0);
    }

    /**
     * @expectedException \Samba\SambaException
     */
    public function testDirOpenDirExceptionErrorType()
    {
        $urlHost = "smb://";

        $sambaMock = $this->getSambaClientMock(array('look'));

        $wrapper = new SambaStreamWrapper($sambaMock);
        $wrapper->dir_opendir($urlHost, 0);
    }

    /**
     * @expectedException \Samba\SambaException
     */
    public function testStreamOpenExceptionHost()
    {
        $urlHost = "smb://user:password@host";

        $sambaMock = $this->getSambaClientMock(array('look'));

        $wrapper = new SambaStreamWrapper($sambaMock);

        $path = null;
        $wrapper->stream_open($urlHost, '', '', $path);
    }

    /**
     * @expectedException \Samba\SambaException
     */
    public function testStreamOpenExceptionShare()
    {
        $urlHost = "smb://user:password@host/share";

        $sambaMock = $this->getSambaClientMock(array('look'));

        $wrapper = new SambaStreamWrapper($sambaMock);

        $path = null;
        $wrapper->stream_open($urlHost, '', '', $path);
    }

    /**
     * @runInSeparateProcess
     */
    public function testWrapperRegister()
    {
        static::assertFalse(SambaStreamWrapper::is_registered());

        SambaStreamWrapper::register();

        static::assertTrue(SambaStreamWrapper::is_registered());

        SambaStreamWrapper::unregister();

        static::assertFalse(SambaStreamWrapper::is_registered());
    }

    /**
     * @runInSeparateProcess
     * @expectedException \PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage Unable to unregister protocol smb://
     */
    public function testWrapperUnregisterNotRegistered()
    {
        SambaStreamWrapper::unregister();
    }

    /**
     * @runInSeparateProcess
     * @expectedException \PHPUnit_Framework_Error_Warning
     * @expectedExceptionMessage Protocol smb:// is already defined
     */
    public function testWrapperDoubleRegister()
    {
        SambaStreamWrapper::register();
        SambaStreamWrapper::register();
    }
}
