@extends('crudbooster::admin_template')

@push('head')

@endpush

@section('content')
<div class="box">
    <div class="panel panel-default">
        <div class="panel-heading text-center">
            User Export Privilege Update
        </div>
        <form action="{{ route('export-privileges.save') }}" method="post" id="user-export">
            <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
            <input type="hidden" name="table_name" class="tableName">
            <div class="panel-body">
                <div class="col-md-4">

                    <div class="form-group">
                        <label for="modules">Modules</label>
                        <select name="modules" id="modules" class="form-control" required>
                            <option value="">Select Module</option>
                            @foreach ($modules as $module)
                                <option value="{{ $module->id }}" {{ ($row->cms_moduls_id == $module->id ) ? "selected" : "" }} data-table="{{ $module->table_name }}">{{ Illuminate\Support\Str::upper($module->name) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="action_types">Action Type</label>
                        <select name="action_types" id="action_types" class="form-control" required>
                            <option value="">Select Action Type</option>
                            @foreach ($actionTypes as $action)
                                <option value="{{ $action->id }}" {{ ($row->action_types_id == $action->id ) ? "selected" : "" }}>{{ Illuminate\Support\Str::upper($action->action_type) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="cms_privileges">Privilege</label>
                        <select name="cms_privileges" id="cms_privileges" class="form-control" required>
                            <option value="">Select Privilege</option>
                            @foreach ($privileges as $privilege)
                                <option value="{{ $privilege->id }}" {{ ($row->cms_privileges_id == $privilege->id ) ? "selected" : "" }}>{{ Illuminate\Support\Str::upper($privilege->name) }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="col-md-8">
                    <div class="form-group">
                        <label>Columns</label>
                        <div class="left col-md-4">

                        </div>
                        <div class="mid col-md-4">

                        </div>
                        <div class="right col-md-4">

                        </div>
                    </div>
                </div>

            </div>
            <div class="panel-footer">
                <a href='#' class='btn btn-default'>Cancel</a>
                <input type='submit' id="btnSave" class='btn btn-primary pull-right' value='Save' />
            </div>
        </form>
    </div>
</div>

@endsection


@push('bottom')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.3/jquery.min.js" integrity="sha512-SFaNb3xC08k/Wf6CRM1J+O/vv4YWyrPBSdy0o+1nqKzf+uLrIBnaeo8aYoAAOd31nMNHwX8zwVwTMbbCJjA8Kg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        $(document).ready(function() {
            $("#modules").trigger('change');

            $("#modules").change(function () {
                let selectedTable = $(this).find(':selected').data('table');
                $('.tableName').val(selectedTable);
                $.ajax({
                    url: "{{ route('export-privileges.getUserTableColumns') }}",
                    method: "POST",
                    data:{
                        _token: $('#token').val(),
                        tableName: selectedTable,
                        cms_privileges: $('#cms_privileges').find(':selected').val(),
                        action_types: $('#action_types').find(':selected').val()
                    },
                    success : function (data){
                        const length = Object.entries(data.columns).length;
                        const oldData = data.rows;
                        
                        $('.left').empty();
                        $('.mid').empty();
                        $('.right').empty();

                        $.each(data.columns, function(i,val) {
                            const currentLength = $('.report-headers').get().length;
                            
                            const checkboxElement = $('<input type="checkbox" class="report-headers">').attr({
                                id: 'checkbox_'+i,
                                name: 'table_columns['+i+']',
                                value: val,
                                checked: (oldData.includes(i)) ? true : false
                            });

                            const labelElement = $('<label>').attr('for', 'checkbox_' + i).text(val);

                            if (currentLength < length*(1/3)) {
                                $('.left').append('<br>',checkboxElement, labelElement);
                            }
                            else if (currentLength < length*(2/3)) {
                                $('.mid').append('<br>',checkboxElement, labelElement);
                            }
                            else {
                                $('.right').append('<br>',checkboxElement, labelElement);
                            }

                        });
                    },
                    error: function(xhr){
                        console.log('Request Status: ' + xhr.status + ' Status Text: ' + xhr.statusText + ' ' + xhr.responseText);
                    }

                });
            });
        });
    </script>
@endpush
