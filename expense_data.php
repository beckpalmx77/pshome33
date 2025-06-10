<?php
// form_expense.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Expense Entry Form</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

    <!-- Dropzone CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.css" />

    <style>
        body {
            background-color: #f8f9fa;
            color: #212529;
        }

        .dropzone {
            border: 2px dashed #0d6efd;
            background: #e9ecef;
            padding: 1rem;
            border-radius: 0.5rem;
            min-height: 100px;
        }

        .dropzone .dz-message {
            font-weight: 500;
            color: #0d6efd;
            font-size: 0.95rem;
        }

        /* Responsive table font size */
        @media (max-width: 576px) {
            table {
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
<div class="container py-5">
    <h1 class="mb-4 text-primary">Expense Master / Detail</h1>

    <form action="save_expense.php" method="post" id="expenseForm" novalidate>
        <div class="row mb-3 align-items-center">
            <label for="doc_id" class="col-sm-2 col-form-label fw-semibold">Doc ID</label>
            <div class="col-sm-4">
                <input type="text" id="doc_id" name="doc_id" class="form-control" placeholder="Enter document ID" required />
                <div class="invalid-feedback">Please enter Doc ID.</div>
            </div>
        </div>

        <div class="row mb-4 align-items-center">
            <label for="doc_date" class="col-sm-2 col-form-label fw-semibold">Doc Date</label>
            <div class="col-sm-4">
                <input type="date" id="doc_date" name="doc_date" class="form-control" required />
                <div class="invalid-feedback">Please select the document date.</div>
            </div>
        </div>

        <div class="table-responsive mb-3">
            <table class="table table-bordered align-middle" id="expenseTable">
                <thead class="table-light">
                <tr>
                    <th>Description</th>
                    <th style="width: 120px;">Amount</th>
                    <th style="width: 200px;">Upload</th>
                    <th style="width: 100px;">Action</th>
                </tr>
                </thead>
                <tbody id="expenseTableBody">
                <tr class="text-center text-muted" id="noRowsMsg">
                    <td colspan="4" class="py-4">No expense rows added yet. Click "Add Row" to start.</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="d-flex gap-3 mb-4">
            <button type="button" class="btn btn-outline-primary" id="btnAddRow">➕ Add Row</button>
            <button type="submit" class="btn btn-success">✅ Submit</button>
        </div>
    </form>
</div>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Dropzone JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js"></script>

<script>
    (() => {
        let rowIndex = 0;
        const dropzoneFiles = {};
        const expenseTableBody = document.getElementById('expenseTableBody');
        const noRowsMsg = document.getElementById('noRowsMsg');
        const btnAddRow = document.getElementById('btnAddRow');

        function updateNoRowsMessage() {
            if (expenseTableBody.rows.length === 0) {
                expenseTableBody.appendChild(noRowsMsg);
            } else if (noRowsMsg.parentNode) {
                noRowsMsg.parentNode.removeChild(noRowsMsg);
            }
        }

        function addRow() {
            if (noRowsMsg.parentNode) noRowsMsg.remove();

            const row = document.createElement('tr');
            const currentIndex = rowIndex;

            row.innerHTML = `
            <td>
                <input type="text" name="description[]" class="form-control" placeholder="Description" required />
                <div class="invalid-feedback">Please enter a description.</div>
            </td>
            <td>
                <input type="number" name="amount[]" class="form-control" step="0.01" min="0" placeholder="0.00" required />
                <div class="invalid-feedback">Please enter an amount greater than 0.</div>
            </td>
            <td>
                <div class="dropzone dz-row" id="dropzone-${currentIndex}" data-row="${currentIndex}"></div>
            </td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-danger" aria-label="Remove row">🗑 Remove</button>
            </td>
        `;

            expenseTableBody.appendChild(row);

            const removeBtn = row.querySelector('button.btn-danger');
            removeBtn.addEventListener('click', () => {
                if (dropzoneFiles[currentIndex]) {
                    // Optional: Clear files or cleanup if needed
                    delete dropzoneFiles[currentIndex];
                }
                row.remove();
                updateNoRowsMessage();
            });

            createDropzone(currentIndex);

            rowIndex++;
        }

        function createDropzone(index) {
            new Dropzone(`#dropzone-${index}`, {
                url: "upload_temp.php",
                paramName: "file",
                maxFilesize: 5, // MB
                acceptedFiles: "image/*",
                addRemoveLinks: true,
                dictDefaultMessage: "Drag & drop or click to upload images",
                init: function () {
                    this.on("success", (file, response) => {
                        if (!dropzoneFiles[index]) dropzoneFiles[index] = [];
                        dropzoneFiles[index].push(response.file);

                        // Add hidden input to form with uploaded filename
                        const input = document.createElement("input");
                        input.type = "hidden";
                        input.name = `uploaded_images[${index}][]`;
                        input.value = response.file;
                        document.getElementById(`dropzone-${index}`).appendChild(input);
                    });

                    this.on("removedfile", (file) => {
                        // Handle removal of files if you want to sync with server or UI
                        // You can implement an AJAX call to delete file if needed
                    });
                }
            });
        }

        // Form validation
        const form = document.getElementById('expenseForm');
        form.addEventListener('submit', e => {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });

        btnAddRow.addEventListener('click', addRow);

        // Initialize with one row for better UX
        addRow();
    })();
</script>
</body>
</html>
