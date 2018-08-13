<?php
include_once "DRMAccess.php";
include_once "Constants.php";
/**
 * Created by PhpStorm.
 * User: guild
 * Date: 8/10/2018
 * Time: 8:54 AM
 */

class DRMInserter{
    public static $opfPath;
    public static function insertDRM($bookPath){
        $pathList = FolderManipulator::getFolders($bookPath);
        $cssFolder = DRMInserter::getCSSFolder($bookPath);
        foreach($pathList as $i){
            if(strcmp('TOC', NameManipulator::getFileName($i))!=0) {
                $tempExt = NameManipulator::getFileExtension($i);
                if (DRMInserter::hasModifiableExtension($tempExt) && !is_dir($i)) {
                    if(strcmp('css', $tempExt)!=0)
                        DRMInserter::modifyStructure($i, $tempExt, $cssFolder);
                    else
                        DRMInserter::createCSSFile($i);
                }
            }


        }
    }

    private static function getExtensions(){
        return ['xhtml', 'html','css', 'opf'];

    }

    private static function hasModifiableExtension($ext){
        foreach(DRMInserter::getExtensions() as $i){
            //echo "actExt: $i, destExt: $ext<br>";
            if(strcasecmp($ext, $i)==0)
                return true;
        }
        return false;
    }

    private static function modifyStructure($path, $ext, $cssFolder){
        $read = fopen($path, 'r');
        $tempName = $path . '.tmp';
        $write = fopen($tempName, 'w');
        $replaced = false;
        while(!feof($read)){
            $line = fgets($read);
            if(strcmp('html', $ext)==0 | strcmp('xhtml', $ext)==0){
                if(stristr($line, '</head>')){
                    $pos = strpos($line, '</head>');
                    $cssFolder = substr($cssFolder, strrpos($path, '/')+1, strlen($cssFolder));
                    $line = substr($line, 0, $pos) . DRMAccess::getHTMLDRMRef($cssFolder) . substr($line, $pos, strlen($line));
                    $replaced = true;
                }
                if(stristr($line, '</body>')){
                    $pos = strpos($line, '</body>');
                    $line = substr($line, 0, $pos) . DRMAccess::getHTMLDRM(Constants::$htmlPath) . substr($line, $pos, strlen($line));
                    $replaced = true;
                }
                fputs($write, $line);
            }
            if(strcmp('opf', $ext)==0){
                DRMInserter::$opfPath = $path;
                if(stristr($line, '</manifest>')){
                    $pos = strpos($line, '</manifest>');
                    $cssFolder = substr($cssFolder, strrpos($path, '/')+1, strlen($cssFolder)) . '/DRM.css';
                    $line = substr($line, 0, $pos) . DRMAccess::getOPFRef($cssFolder) . substr($line, $pos, strlen($line));
                    $replaced = true;
                }
                fputs($write, $line);
            }

        }
        if(!feof($read))
            DefaultMessages::fileOpenError();
        fclose($read);
        fclose($write);
        if($replaced)
            rename($tempName, $path);
        else
            unlink($tempName);

    }

    private static function createCSSFile($path){
        $cssRelPath = substr($path, 0, strrpos($path, '/')) . '/DRM.css';
        if(!file_exists($cssRelPath)){
            $readCSS = fopen(Constants::$cssPath, 'r');
            $writeCSS = fopen($cssRelPath, 'w');
            while(!feof($readCSS)){
                $line = fgets($readCSS);
                fputs($writeCSS, $line);
            }
            fclose($readCSS);
            fclose($writeCSS);
        }
    }

    private static function getCSSFolder($bookPath){
        $pathList = FolderManipulator::getFolders($bookPath);
        foreach($pathList as $i){
            $tempExt = NameManipulator::getFileExtension($i);
            if(strcmp($tempExt, 'css')==0 && !is_dir($i))
                return substr($i, 0, strrpos($i, '/'));



        }
        return null;
    }

}