<?php

namespace PhpDruidIngest\Preparer;

use PhpDruidIngest\Exception\CannotWriteException;
use PHPUnit_Framework_TestCase;

class LocalFilePreparerTest extends PHPUnit_Framework_TestCase
{

    public function testWritesWithDelimiter()
    {
        $p = $this->getMock('PhpDruidIngest\Preparer\LocalFilePreparer', array('writeFile', 'delimit', 'getPreparedPath'));
        $p->outputBaseDir = 'some/dir';
        $p->outputFilename = 'filename.json';

        $fakeData = array(
            'row1',
            'row2',
            'row3',
        );

        $p->expects($this->once())->method('getPreparedPath')->willReturn('some/dir/filename.json');
        $p->expects($this->once())->method('delimit')->with( $fakeData )->willReturn( 'row1\nrow2\nrow3' );
        $p->expects($this->once())->method('writeFile')->with( 'some/dir/filename.json', 'row1\nrow2\nrow3' );

        /**
         * @var \PhpDruidIngest\Preparer\LocalFilePreparer $p
         */
        $fakePath = $p->prepare( $fakeData );

        $this->assertEquals('some/dir/filename.json', $fakePath);
    }

    public function testWriteJSON()
    {
        $p = $this->getMock('PhpDruidIngest\Preparer\LocalFilePreparer', array('writeFile'));

        $p->expects($this->once())->method('writeFile')->with( $this->anything(), '{"id":1,"animal":"cat","legs":4}' . "\n" . '{"id":2,"animal":"cat","legs":3}' );

        $fakeData = array(
            '{"id":1,"animal":"cat","legs":4}',
            '{"id":2,"animal":"cat","legs":3}',
        );

        /**
         * @var \PhpDruidIngest\Preparer\LocalFilePreparer $p
         */
        $p->prepare( $fakeData );
    }

    public function testWriteCSV()
    {
        $p = $this->getMock('PhpDruidIngest\Preparer\LocalFilePreparer', array('writeFile'));

        $p->expects($this->once())->method('writeFile')->with( $this->anything(), "id,animal,legs\n1,cat,4\n2,cat,3" );

        $fakeData = array(
            'id,animal,legs',
            '1,cat,4',
            '2,cat,3',
        );

        /**
         * @var \PhpDruidIngest\Preparer\LocalFilePreparer $p
         */
        $p->prepare( $fakeData );
    }

    public function testWriteTSV()
    {
        $p = $this->getMock('PhpDruidIngest\Preparer\LocalFilePreparer', array('writeFile'));

        $p->expects($this->once())->method('writeFile')->with( $this->anything(), "id animal  legs\n1  cat 4\n2  cat 3" );

        $fakeData = array(
            'id animal  legs',
            '1  cat 4',
            '2  cat 3',
        );

        /**
         * @var \PhpDruidIngest\Preparer\LocalFilePreparer $p
         */
        $p->prepare( $fakeData );
    }

    public function testPrepareRequiresArray()
    {
        $p = $this->getMock('PhpDruidIngest\Preparer\LocalFilePreparer', array('writeFile'));

        $p->expects($this->never())->method('writeFile');
        $this->setExpectedException('PhpDruidIngest\Exception\UnexpectedTypeException');

        $fakeData = 'string of text';

        /**
         * @var \PhpDruidIngest\Preparer\LocalFilePreparer $p
         */
        $p->prepare( $fakeData );
    }

    public function testPrepareRequiresArrayOfStrings()
    {
        $p = $this->getMock('PhpDruidIngest\Preparer\LocalFilePreparer', array('writeFile'));

        $p->expects($this->never())->method('writeFile');
        $this->setExpectedException('PhpDruidIngest\Exception\UnexpectedTypeException');


        $fakeData = array(
            array('a', 'b', 'c'),
            'a',
            'b',
            123
        );

        /**
         * @var \PhpDruidIngest\Preparer\LocalFilePreparer $p
         */
        $p->prepare( $fakeData );
    }

    public function testPrepareRequiresAPathToBeGeneratable()
    {
        $p = $this->getMock('PhpDruidIngest\Preparer\LocalFilePreparer', array('getPreparedPath', 'writeFile'));

        $p->expects($this->once())->method('getPreparedPath')->willReturn('');
        $p->expects($this->never())->method('writeFile');

        $this->setExpectedException('PhpDruidIngest\Exception\MalformedFilePathException');
        $fakeData = array(
            'a',
            'b',
            'c'
        );

        /**
         * @var \PhpDruidIngest\Preparer\LocalFilePreparer $p
         */
        $p->prepare( $fakeData );

    }

    public function testCleanupUsesPreparedPath()
    {
        $p = $this->getMock('PhpDruidIngest\Preparer\LocalFilePreparer', array('getPreparedPath', 'deleteFile'));

        $p->expects($this->once())->method('getPreparedPath')->willReturn('some.path');

        // Prevent I/O
        $p->expects($this->any())->method('deleteFile')->with('some.path');

        /**
         * @var \PhpDruidIngest\Preparer\LocalFilePreparer $p
         */
        $p->cleanup();
    }

    public function testCleanupCallsDeleteFile()
    {
        $p = $this->getMock('PhpDruidIngest\Preparer\LocalFilePreparer', array('getPreparedPath', 'deleteFile'));

        $p->expects($this->once())->method('getPreparedPath')->willReturn('some.path');
        $p->expects($this->once())->method('deleteFile')->with('some.path');

        /**
         * @var \PhpDruidIngest\Preparer\LocalFilePreparer $p
         */
        $p->cleanup();

    }

}