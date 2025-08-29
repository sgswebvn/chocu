<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="icon" href="/assets2/images/favicon.svg" type="image/x-icon">
    <!-- [Page specific CSS] start -->
    <!-- data tables css -->
    <link rel="stylesheet" href="/assets2/css/plugins/dataTables.bootstrap5.min.css">
    <!-- [Page specific CSS] end -->
    <!-- [Google Font] Family -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" id="main-font-link">
    <!-- [Tabler Icons] https://tablericons.com -->
    <link rel="stylesheet" href="/assets2/fonts/tabler-icons.min.css">
    <!-- [Feather Icons] https://feathericons.com -->
    <link rel="stylesheet" href="/assets2/fonts/feather.css">
    <!-- [Font Awesome Icons] https://fontawesome.com/icons -->
    <link rel="stylesheet" href="/assets2/fonts/fontawesome.css">
    <!-- [Material Icons] https://fonts.google.com/icons -->
    <link rel="stylesheet" href="/assets2/fonts/material.css">
    <!-- [Template CSS Files] -->
    <link rel="stylesheet" href="/assets2/css/style.css" id="main-style-link">
    <link rel="stylesheet" href="/assets2/css/style-preset.css" id="preset-style-link">
</head>

<body>
    <?php
    require_once __DIR__ . '/../layouts/navbar.php';
    ?>
    <header class="pc-header">
        <div class="header-wrapper">
            <div class="me-auto pc-mob-drp">
                <ul class="list-unstyled">
                    <li class="pc-h-item pc-sidebar-collapse">
                        <a href="#" class="pc-head-link ms-0" id="sidebar-hide">
                            <i class="ti ti-menu-2"></i>
                        </a>
                    </li>
                    <li class="pc-h-item pc-sidebar-popup">
                        <a href="#" class="pc-head-link ms-0" id="mobile-collapse">
                            <i class="ti ti-menu-2"></i>
                        </a>
                    </li>
                </ul>
            </div>

        </div>
    </header>

    <div class="pc-container">
        <div class="pc-content">
            <div class="page-header">
                <div class="page-block">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="page-header-title">
                                <h5 class="m-b-10">Chỉnh sửa sản phẩm</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="/partners/product/update/<?php echo $product['id']; ?>" method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Tên sản phẩm</label>
                                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($product['title']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Danh mục</label>
                                    <select class="form-control" id="category_id" name="category_id" required>
                                        <option value="">Chọn danh mục</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Mô tả</label>
                                    <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="price" class="form-label">Giá (VNĐ)</label>
                                    <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" value="<?php echo $product['price']; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="image" class="form-label">Ảnh sản phẩm</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                    <?php if ($product['image']): ?>
                                        <img src="<?php echo htmlspecialchars($product['image']
                                                        ? '/uploads/partners/' . $product['image']
                                                        : '/assets/images/default-product.jpg'); ?>" alt="product" width="100" class="mt-2">
                                    <?php endif; ?>
                                </div>
                                <button type="submit" class="btn btn-primary">Cập nhật sản phẩm</button>
                                <a href="/partners/product" class="btn btn-secondary">Hủy</a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="/assets2/js/plugins/jquery.dataTables.min.js"></script>
        <script src="/assets2/js/plugins/dataTables.bootstrap5.min.js"></script>
        <script src="/assets2/js/plugins/popper.min.js"></script>
        <script src="/assets2/js/plugins/simplebar.min.js"></script>
        <script src="/assets2/js/plugins/bootstrap.min.js"></script>
        <script src="/assets2/js/fonts/custom-font.js"></script>
        <script src="/assets2/js/pcoded.js"></script>
        <script src="/assets2/js/plugins/feather.min.js"></script>
        <script>
            // [ Add Rows ]
            var t = $('#add-row-table').DataTable();
            var counter = 1;

            $('#addRow').on('click', function() {
                t.row.add([counter + '.1', counter + '.2', counter + '.3', counter + '.4', counter + '.5']).draw(false);

                counter++;
            });

            $('#addRow').click();

            // [ Individual Column Searching (Text Inputs) ]
            $('#footer-search tfoot th').each(function() {
                var title = $(this).text();
                $(this).html('<input type="text" class="form-control" placeholder="Search ' + title + '">');
            });

            var table = $('#footer-search').DataTable();

            // [ Apply the search ]
            table.columns().every(function() {
                var that = this;

                $('input', this.footer()).on('keyup change', function() {
                    if (that.search() !== this.value) {
                        that.search(this.value).draw();
                    }
                });
            });

            // [ Individual Column Searching (Select Inputs) ]
            $('#footer-select').DataTable({
                initComplete: function() {
                    this.api()
                        .columns()
                        .every(function() {
                            var column = this;
                            var select = $('<select class="form-control form-control-sm"><option value=""></option></select>')
                                .appendTo($(column.footer()).empty())
                                .on('change', function() {
                                    var val = $.fn.dataTable.util.escapeRegex($(this).val());

                                    column.search(val ? '^' + val + '$' : '', true, false).draw();
                                });

                            column
                                .data()
                                .unique()
                                .sort()
                                .each(function(d, j) {
                                    select.append('<option value="' + d + '">' + d + '</option>');
                                });
                        });
                }
            });
            var srow = $('#row-select').DataTable();

            $('#row-select tbody').on('click', 'tr', function() {
                $(this).toggleClass('selected');
            });

            var drow = $('#row-delete').DataTable();

            $('#row-delete tbody').on('click', 'tr', function() {
                if ($(this).hasClass('selected')) {
                    $(this).removeClass('selected');
                } else {
                    drow.$('tr.selected').removeClass('selected');
                    $(this).addClass('selected');
                }
            });

            $('#row-delete-btn').on('click', function() {
                drow.row('.selected').remove().draw(!1);
            });

            function format(d) {
                return (
                    '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;">' +
                    '<tr>' +
                    '<td>Full name:</td>' +
                    '<td>' +
                    d.name +
                    '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td>Extension number:</td>' +
                    '<td>' +
                    d.extn +
                    '</td>' +
                    '</tr>' +
                    '<tr>' +
                    '<td>Extra info:</td>' +
                    '<td>And any further details here (images etc)...</td>' +
                    '</tr>' +
                    '</table>'
                );
            }

            // [ Form input ]
            var table = $('#form-input-table').DataTable();

            $('#form-input-btn').on('click', function() {
                var data = table.$('input, select').serialize();
                alert('The following data would have been submitted to the server: \n\n' + data.substr(0, 120) + '...');
                return false;
            });

            // [ Show-hide table js ]
            var sh = $('#show-hide-table').DataTable({
                scrollY: '200px',
                paging: false
            });

            $('a.toggle-vis').on('click', function(e) {
                e.preventDefault();

                // Get the column API object
                var column = sh.column($(this).attr('data-column'));

                // Toggle the visibility
                column.visible(!column.visible());
            });

            // [ Search API ]
            function filterGlobal() {
                $('#search-api')
                    .DataTable()
                    .search($('#global_filter').val(), $('#global_regex').prop('checked'), $('#global_smart').prop('checked'))
                    .draw();
            }

            function filterColumn(i) {
                $('#search-api')
                    .DataTable()
                    .column(i)
                    .search($('#col' + i + '_filter').val(), $('#col' + i + '_regex').prop('checked'), $('#col' + i + '_smart').prop('checked'))
                    .draw();
            }

            $('#search-api').DataTable();

            $('input.global_filter').on('keyup click', function() {
                filterGlobal();
            });

            $('input.column_filter').on('keyup click', function() {
                filterColumn($(this).parents('tr').attr('data-column'));
            });
        </script>
</body>

</html>



<?php
require_once __DIR__ . '/../layouts/footer.php';
?>