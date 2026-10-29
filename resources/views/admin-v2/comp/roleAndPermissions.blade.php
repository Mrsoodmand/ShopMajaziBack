@foreach($Categories as $category)
    <h5 class="mb-5">{{$category->name}}</h5>
    <div class="row mb-5">
        @foreach($Permissions->where('category_id',$category->id) as $permission)
            <div class="col-4 mb-2">
                <label class="form-check form-check-inline form-check-solid me-5">
                    <input
                            @if(isset($edit))
                            @foreach($edit->permissions as $value)
                            @if($value->id == $permission->id)
                            checked="checked"
                            @endif
                            @endforeach
                            @endif

                            @if(isset($Roles))
                            @foreach ($Roles->permissions as $value)
                            @if ($value->id == $permission->id)
                            checked="checked"
                            disabled="disabled"
                            @endif
                            @endforeach
                            @endif

                            class="form-check-input"
                            name="permissions[]"
                            type="checkbox"
                            value="{{$permission->id}}">
                    <span class="fw-bold ps-2 fs-6">{{$permission->faName}}</span>
                </label>
            </div>
        @endforeach
    </div>
@endforeach
