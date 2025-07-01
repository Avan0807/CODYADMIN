@extends('agent.layouts.master')

@section('title', 'Xuất báo cáo hoa hồng')

@section('main-content')
<div class="dashboard-container">
    <div class="container-fluid">
        
        <!-- Page Heading -->
        <div class="welcome-card animate__animated animate__fadeInDown mb-4">
            <div class="d-sm-flex align-items-center justify-content-between">
                <div>
                    <h1 class="h3 mb-0">📊 Xuất báo cáo hoa hồng</h1>
                    <p class="mb-0 opacity-75">Chọn khoảng thời gian để xuất báo cáo chi tiết</p>
                </div>
                <div class="d-flex">
                    <a href="{{ route('agent.agentcommissions.index') }}" class="quick-action-btn">
                        <i class="fas fa-arrow-left mr-2"></i>Quay lại thống kê
                    </a>
                </div>
            </div>
        </div>

        <!-- Export Form -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="recent-orders-card animate__animated animate__fadeInUp">
                    <div class="card-header-custom">
                        <h6 class="mb-0">
                            <i class="fas fa-download mr-2"></i>Tùy chọn xuất báo cáo
                        </h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('agentcommissions.export') }}" method="GET">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="date_from" class="form-label">Từ ngày</label>
                                        <input type="date" class="form-control" id="date_from" name="date_from" 
                                               value="{{ request('date_from', now()->subMonths(3)->format('Y-m-d')) }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="date_to" class="form-label">Đến ngày</label>
                                        <input type="date" class="form-control" id="date_to" name="date_to" 
                                               value="{{ request('date_to', now()->format('Y-m-d')) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="status" class="form-label">Trạng thái</label>
                                        <select class="form-control" id="status" name="status">
                                            <option value="">Tất cả</option>
                                            <option value="pending">Chờ thanh toán</option>
                                            <option value="paid">Đã thanh toán</option>
                                            <option value="cancelled">Đã hủy</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="format" class="form-label">Định dạng</label>
                                        <select class="form-control" id="format" name="format">
                                            <option value="csv">CSV (Excel)</option>
                                            <option value="pdf">PDF</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between">
                                <a href="{{ route('agent.agentcommissions.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times mr-2"></i>Hủy bỏ
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-download mr-2"></i>Xuất báo cáo
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection