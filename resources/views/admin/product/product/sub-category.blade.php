<!--if sub category-->
@if(isset($subCategory))
@foreach($subCategory as $record)
<option value="{{$record->id}}" {{ (collect(old('subcategory_id',$subCategoryID))->contains($record->id)) ? 'selected':'' }}>{{$record->getCategoryDescription()?$record->getCategoryDescription()->categories_name:''}}</option>
@endforeach
@endif