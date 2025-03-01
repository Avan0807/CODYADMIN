@extends('backend.layouts.master')

@section('main-content')

<div class="card">
    <h5 class="card-header">Chỉnh sửa người dùng</h5>
    <div class="card-body">
      <form method="post" action="{{route('users.update',$user->id)}}">
        @csrf 
        @method('PATCH')
        <div class="form-group">
            <label for="inputTitle" class="col-form-label">Tên</label>
            <input id="inputTitle" type="text" name="name" placeholder="Enter name" value="{{$user->name}}" class="form-control" readonly>
            <!-- Sử dụng thuộc tính readonly để ngăn người dùng chỉnh sửa -->
            @error('name')
            <span class="text-danger">{{$message}}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="inputEmail" class="col-form-label">Email</label>
            <input id="inputEmail" type="email" name="email" placeholder="Enter email" value="{{$user->email}}" class="form-control" readonly>
            <!-- Sử dụng thuộc tính readonly để ngăn người dùng chỉnh sửa -->
            @error('email')
            <span class="text-danger">{{$message}}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="inputPhoto" class="col-form-label">Ảnh</label>
            <div class="input-group">
                <span class="input-group-btn">
                    <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary">
                        <i class="fa fa-picture-o"></i> Chọn
                    </a>
                </span>
                <input id="thumbnail" class="form-control" type="text" name="photo" value="{{$user->photo}}" readonly>
                <!-- Sử dụng thuộc tính readonly để ngăn người dùng chỉnh sửa -->
            </div>
            <img id="holder" style="margin-top:15px;max-height:100px;">
            @error('photo')
            <span class="text-danger">{{$message}}</span>
            @enderror
        </div>

        @php 
        $roles=DB::table('users')->select('role')->where('id',$user->id)->get();
         // dd($roles);
        @endphp
        <div class="form-group">
            <label for="role" class="col-form-label">Vai trò</label>
            <select name="role" class="form-control">
                <option value="">-----Chọn vai trò-----</option>
                @foreach($roles as $role)
                    <option value="{{$role->role}}" {{(($role->role=='admin') ? 'selected' : '')}}>Quản trị viên</option>
                    <option value="{{$role->role}}" {{(($role->role=='user') ? 'selected' : '')}}>Người dùng</option>
                    <option value="{{$role->role}}" {{(($role->role=='doctor') ? 'selected' : '')}}>Bác sĩ</option>
                @endforeach
            </select>
          @error('role')
          <span class="text-danger">{{$message}}</span>
          @enderror
          </div>
          <div class="form-group">
            <label for="status" class="col-form-label">Trạng thái</label>
            <select name="status" class="form-control">
                <option value="active" {{(($user->status=='active') ? 'selected' : '')}}>Hoạt động</option>
                <option value="inactive" {{(($user->status=='inactive') ? 'selected' : '')}}>Không hoạt động</option>
            </select>
          @error('status')
          <span class="text-danger">{{$message}}</span>
          @enderror
          </div>
        <div class="form-group mb-3">
           <button class="btn btn-success" type="submit">Cập nhật</button>
        </div>
      </form>
    </div>
</div>

@endsection

@push('scripts')
<script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
<script>
    $('#lfm').filemanager('image');
</script>
@endpush