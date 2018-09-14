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
     * Simple provider to make tests run with a couple different options when testing php-uglifycss
     * @return array Provider details
     */
    public function optionsHeaderfileProvider() {
        $headerfilePath = __DIR__ . "/../build/headerfile.css";

        return [
            'No options no header files' => [null],
            'No options, header file' => [$headerfilePath]
        ];
    }

    /**
     * Very basic check to see if the cssuglify version flag works
     */
    public function testCheckVersionWorks() {
        $ug = new CSSUglify();
        $this->assertTrue($ug->checkUglifyCssExists(), "Test to run uglifycss failed, is it installed?");
    }

    /**
     * This test purposely sets a bad executable so that when we try and check to see if it exists it fails to run
     */
    public function testUglifyCssFailsWhenMissingExe() {
        $ug = new CSSUglify();
        $ug->setUglifyBinaryPath("not_uglifycss");
        $this->assertFalse($ug->checkUglifyCssExists());
    }

    /**
     * Test to make sure that an exception is thrown when uglify is ran when the exe is missing
     * @expectedException \Chewett\UglifyCss\UglifyCSSException
     */
    public function testRunningUglifyCssWhenMissingExe() {
        $ug = new CSSUglify();
        $ug->setUglifyBinaryPath("not_uglifycss");
        $ug->uglify([__DIR__ . '/../vendor/twbs/bootstrap/dist/css/bootstrap.css'], self::$buildDir . 'bootstrap.min.css');
    }

    /**
     * Tests to see if it fails with the expected exception when ran on a non file
     * @expectedException \Chewett\UglifyCSS\UglifyCSSException
     */
    public function testFileNotReadable() {
        $ug = new CSSUglify();
        $ug->uglify(["not_a_file"], "output.css");
    }


    /**
     * Tests to see if running uglify on an empty file works (Expected to work as normal)
     * @dataProvider optionsHeaderfileProvider
     */
    public function testRunningOnEmptyFile($headerfile=null, $options=[]) {
        $outputFilename = self::$buildDir . 'emptyFile.css';
        $ug = new CSSUglify();
        $output = $ug->uglify([__DIR__ . '/../build/emptyFile.css'], $outputFilename, $options, $headerfile);
        $this->assertNotNull($output);

        $this->assertFileExists($outputFilename);
    }

    /**
     * Tests to see if minifying multiple files throws an error
     * @dataProvider optionsHeaderfileProvider
     */
    public function testRunningOnTwitterBootstrap($headerfile=null, $options=[]) {
        $outputFilename = self::$buildDir . 'bootstrap.min.css';

        $ug = new CSSUglify();
        $twitterBootstrapDir = __DIR__ . '/../vendor/twbs/bootstrap/dist/css/';
        $output = $ug->uglify([
            $twitterBootstrapDir . "bootstrap.css",
            $twitterBootstrapDir . "bootstrap-theme.css"
        ], $outputFilename, $options, $headerfile);
        $this->assertNotNull($output);

        $this->assertFileExists($outputFilename);
    }



}