<?php
use wapmorgan\UnifiedArchive\TarArchive;
use wapmorgan\UnifiedArchive\UnifiedArchive;

class UnifiedArchiveTest extends PhpUnitTestCase
{

    public function getFixtures()
    {
        return self::$archives;
    }

    /**
     * @return array
     */
    public function archiveTypes()
    {
        return [
            ['archive.tar', TarArchive::TAR],
            ['archive.tgz', TarArchive::TAR_GZIP],
            ['archive.tar.gz', TarArchive::TAR_GZIP],
            ['archive.tbz2', TarArchive::TAR_BZIP],
            ['archive.tar.bz2', TarArchive::TAR_BZIP],
            ['archive.txz', TarArchive::TAR_LZMA],
            ['archive.tar.xz', TarArchive::TAR_LZMA],
            ['archive.zip', UnifiedArchive::ZIP],
            ['archive.rar', UnifiedArchive::RAR],
            ['archive.iso', UnifiedArchive::ISO],
            ['archive.7z', UnifiedArchive::SEVEN_ZIP],
        ];
    }

    /**
     * @dataProvider archiveTypes
     */
    public function testDetectArchiveType($filename, $type)
    {
        $this->assertEquals($type, UnifiedArchive::detectArchiveType($filename));
    }

    /**
     * @dataProvider creatableArchiveTypes
     *
     * @param string $archiveFileName
     * @param string $archiveType
     *
     * @throws \Exception
     */
    public function testCreateAndModify($archiveFileName, $archiveType)
    {
        if (!UnifiedArchive::canOpenType($archiveType))
            $this->markTestSkipped($archiveType.' is not supported with current system configuration');

        $test_archive_filename = __DIR__.'/'.$archiveFileName;
        if (file_exists($test_archive_filename))
            $this->assertTrue(unlink($test_archive_filename));

        $result = UnifiedArchive::archiveFiles(__DIR__.'/fixtures', $test_archive_filename);
        $this->assertInternalType('integer', $result);
        $this->assertEquals(6, $result);
    }

    /**
     * @dataProvider getFixtures
     * @return bool
     * @throws \Exception
     */
    public function testOpen($md5hash, $filename, $remoteUrl)
    {
        $class = (strpos($filename, '.tar') !== false)
            ? 'wapmorgan\UnifiedArchive\TarArchive'
            : 'wapmorgan\UnifiedArchive\UnifiedArchive';

        $full_filename = self::getArchivePath($filename);

        if (!UnifiedArchive::canOpenArchive($full_filename))
            $this->markTestSkipped(UnifiedArchive::detectArchiveType($full_filename).' is not supported with current system configuration');

        $this->assertInstanceOf($class, UnifiedArchive::open($full_filename),
            'UnifiedArchive::open() on '.$full_filename.' should return an object');
    }

    /**
     * @depends testOpen
     * @dataProvider getFixtures
     * @throws Exception
     */
    public function testCountFiles($md5hash, $filename, $remoteUrl)
    {
        $files_number = count(self::$fixtureContents, COUNT_RECURSIVE);
        $full_filename = self::getArchivePath($filename);

        if (!UnifiedArchive::canOpenArchive($full_filename))
            $this->markTestSkipped(UnifiedArchive::detectArchiveType($full_filename).' is not supported with current system configuration');

        $archive = UnifiedArchive::open($full_filename);
        $this->assertEquals($files_number, $archive->countFiles(), 'Invalid files count for '.$filename);
    }

    /**
     * @return array
     */
    public function creatableArchiveTypes()
    {
        return [
            ['archive.zip', UnifiedArchive::ZIP],
            ['archive.7z', UnifiedArchive::SEVEN_ZIP],
            ['archive.tar', TarArchive::TAR],
        ];
    }

    //    /**
//     * @depends testCountFiles
//     * @dataProvider getFixtures
//     */
//    public function testFilesData($md5hash, $filename, $remoteUrl)
//    {
//        $full_filename = self::getFixturePath($filename);
//
//        if (!UnifiedArchive::canOpenArchive($full_filename))
//            $this->markTestSkipped(UnifiedArchive::detectArchiveType($full_filename).' is not supported with current system configuration');
//
//        $archive = UnifiedArchive::open($full_filename);
//        $flatten_list = [];
//        $this->flattenFilesList(null, self::$fixtureContents, $flatten_list);
//
//        foreach ($flatten_list as $filename => $content) {
//            var_dump($archive, $filename);
//            $file_data = $archive->getFileData($filename);
//            $this->assertInstanceOf('wapmorgan\\UnifiedArchive\\ArchiveEntry', $file_data);
//
//            $this->assertAttributeEquals(strlen($content), 'uncompressedSize', $file_data);
//        }
//    }

    protected function flattenFilesList($prefix, array $list, array &$output)
    {
        foreach ($list as $name => $value) {
            if (is_array($value))
                $this->flattenFilesList($prefix.$name.'/', $value, $output);
            else
                $output[$prefix.$name] = $value;
        }
    }
}
