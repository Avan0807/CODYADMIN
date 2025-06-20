@extends('agent.layouts.master')
@section('title', 'Tạo Link Affiliate - Đại lý')

@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
<style>
    .create-links-container {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        padding: 20px 0;
    }
    
    .header-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        padding: 40px;
        color: white;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }
    
    .header-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 200px;
        height: 200px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }
    
    .search-card {
        background: white;
        border-radius: 20px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        margin-bottom: 30px;
        border: none;
    }
    
    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }
    
    .product-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border: none;
        position: relative;
    }
    
    .product-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }
    
    .product-image {
        height: 200px;
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    
    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .product-card:hover .product-image img {
        transform: scale(1.1);
    }
    
    .product-image .overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .product-card:hover .product-image .overlay {
        opacity: 1;
    }
    
    .product-info {
        padding: 25px;
    }
    
    .product-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #2d3748;
        margin-bottom: 10px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .product-price {
        font-size: 1.3rem;
        font-weight: 700;
        color: #1cc88a;
        margin-bottom: 15px;
    }
    
    .product-stats {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
        padding: 15px;
        background: #f8f9fc;
        border-radius: 10px;
    }
    
    .stat-item {
        text-align: center;
        flex: 1;
    }
    
    .stat-value {
        font-weight: 600;
        color: #5a5c69;
        font-size: 0.9rem;
    }
    
    .stat-label {
        font-size: 0.8rem;
        color: #858796;
        margin-top: 2px;
    }
    
    .commission-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: linear-gradient(45deg, #11998e, #38ef7d);
        color: white;
        padding: 8px 15px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.8rem;
        box-shadow: 0 4px 15px rgba(17, 153, 142, 0.4);
    }
    
    .generate-btn {
        width: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 15px;
        padding: 15px;
        color: white;
        font-weight: 600;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    
    .generate-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        color: white;
    }
    
    .generate-btn:disabled {
        background: #6c757d;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
    
    .generate-btn .loading {
        display: none;
    }
    
    .generate-btn.loading .loading {
        display: inline-block;
    }
    
    .generate-btn.loading .default-text {
        display: none;
    }
    
    .search-input {
        border: 2px solid #e3e6f0;
        border-radius: 15px;
        padding: 15px 20px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #f8f9fc;
    }
    
    .search-input:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .filter-buttons {
        display: flex;
        gap: 10px;
        margin-top: 20px;
        flex-wrap: wrap;
    }
    
    .filter-btn {
        background: white;
        border: 2px solid #e3e6f0;
        border-radius: 20px;
        padding: 10px 20px;
        color: #5a5c69;
        font-weight: 500;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .filter-btn:hover,
    .filter-btn.active {
        background: #667eea;
        border-color: #667eea;
        color: white;
    }
    
    .no-products {
        text-align: center;
        padding: 80px 20px;
        color: #858796;
    }
    
    .no-products i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.5;
    }
    
    .success-modal {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
    }
    
    .success-content {
        background: white;
        border-radius: 20px;
        padding: 40px;
        max-width: 500px;
        width: 90%;
        text-align: center;
        position: relative;
    }
    
    .success-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 2rem;
        color: white;
    }
    
    @media (max-width: 768px) {
        .product-grid {
            grid-template-columns: 1fr;
        }
        
        .filter-buttons {
            justify-content: center;
        }
        
        .header-card {
            padding: 30px 20px;
        }
    }
    
    .loading-shimmer {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }
    
    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
</style>
@endpush

@section('main-content')
<div class="create-links-container">
    <div class="container-fluid">
        
        <!-- Header Section -->
        <div class="header-card animate__animated animate__fadeInDown">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="h2 mb-3">🔗 Tạo Link Affiliate</h1>
                    <p class="mb-0 opacity-75">Chọn sản phẩm và tạo link affiliate của bạn. Mỗi lần khách hàng mua hàng qua link này, bạn sẽ nhận được hoa hồng!</p>
                </div>
                <div class="col-lg-4 text-right">
                    <div class="d-flex align-items-center justify-content-end">
                        <div class="mr-3">
                            <div class="text-white-50 small">Tổng links đã tạo</div>
                            <div class="h3 mb-0">{{ Auth::guard('agent')->user()->agentLinks()->count() }}</div>
                        </div>
                        <div class="ml-3">
                            <div class="text-white-50 small">Hoa hồng mặc định</div>
                            <div class="h3 mb-0">{{ Auth::guard('agent')->user()->commission_rate }}%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Filter Section -->
        <div class="search-card animate__animated animate__fadeInUp">
            <div class="row">
                <div class="col-lg-8">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-transparent border-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                        </div>
                        <input type="text" 
                               class="search-input form-control border-0" 
                               id="productSearch" 
                               placeholder="Tìm kiếm sản phẩm theo tên, mô tả...">
                    </div>
                </div>
                <div class="col-lg-4">
                    <select class="form-control search-input" id="sortSelect">
                        <option value="latest">Mới nhất</option>
                        <option value="price_asc">Giá thấp đến cao</option>
                        <option value="price_desc">Giá cao đến thấp</option>
                        <option value="commission_desc">Hoa hồng cao nhất</option>
                        <option value="popular">Phổ biến nhất</option>
                    </select>
                </div>
            </div>
            
            <!-- Filter Buttons -->
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="all">
                    <i class="fas fa-list mr-2"></i>Tất cả ({{ count($products) }})
                </button>
                <button class="filter-btn" data-filter="high-commission">
                    <i class="fas fa-star mr-2"></i>Hoa hồng cao (>15%)
                </button>
                <button class="filter-btn" data-filter="new">
                    <i class="fas fa-sparkles mr-2"></i>Sản phẩm mới
                </button>
                <button class="filter-btn" data-filter="popular">
                    <i class="fas fa-fire mr-2"></i>Bán chạy
                </button>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="products-container">
            @if(count($products) > 0)
                <div class="product-grid" id="productsGrid">
                    @foreach($products as $product)
                    <div class="product-card animate__animated animate__fadeInUp" 
                         style="animation-delay: {{ $loop->index * 0.1 }}s"
                         data-product-id="{{ $product->id }}"
                         data-commission="{{ $product->commission_percentage }}"
                         data-price="{{ $product->price }}"
                         data-title="{{ strtolower($product->title) }}"
                         data-created="{{ $product->created_at ? $product->created_at->diffInDays() : 999 }}">>
                        
                        <!-- Commission Badge -->
                        <div class="commission-badge">
                            {{ $product->commission_percentage }}% hoa hồng
                        </div>
                        
                        <!-- Product Image -->
                        <div class="product-image">
                            @if($product->photo)
                                <img src="{{ $product->photo }}" alt="{{ $product->title }}">
                            @else
                                <i class="fas fa-box fa-3x text-white"></i>
                            @endif
                            <div class="overlay">
                                <i class="fas fa-eye fa-2x text-white"></i>
                            </div>
                        </div>
                        
                        <!-- Product Info -->
                        <div class="product-info">
                            <h5 class="product-title">{{ $product->title }}</h5>
                            <div class="product-price">{{ number_format($product->price, 0, ',', '.') }}đ</div>
                            
                            <!-- Product Stats -->
                            <div class="product-stats">
                                <div class="stat-item">
                                    <div class="stat-value">{{ number_format(($product->price * $product->commission_percentage) / 100, 0, ',', '.') }}đ</div>
                                    <div class="stat-label">Hoa hồng dự kiến</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value">{{ $product->stock ?? 'N/A' }}</div>
                                    <div class="stat-label">Tồn kho</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-value">{{ $product->reviews_avg_rate ?? '0' }}<i class="fas fa-star text-warning ml-1"></i></div>
                                    <div class="stat-label">Đánh giá</div>
                                </div>
                            </div>
                            
                            <!-- Generate Button -->
                            <button class="generate-btn" onclick="generateAffiliateLink('{{ $product->slug }}', this)">
                                <span class="default-text">
                                    <i class="fas fa-link mr-2"></i>Tạo Link Affiliate
                                </span>
                                <span class="loading">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>Đang tạo...
                                </span>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="no-products animate__animated animate__fadeInUp">
                    <i class="fas fa-box-open"></i>
                    <h4>Chưa có sản phẩm nào</h4>
                    <p>Hiện tại chưa có sản phẩm nào có thể tạo link affiliate.</p>
                </div>
            @endif
        </div>

    </div>
</div>

<!-- Success Modal -->
<div class="success-modal" id="successModal">
    <div class="success-content">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        <h4 class="mb-3">Link Affiliate Đã Sẵn Sàng!</h4>
        <p class="text-muted mb-4">Link của bạn đã được tạo thành công. Hãy chia sẻ để bắt đầu kiếm hoa hồng!</p>
        
        <div class="input-group mb-4">
            <input type="text" class="form-control" id="generatedLink" readonly>
            <div class="input-group-append">
                <button class="btn btn-outline-primary" onclick="copyLink()">
                    <i class="fas fa-copy"></i>
                </button>
            </div>
        </div>
        
        <div class="d-flex justify-content-center">
            <button class="btn btn-primary mr-2" onclick="shareLink()">
                <i class="fas fa-share mr-2"></i>Chia sẻ
            </button>
            <button class="btn btn-success mr-2" onclick="viewMyLinks()">
                <i class="fas fa-list mr-2"></i>Xem Links
            </button>
            <button class="btn btn-secondary" onclick="closeModal()">
                <i class="fas fa-times mr-2"></i>Đóng
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Search functionality
    const searchInput = document.getElementById('productSearch');
    const sortSelect = document.getElementById('sortSelect');
    const filterButtons = document.querySelectorAll('.filter-btn');
    const productsGrid = document.getElementById('productsGrid');
    
    let currentFilter = 'all';
    let currentSort = 'latest';
    
    // Search input handler
    searchInput.addEventListener('input', debounce(filterProducts, 300));
    sortSelect.addEventListener('change', function() {
        currentSort = this.value;
        filterProducts();
    });
    
    // Filter buttons
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.filter;
            filterProducts();
        });
    });
    
    function filterProducts() {
        const searchTerm = searchInput.value.toLowerCase();
        const productCards = document.querySelectorAll('.product-card');
        
        let visibleProducts = [];
        
        productCards.forEach(card => {
            const title = card.dataset.title;
            const commission = parseFloat(card.dataset.commission);
            const price = parseFloat(card.dataset.price);
            const daysOld = parseInt(card.dataset.created);
            
            let shouldShow = true;
            
            // Search filter
            if (searchTerm && !title.includes(searchTerm)) {
                shouldShow = false;
            }
            
            // Category filter
            if (currentFilter === 'high-commission' && commission <= 15) {
                shouldShow = false;
            } else if (currentFilter === 'new' && daysOld > 30) {
                shouldShow = false;
            } else if (currentFilter === 'popular') {
                // Add logic for popular products
            }
            
            if (shouldShow) {
                visibleProducts.push({
                    element: card,
                    commission: commission,
                    price: price,
                    daysOld: daysOld
                });
            }
            
            card.style.display = shouldShow ? 'block' : 'none';
        });
        
        // Sort products
        sortProducts(visibleProducts);
    }
    
    function sortProducts(products) {
        products.sort((a, b) => {
            switch(currentSort) {
                case 'price_asc':
                    return a.price - b.price;
                case 'price_desc':
                    return b.price - a.price;
                case 'commission_desc':
                    return b.commission - a.commission;
                case 'latest':
                    return a.daysOld - b.daysOld;
                default:
                    return 0;
            }
        });
        
        // Reorder DOM elements
        products.forEach((product, index) => {
            product.element.style.order = index;
        });
    }
    
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
});

// Generate affiliate link
function generateAffiliateLink(productSlug, button) {
    button.classList.add('loading');
    button.disabled = true;
    
    fetch(`/agent/links/generate/${productSlug}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.link) {
            document.getElementById('generatedLink').value = data.link;
            document.getElementById('successModal').style.display = 'flex';
            
            // Success animation
            setTimeout(() => {
                document.querySelector('.success-content').classList.add('animate__animated', 'animate__bounceIn');
            }, 100);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi tạo link. Vui lòng thử lại!');
    })
    .finally(() => {
        button.classList.remove('loading');
        button.disabled = false;
    });
}

// Modal functions
function copyLink() {
    const linkInput = document.getElementById('generatedLink');
    linkInput.select();
    document.execCommand('copy');
    
    // Show success feedback
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i>';
    btn.classList.add('btn-success');
    btn.classList.remove('btn-outline-primary');
    
    setTimeout(() => {
        btn.innerHTML = originalHTML;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-primary');
    }, 2000);
}

function shareLink() {
    const link = document.getElementById('generatedLink').value;
    if (navigator.share) {
        navigator.share({
            title: 'Link Affiliate',
            text: 'Xem sản phẩm tuyệt vời này!',
            url: link
        });
    } else {
        // Fallback: copy to clipboard
        copyLink();
        alert('Link đã được copy! Bạn có thể chia sẻ ở bất kỳ đâu.');
    }
}

function viewMyLinks() {
    window.location.href = '{{ route("agent.links.index") }}';
}

function closeModal() {
    document.getElementById('successModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('successModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Add CSRF token to meta if not exists
if (!document.querySelector('meta[name="csrf-token"]')) {
    const meta = document.createElement('meta');
    meta.name = 'csrf-token';
    meta.content = '{{ csrf_token() }}';
    document.getElementsByTagName('head')[0].appendChild(meta);
}
</script>
@endpush