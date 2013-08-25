
<div class="span9">
    <div class="space"></div>

    <div id="calendar"></div>
</div>

<div class="span3">
    <div class="widget-box transparent">
        <div class="widget-header">
            <h4>Draggable events</h4>
        </div>

        <div class="widget-main">
            <div id="external-events">
                <% _.each(alternative, function(event) { %> 
                <div class="external-event label-info" data-class="label-info">
                    <i class="icon-move"></i>
                    <%= event.name %>
                </div>
                <% }); %>

                <!--label>
                    <input type="checkbox" class="ace-checkbox" id="drop-remove" />
                    <span class="lbl"> Remove after drop</span>
                </label-->
            </div>
        </div>
    </div>
</div>
