<!--if sub category-->
@if(isset($subCategory))
@foreach($subCategory as $record)
<option value="{{$record->id}}" {{ (collect(old('subcategory_id',$subCategoryID))->contains($record->id)) ? 'selected':'' }}>{{$record->getCategoryDescription()?$record->getCategoryDescription()->categories_name:''}}</option>
@endforeach
@endif

<!--if product-->
@if(isset($product))
@foreach($product as $record)
<option value="{{$record->products_id}}" {{ (collect(old('product_id',$productID))->contains($record->products_id)) ? 'selected':'' }}>{{$record->getProductDescription()?$record->getProductDescription()->products_name:''}}</option>
@endforeach
@endif