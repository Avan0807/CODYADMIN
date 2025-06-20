@extends('backend.layouts.master')

@section('main-content')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Chỉnh sửa Link Affiliate</h6>
    </div>
    <div class="card-body">
        <form method="post" action="{{ route('agent.links.update', $agentLink->id) }}">
            @csrf
            @method('PATCH')
            
            <div class="row">
                <!-- Thông tin hiện tại -->
                <div class="col-md-12">
                    <div class="card mb-4 border-info">
                        <div class="card-header bg-info text-white">
                            <h6 class="m-0 font-weight-bold">Thông tin hiện tại</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        @if($agentLink->agent->photo)
                                            <img src="{{ asset('storage/' . $agentLink->agent->photo) }}" class="rounded-circle mr-3" width="50" height="50">
                                        @else
                                            <div class="bg-primary rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-weight-bold">{{ $agentLink->agent->name }}</div>
                                            <div class="text-muted">{{ $agentLink->agent->company ?? 'Cá nhân' }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        @if($agentLink->product->photo)
                                            <img src="{{ $agentLink->product->photo }}" class="rounded mr-3" width="50" height="50" style="object-fit: cover;">
                                        @else
                                            <div class="bg-secondary rounded mr-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                <i class="fas fa-box text-white"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-weight-bold">{{ $agentLink->product->title }}</div>
                                            <div class="text-success">{{ number_format($agentLink->product->price, 0, ',', '.') }}đ</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="text-center p-2 bg-light rounded">
                                        <div class="text-muted small">Hash Reference</div>
                                        <code class="font-weight-bold">{{ $agentLink->hash_ref }}</code>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-2 bg-light rounded">
                                        <div class="text-muted small">Hoa hồng hiện tại</div>
                                        <span class="font-weight-bold text-success">{{ $agentLink->commission_percentage }}%</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-2 bg-light rounded">
                                        <div class="text-muted small">Ngày tạo</div>
                                        <span class="font-weight-bold">{{ $agentLink->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <label>Link hiện tại:</label>
                                <div class="input-group">
                                    <input type="text" value="{{ $agentLink->product_link }}" class="form-control" readonly>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary" onclick="copyCurrentLink()">
                                            <i class="fas fa-copy"></i> Copy
                                        </button>
                                        <a href="{{ $agentLink->product_link }}" target="_blank" class="btn btn-outline-primary">
                                            <i class="fas fa-external-link-alt"></i> Mở
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Chọn đại lý -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Thay đổi đại lý</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="agent_id">Chọn đại lý <span class="text-danger">*</span></label>
                                <select id="agent_id" name="agent_id" class="form-control" required>
                                    <option value="">-- Chọn đại lý --</option>
                                    @foreach($agents as $agent)
                                        <option value="{{ $agent->id }}" 
                                                {{ old('agent_id', $agentLink->agent_id) == $agent->id ? 'selected' : '' }}>
                                            {{ $agent->name }} 
                                            @if($agent->company)
                                                - {{ $agent->company }}
                                            @endif
                                            ({{ $agent->commission_rate }}% hoa hồng)
                                        </option>
                                    @endforeach
                                </select>
                                @error('agent_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Hiển thị thông tin đại lý khi chọn -->
                            <div id="agentInfo" class="mt-3" style="display: none;">
                                <div class="card border-primary">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div id="agentPhoto" class="mr-3"></div>
                                            <div>
                                                <div class="font-weight-bold" id="agentName"></div>
                                                <div class="text-muted" id="agentCompany"></div>
                                                <div class="text-info" id="agentCommission"></div>
                                                <div class="text-muted" id="agentContact"></div>
                                            </div>
                                        </div>
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
                            <h6 class="m-0 font-weight-bold text-primary">Thay đổi sản phẩm</h6>
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
                                                {{ old('product_id', $agentLink->product_id) == $product->id ? 'selected' : '' }}>
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
                                               value="{{ old('commission_percentage', $agentLink->commission_percentage) }}" 
                                               class="form-control" required>
                                        @error('commission_percentage')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Dự tính hoa hồng mới</label>
                                        <div class="form-control-plaintext">
                                            <span id="estimatedCommission" class="font-weight-bold text-success">
                                                {{ number_format(($agentLink->product->price * $agentLink->commission_percentage) / 100, 0, ',', '.') }}đ
                                            </span>
                                        </div>
                                        <small class="text-muted">Dựa trên giá sản phẩm hiện tại</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Hash Reference</label>
                                        <div class="form-control-plaintext">
                                            <code id="hashRef" class="bg-light p-2 rounded">{{ $agentLink->hash_ref }}</code>
                                        </div>
                                        <small class="text-muted">Không thay đổi khi chỉnh sửa</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview link mới -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4 border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="m-0 font-weight-bold">Preview Link mới (nếu thay đổi sản phẩm)</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label>Link sẽ được cập nhật:</label>
                                <div class="input-group">
                                    <input type="text" id="previewLink" class="form-control" readonly 
                                           value="{{ $agentLink->product_link }}">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary" onclick="copyPreviewLink()">
                                            <i class="fas fa-copy"></i> Copy
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted">Link chỉ thay đổi khi bạn chọn sản phẩm khác</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit buttons -->
            <div class="form-group text-center">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Cập nhật Link
                </button>
                <a href="{{ route('agent.links.index') }}" class="btn btn-secondary btn-lg ml-2">
                    <i class="fas fa-times"></i> Hủy
                </a>
                <button type="button" class="btn btn-warning btn-lg ml-2" onclick="resetForm()">
                    <i class="fas fa-undo"></i> Reset
                </button>
            </div>
        </form>
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
    .bg-warning {
        background-color: #f6c23e !important;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Agent data for quick access
    const agentData = @json($agents->keyBy('id'));
    const productData = @json($products->keyBy('id'));
    const currentHashRef = "{{ $agentLink->hash_ref }}";

    // Show current agent/product info if selected
    const currentAgentId = $('#agent_id').val();
    const currentProductId = $('#product_id').val();
    
    if (currentAgentId && agentData[currentAgentId]) {
        showAgentInfo(agentData[currentAgentId]);
    }
    if (currentProductId && productData[currentProductId]) {
        showProductInfo(productData[currentProductId]);
    }

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
            // Auto-fill commission from product (only if different from current)
            if (productId != "{{ $agentLink->product_id }}") {
                $('#commission_percentage').val(productData[productId].commission_percentage);
            }
        } else {
            $('#productInfo').hide();
        }
        updatePreview();
        calculateCommission();
    });

    // Handle commission change
    $('#commission_percentage').on('input', function() {
        calculateCommission();
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
        
        if (productId && productData[productId]) {
            const product = productData[productId];
            const previewLink = `${window.location.origin}/product/${product.slug}?ref=${currentHashRef}`;
            $('#previewLink').val(previewLink);
        } else {
            $('#previewLink').val("{{ $agentLink->product_link }}");
        }
    }

    // Initial calculation
    calculateCommission();
});

function copyCurrentLink() {
    const link = "{{ $agentLink->product_link }}";
    navigator.clipboard.writeText(link).then(function() {
        showCopySuccess('Đã copy link hiện tại!');
    });
}

function copyPreviewLink() {
    const link = document.getElementById('previewLink');
    link.select();
    document.execCommand('copy');
    showCopySuccess('Đã copy link preview!');
}

function showCopySuccess(message) {
    // You can implement a toast notification here
    alert(message);
}

function resetForm() {
    if (confirm('Bạn có chắc chắn muốn reset form về trạng thái ban đầu?')) {
        // Reset to original values
        $('#agent_id').val("{{ $agentLink->agent_id }}").trigger('change');
        $('#product_id').val("{{ $agentLink->product_id }}").trigger('change');
        $('#commission_percentage').val("{{ $agentLink->commission_percentage }}");
        $('#product_search').val('');
        
        // Show all product options
        $('#product_id option').show();
    }
}
</script>
@endpush