// Đặt cấu hình mặc định cho tất cả các bảng DataTables
$.extend(true, $.fn.dataTable.defaults, {
  "language": {
      "sLengthMenu": "Hiển thị _MENU_ mục",
      "sZeroRecords": "Không tìm thấy dữ liệu",
      "sInfo": "Hiển thị _START_ đến _END_ của _TOTAL_ mục",
      "sInfoEmpty": "Không có mục nào để hiển thị",
      "sInfoFiltered": "(lọc từ _MAX_ mục)",
      "sSearch": "Tìm kiếm:",
      "oPaginate": {
          "sFirst": "Đầu tiên",
          "sLast": "Cuối cùng",
          "sNext": "Tiếp",
          "sPrevious": "Trước"
      }
  }
});

// Tự động khởi tạo DataTables cho các bảng có class 'dataTable'
$(document).ready(function () {
  $('.dataTable').DataTable();
});
