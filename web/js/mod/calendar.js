define(function(require,exports,module){

    require("jquery-ui"); // for drag
    
    require("bootstrap");
    require("bootbox");

    require("fullcalendar");
    require("/css/fullcalendar.css");

    function initEvents(event_elems){
        event_elems.each(function() {
            // create an Event Object (http://arshaw.com/fullcalendar/docs/event_data/Event_Object/)
            // it doesn't need to have a start or end
            var eventObject = {
                title: $.trim($(this).text()) // use the element's text as the event title
            };

            // store the Event Object in the DOM element so we can get to it later
            $(this).data('eventObject', eventObject);

            // make the event draggable using jQuery UI
            $(this).draggable({
                zIndex: 999,
                revert: true,      // will cause the event to go back to its
                revertDuration: 0  //  original position after the drag
            });
            
        });

    }




    /* initialize the calendar
    -----------------------------------------------------------------*/

    var date = new Date();
    var d = date.getDate();
    var m = date.getMonth();
    var y = date.getFullYear();

    function initCalendar(calendar_elem,data){
        var events = data.events;
        calendar_elem.fullCalendar({
            buttonText: {
                prev: '<i class="icon-chevron-left"></i>',
                next: '<i class="icon-chevron-right"></i>'
            },
        
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            events: _.map(events,function(ev){
                ev.start = new Date(ev.start);
                if(ev.end){
                    ev.end = new Date(ev.end);
                }
                return ev;
            }),
            editable: true,
            droppable: true, // this allows things to be dropped onto the calendar !!!
            drop: function(date, allDay) { // this function is called when something is dropped
            
                // retrieve the dropped element's stored Event Object
                var originalEventObject = $(this).data('eventObject');
                var $extraEventClass = $(this).attr('data-class');
                
                
                // we need to copy it, so that multiple events don't have a reference to the same object
                var copiedEventObject = $.extend({}, originalEventObject);
                
                // assign it the date that was reported
                copiedEventObject.start = date;
                copiedEventObject.allDay = allDay;
                if($extraEventClass) copiedEventObject.className = [$extraEventClass];
                
                // render the event on the calendar
                // the last `true` argument determines if the event "sticks" (http://arshaw.com/fullcalendar/docs/event_rendering/renderEvent/)
                calendar_elem.fullCalendar('renderEvent', copiedEventObject, true);
                
                // is the "remove after drop" checkbox checked?
                // if ($('#drop-remove').is(':checked')) {
                    // if so, remove the element from the "Draggable Events" list
                    $(this).remove();
                // }
                
            }
            ,
            selectable: true,
            selectHelper: true,
            select: function(start, end, allDay) {
                var calendar = this;
                bootbox.prompt("New Event Title:", function(title) {
                    if (title !== null) {
                        calendar.fullCalendar('renderEvent',
                            {
                                title: title,
                                start: start,
                                end: end,
                                allDay: allDay
                            },
                            true // make the event "stick"
                        );
                    }
                });
                

                calendar.fullCalendar('unselect');
                
            }
            ,
            eventClick: function(calEvent, jsEvent, view) {
                var calendar = this;
                var form = $("<form class='form-inline'><label>Change event name &nbsp;</label></form>");
                form.append("<input autocomplete=off type=text value='" + calEvent.title + "' /> ");
                form.append("<button type='submit' class='btn btn-small btn-success'><i class='icon-ok'></i> Save</button>");
                
                var div = bootbox.dialog(form,
                    [
                    {
                        "label" : "<i class='icon-trash'></i> Delete Event",
                        "class" : "btn-small btn-danger",
                        "callback": function() {
                            calendar.fullCalendar('removeEvents' , function(ev){
                                return (ev._id == calEvent._id);
                            })
                        }
                    }
                    ,
                    {
                        "label" : "<i class='icon-remove'></i> Close",
                        "class" : "btn-small"
                    }
                    ]
                    ,
                    {
                        // prompts need a few extra options
                        "onEscape": function(){div.modal("hide");}
                    }
                );
                
                form.on('submit', function(){
                    calEvent.title = form.find("input[type=text]").val();
                    calendar.fullCalendar('updateEvent', calEvent);
                    div.modal("hide");
                    return false;
                });
            }
            
        });
    }


    module.exports = {
        render:function(data,event_elems,calendar_elem){
            initEvents(event_elems);
            initCalendar(calendar_elem,data);
        }
    }




});