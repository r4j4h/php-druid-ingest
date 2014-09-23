<?php

namespace PhpDruidIngest\Preparer;

use PHPUnit_Framework_TestCase;

class LocalPhpArrayToJsonPreparerTest extends PHPUnit_Framework_TestCase
{
    public function testWritesWithNewlineDelimiter()
    {
        $p = new LocalPhpArrayToJsonFilePreparer();

        $fakeData = array(
            array( 'id' => 1,   'animal' => 'cat',    'legs' => 4 ),
            array( 'id' => 2,   'animal' => 'cat',    'legs' => 3 ),
            array( 'id' => 3,   'animal' => 'dog',    'legs' => 4 ),
            array( 'id' => 4,   'animal' => 'spider', 'legs' => 8 ),
        );

        $p->prepare( $fakeData );

        $this->markTestIncomplete('need to mock io');
    }

}