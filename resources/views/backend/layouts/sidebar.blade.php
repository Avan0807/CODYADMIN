<ul class="navbar-nav bg-gradient-info sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{route('admin')}}">
      <div class="sidebar-brand-icon rotate-n-15">
        <i class="fas fa-cart-arrow-down"></i>
      </div>
      <div class="sidebar-brand-text mx-3">Quản trị viên</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
      <a class="nav-link" href="{{route('admin')}}">
        <i class="fas fa-fw fa-tachometer-alt"></i>
        <span>Trang Tổng Quan</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Đặt hàng
    </div>
    <!--Orders -->
    <li class="nav-item">
        <a class="nav-link" href="{{route('order.index')}}">
            <i class="fas fa-cart-plus"></i>
            <span>Đơn Hàng</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="{{ route('affiliate_orders.index') }}">
            <i class="fas fa-link"></i>
            <span>Đơn hàng tiếp thị</span>
        </a>
    </li>

    <!-- Heading -->
    <hr class="sidebar-divider">
    <!-- Heading -->
    <div class="sidebar-heading">
        Banner
    </div>


    <!-- Nav Item - Pages Collapse Menu -->
    <!-- Nav Item - Charts -->

    <li class="nav-item">
        <a class="nav-link" href="{{route('file-manager')}}">
            <i class="fas fa-fw fa-chart-area"></i>
            <span>Quản lý ảnh</span></a>


    <li class="nav-item">
      <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
        <i class="fas fa-image"></i>
        <span>Banners</span>
      </a>
      <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
          <h6 class="collapse-header">Tùy chọn Banners:</h6>
          <a class="collapse-item" href="{{route('banner.index')}}">Banners</a>
          <a class="collapse-item" href="{{route('banner.create')}}">Thêm Banners</a>
        </div>
      </div>
    </li>
    <!-- Divider -->
    <hr class="sidebar-divider">
        <!-- Heading -->
        <div class="sidebar-heading">
            Cửa hàng
        </div>

    <!-- Categories -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#categoryCollapse" aria-expanded="true" aria-controls="categoryCollapse">
          <i class="fas fa-sitemap"></i>
          <span>Danh Mục</span>
        </a>
        <div id="categoryCollapse" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Tùy Chọn Danh Mục:</h6>
            <a class="collapse-item" href="{{route('category.index')}}">Danh Mục</a>
            <a class="collapse-item" href="{{route('category.create')}}">Thêm Danh Mục</a>
          </div>
        </div>
    </li>
    {{-- Products --}}
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#productCollapse" aria-expanded="true" aria-controls="productCollapse">
          <i class="fas fa-cubes"></i>
          <span>Các sản phẩm</span>
        </a>
        <div id="productCollapse" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Tùy Chọn Sản Phẩm:</h6>
            <a class="collapse-item" href="{{route('product.index')}}">Sản Phẩm</a>
            <a class="collapse-item" href="{{route('product.create')}}">Thêm Sản Phẩm</a>
          </div>
        </div>
    </li>

    {{-- Brands --}}
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#brandCollapse" aria-expanded="true" aria-controls="brandCollapse">
          <i class="fas fa-table"></i>
          <span>Các thương hiệu</span>
        </a>
        <div id="brandCollapse" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Tùy chọn thương hiệu:</h6>
            <a class="collapse-item" href="{{route('brand.index')}}">Thương hiệu</a>
            <a class="collapse-item" href="{{route('brand.create')}}">Thêm thương hiệu</a>
          </div>
        </div>
    </li>

    {{-- Shipping --}}
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#shippingCollapse" aria-expanded="true" aria-controls="shippingCollapse">
          <i class="fas fa-truck"></i>
          <span>Vận chuyển</span>
        </a>
        <div id="shippingCollapse" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Tùy chọn vận chuyển:</h6>
            <a class="collapse-item" href="{{route('shipping.index')}}">Vận chuyển</a>
            <a class="collapse-item" href="{{route('shipping.create')}}">Thêm vận chuyển</a>
          </div>
        </div>
    </li>

    <!-- Reviews -->
    <li class="nav-item">
        <a class="nav-link" href="{{route('review.index')}}">
            <i class="fas fa-comments"></i>
            <span>Đánh giá</span></a>
    </li>

    <!-- Divider -->

    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
      Bác Sĩ
    </div>
    <!-- Doctors -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#doctorCollapse" aria-expanded="false" aria-controls="doctorCollapse">
            <i class="fas fa-user-md"></i>
            <span>Bác Sĩ</span>
        </a>
        <div id="doctorCollapse" class="collapse" aria-labelledby="headingDoctor" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Quản lý bác sĩ:</h6>
                <a class="collapse-item" href="{{ route('doctor.index') }}">Danh sách bác sĩ</a>
                <a class="collapse-item" href="{{ route('doctor.create') }}">Thêm bác sĩ</a>
            </div>
        </div>
    </li>

    <!-- Phòng Khám -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#clinicCollapse" aria-expanded="false" aria-controls="clinicCollapse">
            <i class="fas fa-hospital"></i>
            <span>Phòng Khám</span>
        </a>
        <div id="clinicCollapse" class="collapse" aria-labelledby="headingClinic" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Quản lý phòng khám:</h6>
                <a class="collapse-item" href="{{ route('clinics.index') }}">Danh sách phòng khám</a>
                <a class="collapse-item" href="{{ route('clinics.create') }}">Thêm phòng khám</a>
            </div>
        </div>
    </li>

    <!-- Hoa Hồng -->
    <li class="nav-item">
        <a class="nav-link" href="{{ route('commissions.index') }}">
            <i class="fas fa-coins"></i>
            <span>Hoa Hồng</span>
        </a>
    </li>

    <hr class="sidebar-divider">
    <!-- Đơn Hàng Tiếp Thị -->
    <div class="sidebar-heading">
        Đơn Hàng Affiliate
    </div>

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#affiliateOrdersCollapse" aria-expanded="false" aria-controls="affiliateOrdersCollapse">
            <i class="fas fa-link"></i>
            <span>Đơn hàng Affiliate</span>
        </a>
        <div id="affiliateOrdersCollapse" class="collapse" aria-labelledby="headingAffiliateOrders" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Quản lý đơn hàng:</h6>
                <a class="collapse-item" href="{{ route('products.affiliate.index') }}">Danh sách đơn hàng</a>
            </div>
        </div>
    </li>


    <!-- Divider -->
    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Thông báo
    </div>

    <!-- Chiến Dịch -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#campaignCollapse" aria-expanded="false" aria-controls="campaignCollapse">
            <i class="fas fa-bullhorn"></i>
            <span>Chiến Dịch</span>
        </a>
        <div id="campaignCollapse" class="collapse" aria-labelledby="headingCampaign" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Tùy chọn chiến dịch:</h6>
                <a class="collapse-item" href="{{ route('campaign_notifications.index') }}">Danh sách chiến dịch</a>
                <a class="collapse-item" href="{{ route('campaign_notifications.create') }}">Thêm chiến dịch</a>
            </div>
        </div>
    </li>

    <!-- Tin tức công ty -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#adsCollapse" aria-expanded="false" aria-controls="adsCollapse">
            <i class="fas fa-ad"></i>
            <span>Tin Tức Công Ty</span>
        </a>
        <div id="adsCollapse" class="collapse" aria-labelledby="headingAds" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Tùy chọn tin tức:</h6>
                <a class="collapse-item" href="{{ route('company_news.index') }}">Danh sách tin tức</a>
                <a class="collapse-item" href="{{ route('company_news.create') }}">Thêm tin tức</a>
            </div>
        </div>
    </li>


    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
      Bài viết
    </div>

    <!-- Posts -->
    <li class="nav-item">
      <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#postCollapse" aria-expanded="true" aria-controls="postCollapse">
        <i class="fas fa-fw fa-folder"></i>
        <span>Các bài viết</span>
      </a>
      <div id="postCollapse" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
        <div class="bg-white py-2 collapse-inner rounded">
          <h6 class="collapse-header">Tùy chọn bài đăng:</h6>
          <a class="collapse-item" href="{{route('post.index')}}">Bài viết</a>
          <a class="collapse-item" href="{{route('post.create')}}">Thêm bài viết</a>
        </div>
      </div>
    </li>

     <!-- Category -->
     <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#postCategoryCollapse" aria-expanded="true" aria-controls="postCategoryCollapse">
          <i class="fas fa-sitemap fa-folder"></i>
          <span>Loại</span>
        </a>
        <div id="postCategoryCollapse" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Tùy chọn danh mục:</h6>
            <a class="collapse-item" href="{{route('post-category.index')}}">Loại</a>
            <a class="collapse-item" href="{{route('post-category.create')}}">Thêm danh mục</a>
          </div>
        </div>
      </li>


    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#tagCollapse" aria-expanded="true" aria-controls="tagCollapse">
            <i class="fas fa-tags fa-folder"></i>
            <span>Thẻ</span>
        </a>
        <div id="tagCollapse" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Tùy chọn thẻ:</h6>
            <a class="collapse-item" href="{{route('post-tag.index')}}">Thẻ</a>
            <a class="collapse-item" href="{{route('post-tag.create')}}">Thêm Thẻ</a>
            </div>
        </div>
    </li>

      <!-- Comments -->
      <li class="nav-item">
        <a class="nav-link" href="{{route('comment.index')}}">
            <i class="fas fa-comments fa-chart-area"></i>
            <span>Bình luận</span>
        </a>
      </li>


    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">
     <!-- Heading -->
    <div class="sidebar-heading">
          Cài đặt chung
    </div>
    <li class="nav-item">
      <a class="nav-link" href="{{route('coupon.index')}}">
          <i class="fas fa-table"></i>
          <span>Phiếu giảm giá</span></a>
    </li>
     <!-- Users -->
     <li class="nav-item">
        <a class="nav-link" href="{{route('users.index')}}">
            <i class="fas fa-users"></i>
            <span>Người sử dụng</span></a>
    </li>
     <!-- General settings -->
     <li class="nav-item">
        <a class="nav-link" href="{{route('settings')}}">
            <i class="fas fa-cog"></i>
            <span>Cài đặt</span></a>
    </li>

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
      <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
