<?php
/*
 * Copyright (c) 2011 Dalton Tan
 * Licensed under the MIT license http://www.opensource.org/licenses/mit-license
 */

/**
 * This class represents a file and is used to count the number of lines it has.
 *
 * @author Dalton Tan
 */
class File {

    private $_path;
    private $_option;
    private $_lines = 0;

    public function __construct($path, $option) {
        $this->_path = $path;
        $this->_option = $option;
    }

    /**
     * Counts the number of lines in the current file, considering black lines
     * and comments.
     * @return array contains 'lines', 'whitespace' and 'comments'.
     */
    public function countLines() {
        $lines = file($this->_path);
        $countWhitespace = $this->_option->whitespace;
        $countComments = $this->_option->comments;
        $count = 0;
        $whitespace = 0;
        $comments = 0;
        $hadComment = false;

        for($i = 0, $length = count($lines); $i < $length; $i++) {
            $line = $lines[$i];

            //If it's an empty line continue
            if(!$countWhitespace && preg_match('/^\s+$/', $line)) {
                $whitespace++;
                continue;
            }

            //Check comments
            if(!$countComments) {
                //Check to see if multi-line comment has ended
                if($hadComment) {
                    $comments++;
                    //End of comment found
                    if(preg_match('/\*\//', $line)) {
                        $hadComment = false;
                    } else {
                        continue;
                    }
                }

                //Found multi-line comments
                if(preg_match('/^.*\/\*/', $line)) {
                    $comments++;
                    $hadComment = true;
                    //See if found end of comment on same line
                    if(preg_match('/\*\//', $line)) {
                        $hadComment = false;
                    }
                    continue;
                }

                //Found inline comments
                if(preg_match('/^.*(\/\/|#)/', $line)) {
                    $comments++;
                    continue;
                }
            }

            //Still no empty line and comment then add line number.
            $count++;
        }

        return array(
            'lines' => $count,
            'whitespace' => $whitespace,
            'comments' => $comments,
        );
    }

}

?>
