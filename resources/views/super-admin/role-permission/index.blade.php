@extends('layout/main')
@section('page_title',  trans('admin.role_permission'))
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
@endsection
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            @include('layout/toaster')
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{__('admin.role_permission')}}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('super-admin.role-permission.index')}}">{{__('admin.role_permission')}}</a></li>
                        <li class="breadcrumb-item active">{{__('admin.list')}}</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card {{config('custom.card-primary')}}">
                <div class="card-header">
                    <h3 class="card-title">{{__('admin.assign_role_permission')}}</h3>
                </div>
                <form method="post" action="{{route('super-admin.role-permission.save')}}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>{{__('admin.select_role')}}</label><span class="required"></span><small class="{{config('custom.text-note-css')}}"> {{__('admin.select_role_note')}} </small>
                                    <select name="role_id[]" multiple="" class="form-control select2 {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}">
                                        @foreach($roles as $record)
                                        @if(!$record->permissions()->count())
                                        <option value="{{$record->id}}" {{ (old('role_id') && collect(old('role_id'))->contains($record->id)) ? 'selected' : ''  }}>{{ucfirst($record->name)}}</option>
                                        @endif
                                        @endforeach
                                    </select>
                                    @error('role_id')
                                    <div class="label-message text-danger">{{$message}}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card-body table-responsive p-0">
                                    <label>{{__('admin.select_permission')}}</label><span class="required"></span>
                                    @error('permissions')
                                    <div class="label-message text-danger">{{$message}}</div>
                                    @enderror
                                    <table class="table table-head-fixed text-nowrap table-striped">
                                        <thead>
                                            <tr>
                                                <th>{{__('admin.permission_list')}}
                                                    <div class="icheck-primary">
                                                        <input type="checkbox" class="chk_all" id="chk_all">
                                                        <label for="chk_all">
                                                            {{__('admin.select_all')}}
                                                        </label>
                                                    </div>
                                                </th>
                                                <th>{{__('admin.view')}}
                                                    <div class="icheck-primary">
                                                        <input type="checkbox" class="chk_all_view" id="chk_all_view">
                                                        <label for="chk_all_view">
                                                            {{__('admin.select_all')}}
                                                        </label>
                                                    </div>
                                                </th>
                                                <th>{{__('admin.create')}}
                                                    <div class="icheck-primary">
                                                        <input type="checkbox" class="chk_all_create" id="chk_all_create">
                                                        <label for="chk_all_create">
                                                            {{__('admin.select_all')}}
                                                        </label>
                                                    </div>
                                                </th>
                                                <th>{{__('admin.edit')}}
                                                    <div class="icheck-primary">
                                                        <input type="checkbox" class="chk_all_edit" id="chk_all_edit">
                                                        <label for="chk_all_edit">
                                                            {{__('admin.select_all')}} 
                                                        </label>
                                                    </div>
                                                </th>
                                                <th>{{__('admin.delete')}}
                                                    <div class="icheck-primary">
                                                        <input type="checkbox" class="chk_all_delete" id="chk_all_delete">
                                                        <label for="chk_all_delete">
                                                            {{__('admin.select_all')}}
                                                        </label>
                                                    </div>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($menu_level1 as $level1)
                                            <tr>
                                                <td class="level_1">{{$level1->name}}
                                                    <!--Level 1-->
                                                    <div class="icheck-primary">
                                                        <input type="checkbox" class="i-level1_{{$level1->id}}-all level1_chk_all level1_{{$level1->id}}" id="level1_{{$level1->id}}">
                                                        <label for="level1_{{$level1->id}}">
                                                            {{__('admin.select_all')}}
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <!--View-->
                                                    <div class="icheck-primary">
                                                        <input {{ (old('permissions') && collect(old('permissions'))->contains('View_'.$level1->id)) ? 'checked' : ''  }} type="checkbox" name="permissions[]" class="level1 view_chk level1_{{$level1->id}}" data-level1='i-level1_{{$level1->id}}' value="View_{{$level1->id}}" id="View_{{$level1->id}}">
                                                            <label for="View_{{$level1->id}}">
                                                            {{__('admin.view')}} {{$level1->name}}
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <!--Create-->
                                                    <div class="icheck-primary">
                                                        <input {{ (old('permissions') && collect(old('permissions'))->contains('Create_'.$level1->id)) ? 'checked' : ''  }} type="checkbox" name="permissions[]" class="level1 create_chk level1_{{$level1->id}}" data-level1='i-level1_{{$level1->id}}' value="Create_{{$level1->id}}" id="Create_{{$level1->id}}">
                                                            <label for="Create_{{$level1->id}}">
                                                            {{__('admin.create')}} {{$level1->name}}
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <!--Edit-->
                                                    <div class="icheck-primary">
                                                        <input {{ (old('permissions') && collect(old('permissions'))->contains('Edit_'.$level1->id)) ? 'checked' : ''  }} type="checkbox" name="permissions[]" class="level1 edit_chk level1_{{$level1->id}}" data-level1='i-level1_{{$level1->id}}' value="Edit_{{$level1->id}}" id="Edit_{{$level1->id}}">
                                                            <label for="Edit_{{$level1->id}}">
                                                            {{__('admin.edit')}} {{$level1->name}}
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <!--Delete-->
                                                    <div class="icheck-primary">
                                                        <input {{ (old('permissions') && collect(old('permissions'))->contains('Delete_'.$level1->id)) ? 'checked' : ''  }}  type="checkbox" name="permissions[]" class="level1 delete_chk level1_{{$level1->id}}" data-level1='i-level1_{{$level1->id}}' value="Delete_{{$level1->id}}" id="Delete_{{$level1->id}}">
                                                            <label for="Delete_{{$level1->id}}">
                                                            {{__('admin.delete')}} {{$level1->name}}
                                                        </label>
                                                    </div>
                                                </td>
                                                @if($level1->getMenuLevel2())
                                            </tr>
                                            @foreach($level1->getMenuLevel2() as $level2)
                                            <tr>
                                                <td class="level_2">{{$level2->name}}
                                                    <!--Level 2-->
                                                    <div class="icheck-primary">
                                                        <input  type="checkbox" class="i-level2_{{$level1->id}}_{{$level2->id}}-all level2_chk_all level1_{{$level1->id}} level2_{{$level2->id}}" id="level1_{{$level1->id}}_level2_{{$level2->id}}">
                                                        <label for="level1_{{$level1->id}}_level2_{{$level2->id}}">
                                                            {{__('admin.select_all')}}
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <!--View-->
                                                    <div class="icheck-primary">
                                                        <input {{ (old('permissions') && collect(old('permissions'))->contains('View_'.$level1->id.'_'.$level2->id)) ? 'checked' : ''  }} type="checkbox" name="permissions[]" class="level1 view_chk level1_{{$level1->id}} level2_{{$level2->id}}" data-level2='i-level2_{{$level1->id}}_{{$level2->id}}' value="View_{{$level1->id}}_{{$level2->id}}" id="View_{{$level1->id}}_{{$level2->id}}">
                                                            <label for="View_{{$level1->id}}_{{$level2->id}}">
                                                            {{__('admin.view')}} {{$level2->name}}
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <!--Create-->
                                                    <div class="icheck-primary">
                                                        <input {{ (old('permissions') && collect(old('permissions'))->contains('Create_'.$level1->id.'_'.$level2->id)) ? 'checked' : ''  }} type="checkbox" name="permissions[]" class="level1 create_chk level1_{{$level1->id}} level2_{{$level2->id}}" data-level2='i-level2_{{$level1->id}}_{{$level2->id}}' value="Create_{{$level1->id}}_{{$level2->id}}" id="Create_{{$level1->id}}_{{$level2->id}}">
                                                            <label for="Create_{{$level1->id}}_{{$level2->id}}">
                                                            {{__('admin.create')}} {{$level2->name}}
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <!--Edit-->
                                                    <div class="icheck-primary">
                                                        <input {{ (old('permissions') && collect(old('permissions'))->contains('Edit_'.$level1->id.'_'.$level2->id)) ? 'checked' : ''  }} type="checkbox" name="permissions[]" class="level1 edit_chk level1_{{$level1->id}} level2_{{$level2->id}}" data-level2='i-level2_{{$level1->id}}_{{$level2->id}}' value="Edit_{{$level1->id}}_{{$level2->id}}" id="Edit_{{$level1->id}}_{{$level2->id}}">
                                                            <label for="Edit_{{$level1->id}}_{{$level2->id}}">
                                                            {{__('admin.edit')}} {{$level2->name}}
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <!--Delete-->
                                                    <div class="icheck-primary">
                                                        <input {{ (old('permissions') && collect(old('permissions'))->contains('Delete_'.$level1->id.'_'.$level2->id)) ? 'checked' : ''  }} type="checkbox" name="permissions[]" class="level1 delete_chk level1_{{$level1->id}} level2_{{$level2->id}}" data-level2='i-level2_{{$level1->id}}_{{$level2->id}}' value="Delete_{{$level1->id}}_{{$level2->id}}" id="Delete_{{$level1->id}}_{{$level2->id}}">
                                                            <label for="Delete_{{$level1->id}}_{{$level2->id}}">
                                                            {{__('admin.delete')}} {{$level2->name}}
                                                        </label>
                                                    </div>
                                                </td>
                                                @if($level2->getMenuLevel3())
                                            </tr>
                                            @foreach($level2->getMenuLevel3() as $level3)
                                            <tr>
                                                <td class="level_3">{{$level3->name}}
                                                    <!--Level 3-->
                                                    <div class="icheck-primary">
                                                        <input type="checkbox" class="i-level3_{{$level1->id}}_{{$level2->id}}_{{$level3->id}}-all level3_chk_all level1_{{$level1->id}} level2_{{$level2->id}} level3_{{$level3->id}}" id='level1_{{$level1->id}}_level2_{{$level2->id}}_level3_{{$level3->id}}'>
                                                        <label for="level1_{{$level1->id}}_level2_{{$level2->id}}_level3_{{$level3->id}}">
                                                            {{__('admin.select_all')}}
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <!--View-->
                                                    <div class="icheck-primary">
                                                        <input {{ (old('permissions') && collect(old('permissions'))->contains('View_'.$level1->id.'_'.$level2->id.'_'.$level3->id)) ? 'checked' : ''  }}  type="checkbox" name="permissions[]" class="level1 view_chk level1_{{$level1->id}} level2_{{$level2->id}} level3_{{$level3->id}}" data-level3='i-level3_{{$level1->id}}_{{$level2->id}}_{{$level3->id}}' value="View_{{$level1->id}}_{{$level2->id}}_{{$level3->id}}" id="View_{{$level1->id}}_{{$level2->id}}_{{$level3->id}}">
                                                            <label for="View_{{$level1->id}}_{{$level2->id}}_{{$level3->id}}">
                                                            {{__('admin.view')}} {{$level3->name}}
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <!--Create-->
                                                    <div class="icheck-primary">
                                                        <input {{ (old('permissions') && collect(old('permissions'))->contains('Create_'.$level1->id.'_'.$level2->id.'_'.$level3->id)) ? 'checked' : ''  }}  type="checkbox" name="permissions[]" class="level1 create_chk level1_{{$level1->id}} level2_{{$level2->id}} level3_{{$level3->id}}" data-level3='i-level3_{{$level1->id}}_{{$level2->id}}_{{$level3->id}}' value="Create_{{$level1->id}}_{{$level2->id}}_{{$level3->id}}" id="Create_{{$level1->id}}_{{$level2->id}}_{{$level3->id}}">
                                                            <label for="Create_{{$level1->id}}_{{$level2->id}}_{{$level3->id}}">
                                                            {{__('admin.create')}} {{$level3->name}}
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <!--Edit-->
                                                    <div class="icheck-primary">
                                                        <input {{ (old('permissions') && collect(old('permissions'))->contains('Edit_'.$level1->id.'_'.$level2->id.'_'.$level3->id)) ? 'checked' : ''  }}  type="checkbox" name="permissions[]" class="level1 edit_chk level1_{{$level1->id}} level2_{{$level2->id}} level3_{{$level3->id}}" data-level3='i-level3_{{$level1->id}}_{{$level2->id}}_{{$level3->id}}' value="Edit_{{$level1->id}}_{{$level2->id}}_{{$level3->id}}" id="Edit_{{$level1->id}}_{{$level2->id}}_{{$level3->id}}">
                                                            <label for="Edit_{{$level1->id}}_{{$level2->id}}_{{$level3->id}}">
                                                            {{__('admin.edit')}} {{$level3->name}}
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <!--Delete-->
                                                    <div class="icheck-primary">
                                                        <input {{ (old('permissions') && collect(old('permissions'))->contains('Delete_'.$level1->id.'_'.$level2->id.'_'.$level3->id)) ? 'checked' : ''  }}  type="checkbox" name="permissions[]" class="level1 delete_chk level1_{{$level1->id}} level2_{{$level2->id}} level3_{{$level3->id}}" data-level3='i-level3_{{$level1->id}}_{{$level2->id}}_{{$level3->id}}' value="Delete_{{$level1->id}}_{{$level2->id}}_{{$level3->id}}" id="Delete_{{$level1->id}}_{{$level2->id}}_{{$level3->id}}">
                                                            <label for="Delete_{{$level1->id}}_{{$level2->id}}_{{$level3->id}}">
                                                            {{__('admin.delete')}} {{$level3->name}}
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                            @endif
                                            @endforeach
                                            @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        @can(config('custom_middleware.create_super_admin_role_permission'))
                        <div class="btn-toolbar float-right">
                            <div class="btn-group mr-2">
                                <a href="{{route('super-admin.role-permission.index')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a>
                            </div>
                            <div class="btn-group">
                                <button type="submit" class="{{config('custom.btn-success-form')}}" title="{{__('admin.submit')}}">{{__('admin.submit')}}</button>
                            </div>
                        </div>
                        @endcan
                    </div>
                </form>
            </div>
        </div>
    </section>
    <!-- /.content -->
</div>
@endsection
@section('page_specific_js')
@include('layout/select2')
<script>
$(document).ready(function ()
{
    $(window).keydown(function (event)
    {
        if (event.keyCode == 13)
        {
            event.preventDefault();
            return false;
        }
    });

    check_uncheck('view_chk', 'chk_all_view');
    check_uncheck('create_chk', 'chk_all_create');
    check_uncheck('edit_chk', 'chk_all_edit');
    check_uncheck('delete_chk', 'chk_all_delete');

    $('.level1_chk_all').change(function ()
    {
        var classes = $(this).attr("class").split(" ");
        var selected_class = classes[classes.length - 1];

        if (this.checked)
        {
            $("." + selected_class).prop('checked', true);
        }
        else
        {
            $("." + selected_class).prop('checked', false);
        }
    });
    $('.level2_chk_all').change(function ()
    {
        var classes = $(this).attr("class").split(" ");
        var selected_class = classes[classes.length - 1];

        if (this.checked)
        {
            $("." + selected_class).prop('checked', true);
        }
        else
        {
            $("." + selected_class).prop('checked', false);
        }
    });
    $('.level3_chk_all').change(function ()
    {
        var classes = $(this).attr("class").split(" ");
        var selected_class = classes[classes.length - 1];

        if (this.checked)
        {
            $("." + selected_class).prop('checked', true);
        }
        else
        {
            $("." + selected_class).prop('checked', false);
        }
    });
    $('.chk_all').change(function ()
    {
        if (this.checked)
        {
            $('input[type=checkbox]').prop('checked', true);
        }
        else
        {
            $('input[type=checkbox]').prop('checked', false);
        }
    });
    $('.chk_all_view').change(function ()
    {
        if (this.checked)
        {
            $('.view_chk').prop('checked', true);
        }
        else
        {
            $('.view_chk').prop('checked', false);
        }
    });
    $('.chk_all_create').change(function ()
    {
        if (this.checked)
        {
            $('.create_chk').prop('checked', true);
        }
        else
        {
            $('.create_chk').prop('checked', false);
        }
    });
    $('.chk_all_edit').change(function ()
    {
        if (this.checked)
        {
            $('.edit_chk').prop('checked', true);
        }
        else
        {
            $('.edit_chk').prop('checked', false);
        }
    });
    $('.chk_all_delete').change(function ()
    {
        if (this.checked)
        {
            $('.delete_chk').prop('checked', true);
        }
        else
        {
            $('.delete_chk').prop('checked', false);
        }
    });
    $('.view_chk').change(function ()
    {
        check_uncheck('view_chk', 'chk_all_view');
    });
    $('.create_chk').change(function ()
    {
        check_uncheck('create_chk', 'chk_all_create');
    });
    $('.edit_chk').change(function ()
    {
        check_uncheck('edit_chk', 'chk_all_edit');
    });
    $('.delete_chk').change(function ()
    {
        check_uncheck('delete_chk', 'chk_all_delete');
    });
    $('.level1').change(function ()
    {

//        var total_chk = $("[data-level3=" + $(this).attr('data-level3') + "]").length;
//        var checked_chk = $("[data-level3=" + $(this).attr('data-level3') + "]:checked").length;
//        alert(total_chk);
//        if (total_chk == checked_chk)
//        {
//            $("." + $(this).attr('data-level3') + "-all").prop('checked', true);
//        }
//        else
//        {
//            $("." + $(this).attr('data-level3') + "-all").prop('checked', false);
//        }
        check_uncheck_inner($(this), "data-level3");
        check_uncheck_inner($(this), "data-level2");
        check_uncheck_inner($(this), "data-level1");
    });
});
function check_uncheck_inner(current_element, selected_attr)
{
    var total_chk = $("[" + selected_attr + "=" + $(current_element).attr(selected_attr) + "]").length;
    var checked_chk = $("[" + selected_attr + "=" + $(current_element).attr(selected_attr) + "]:checked").length;
//    alert(total_chk);
//    alert(checked_chk);
    if (total_chk == checked_chk)
    {
        $("." + $(current_element).attr(selected_attr) + "-all").prop('checked', true);
    }
    else
    {
        $("." + $(current_element).attr(selected_attr) + "-all").prop('checked', false);
    }
}
function check_uncheck(single_chk_class, select_all_chk_class)
{
    var total_chk = $("." + single_chk_class).length;
    var checked_chk = $("." + single_chk_class + ":checked").length;
    if (total_chk == checked_chk)
    {
        $("." + select_all_chk_class).prop('checked', true);
    }
    else
    {
        $("." + select_all_chk_class).prop('checked', false);
    }
}
</script>
@endsection
