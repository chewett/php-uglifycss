<?php
namespace Chewett\UglifyCSS\Test;

use Chewett\UglifyCSS\CSSUglify;

class CSSUglifyTest extends \PHPUnit_Framework_TestCase
{

    public static $buildDir = __DIR__ . '/../build/output/';

    /**
     * Get the build directory ready to dump out data to it
     */
    public function setUp() {
        if(!is_dir(self::$buildDir)) {
            mkdir(self::$buildDir);
        }
    }

    /**
     * Very basic check to see if the jsuglify version flag works
     */
    public function testCheckVersionWorks() {
        $ug = new CSSUglify();
        $this->assertTrue($ug->checkUglifyCssExists(), "Test to run uglifycss failed, is it installed?");
    }

    /**
     * Tests to see if it fails with the expected exception when ran on a non file
     * @expectedException \Chewett\UglifyCSS\UglifyCSSException
     */
    public function testFileNotReadable() {
        $ug = new CSSUglify();
        $ug->uglify(["not_a_file"], "output.css");
    }


}