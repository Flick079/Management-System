// assets/js/transactions.js

document.addEventListener('DOMContentLoaded', function () {
    // Add debounce to search input
    const searchInput = document.getElementById('searchInput');
    let searchTimeout;

    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadTransactions();
        }, 500); // 500ms delay after typing stops
    });
    // Set default date range (current month)
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

    document.getElementById('dateFrom').valueAsDate = firstDay;
    document.getElementById('dateTo').valueAsDate = today;

    // Load transactions on page load
    loadTransactions();

    // Event listeners
    document.getElementById('searchBtn').addEventListener('click', loadTransactions);
    document.getElementById('dateFrom').addEventListener('change', loadTransactions);
    document.getElementById('dateTo').addEventListener('change', loadTransactions);
    document.getElementById('discountFilter').addEventListener('change', loadTransactions);
    document.getElementById('searchInput').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            loadTransactions();
        }
    });

    document.getElementById('exportBtn').addEventListener('click', exportTransactions);
    document.getElementById('printReceiptBtn').addEventListener('click', printCurrentReceipt);
});

// Global variable to store current transaction ID for printing
let currentTransactionId = null;

function loadTransactions(page = 1) {
    const searchTerm = document.getElementById('searchInput').value.trim();
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    const discountFilter = document.getElementById('discountFilter').value;

    // Show loading indicator
    const tableBody = document.getElementById('transactionsTableBody');
    tableBody.innerHTML = '<tr><td colspan="9" class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';

    // Build URL with parameters
    const params = new URLSearchParams();
    params.append('page', page);
    if (searchTerm) params.append('search', searchTerm);
    if (dateFrom) params.append('dateFrom', dateFrom);
    if (dateTo) params.append('dateTo', dateTo);
    if (discountFilter && discountFilter !== 'all') params.append('discount', discountFilter);

    fetch(`../../views/pos/get_transactions.php?${params.toString()}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Server returned ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (!data || data.status !== 'success') {
                throw new Error(data?.message || 'Invalid response from server');
            }

            // Ensure we have transactions array
            if (!Array.isArray(data.transactions)) {
                data.transactions = [];
            }

            renderTransactions(data.transactions);
            renderPagination(data.pagination);
            updatePageInfo(data.pagination);
        })
        .catch(error => {
            console.error('Fetch error:', error);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center text-danger">
                        Error loading transactions: ${error.message}
                        <button class="btn btn-sm btn-warning mt-2" onclick="loadTransactions()">Retry</button>
                    </td>
                </tr>
            `;
        });
}

function renderTransactions(transactions) {
    const tableBody = document.getElementById('transactionsTableBody');

    if (!transactions || transactions.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="9" class="text-center">No transactions found</td></tr>';
        return;
    }

    tableBody.innerHTML = transactions.map(transaction => {
        const date = transaction.transaction_date ? new Date(transaction.transaction_date) : null;
        const formattedDate = date
            ? date.toLocaleString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: 'numeric',
                minute: 'numeric',
                second: 'numeric',
                hour12: true
            })
            : 'N/A';
        // <td>₱${parseFloat(transaction.vat || 0).toFixed(2)}</td>
        //<td>₱${parseFloat(transaction.subtotal || 0).toFixed(2)}</td>
        //<td>₱${(parseFloat(transaction.subtotal || 0) + parseFloat(transaction.vat || 0)).toFixed(2)}</td>
        //     `${((parseFloat(transaction.discount_amount || 0) /
        // (parseFloat(transaction.subtotal || 0) + parseFloat(transaction.vat || 0))) * 100).toFixed(2)}%`
        return `
            <tr>
                <td>${transaction.transaction_id || 'N/A'}</td>
                <td>${formattedDate}</td>
                <td>${transaction.cashier_name || 'System'}</td>
                <td>${transaction.item_count || 0} items</td>
                <td>₱${(parseFloat(transaction.subtotal || 0))}</td>
                <td>
                ${transaction.discount_type !== 'none' ?
                `${((parseFloat(transaction.discount_amount || 0) /
                    parseFloat(transaction.subtotal || 0)) * 100)}%`
                : 'None'}
                </td>
                <td>₱${parseFloat(transaction.total || 0).toFixed(2)}</td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="viewTransaction(${transaction.transaction_id})">
                        <i class="bi bi-eye"></i>
                    </button>
                </td>
            </tr>
        `;

    }).join('');
}

function renderPagination(pagination) {
    const paginationElement = document.getElementById('pagination');
    paginationElement.innerHTML = '';

    // Previous button
    const prevLi = document.createElement('li');
    prevLi.className = `page-item ${pagination.current_page === 1 ? 'disabled' : ''}`;
    prevLi.innerHTML = `<a class="page-link" href="#" onclick="loadTransactions(${pagination.current_page - 1}); return false;">Previous</a>`;
    paginationElement.appendChild(prevLi);

    // Page numbers
    for (let i = 1; i <= pagination.total_pages; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === pagination.current_page ? 'active' : ''}`;
        li.innerHTML = `<a class="page-link" href="#" onclick="loadTransactions(${i}); return false;">${i}</a>`;
        paginationElement.appendChild(li);
    }

    // Next button
    const nextLi = document.createElement('li');
    nextLi.className = `page-item ${pagination.current_page === pagination.total_pages ? 'disabled' : ''}`;
    nextLi.innerHTML = `<a class="page-link" href="#" onclick="loadTransactions(${pagination.current_page + 1}); return false;">Next</a>`;
    paginationElement.appendChild(nextLi);
}

function updatePageInfo(pagination) {
    const start = (pagination.current_page - 1) * pagination.per_page + 1;
    const end = Math.min(start + pagination.per_page - 1, pagination.total_records);

    document.getElementById('pageInfo').textContent =
        `Showing ${start} to ${end} of ${pagination.total_records} entries`;
}

let currentTransactionDetails = null; // Global variable to store transaction details

function viewTransaction(transactionId) {
    // Fetch transaction details
    fetch(`../../views/pos/get_transaction_details.php?id=${transactionId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Store all transaction details including cashier name
                currentTransactionDetails = {
                    id: transactionId,
                    cashierName: data.transaction.cashier_name || "System",
                    // Store other details you might need
                    transaction: data.transaction,
                    items: data.items
                };

                renderTransactionDetails(data.transaction, data.items);
                const transactionModal = new bootstrap.Modal(document.getElementById('transactionModal'));
                transactionModal.show();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error fetching transaction details:', error);
            alert('Error loading transaction details');
        });
}

function renderTransactionDetails(transaction, items) {
    // Get the container element properly
    const detailsContainer = document.getElementById('transactionDetails');

    if (!detailsContainer) {
        console.error('Transaction details container not found');
        return;
    }

    // Format discount info
    let discountInfo = 'None';
    if (transaction.discount_type && transaction.discount_type !== 'none' && transaction.discount_amount > 0) {
        const discountTypes = {
            'senior': 'Senior Citizen',
            'student': 'Student',
            'pwd': 'PWD',
            'employee': 'Employee',
            'custom': 'Custom'
        };

        const discountName = discountTypes[transaction.discount_type] || transaction.discount_type;
        discountInfo = transaction.discount_type !== 'none'
            ? `${discountName} (${(parseFloat(transaction.discount_amount || 0) /
                (parseFloat(transaction.subtotal || 0))) * 100}%)`
            : 'None';

    }

    // Calculate total before discount (subtotal + VAT)
    const totalBeforeDiscount = parseFloat(transaction.subtotal);// + parseFloat(transaction.vat);
    const discountAmount = parseFloat(transaction.discount_amount);

    // Transaction header info
    let html = `
        <div class="row mb-3">
            <div class="col-md-6">
                <h6>Transaction #${transaction.transaction_id}</h6>
                <p>Date: ${new Date(transaction.transaction_date).toLocaleString()}</p>
                <p>Cashier: ${transaction.cashier_name || 'System'}</p>
            </div>
            <div class="col-md-6">
                <p>Amount Tendered: ₱${parseFloat(transaction.amount_tendered).toFixed(2)}</p>
                <p>Change: ₱${parseFloat(transaction.change_amount).toFixed(2)}</p>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
    `;

    // Items - show original prices only
    items.forEach(item => {
        html += `
            <tr>
                <td>${item.product_name}</td>
                <td>${item.quantity}</td>
                <td>₱${parseFloat(item.original_price).toFixed(2)}</td>
                <td>₱${(parseFloat(item.original_price) * item.quantity).toFixed(2)}</td>
            </tr>
        `;
    });

    // Summary with correct calculation flow

    html += `
                </tbody>
            </table>
        </div>
        
        <div class="row mt-3">
            <div class="col-md-6">
                <p>Discount Applied: ${discountInfo}</p>
            </div>
            <div class="col-md-6 text-end">
                <p>Subtotal: ₱${parseFloat(transaction.subtotal).toFixed(2)}</p>
                <p>VAT (1.12%): ₱${parseFloat(transaction.vat).toFixed(2)}</p>
                <p>Total Before Discount: ₱${totalBeforeDiscount.toFixed(2)}</p>
                ${transaction.discount_type !== 'none' && transaction.discount_amount > 0 ?
            `<p class="text-danger">Discount: -₱${discountAmount.toFixed(2)}</p>` : ''}
                <p><strong>Final Total: ₱${parseFloat(transaction.total).toFixed(2)}</strong></p>
            </div>
        </div>
    `;

    detailsContainer.innerHTML = html;
}
// Update the printCurrentReceipt function
function printCurrentReceipt() {
    if (!currentTransactionDetails) return;

    // Open the dedicated receipt file with the transaction ID
    window.open(
        `../../views/pos/transaction_receipt.php?id=${currentTransactionDetails.id}`,
        'ReceiptWindow',
        'width=400,height=600'
    );
}
function exportTransactions() {
    const searchTerm = encodeURIComponent(document.getElementById('searchInput').value);
    const dateFrom = encodeURIComponent(document.getElementById('dateFrom').value);
    const dateTo = encodeURIComponent(document.getElementById('dateTo').value);
    const discountFilter = encodeURIComponent(document.getElementById('discountFilter').value);

    // Generate export URL with filters
    const exportUrl = `../../views/pos/export_transactions.php?search=${searchTerm}&dateFrom=${dateFrom}&dateTo=${dateTo}&discount=${discountFilter}`;

    // Trigger download
    window.location.href = exportUrl;
}