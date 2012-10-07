<?php
/*
 * Copyright (c) 2011 Dalton Tan
 * Licensed under the MIT license http://www.opensource.org/licenses/mit-license
 */

/**
 * Yhis class represents a folder. It it used to search for more foders and files
 * within in. It will collect the total number of lines in the current folder,
 * including all subfolders.
 *
 * @author Dalton Tan
 */
class Folder {

    private $_path;
    private $_option;
    private $_lines = 0;
    private $_whitespace = 0;
    private $_comments = 0;

    public function __construct($path, $option) {
        $this->_path = $path;
        $this->_option = $option;
    }

    /**
     * Starts by retrieving all files and folders and pass it to respective methods
     */
    public function init() {
        $dir = opendir($this->_path);
        if($dir !== false) {
            //echo Html::open('ul');
            while(($filename = readdir($dir)) !== false) {
                $path = $this->_path . '/' . $filename;
                $result = null;
                if(is_dir($path)) {
                    $result = $this->searchFolders($path, $filename);
                } elseif(is_file($path)) {
                    $result = $this->searchFiles($path, $filename);
                }
                $this->_lines += $result['lines'];
                $this->_whitespace += $result['whitespace'];
                $this->_comments += $result['comments'];
            }
            //echo Html::close('ul');
        }
        closedir($dir);
    }

    /**
     * Start a new search on the current folder.
     * @param string $path path to the folder.
     * @param string $name name of the folder.
     * @return array contains 'lines', 'whitespace' and 'comments'.
     */
    private function searchFolders($path, $name) {
        //Check that folder is the one we want
        if($name !== '.' && $name !== '..'
                && !in_array($name, $this->_option->ignoreFolders)) {
           // echo Html::folder($name);
            $folder = new Folder($path, $this->_option);
            $folder->init();
            $lines = $folder->getLines();
            if($lines > 0) {
                //echo Html::summary('Total: ' . $lines);
            }

            return array(
                'lines' => $lines,
                'whitespace' => $folder->getWhitespace(),
                'comments' => $folder->getComments()
            );
        } else {
            return 0;
        }
    }

    /**
     * Counts the numder of line in the file for, excluding unwanted lines.
     * @param string $path path to the file.
     * @param string $name name of the file.
     * @return array contains 'lines', 'whitespace' and 'comments'.
     */
    private function searchFiles($path, $name) {
        if(in_array(pathinfo($name, PATHINFO_EXTENSION), $this->_option->extensions)
                && !in_array($name, $this->_option->ignoreFiles)) {
            $file = new File($path, $this->_option);
            $results = $file->countLines();
            //echo Html::file("$name [{$results['lines']}]");

            return $results;
        } else {
            return 0;
        }
    }

    public function getLines() {
        return $this->_lines;
    }

    public function getWhitespace() {
        return $this->_whitespace;
    }

    public function getComments() {
        return $this->_comments;
    }

}

?>
