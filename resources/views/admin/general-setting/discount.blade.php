@extends('layout/main')
@section('page_title', 'Discount')
@section('additional_css')
<link rel="stylesheet" href="{{asset('plugins/select2/css/select2.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/icheck-bootstrap/icheck-bootstrap.min.css')}}">
<link rel="stylesheet" href="{{asset('plugins/datetimepicker/bootstrap-4.min.css')}}" crossorigin="anonymous" />
@endsection
@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Store Discount</h1>
                </div>
                <div class="col-sm-12">
                    <ol class="breadcrumb float-sm-left">
                        <li class="breadcrumb-item"><a href="{{route('admin.home.index')}}">{{__('admin.home')}}</a></li>
                        <li class="breadcrumb-item"><a href="{{route('admin.product.category.index')}}">Store Discount</a></li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <section class="content">
        @include('layout/toaster')
        <div class="container-fluid">
            <div class="card {{config('custom.card-primary')}}">
                <div class="card-header">
                    <h3 class="card-title">{{__('admin.edit')}}</h3>
                </div>
                <form method="post" action="{{route('admin.general-setting.setting.updateDiscount')}}" enctype="multipart/form-data">
                    @csrf

                    <input type="hidden" name="discount_id" value="{{$id}}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group text-center">
                                    <label>Brand Discount</label>
                                    <div class="icheck-primary ">
                                        <input name="discount_on" type="radio" value="1" id="discount_on1" {{$type==1?'checked="true"':''}} onchange="confirmChange(this)">
                                        <label for="discount_on1">
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 col-sm-12">
                                <div class="form-group text-center">
                                    <label>Category Discount</label>
                                    <div class="icheck-primary">
                                        <input name="discount_on" type="radio" value="2" id="discount_on2" {{$type==2?'checked="true"':''}} onchange="confirmChange(this)">
                                        <label for="discount_on2">
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- <div class="col-md-4 col-sm-12">
                                <div class="form-group text-center">
                                    <label>Category & Brand Discount</label>
                                    <div class="icheck-primary">
                                        <input name="discount_on" type="radio" value="3" id="discount_on3" {{$type==3?'checked="true"':''}}>
                                        <label for="discount_on3">
                                        </label>
                                    </div>
                                </div>
                            </div> -->


                        </div>

                        @if($type ==2 || $type ==3)
                        <hr class="hr-custom">
                        <div class="row mt-5">
                            <div class="col-xs-12 col-md-12">
                                <div class="form-group">
                                    <label>{{__('admin.category')}}</label>
                                    <select id="category_id" title="" name="category_id[]" multiple="" class="form-control select2 select-category {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}">
                                        @foreach($category as $category)
                                        @php
                                        $categoryName = $category->getCategoryDescription();
                                        @endphp
                                        <option value="{{$category->id}}" {{ (collect(old('category_id',$catDiscCat))->contains($category->id)) ? 'selected':'' }}>
                                            {{$categoryName?ucfirst($categoryName->categories_name):''}}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>

                        </div>
                        @endif

                        @if($type ==1 || $type ==3 )
                        <hr class="hr-custom">
                        <div class="row mt-5">
                            <div class="col-xs-12 col-md-12">
                                <div class="form-group">
                                    <label>{{__('admin.brand')}}</label>
                                    <select id="brand_id" title="" name="brand_id[]" multiple="" class="form-control select2 select-category {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}">
                                        @foreach($brand as $brand)
                                        @php
                                        $brandName = $brand->getDescription();
                                        @endphp
                                        <option value="{{$brand->id}}" {{ (collect(old('brand_id',$catDiscBrand))->contains($brand->id)) ? 'selected':'' }}>
                                            {{$brandName?ucfirst($brandName->brand_name):''}}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('brand_id')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif


                        <hr class="hr-custom">
                        <div class="row mt-5">
                            <input type="hidden" name="id" value="1">
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>Apply Discount</label><span class="required"></span>
                                    <select class="form-control select2 {{config('custom.select2-css')}}" data-dropdown-css-class="{{config('custom.select2-css')}}" name="is_discount">
                                        <option value="1" {{old('is_discount',$singleRecord->is_discount)== 1 ? 'selected' : '' }}>Yes</option>
                                        <option value="0" {{old('is_discount',$singleRecord->is_discount)== 0 ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-2">
                                <div class="form-group">
                                    <label>Discount Percentage(%)</label>
                                    <input name="discount" type="text" class="form-control length-30 float-input length-4 {{ $errors->has('discount_percentage') ?'is-invalid':'' }}" placeholder="Discount Percentage(%)" value="{{old('discount',$singleRecord?$singleRecord->discount:'')}}">
                                    @error('discount')
                                    <div class="text-red">{!!$message!!}</div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-xs-12 col-md-4">
                                <label>From Date</label>
                                <?php
                                $today = $singleRecord->start_date ? $singleRecord->start_date : date("Y-m-d");
                                ?>
                                <input readonly="" data-toggle="datetimepicker" data-target=".start-date" class="start-date form-control datetimepicker-input" placeholder="{{__('admin.from_date')}}" name="from_date" type="text" data-value="{{$singleRecord->start_date ? date("d-m-Y",strtotime($today)) : ''}}" data-start-date="{{$singleRecord->start_date ? $singleRecord->start_date : $today }}">
                                @error('from_date')
                                <div class="text-red">{!!$message!!}</div>
                                @endif
                            </div>

                            <div class="col-xs-12 col-md-4">
                                <label>To Date</label>
                                <?php
                                $todate = $singleRecord->end_date ? $singleRecord->end_date : date("Y-m-d");
                                ?>
                                <input readonly="" data-toggle="datetimepicker" data-target=".end-date" class="end-date form-control datetimepicker-input" placeholder="{{__('admin.to_date')}}" name="to_date" type="text" data-value="{{ $singleRecord->end_date ? date("d-m-Y",strtotime($todate)) : ''}}">
                                @error('to_date')
                                <div class="text-red">{!!$message!!}</div>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            @if(false)
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{__('admin.min_order_quantity')}}<span class="required"></span></label>
                                    <input type="text" name="min_order_quantity" placeholder="{{__('admin.min_order_quantity')}}" class="form-control number-input {{ $errors->has('min_order_quantity') ?'is-invalid':'' }}" value="{{old('min_order_quantity',$singleRecord->min_order_quantity)}}">
                                    @error('min_order_quantity')
                                    <div class="text-red">{!!$message!!}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{__('admin.max_order_quantity')}}<span class="required"></span></label>
                                    <input type="text" name="max_order_quantity" placeholder="{{__('admin.max_order_quantity')}}" class="form-control number-input {{ $errors->has('max_order_quantity') ?'is-invalid':'' }}" value="{{old('max_order_quantity',$singleRecord->max_order_quantity)}}">
                                    @error('max_order_quantity')
                                    <div class="text-red">{!!$message!!}</div>
                                    @endif
                                </div>
                            </div>
                            @endif

                            @if(false)
                            <div class="col-md-6 col-sm-12">
                                <div class="form-group inline-flex">
                                    <label>{{__('admin.show_customized_form')}}</label>
                                    <div class="icheck-primary ">
                                        <input name="show_customized_form" type="checkbox" value="1" id="homepage" {{old('show_customized_form', $singleRecord->show_customized_form)?'checked':''}}>
                                        <input name="show_customized_form" type="hidden" value="" {{old('show_customized_form', $singleRecord->show_customized_form)?'disabled':''}}>
                                        <label for="homepage">
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        @if(false)
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{__('admin.category_image')}} <small class="{{config('custom.text-note-css')}}">{{__('admin.category_image_dimention')}}</small></label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="category_image" class="custom-file-input {{ $errors->has('category_image') ?'is-invalid':'' }}" onchange="readURL(this, 'category-image')">
                                            <label class="custom-file-label">{{__('admin.category_image')}}</label>
                                        </div>
                                        <div class="input-group-append">
                                            <!--<span class = "input-group-text">Upload</span> -->
                                        </div>
                                    </div>
                                    @error('category_image')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-2 text-center">
                                <label class="profile_preview">{{__('admin.category_image')}}</label>
                                <div class="text-center">
                                    <img class="logo-table category-image text-center" src="{{ asset(Storage::url($singleRecord->categories_image))}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{__('admin.category_icon')}} <small class="{{config('custom.text-note-css')}}">{{__('admin.category_icon_dimention')}}</small></label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="category_icon" class="custom-file-input {{ $errors->has('category_icon') ?'is-invalid':'' }}" onchange="readURL(this, 'category-icon')">
                                            <label class="custom-file-label">{{__('admin.category_icon')}}</label>
                                        </div>
                                        <div class="input-group-append">
                                            <!--<span class = "input-group-text">Upload</span> -->
                                        </div>
                                    </div>
                                    @error('category_icon')
                                    <div class="text-red">{{ $message }}</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-2 text-center">
                                <label class="profile_preview">{{__('admin.category_icon')}}</label>
                                <div class="text-center">
                                    <img class="logo-table category-icon text-center" src="{{ asset(Storage::url($singleRecord->categories_icon))}}" onerror="this.onerror=null;this.src='{{ asset(Storage::url('default.png')) }}'" onclick="showDetails(this.src)" /></span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="icheck-primary">
                                        <input type="checkbox" name="have_price_variant" id="have_price_variant" {{ (collect(old('have_price_variant', $singleRecord->have_price_variant))->contains("1")) ? 'checked':'' }} value="1">
                                        <label for="have_price_variant">
                                            {{__('admin.have_price_variant')}}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                    </div>

                    <div class="card-footer">
                        <input name="id" type="hidden" value="{{$singleRecord->id}}">
                        <span class="float-left">{!!__('admin.additional_notes')!!}</span>
                        <div class="btn-toolbar float-right">
                            <div class="btn-group mr-2">
                                <!-- <a href="{{route('admin.product.category.index')}}" class="{{config('custom.btn-danger-form')}}" title="{{__('admin.cancel')}}">{{__('admin.cancel')}}</a> -->
                            </div>
                            <div class="btn-group">

                                @if($id)
                                <button type="submit" class="{{config('custom.btn-success-form')}}" title="{{__('admin.update')}}">{{__('admin.update')}}</button>
                                @else
                                <button type="submit" class="{{config('custom.btn-success-form')}}" title="{{__('admin.update')}}">Save</button>
                                @endif

                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>


    <section class="content">
        <div class="container-fluid">
            <!-- /.row -->
            <div class="row">
                <div class="col-12">
                    <div class="card {{config('custom.card-primary')}}">
                        <table class="table  table-striped">
                            <thead>
                                <tr>
                                    <th class="asc-desc text-center">No</th>
                                    <th class="asc-desc"> {{$type==1 ? 'Brands' : 'Category'}}</th>
                                    <th class="asc-desc">Start Date</th>
                                    <th class="asc-desc">End Date</th>
                                    <th class="asc-desc">Discount</th>
                                    <th class="asc-desc text-center">Apply Discount</th>
                                    <th class="text-center">{{__('admin.action')}}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($discounts as $key => $record)

                                <?php
                                $cats = '';
                                if ($type == 2) {
                                    $data = $record->gerCategoryDiscount();

                                    if (!empty($data)) {
                                        $cats =   App\AdminModel\Product\AdminCategoryDescription::whereIn('categories_id', $data)->where('language_id', 1)->pluck('categories_name')->toArray();
                                    }
                                } else {

                                    $data  =  $record->gerBrandDiscount();
                                    if (!empty($data)) {
                                        $cats =   App\AdminModel\Brand\AdminBrandDescription::whereIn('brand_id', $data)->where('language_id', 1)->pluck('brand_name')->toArray();
                                    }
                                }

                                if (!empty($cats)) {
                                    $cats = implode(',', $cats);
                                }
                                if(empty($cats))
                                {
                                    $cats ='';
                                }


                                ?>
                                <tr id="5" role="row" class="odd">
                                    <td class="text-center dtr-control">{{ $key + 1}}</td>
                                    <td>{{$cats}}</td>
                                    <td>{{$record->start_date}}</td>
                                    <td>{{$record->end_date}}</td>
                                    <td>{{$record->discount}}</td>

                                    <td class="text-center">
                                        @if($record->is_discount)
                                        <span class="{{config('custom.badge-success','badge bg-success')}}">Yes</span>
                                        @else
                                        <span class="{{config('custom.badge-danger','badge bg-danger')}}">No</span>
                                        @endif
                                    </td>
                                    <td class="text-center">

                                        @if($id != $record->id)
                                        <a href="{{route('admin.general-setting.setting.discount',['type'=>$type,'id'=>$record->id])}}"><button type="button" class="{{config('custom.btn-primary','btn btn-outline-primary btn-sm')}}" title="{{__('admin.edit')}}"><i class="fas fa-edit"></i></button></a>
                                        @if($record->is_discount)
                                        <a href="{{route('admin.general-setting.setting.change-status',['id'=>$record->id,'active'=>0])}}"><button type="button" class="{{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.click_in_active')}}"><i class="fas fa-times"></i></button></a>
                                        @else
                                        <a href="{{route('admin.general-setting.setting.change-status',['id'=>$record->id,'active'=>1])}}"><button type="button" class="{{config('custom.btn-success','btn btn-outline-success btn-sm')}}" title="{{__('admin.click_active')}}"><i class="fas fa-check"></i></button></a>
                                        @endif
                                        <a onclick="showSweetAlert('{{route('admin.general-setting.setting.delete',['id'=>$record->id])}}')"><button type="button" class="{{config('custom.btn-danger','btn btn-outline-danger btn-sm')}}" title="{{__('admin.delete')}}"><i class="fas fa-trash"></i></button></a>
                                        @endif
                                    </td>
                                </tr>

                                @endforeach

                            </tbody>

                        </table>
                        <!-- /.card-body -->
                    </div>




                    <!-- /.card -->
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
</div>
@endsection
@section('page_specific_js')
@include('layout/select2')
@include('layout/fileupload')
@include('layout/sweetalert')
@include('layout/image_preview')
<script src="{{asset('plugins/moment/moment.min.js')}}" crossorigin="anonymous"></script>
<script src="{{asset('plugins/datetimepicker/bootstrap-4.js')}}" crossorigin="anonymous"></script>
<script>
    $(document).ready(function() {
        $("input[name=discount_on]").change(function() {
            const previousType = "{{$type}}"; // Get the previously checked value
            const newType = $(this).val(); // Get the newly selected value

            // Confirmation logic
            if (previousType !== newType) {
                const confirmMessage =
                    previousType == 1 ?
                    "Are you sure you want to change the discount type from Brand?" :
                    previousType == 2 ?
                    "Are you sure you want to change the discount type from Category?" :
                    "Are you sure you want to change the discount type from Custom?";

                if (!confirm(confirmMessage)) {
                    // Revert to the previously selected radio button if user cancels
                    $("input[value=" + previousType + "]").prop("checked", true);
                    return; // Exit the function
                }
            }

            // History pushState and page reload logic
            const url = "{{$url}}"; // Assuming `$url` is defined in your backend template
            history.pushState(null, null, `${url}/${newType}`);
            $("#pageloader").fadeIn();
            window.location.reload();
        });
    });
</script>
<script>
    $(document).ready(function() {
        var startDateVal = $(".start-date").attr('data-value');
        var endDateVal = $(".end-date").attr('data-value');

        startDate(startDateVal);
        endDate($(".start-date").attr("data-start-date"), endDateVal);

        $(".start-date").on('change.datetimepicker', function(e) {
            $(".end-date").datetimepicker("destroy");
            $(".end-date").val("");
            endDate(e.date, '');
            $(".end-date").datetimepicker('minDate', e.date);
        });
    });

    function startDate(startDateVal) {
        $(".start-date").datetimepicker({
            format: "{{config('custom.js_date_format')}}",
            ignoreReadonly: true,

            buttons: {
                showClear: false,
            },
            icons: {
                clear: 'fa fa-trash',
            },

        });
        $(".start-date").val(startDateVal);
    }

    function endDate(startDateVal, endDateVal = '') {
        $(".end-date").datetimepicker({
            format: "{{config('custom.js_date_format')}}",
            minDate: moment(startDateVal),
            ignoreReadonly: true,
            buttons: {
                showClear: false,
            },
            icons: {
                clear: 'fa fa-trash',
            }
        });

        if (endDateVal) {
            $(".end-date").val(endDateVal);
        }
    }

    function confirmChange(radio) {
        const previousType = document.querySelector('input[name="discount_on"]:checked').value;
        const newType = radio.value;

        if (previousType !== newType) {
            const confirmMessage = previousType == 1 ?
                "Are you sure you want to change the discount type from Brand to Category?" :
                "Are you sure you want to change the discount type from Category to Brand?";

            if (!confirm(confirmMessage)) {
                // Revert to the previously selected radio button if user cancels
                radio.checked = false;
                document.getElementById(previousType == 1 ? 'discount_on1' : 'discount_on2').checked = true;
            }
        }
    }
</script>
@endsection

