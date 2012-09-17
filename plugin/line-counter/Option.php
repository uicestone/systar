<?php
/*
 * Copyright (c) 2011 Dalton Tan
 * Licensed under the MIT license http://www.opensource.org/licenses/mit-license
 */

/**
 * A value object to store options used by File and Folder.
 *
 * @author Dalton Tan
 */
class Option {

    /**
     * @var boolean Whether to count blanks lines, true to count. Defautls to false.
     */
    public $whitespace = false;
    /**
     * @var boolean Whether to count comments, true to count. Defautls to false.
     */
    public $comments = false;
    /**
     * @var array An array of folders to ignore
     */
    public $ignoreFolders = array();
    /**
     * @var array An array of filde to ignore, including the extension.
     */
    public $ignoreFiles = array();
    /**
     * @var array An array of extension names with the leading ".". This array is
     * mandotary for the scanning to work.
     */
    public $extensions = array();

    public function __construct($array = array()) {
        foreach($array as $key => $value) {
            $this->$key = $value;
        }
    }

}

?>
