<?php
/*
 * Copyright (c) 2011 Dalton Tan
 * Licensed under the MIT license http://www.opensource.org/licenses/mit-license
 */

/**
 * This is a simple static class for formatting HTML code.
 *
 * @author Dalton Tan
 */
class Html {

    public static function tag($tag, $txt = '') {
        return "<$tag>$txt</$tag>";
    }

    public static function open($tag) {
        return "<$tag>";
    }

    public static function close($tag) {
        return "</$tag>";
    }

    public static function p($txt) {
        return self::tag('p', $txt);
    }

    public static function comment($txt) {
        return self::tag('p', self::tag('i', $txt));
    }

    public static function file($txt) {
        return "<li class='file'>$txt</li>";
    }

    public static function folder($txt) {
        return "<li class='folder'>$txt</li>";
    }

    public static function summary($txt) {
        return "<ul><li><i><b>$txt</b></i></li></ul>";
    }

    public static function b($txt) {
        return self::tag('b', $txt);
    }

}

?>
