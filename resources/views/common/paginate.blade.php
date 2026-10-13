<div class="float-left float-sm-left">
    <div class="input-group input-group-sm">
        <select class="form-control float-right float-sm-right" name="pageLength">
            @foreach(Helper::paginationDropdown() as $pageNumber)
            <option {{$pageLength==$pageNumber?'selected':''}}>{{$pageNumber}}</option>
            @endforeach
        </select>
    </div>
</div>