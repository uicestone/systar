<table id="sample-table-1" data-removable="true" class="table table-striped table-bordered table-hover">
    <thead>
        <tr>
            <th>Name</th>
            <th>Age</th>
            <th>Job</th>
            <th></th>
        </tr>
    </thead>
    <% _.each(peoples,function(people){ %>
        <tr>
            <td><%= people.name %></td>
            <td><%= people.age %></td>
            <td><%= people.job %></td>
            <td>
                <div class="hidden-phone visible-desktop btn-group">
                    <button class="btn btn-mini btn-danger">
                        <i class="icon-trash bigger-120"></i>
                    </button>
                </div>
            </td>
        </tr>
    <% }); %>
</table>