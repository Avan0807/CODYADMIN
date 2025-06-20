@extends('backend.layouts.master')

@section('main-content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Tạo Link Affiliate cho Đại lý</h6>
    </div>
    <div class="card-body">
        <form id="agentLinkForm" style="display: none;">
            @csrf
            
            <div class="row">
                <!-- Thông tin đại lý hiện tại -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin của bạn</h6>
                        </div>
                        <div class="card-body">
                            @php $currentAgent = Auth::user(); @endphp
                            <div class="d-flex align-items-center">
                                @if($currentAgent->photo)
                                    <img src="{{ asset('storage/' . $currentAgent->photo) }}" class="rounded-circle mr-3" width="60" height="60">
                                @else
                                    <div class="bg-primary rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                @endif
                                <div>
                                    <div class="font-weight-bold h5">{{ $currentAgent->name }}</div>
                                    @if($currentAgent->company)
                                        <div class="text-muted">{{ $currentAgent->company }}</div>
                                    @endif
                                    <div class="text-success">Hoa hồng: {{ $currentAgent->commission_rate }}%</div>
                                    <small class="text-muted">{{ $currentAgent->email }}</small>
                                </div>
                            </div>
                            
                            <div class="mt-3 p-3 bg-light rounded">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="font-weight-bold text-primary">{{ $currentAgent->agentLinks()->count() }}</div>
                                        <small class="text-muted">Links đã tạo</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="font-weight-bold text-success">{{ number_format($currentAgent->total_commission, 0, ',', '.') }}đ</div>
                                        <small class="text-muted">Tổng hoa hồng</small>
                                    </div>
                                    <div class="col-4">
                                        <div class="font-weight-bold text-warning">{{ $currentAgent->agentOrders()->pending()->count() }}</div>
                                        <small class="text-muted">Chờ thanh toán</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chọn sản phẩm -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Thông tin sản phẩm</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="product_search">Tìm sản phẩm</label>
                                <input type="text" id="product_search" class="form-control" placeholder="Nhập tên sản phẩm để tìm kiếm...">
                            </div>

                            <div class="form-group">
                                <label for="product_id">Chọn sản phẩm <span class="text-danger">*</span></label>
                                <select id="product_id" name="product_id" class="form-control" required>
                                    <option value="">-- Chọn sản phẩm --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" 
                                                data-price="{{ $product->price }}" 
                                                data-commission="{{ $product->commission_percentage }}"
                                                data-photo="{{ $product->photo }}"
                                                data-slug="{{ $product->slug }}"
                                                {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                            {{ $product->title }} 
                                            ({{ number_format($product->price, 0, ',', '.') }}đ - {{ $product->commission_percentage }}%)
                                        </option>
                                    @endforeach
                                </select>
                                @error('product_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Hiển thị thông tin sản phẩm khi chọn -->
                            <div id="productInfo" class="mt-3" style="display: none;">
                                <div class="card border-success">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div id="productPhoto" class="mr-3"></div>
                                            <div>
                                                <div class="font-weight-bold" id="productName"></div>
                                                <div class="text-success" id="productPrice"></div>
                                                <div class="text-info" id="productCommission"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cài đặt commission -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Cài đặt hoa hồng</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="commission_percentage">Tỷ lệ hoa hồng (%) <span class="text-danger">*</span></label>
                                        <input id="commission_percentage" name="commission_percentage" type="number" 
                                               step="0.01" min="0" max="100" 
                                               placeholder="VD: 10.00" 
                                               value="{{ old('commission_percentage', Auth::user()->commission_rate) }}" 
                                               class="form-control" required>
                                        @error('commission_percentage')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        <small class="text-muted">Mặc định từ cài đặt tài khoản của bạn</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Dự tính hoa hồng</label>
                                        <div class="form-control-plaintext">
                                            <span id="estimatedCommission" class="font-weight-bold text-success">0đ</span>
                                        </div>
                                        <small class="text-muted">Dựa trên giá sản phẩm hiện tại</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Hash Reference</label>
                                        <div class="form-control-plaintext">
                                            <code id="previewHashRef" class="bg-light p-2 rounded">Sẽ tự động tạo</code>
                                        </div>
                                        <small class="text-muted">Mã định danh unique cho link</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview link -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4 border-info">
                        <div class="card-header bg-info text-white">
                            <h6 class="m-0 font-weight-bold">Preview Link Affiliate</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Link sẽ được tạo:</label>
                                <div class="input-group">
                                    <input type="text" id="previewLink" class="form-control" readonly placeholder="Chọn sản phẩm để xem preview link">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary" onclick="copyPreviewLink()" disabled id="copyBtn">
                                            <i class="fas fa-copy"></i> Copy
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit buttons -->
            <div class="form-group text-center">
                <button type="button" class="btn btn-primary btn-lg" onclick="generateLinkAPI()">
                    <i class="fas fa-link"></i> Tạo Link Affiliate
                </button>
                <a href="{{ route('agent.links.index') }}" class="btn btn-secondary btn-lg ml-2">
                    <i class="fas fa-times"></i> Hủy
                </a>
            </div>
        </form id="agentLinkForm">
    </div>
</div>
@endsection

@push('styles')
<style>
    .card-header h6 {
        margin-bottom: 0;
    }
    .form-group label {
        font-weight: 600;
        color: #5a5c69;
    }
    .text-danger {
        font-size: 0.875rem;
    }
    #agentInfo, #productInfo {
        animation: fadeIn 0.3s ease-in;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Add CSRF token to meta
    if (!$('meta[name="csrf-token"]').length) {
        $('head').append('<meta name="csrf-token" content="{{ csrf_token() }}">');
    }
    
    // Agent data for quick access
    const agentData = @json($agents->keyBy('id'));
    const productData = @json($products->keyBy('id'));

    // Handle agent selection
    $('#agent_id').on('change', function() {
        const agentId = $(this).val();
        if (agentId && agentData[agentId]) {
            showAgentInfo(agentData[agentId]);
        } else {
            $('#agentInfo').hide();
        }
        updatePreview();
    });

    // Handle product selection
    $('#product_id').on('change', function() {
        const productId = $(this).val();
        if (productId && productData[productId]) {
            showProductInfo(productData[productId]);
            // Auto-fill commission from product
            $('#commission_percentage').val(productData[productId].commission_percentage);
        } else {
            $('#productInfo').hide();
        }
        updatePreview();
        calculateCommission();
    });

    // Handle commission change
    $('#commission_percentage').on('input', function() {
        calculateCommission();
        updatePreview();
    });

    // Product search
    $('#product_search').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('#product_id option').each(function() {
            const text = $(this).text().toLowerCase();
            if (text.includes(searchTerm) || $(this).val() === '') {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    function showAgentInfo(agent) {
        const photoHtml = agent.photo 
            ? `<img src="/storage/${agent.photo}" class="rounded-circle" width="50" height="50">`
            : `<div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;"><i class="fas fa-user text-white"></i></div>`;
        
        $('#agentPhoto').html(photoHtml);
        $('#agentName').text(agent.name);
        $('#agentCompany').text(agent.company || 'Cá nhân');
        $('#agentCommission').text(`Hoa hồng mặc định: ${agent.commission_rate}%`);
        $('#agentContact').text(`${agent.email} - ${agent.phone}`);
        $('#agentInfo').show();
    }

    function showProductInfo(product) {
        const photoHtml = product.photo 
            ? `<img src="${product.photo}" class="rounded" width="50" height="50" style="object-fit: cover;">`
            : `<div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;"><i class="fas fa-box text-white"></i></div>`;
        
        $('#productPhoto').html(photoHtml);
        $('#productName').text(product.title);
        $('#productPrice').text(new Intl.NumberFormat('vi-VN').format(product.price) + 'đ');
        $('#productCommission').text(`Hoa hồng: ${product.commission_percentage}%`);
        $('#productInfo').show();
    }

    function calculateCommission() {
        const productId = $('#product_id').val();
        const commission = parseFloat($('#commission_percentage').val()) || 0;
        
        if (productId && productData[productId] && commission > 0) {
            const price = productData[productId].price;
            const estimatedCommission = (price * commission) / 100;
            $('#estimatedCommission').text(new Intl.NumberFormat('vi-VN').format(estimatedCommission) + 'đ');
        } else {
            $('#estimatedCommission').text('0đ');
        }
    }

    function updatePreview() {
        const productId = $('#product_id').val();
        const agentId = $('#agent_id').val();
        
        if (productId && productData[productId] && agentId) {
            const product = productData[productId];
            const hashRef = generateHashRef();
            const previewLink = `${window.location.origin}/product/${product.slug}?ref=${hashRef}`;
            
            $('#previewHashRef').text(hashRef);
            $('#previewLink').val(previewLink);
            $('#copyBtn').prop('disabled', false);
        } else {
            $('#previewHashRef').text('Sẽ tự động tạo');
            $('#previewLink').val('');
            $('#copyBtn').prop('disabled', true);
        }
    }

// Generate link via API (new method)
function generateLinkAPI() {
    const productId = $('#product_id').val();
    
    if (!productId) {
        swal("Lỗi!", "Vui lòng chọn sản phẩm trước", "error");
        return;
    }
    
    const product = productData[productId];
    if (!product || !product.slug) {
        swal("Lỗi!", "Không tìm thấy thông tin sản phẩm", "error");
        return;
    }
    
    // Show loading
    swal({
        title: "Đang tạo link...",
        text: "Vui lòng chờ trong giây lát",
        icon: "info",
        buttons: false,
        closeOnClickOutside: false,
    });
    
    // Call API
    $.ajax({
        url: `/admin/agent-links/generate/${product.slug}`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            swal.close();
            
            if (response.product_link) {
                // Show success with link
                swal({
                    title: "Thành công!",
                    text: response.message,
                    icon: "success",
                    content: {
                        element: "div",
                        attributes: {
                            innerHTML: `
                                <div class="mt-3">
                                    <label class="font-weight-bold">Link được tạo:</label>
                                    <div class="input-group mt-2">
                                        <input type="text" id="generatedLink" value="${response.product_link}" class="form-control" readonly>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-primary" onclick="copyGeneratedLink()">Copy</button>
                                        </div>
                                    </div>
                                    <small class="text-muted">Commission: ${response.commission_percentage}%</small>
                                </div>
                            `
                        }
                    },
                    buttons: {
                        viewList: {
                            text: "Xem danh sách",
                            value: "list",
                            className: "btn-info"
                        },
                        createNew: {
                            text: "Tạo link khác",
                            value: "new",
                            className: "btn-success"
                        }
                    }
                }).then((value) => {
                    if (value === "list") {
                        window.location.href = "{{ route('agent.links.index') }}";
                    } else if (value === "new") {
                        // Reset form
                        $('#product_id').val('').trigger('change');
                        $('#commission_percentage').val('');
                    }
                });
            }
        },
        error: function(xhr) {
            swal.close();
            
            let errorMessage = "Có lỗi xảy ra khi tạo link";
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            swal("Lỗi!", errorMessage, "error");
        }
    });
}

function copyGeneratedLink() {
    const linkInput = document.getElementById('generatedLink');
    if (linkInput) {
        linkInput.select();
        document.execCommand('copy');
        
        // Show mini notification
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = 'Đã copy!';
        button.classList.add('btn-success');
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('btn-success');
            button.classList.add('btn-primary');
        }, 1500);
    }
}

    // Auto-select if only one agent/product
    if ($('#agent_id option').length === 2) {
        $('#agent_id option:last').prop('selected', true).trigger('change');
    }
    if ($('#product_id option').length === 2) {
        $('#product_id option:last').prop('selected', true).trigger('change');
    }
});

function copyPreviewLink() {
    const link = document.getElementById('previewLink');
    link.select();
    document.execCommand('copy');
    
    // Show success message
    const btn = document.getElementById('copyBtn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i> Đã copy!';
    btn.classList.remove('btn-outline-secondary');
    btn.classList.add('btn-success');
    
    setTimeout(() => {
        btn.innerHTML = originalText;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-secondary');
    }, 2000);
}
</script>
@endpush