<?php
/**
 * Created by PhpStorm.
 * User: guild
 * Date: 8/12/2018
 * Time: 5:07 PM
 */

class DRMAccess{
    public static function getHTMLDRM($htmlPath){
        $file = fopen($htmlPath, 'r');
        while(!feof($file)){
            $line = fgets($file);
            if(stristr($line, '<footer')){
                fclose($file);
                return $line;
            }
        }
        return null;
    }
    public static function getHTMLDRMRef($cssRelPath){
        return '<link rel="stylesheet" href="' . $cssRelPath . '/DRM.css" />';
    }

    public static function getOPFRef($cssPath){
        return '<item id="drm"  href="' . $cssPath . '" media-type="text/css" />';
    }

}