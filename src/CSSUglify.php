<?php
namespace Chewett\UglifyCSS;

class CSSUglify
{

    /** @var string Path to the uglifycss script */
    private $uglifyBinaryPath = 'uglifycss';

    public static $options = [
        "max-line-len" => "string",
        "expand-vars" => "string",
        "ugly-comments" => "string",
        "cute-comments" => "string",
        "convert-urls" => "string",
        "debug" => "string"
    ];


    public function setUglifyBinaryPath($uglifyBinaryPath) {
        $this->uglifyBinaryPath = $uglifyBinaryPath;
    }

    public function getUglifyBinaryPath() {
        return $this->uglifyBinaryPath;
    }

    /**
     * Internal function used to validate that the uglifycss script exists and works (to some degree)
     * @return bool
     */
    public function checkUglifyCssExists() {
        $command = $this->uglifyBinaryPath . " --version";
        exec($command, $outputText, $returnCode);
        return ($returnCode == 0);
    }

    public function uglify(array $files, $outputFilename, array $options = [], $finalCssHeaderFilename=null) {
        foreach($files as $filename) {
            if(!is_readable($filename)) {
                throw new UglifyCssException("Filename " . $filename . " is not readable");
            }
        }
        $optionsString = $this->validateOptions($options);
        $fileNames = implode(' ', array_map('escapeshellarg', $files));

        $tmpUglifyCssOutput = tempnam(sys_get_temp_dir(), "uglify_css_intermediate_out_");
        $safeShellTmpUglifyCssFilename = escapeshellarg($tmpUglifyCssOutput);

        $commandString = $this->uglifyBinaryPath . "  {$optionsString} --output {$safeShellTmpUglifyCssFilename} {$fileNames}";

        exec($commandString, $output, $returnCode);
        if($returnCode !== 0) {
            throw new UglifyCssException("Failed to run uglifycss, something went wrong... command: " . $commandString);
        }

        if($finalCssHeaderFilename) {
            //If we have provided a header filename then we are going to get the uglified file then prepend the data
            $context = stream_context_create();
            //Open both files in stream mode so we dont load the entire file into memory, streams are the best!
            $uglifyCssOutputFileHandler = fopen($tmpUglifyCssOutput, 'r', false, $context);
            $cssHeaderFileHandler = fopen($finalCssHeaderFilename, 'r',false, $context);

            $tmpFinalOutput = tempnam(sys_get_temp_dir(), 'php_uglify_css_out_');
            file_put_contents($tmpFinalOutput, $cssHeaderFileHandler);
            file_put_contents($tmpFinalOutput, $uglifyCssOutputFileHandler, FILE_APPEND);

            //Close unlink and move the files we dont need
            fclose($uglifyCssOutputFileHandler);
            fclose($cssHeaderFileHandler);
            unlink($tmpUglifyCssOutput);
            rename($tmpFinalOutput, $outputFilename);
        }else{
            //Dont try and add any files, just move the temporary file into the final location
            rename($tmpUglifyCssOutput, $outputFilename);
        }

        return $output;


    }

    private function validateOptions($options) {
        $optionsString = '';
        foreach($options as $option => $value) {
            if(!array_key_exists($option, self::$options)) {
                throw new UglifyCSSException('Option not supported');
            }

            $optionType = self::$options[$option];
            if($optionType === 'string') {
                if($value === '') {
                    $optionsString .= "--{$option} ";
                }else{
                    $optionValue = escapeshellarg($value);
                    $optionsString .= "--{$option}={$optionValue} ";
                }

            }else{
                throw new UglifyCSSException('Option type ' . $option . ' not supported');
            }
        }
        return $optionsString;
    }


}