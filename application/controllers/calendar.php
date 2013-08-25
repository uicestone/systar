<?php
class Calendar extends SS_Controller{
    

    function __construct() {
        parent::__construct();
    }

    function getList(){
        $events = array();
        $events[0] = array(
            "title" => "All Day Event",
            "start" => 1377403953873,
            "className" => "label-important"
        );
        $events[1] = array(
            "title" => "Long Events",
            "start" => 1376928000000,
            "end" => 1377014400000,
            "className" => "label-success"
        );
        $events[2] = array(
            "title" => "Some Event",
            "start" => 1377331200000,
            "allDay" => false
        );

        $alternative = array(
            array(
                "name" => "event 1"
            ),
            array(
                "name" => "event 2"
            ),
            array(
                "name" => "event 3"
            )
        );

        $data = array(
            "events" => $events,
            "alternative" => $alternative
        );
        $this->output->set_output(json_encode($data));
    }
}
?>
