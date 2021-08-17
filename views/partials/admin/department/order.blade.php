<div class="ui inverted segment">
    <h2 class="ui header">Order A Department's Jobs</h2>
    <p>Set the order of a department's jobs.</p>
    <button id="order_department_button" class="fluid ui pink button">
        Order
    </button>
</div>

<div id="order_department_modal" class="ui modal">
    <div class="header">
        Order A Department's Jobs
    </div>
    <div class="scrolling content">
        <form id="order_department_form" action="/admin/department/order" method="POST" class="ui form">
            <div class="ui error message"></div>
            <div class="field">
                <label>Department</label>
                <input type="hidden" id="order_department_order" name="order" value="">
                <select id="order_department_select" name="department" class="ui search dropdown">
                    <option value="">Select A Department</option>
                    <?php $departments = new Department(); // Would prefer to do this in blade but can't :/ ?>
                    @foreach ($departments->GetAll() as $dep)
                        <option value="{{ $dep->GetID() }}">{{ $dep->GetName() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>Jobs (Drag to reorder)</label>

                <div id="order_department_list">
                </div>
            </div>
        </form>
    </div>
    <div class="actions">
        <div class="ui black deny right labeled icon button">
            Nevermind
            <i class="times icon"></i>
        </div>
        <button class="ui green right labeled icon button" type="submit" form="order_department_form" value="Submit">
            Lets do it!
            <i class="checkmark icon"></i>
        </button>
    </div>
</div>

<script>
    $(document).ready(function(){
        $("#order_department_button").click(function(){
            $("#order_department_modal").modal('show');
        });
        $("#order_department_select").on('change', function() {
            $('#order_department_list').empty();

            for (var key in deps[this.value]) {
                let name = deps[this.value][key];

                $('#order_department_list').append('<div class="ui inverted segment" data-class="'+ key + '" data-name="'+ name + '">' + name + '</div>');
            }
        });
        $('.ui.dropdown').dropdown();
        $('#order_department_form')
            .form({
                fields: {
                    department: {
                        identifier: 'department',
                        rules: [
                            {
                                type   : 'empty',
                                prompt : 'Please select a department'
                            }
                        ]
                    }
                }
            })
        ;
        $('#order_department_list').sortable({
            group: 'list',
            ghostClass: 'blue',
            onSort: rebuildOrder
        });
        function rebuildOrder() {
            let order = '';
            let jobs = [];
            $('#order_department_list').children().each(function() {
                jobs.push(this.getAttribute("data-class"));
            });

            $('#order_department_order').val(JSON.stringify(jobs));
        }
    });
</script>