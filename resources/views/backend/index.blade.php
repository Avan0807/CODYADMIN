@extends('backend.layouts.master')
@section('title','CODY || Trang tổng quan')
@section('main-content')
<div class="container-fluid">
    @include('backend.layouts.notification')
    
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
      <h1 class="h3 mb-0 text-gray-800">Trang tổng quan</h1>
    </div>

    <!-- Stats Cards Row 1 -->
    <div class="row">
      <!-- Order -->
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Tổng đơn hàng</div>
                <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{\App\Models\Order::countActiveOrder()}}</div>
              </div>
              <div class="col-auto">
                <i class="fas fa-cart-plus fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Products -->
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Sản Phẩm</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{\App\Models\Product::countActiveProduct()}}</div>
              </div>
              <div class="col-auto">
                <i class="fas fa-cubes fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Category -->
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Loại</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{\App\Models\Category::countActiveCategory()}}</div>
              </div>
              <div class="col-auto">
                <i class="fas fa-sitemap fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Posts -->
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Bài Viết</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{\App\Models\Post::countActivePost()}}</div>
              </div>
              <div class="col-auto">
                <i class="fas fa-newspaper fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Cards Row 2 - Order Status -->
    <div class="row">
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Đơn hàng mới</div>
                <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{\App\Models\Order::countNewReceivedOrder()}}</div>
              </div>
              <div class="col-auto">
                <i class="fas fa-cart-plus fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Đang xử lý</div>
                <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{\App\Models\Order::countProcessingOrder()}}</div>
              </div>
              <div class="col-auto">
                <i class="fas fa-spinner fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Đã giao hàng</div>
                <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{\App\Models\Order::countDeliveredOrder()}}</div>
              </div>
              <div class="col-auto">
                <i class="fas fa-check fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Đã hủy</div>
                <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{\App\Models\Order::countCancelledOrder()}}</div>
              </div>
              <div class="col-auto">
                <i class="fas fa-times fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Cards Row 3 - Affiliate System -->
    <div class="row">
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-purple shadow h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-purple text-uppercase mb-1">Đơn hàng Affiliate</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{\App\Models\AffiliateOrder::count()}}</div>
              </div>
              <div class="col-auto">
                <i class="fas fa-handshake fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Hoa hồng chờ trả</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{number_format(\App\Models\AffiliateOrder::totalPendingCommission(), 0, ',', '.')}}đ</div>
              </div>
              <div class="col-auto">
                <i class="fas fa-clock fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Hoa hồng đã trả</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{number_format(\App\Models\AffiliateOrder::totalPaidCommission(), 0, ',', '.')}}đ</div>
              </div>
              <div class="col-auto">
                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Bác sĩ hoạt động</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">{{\App\Models\Doctor::count()}}</div>
              </div>
              <div class="col-auto">
                <i class="fas fa-user-md fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row">
      <!-- Revenue Overview -->
      <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Tổng quan về thu nhập</h6>
          </div>
          <div class="card-body">
            <div class="chart-area">
              <canvas id="revenueChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Users Pie Chart -->
      <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Người Dùng</h6>
          </div>
          <div class="card-body" style="overflow:hidden">
            <div id="pie_chart" style="width:350px; height:320px;"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row">
      <!-- Top Products -->
      <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Top sản phẩm bán chạy</h6>
          </div>
          <div class="card-body">
            <div class="chart-area">
              <canvas id="topProductsChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Order Status Trend -->
      <div class="col-xl-6 col-lg-6">
        <div class="card shadow mb-4">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Xu hướng trạng thái đơn hàng</h6>
          </div>
          <div class="card-body">
            <div class="chart-area">
              <canvas id="orderStatusChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row 3 -->
    <div class="row">
      <!-- Revenue vs Commission -->
      <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Doanh thu vs Hoa hồng</h6>
          </div>
          <div class="card-body">
            <div class="chart-area">
              <canvas id="revenueVsCommissionChart"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- Top Doctors -->
      <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Top bác sĩ theo hoa hồng</h6>
          </div>
          <div class="card-body">
            <div class="chart-area">
              <canvas id="topDoctorsChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Row 4 -->
    <div class="row">
      <!-- Order Growth -->
      <div class="col-xl-12">
        <div class="card shadow mb-4">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Tăng trưởng đơn hàng (%)</h6>
          </div>
          <div class="card-body">
            <div class="chart-area">
              <canvas id="orderGrowthChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.border-left-purple {
  border-left: 0.25rem solid #6f42c1 !important;
}
.text-purple {
  color: #6f42c1 !important;
}
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

{{-- Pie chart for users --}}
<script type="text/javascript">
  var analytics = <?php echo $users; ?>
  google.charts.load('current', {'packages':['corechart']});
  google.charts.setOnLoadCallback(drawChart);

  function drawChart() {
      var data = google.visualization.arrayToDataTable(analytics);
      var options = {
          title : 'Người dùng đã đăng ký trong 7 ngày qua'
      };
      var chart = new google.visualization.PieChart(document.getElementById('pie_chart'));
      chart.draw(data, options);
  }
</script>

<script type="text/javascript">
// Chart defaults
Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.global.defaultFontColor = '#858796';

// Common chart options
const commonOptions = {
    maintainAspectRatio: false,
    layout: { padding: { left: 10, right: 25, top: 25, bottom: 0 } },
    legend: { display: false },
    scales: {
        xAxes: [{ gridLines: { display: false, drawBorder: false }, ticks: { maxTicksLimit: 12 } }],
        yAxes: [{
            gridLines: { color: "rgb(234, 236, 244)", zeroLineColor: "rgb(234, 236, 244)", drawBorder: false, borderDash: [2], zeroLineBorderDash: [2] }
        }]
    }
};

document.addEventListener("DOMContentLoaded", function() {
    // 1. Revenue Chart
    axios.get("{{ route('product.order.income') }}")
    .then(response => {
        const data_keys = Object.keys(response.data);
        const data_values = Object.values(response.data).map(value => Number(value));
        
        new Chart(document.getElementById("revenueChart"), {
            type: 'line',
            data: {
                labels: data_keys,
                datasets: [{
                    label: "Doanh thu",
                    data: data_values,
                    backgroundColor: "rgba(78, 115, 223, 0.1)",
                    borderColor: "rgba(78, 115, 223, 1)",
                    pointBackgroundColor: "rgba(78, 115, 223, 1)",
                    lineTension: 0.3
                }]
            },
            options: {
                ...commonOptions,
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem) {
                            return 'Doanh thu: ' + tooltipItem.yLabel.toLocaleString('vi-VN') + 'đ';
                        }
                    }
                }
            }
        });
    });

    // 2. Top Products Chart
    axios.get("{{ route('admin.dashboard.top-products') }}")
    .then(response => {
        const products = response.data;
        const labels = products.map(p => p.title.length > 20 ? p.title.substring(0, 20) + '...' : p.title);
        const data = products.map(p => p.total_sold);
        
        new Chart(document.getElementById("topProductsChart"), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: "Đã bán",
                    data: data,
                    backgroundColor: "rgba(28, 200, 138, 0.8)",
                    borderColor: "rgba(28, 200, 138, 1)"
                }]
            },
            options: {
                ...commonOptions,
                scales: {
                    ...commonOptions.scales,
                    yAxes: [{
                        ...commonOptions.scales.yAxes[0],
                        ticks: { beginAtZero: true }
                    }]
                }
            }
        });
    });

    // 3. Order Status Trend Chart
    axios.get("{{ route('admin.dashboard.order-status-trend') }}")
    .then(response => {
        const data = response.data;
        const labels = Object.keys(data.new || {});
        
        new Chart(document.getElementById("orderStatusChart"), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "Mới",
                        data: Object.values(data.new || {}),
                        borderColor: "rgba(78, 115, 223, 1)",
                        backgroundColor: "rgba(78, 115, 223, 0.1)"
                    },
                    {
                        label: "Xử lý",
                        data: Object.values(data.process || {}),
                        borderColor: "rgba(246, 194, 62, 1)",
                        backgroundColor: "rgba(246, 194, 62, 0.1)"
                    },
                    {
                        label: "Đã giao",
                        data: Object.values(data.delivered || {}),
                        borderColor: "rgba(28, 200, 138, 1)",
                        backgroundColor: "rgba(28, 200, 138, 0.1)"
                    }
                ]
            },
            options: {
                ...commonOptions,
                legend: { display: true }
            }
        });
    });

    // 4. Revenue vs Commission Chart
    axios.get("{{ route('admin.dashboard.revenue-vs-commission') }}")
    .then(response => {
        const data = response.data;
        const labels = Object.keys(data.revenue || {});
        
        new Chart(document.getElementById("revenueVsCommissionChart"), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "Doanh thu",
                        data: Object.values(data.revenue || {}),
                        backgroundColor: "rgba(78, 115, 223, 0.8)"
                    },
                    {
                        label: "Hoa hồng",
                        data: Object.values(data.commission || {}),
                        backgroundColor: "rgba(28, 200, 138, 0.8)"
                    }
                ]
            },
            options: {
                ...commonOptions,
                legend: { display: true }
            }
        });
    });

    // 5. Top Doctors Chart
    axios.get("{{ route('admin.dashboard.top-doctors') }}")
    .then(response => {
        const doctors = response.data;
        const labels = doctors.map(d => d.name.length > 15 ? d.name.substring(0, 15) + '...' : d.name);
        const data = doctors.map(d => d.total_commission);
        
        new Chart(document.getElementById("topDoctorsChart"), {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: [
                        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
                        '#858796', '#5a5c69', '#2e59d9', '#17a673', '#2c9faf'
                    ]
                }]
            },
            options: {
                maintainAspectRatio: false,
                legend: { position: 'bottom' }
            }
        });
    });

    // 6. Order Growth Chart
    axios.get("{{ route('admin.dashboard.order-growth') }}")
    .then(response => {
        const data = response.data;
        const labels = Object.keys(data);
        const values = Object.values(data);
        
        new Chart(document.getElementById("orderGrowthChart"), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: "Tăng trưởng (%)",
                    data: values,
                    backgroundColor: "rgba(231, 74, 59, 0.1)",
                    borderColor: "rgba(231, 74, 59, 1)",
                    pointBackgroundColor: "rgba(231, 74, 59, 1)",
                    lineTension: 0.3
                }]
            },
            options: {
                ...commonOptions,
                scales: {
                    ...commonOptions.scales,
                    yAxes: [{
                        ...commonOptions.scales.yAxes[0],
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }]
                }
            }
        });
    });
});
</script>
@endpush