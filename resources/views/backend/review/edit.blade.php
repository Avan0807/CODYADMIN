@extends('backend.layouts.master')

@section('title','Chỉnh sửa đánh giá')

@section('main-content')
<div class="card">
  <h5 class="card-header">Chỉnh sửa đánh giá </h5>
  <div class="card-body">
    <form action="{{route('review.update',$review->id)}}" method="POST">
      @csrf
      @method('PATCH')
      <div class="form-group">
        <label for="name">Đánh giá bởi:</label>
        <input type="text" disabled class="form-control" value="{{$review->user_info->name}}">
      </div>
      <div class="form-group">
        <label for="review">Đánh giá</label>
      <textarea name="review" id="" cols="20" rows="10" class="form-control">{{$review->review}}</textarea>
      </div>
      <div class="form-group">
        <label for="status">Trạng thái :</label>
        <select name="status" id="" class="form-control">
          <option value="">--Chọn trạng thái--</option>
          <option value="active" {{(($review->status=='active')? 'selected' : '')}}>Hoạt động</option>
          <option value="inactive" {{(($review->status=='inactive')? 'selected' : '')}}>Không hoạt động</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Cập nhật</button>
    </form>
  </div>
</div>
@endsection

@push('styles')
<style>
    .order-info,.shipping-info{
        background:#ECECEC;
        padding:20px;
    }
    .order-info h4,.shipping-info h4{
        text-decoration: underline;
    }
</style>
@endpush