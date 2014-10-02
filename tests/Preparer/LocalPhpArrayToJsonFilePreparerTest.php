<?php

namespace PhpDruidIngest\Preparer;

use PHPUnit_Framework_TestCase;

class LocalPhpArrayToJsonFilePreparerTest extends PHPUnit_Framework_TestCase
{
    public function testWritesWithNewlineDelimiter()
    {
        $p = $this->getMock('PhpDruidIngest\Preparer\LocalPhpArrayToJsonFilePreparer', array('writeFile'));

        $expectedJsonResponse = '{"id":1,"animal":"cat","legs":4}' .
            "\n" .
            '{"id":2,"animal":"cat","legs":3}' .
            "\n" .
            '{"id":3,"animal":"dog","legs":4}'
        ;

        $p->expects($this->once())->method('writeFile')->with( $this->anything(), $expectedJsonResponse );


        $fakeData = array(
            array( 'id' => 1,   'animal' => 'cat',    'legs' => 4 ),
            array( 'id' => 2,   'animal' => 'cat',    'legs' => 3 ),
            array( 'id' => 3,   'animal' => 'dog',    'legs' => 4 ),
        );

        /**
         * @var \PhpDruidIngest\Preparer\LocalPhpArrayToJsonFilePreparer $p
         */
        $p->prepare( $fakeData );
    }

}