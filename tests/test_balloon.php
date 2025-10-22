<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Modal Floating Save & Close Bottom Right</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        .modal-body {
            max-height: 60vh;
            overflow-y: auto;
            padding-right: 1rem;
            padding-left: 1rem;
        }

        .modal-content {
            position: relative;
            padding-bottom: 80px; /* เว้นที่ให้กล่องปุ่ม */
        }

        /* กล่องปุ่มลอยมุมล่างขวา */
        .floating-buttons {
            position: absolute;
            bottom: 15px;
            right: 15px;
            display: flex;
            gap: 10px;
            z-index: 1050;
        }

        .floating-buttons button {
            border-radius: 999px;
            padding: 10px 28px;
            font-size: 16px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            cursor: pointer;
            border: none;
            color: white;
        }

        .floating-save-btn {
            background-color: #198754;
        }
        .floating-save-btn:hover {
            background-color: #146c43;
        }

        .floating-close-btn {
            background-color: #dc3545;
        }
        .floating-close-btn:hover {
            background-color: #a71d2a;
        }
    </style>
</head>
<body>

<button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
    เปิด Modal
</button>

<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <form class="modal-content" id="myForm">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">ฟอร์มใน Modal ปุ่ม Save & Close ลอยมุมล่างขวา</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body">
                <!-- เนื้อหาฟอร์มยาว -->
                <div class="mb-3">
                    <label for="field1" class="form-label">ฟิลด์ 1</label>
                    <input type="text" id="field1" name="field1" class="form-control" required />
                </div>
                <!-- เพิ่มฟิลด์อื่นๆ ตามต้องการ -->
            </div>

            <!-- กล่องปุ่มลอยมุมล่างขวา -->
            <div class="floating-buttons">
                <button type="button" class="floating-close-btn" data-bs-dismiss="modal">✖ Close</button>
                <button type="submit" class="floating-save-btn">💾 Save</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
