@extends('agent.layouts.master')
@section('title', 'Quản lý Links - Đại lý')

@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
<style>
    .links-container {
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
    
    .stats-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        text-align: center;
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border: none;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.12);
    }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 1.5rem;
        color: white;
    }
    
    .stat-icon.primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .stat-icon.success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .stat-icon.warning { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    .stat-icon.info { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    
    .stat-value {
        font-size: 2rem;
        font-weight: 700;
        color: #2d3748;
        margin-bottom: 5px;
    }
    
    .stat-label {
        color: #718096;
        font-weight: 500;
    }
    
    .links-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        border: none;
    }
    
    .card-header-custom {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 25px 30px;
        border: none;
    }
    
    .links-grid {
        padding: 30px;
    }
    
    .link-item {
        background: #f8f9fc;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        position: relative;
    }
    
    .link-item:hover {
        background: white;
        border-color: #667eea;
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.15);
    }
    
    .link-header {
        display: flex;
        justify-content: between;
        align-items: flex-start;
        margin-bottom: 20px;
    }
    
    .product-info {
        display: flex;
        align-items: center;
        flex: 1;
    }
    
    .product-image {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        overflow: hidden;
        margin-right: 15px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .product-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .product-details h5 {
        margin: 0 0 5px 0;
        font-weight: 600;
        color: #2d3748;
        font-size: 1.1rem;
    }
    
    .product-price {
        color: #1cc88a;
        font-weight: 600;
        font-size: 1rem;
    }
    
    .commission-badge {
        background: linear-gradient(45deg, #11998e, #38ef7d);
        color: white;
        padding: 8px 15px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
        white-space: nowrap;
    }
    
    .link-url-section {
        margin: 20px 0;
    }
    
    .link-url {
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 15px;
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
        color: #4a5568;
        word-break: break-all;
        position: relative;
    }
    
    .link-actions {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }
    
    .btn-action {
        padding: 8px 15px;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 500;
        border: none;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    .btn-copy {
        background: #667eea;
        color: white;
    }
    
    .btn-copy:hover {
        background: #5a67d8;
        color: white;
        text-decoration: none;
    }
    
    .btn-share {
        background: #48bb78;
        color: white;
    }
    
    .btn-share:hover {
        background: #38a169;
        color: white;
        text-decoration: none;
    }
    
    .btn-qr {
        background: #ed8936;
        color: white;
    }
    
    .btn-qr:hover {
        background: #dd6b20;
        color: white;
        text-decoration: none;
    }
    
    .btn-delete {
        background: #f56565;
        color: white;
    }
    
    .btn-delete:hover {
        background: #e53e3e;
        color: white;
        text-decoration: none;
    }
    
    .link-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        gap: 15px;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
    }
    
    .link-stat {
        text-align: center;
    }
    
    .link-stat-value {
        font-weight: 600;
        color: #2d3748;
        font-size: 1.1rem;
    }
    
    .link-stat-label {
        font-size: 0.8rem;
        color: #718096;
        margin-top: 2px;
    }
    
    .search-section {
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    
    .search-input {
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px 15px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .search-input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }
    
    .filter-buttons {
        display: flex;
        gap: 10px;
        margin-top: 15px;
        flex-wrap: wrap;
    }
    
    .filter-btn {
        background: #f7fafc;
        border: 2px solid #e2e8f0;
        border-radius: 20px;
        padding: 8px 16px;
        color: #4a5568;
        font-weight: 500;
        transition: all 0.3s ease;
        cursor: pointer;
        font-size: 0.9rem;
    }
    
    .filter-btn:hover,
    .filter-btn.active {
        background: #667eea;
        border-color: #667eea;
        color: white;
    }
    
    .empty-state {
        text-align: center;
        padding: 80px 20px;
        color: #718096;
    }
    
    .empty-state i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.5;
    }
    
    .floating-action {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        width: 60px;
        height: 60px;
        border-radius: 50%;
        border: none;
        color: white;
        font-size: 1.5rem;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        cursor: pointer;
        transition: all 0.3s ease;
        z-index: 1000;
    }
    
    .floating-action:hover {
        transform: scale(1.1);
        box-shadow: 0 12px 35px rgba(102, 126, 234, 0.6);
    }
    
    @media (max-width: 768px) {
        .link-header {
            flex-direction: column;
            gap: 15px;
        }
        
        .links-container {
            padding: 10px;
        }
        
        .link-actions {
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .stats-row {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    .qr-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }
    
    .qr-content {
        background: white;
        border-radius: 20px;
        padding: 30px;
        text-align: center;
        max-width: 400px;
        width: 90%;
    }
    
    .animate-fadeIn {
        animation: fadeIn 0.3s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('main-content')
<div class="links-container">
    <div class="container-fluid">
        
        <!-- Header Section -->
        <div class="header-card animate__animated animate__fadeInDown">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="h2 mb-3">🔗 Quản lý Links Affiliate</h1>
                    <p class="mb-0 opacity-75">Theo dõi và quản lý tất cả links affiliate của bạn. Xem thống kê, chia sẻ links và tối ưu hóa doanh thu.</p>
                </div>
                <div class="col-lg-4 text-right">
                    <a href="{{ route('agent.links.create') }}" class="btn btn-light btn-lg">
                        <i class="fas fa-plus mr-2"></i>Tạo Link Mới
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Row -->
        <div class="stats-row animate__animated animate__fadeInUp">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-link"></i>
                </div>
                <div class="stat-value">{{ count($links) }}</div>
                <div class="stat-label">Tổng Links</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-value">{{ $links->where('created_at', '>=', now()->startOfMonth())->count() }}</div>
                <div class="stat-label">Links tháng này</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-fire"></i>
                </div>
                <div class="stat-value">{{ $links->where('created_at', '>=', now()->subDays(7))->count() }}</div>
                <div class="stat-label">Links tuần này</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-value">{{ number_format($links->avg('commission_percentage'), 1) }}%</div>
                <div class="stat-label">Hoa hồng TB</div>
            </div>
        </div>

        <!-- Search & Filter Section -->
        <div class="search-section animate__animated animate__fadeInUp">
            <div class="row">
                <div class="col-lg-8">
                    <input type="text" 
                           class="search-input form-control" 
                           id="linkSearch" 
                           placeholder="Tìm kiếm theo tên sản phẩm, link hoặc hash reference...">
                </div>
                <div class="col-lg-4">
                    <select class="search-input form-control" id="sortSelect">
                        <option value="latest">Mới nhất</option>
                        <option value="oldest">Cũ nhất</option>
                        <option value="commission_desc">Hoa hồng cao nhất</option>
                        <option value="commission_asc">Hoa hồng thấp nhất</option>
                        <option value="product_name">Tên sản phẩm A-Z</option>
                    </select>
                </div>
            </div>
            
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="all">
                    <i class="fas fa-list mr-1"></i>Tất cả ({{ count($links) }})
                </button>
                <button class="filter-btn" data-filter="high-commission">
                    <i class="fas fa-star mr-1"></i>Hoa hồng cao (>15%)
                </button>
                <button class="filter-btn" data-filter="recent">
                    <i class="fas fa-clock mr-1"></i>Gần đây (7 ngày)
                </button>
                <button class="filter-btn" data-filter="this-month">
                    <i class="fas fa-calendar mr-1"></i>Tháng này
                </button>
            </div>
        </div>

        <!-- Links List -->
        <div class="links-card animate__animated animate__fadeInUp">
            <div class="card-header-custom">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list-alt mr-2"></i>Danh sách Links Affiliate
                    </h5>
                    <small>Cập nhật: {{ now()->format('d/m/Y H:i') }}</small>
                </div>
            </div>
            
            <div class="links-grid" id="linksContainer">
                @forelse($links as $link)
                    <div class="link-item animate-fadeIn" 
                         data-commission="{{ $link->commission_percentage }}"
                         data-created="{{ $link->created_at->diffInDays() }}"
                         data-product-name="{{ strtolower($link->product->title ?? '') }}">
                        
                        <!-- Link Header -->
                        <div class="link-header">
                            <div class="product-info">
                                <div class="product-image">
                                    @if($link->product && $link->product->photo)
                                        <img src="{{ $link->product->photo }}" alt="{{ $link->product->title }}">
                                    @else
                                        <i class="fas fa-box fa-lg text-white"></i>
                                    @endif
                                </div>
                                <div class="product-details">
                                    <h5>{{ $link->product->title ?? 'Sản phẩm đã xóa' }}</h5>
                                    <div class="product-price">{{ number_format($link->product->price ?? 0, 0, ',', '.') }}đ</div>
                                </div>
                            </div>
                            <div class="commission-badge">
                                {{ $link->commission_percentage }}% hoa hồng
                            </div>
                        </div>

                        <!-- Link URL -->
                        <div class="link-url-section">
                            <div class="link-url" id="link-{{ $link->id }}">
                                {{ $link->product_link }}
                            </div>
                        </div>

                        <!-- Link Actions -->
                        <div class="link-actions">
                            <button class="btn-action btn-copy" onclick="copyLink('{{ $link->id }}')">
                                <i class="fas fa-copy"></i>Copy Link
                            </button>
                            <button class="btn-action btn-share" onclick="shareLink('{{ $link->product_link }}')">
                                <i class="fas fa-share-alt"></i>Chia sẻ
                            </button>
                            <button class="btn-action btn-qr" onclick="generateQR('{{ $link->product_link }}')">
                                <i class="fas fa-qrcode"></i>QR Code
                            </button>
                            <a href="{{ $link->product_link }}" target="_blank" class="btn-action" style="background: #4299e1; color: white;">
                                <i class="fas fa-external-link-alt"></i>Xem
                            </a>
                            <button class="btn-action btn-delete" onclick="deleteLink('{{ $link->id }}')">
                                <i class="fas fa-trash"></i>Xóa
                            </button>
                        </div>

                        <!-- Link Statistics -->
                        <div class="link-stats">
                            <div class="link-stat">
                                <div class="link-stat-value">{{ number_format(($link->product->price ?? 0) * $link->commission_percentage / 100, 0, ',', '.') }}đ</div>
                                <div class="link-stat-label">Hoa hồng dự kiến</div>
                            </div>
                            <div class="link-stat">
                                <div class="link-stat-value">{{ $link->created_at->format('d/m/Y') }}</div>
                                <div class="link-stat-label">Ngày tạo</div>
                            </div>
                            <div class="link-stat">
                                <div class="link-stat-value">{{ $link->created_at->diffForHumans() }}</div>
                                <div class="link-stat-label">Thời gian</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="fas fa-link"></i>
                        <h4>Chưa có links affiliate nào</h4>
                        <p>Hãy tạo link affiliate đầu tiên để bắt đầu kiếm hoa hồng!</p>
                        <a href="{{ route('agent.links.create') }}" class="btn btn-primary btn-lg mt-3">
                            <i class="fas fa-plus mr-2"></i>Tạo Link Đầu Tiên
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

<!-- Floating Action Button -->
<button class="floating-action" onclick="window.location.href='{{ route('agent.links.create') }}'">
    <i class="fas fa-plus"></i>
</button>

<!-- QR Code Modal -->
<div class="qr-modal" id="qrModal">
    <div class="qr-content">
        <h4 class="mb-3">QR Code</h4>
        <div id="qrCodeContainer" class="mb-3"></div>
        <button class="btn btn-secondary" onclick="closeQRModal()">Đóng</button>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('linkSearch');
    const sortSelect = document.getElementById('sortSelect');
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    let currentFilter = 'all';
    let currentSort = 'latest';
    
    // Search functionality
    searchInput.addEventListener('input', debounce(filterLinks, 300));
    sortSelect.addEventListener('change', function() {
        currentSort = this.value;
        filterLinks();
    });
    
    // Filter buttons
    filterButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            filterButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.dataset.filter;
            filterLinks();
        });
    });
    
    function filterLinks() {
        const searchTerm = searchInput.value.toLowerCase();
        const linkItems = document.querySelectorAll('.link-item');
        
        let visibleLinks = [];
        
        linkItems.forEach(item => {
            const productName = item.dataset.productName;
            const commission = parseFloat(item.dataset.commission);
            const daysOld = parseInt(item.dataset.created);
            
            let shouldShow = true;
            
            // Search filter
            if (searchTerm) {
                const linkText = item.querySelector('.link-url').textContent.toLowerCase();
                const hashRef = item.querySelector('.link-stat-value').textContent.toLowerCase();
                
                if (!productName.includes(searchTerm) && 
                    !linkText.includes(searchTerm) && 
                    !hashRef.includes(searchTerm)) {
                    shouldShow = false;
                }
            }
            
            // Category filter
            if (currentFilter === 'high-commission' && commission <= 15) {
                shouldShow = false;
            } else if (currentFilter === 'recent' && daysOld > 7) {
                shouldShow = false;
            } else if (currentFilter === 'this-month' && daysOld > 30) {
                shouldShow = false;
            }
            
            if (shouldShow) {
                visibleLinks.push({
                    element: item,
                    commission: commission,
                    daysOld: daysOld,
                    productName: productName
                });
            }
            
            item.style.display = shouldShow ? 'block' : 'none';
        });
        
        // Sort links
        sortLinks(visibleLinks);
    }
    
    function sortLinks(links) {
        links.sort((a, b) => {
            switch(currentSort) {
                case 'oldest':
                    return b.daysOld - a.daysOld;
                case 'commission_desc':
                    return b.commission - a.commission;
                case 'commission_asc':
                    return a.commission - b.commission;
                case 'product_name':
                    return a.productName.localeCompare(b.productName);
                case 'latest':
                default:
                    return a.daysOld - b.daysOld;
            }
        });
        
        // Reorder DOM elements
        links.forEach((link, index) => {
            link.element.style.order = index;
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

// Copy link function
function copyLink(linkId) {
    const linkElement = document.getElementById('link-' + linkId);
    const textArea = document.createElement('textarea');
    textArea.value = linkElement.textContent;
    document.body.appendChild(textArea);
    textArea.select();
    document.execCommand('copy');
    document.body.removeChild(textArea);
    
    // Show feedback
    const button = event.target.closest('.btn-copy');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i>Đã copy!';
    button.style.background = '#48bb78';
    
    setTimeout(() => {
        button.innerHTML = originalText;
        button.style.background = '#667eea';
    }, 2000);
}

// Share link function
function shareLink(url) {
    if (navigator.share) {
        navigator.share({
            title: 'Link Affiliate',
            text: 'Xem sản phẩm tuyệt vời này!',
            url: url
        }).catch(console.error);
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(url).then(() => {
            alert('Link đã được copy! Bạn có thể chia sẻ ở bất kỳ đâu.');
        });
    }
}

// Generate QR Code
function generateQR(url) {
    const qrContainer = document.getElementById('qrCodeContainer');
    qrContainer.innerHTML = '';
    
    QRCode.toCanvas(qrContainer, url, {
        width: 256,
        height: 256,
        colorDark: '#667eea',
        colorLight: '#ffffff'
    }, function (error) {
        if (error) console.error(error);
        document.getElementById('qrModal').style.display = 'flex';
    });
}

// Close QR Modal
function closeQRModal() {
    document.getElementById('qrModal').style.display = 'none';
}

// Delete link function
function deleteLink(linkId) {
    if (confirm('Bạn có chắc chắn muốn xóa link này không?')) {
        // Add delete logic here
        console.log('Deleting link:', linkId);
        // You can make an AJAX request to delete the link
    }
}

// Close modal when clicking outside
document.getElementById('qrModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeQRModal();
    }
});
</script>
@endpush