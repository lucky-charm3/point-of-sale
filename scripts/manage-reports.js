function fetchReports() {
    const reportsTableBody = document.getElementById('reportsTableBody');
    if (!reportsTableBody) return;

    fetch('../apis/reports-api.php?action=getAll')
        .then(res  => {
            if (!res.ok) {
                throw new Error(`HTTP error! status: ${res.status}`);
            }
            return res.json();
})
        .then(response => {
            console.log('API Response:', response);
            if (!response.success) {
                showToast('Error loading reports', 'error');
                return;
            }
            
            const data = response.data || [];
            reportsTableBody.innerHTML = '';
            
            data.forEach(report => {
                let actions = [];
                
                actions.push({
                    type: 'view',
                    label: 'View Details',
                    onclick: `viewReport(${report.id}); actionDropdown.closeAll();`
                });
                
                actions.push({
                    type: 'print',
                    label: 'Print Report',
                    onclick: `printReport(${report.id}); actionDropdown.closeAll();`
                });
                
                actions.push({
                    type: 'download',
                    label: 'Download CSV',
                    onclick: `downloadReport(${report.id}); actionDropdown.closeAll();`
                });

                if (window.userRole === 'admin') {
                    actions.push({
                        type: 'delete',
                        label: 'Delete Report',
                        onclick: `confirmDelete(${report.id}, 'report', () => deleteReport(${report.id})); actionDropdown.closeAll();`
                    });
                }

                const actionDropdownHTML = actionDropdown.createDropdown(actions);
                
                reportsTableBody.innerHTML += `
                    <tr>
                        <td>#${report.id}</td>
                        <td>${report.type}</td>
                        <td>${new Date(report.start_date).toLocaleDateString()} - ${new Date(report.end_date).toLocaleDateString()}</td>
                        <td>${new Date(report.created_at).toLocaleDateString()}</td>
                        <td>${report.generated_by || 'System'}</td> 
                        <td>${actionDropdownHTML}</td>
                    </tr>
                `;
            });
        })
        .catch(err => {
            console.error('Error fetching reports:', err);
            showToast('Network error loading reports', 'error');
        });
}

function generatePOSReportHTML(report) {
    const data = report.content ? JSON.parse(report.content) : [];
    
    let totalAmount = 0;
    let totalItems = data.length;
    
    if (report.type === 'sales') {
        totalAmount = data.reduce((sum, item) => sum + parseFloat(item.total_price || 0), 0);
    } else if (report.type === 'expenses') {
        totalAmount = data.reduce((sum, item) => sum + parseFloat(item.amount || 0), 0);
    }
    
    let tableRows = '';
    if (data && data.length > 0) {
        tableRows = data.map(item => {
            if (report.type === 'sales') {
                return `
    <tr>
        <td>${item.id}</td>
        <td>${(item.cashier || '').substring(0, 12)}</td>
        <td style="text-align:right">${Number(item.total_price || 0).toLocaleString()}</td>
        <td>${(item.payment_method || '').substring(0, 8)}</td>
    </tr>`;
            } else if (report.type === 'expenses') {
                return `
    <tr>
        <td>${item.id}</td>
        <td>${(item.name || '').substring(0, 15)}</td>
        <td style="text-align:right">${Number(item.amount || 0).toLocaleString()}</td>
        <td>${(item.category || '').substring(0, 10)}</td>
    </tr>`;
            } else {
                return `
    <tr>
        <td colspan="4">${JSON.stringify(item).substring(0, 30)}...</td>
    </tr>`;
            }
        }).join('');
    } else {
        tableRows = '<tr><td colspan="4" style="text-align:center">No data available</td></tr>';
    }
    
    return `
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>${report.type.toUpperCase()} REPORT</title>
    <style>
        /* POS THERMAL PRINTER STYLES */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.2;
            width: 80mm;
            margin: 0 auto;
            padding: 3mm;
            color: #000;
            background: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 5px;
            padding-bottom: 3px;
            border-bottom: 1px dashed #000;
        }
        .business-name {
            font-weight: bold;
            font-size: 14px;
            margin: 2px 0;
            text-transform: uppercase;
        }
        .report-info {
            margin: 4px 0;
            font-size: 11px;
        }
        .report-info p {
            margin: 1px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 3px 0;
            font-size: 10px;
        }
        th, td {
            padding: 2px 1px;
            text-align: left;
            border-bottom: 1px dotted #ccc;
        }
        th {
            font-weight: bold;
            border-bottom: 1px solid #000;
        }
        .summary {
            border-top: 2px solid #000;
            margin-top: 5px;
            padding-top: 3px;
            font-weight: bold;
            font-size: 11px;
        }
        .footer {
            text-align: center;
            margin-top: 8px;
            padding-top: 3px;
            border-top: 1px dashed #000;
            font-size: 9px;
        }
        .no-print {
            text-align: center;
            margin-top: 10px;
        }
        .btn {
            padding: 3px 6px;
            margin: 1px;
            border: 1px solid #000;
            background: #f0f0f0;
            cursor: pointer;
            font-size: 10px;
        }
        @media print {
            .no-print { display: none !important; }
            body { width: 80mm !important; margin: 0 !important; padding: 2mm !important; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="business-name">NEW DF HOTEL</div>
        <div style="font-size:9px;">
            Dar es Salaam, Kimara<br>
            +255 712 345 678
        </div>
    </div>

    <div class="report-info">
        <p><strong>${report.type.toUpperCase()} REPORT</strong></p>
        <p><strong>Period:</strong> ${report.start_date} to ${report.end_date}</p>
        <p><strong>Generated:</strong> ${new Date().toLocaleDateString()}</p>
    </div>

    <table>
        <thead>
            <tr>
                ${report.type === 'sales' ? 
                  '<th>ID</th><th>Cashier</th><th>Amount</th><th>Method</th>' :
                  report.type === 'expenses' ?
                  '<th>ID</th><th>Name</th><th>Amount</th><th>Category</th>' :
                  '<th>Data</th>'
                }
            </tr>
        </thead>
        <tbody>
            ${tableRows}
        </tbody>
    </table>

    <div class="summary" style="text-align:right;">
        <p>Total Records: ${totalItems}</p>
        ${totalAmount > 0 ? `<p>Total Amount: Tshs ${totalAmount.toLocaleString()}</p>` : ''}
    </div>

    <div class="footer">
        <p>** END OF REPORT **</p>
        <p>Printed: ${new Date().toLocaleString()}</p>
    </div>

    <div class="no-print">
        <button class="btn" onclick="window.print()">🖨️ Print</button>
        <button class="btn" onclick="window.close()">❌ Close</button>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 300);
        };
    </script>
</body>
</html>`;
}

function generateReport() {
    const type = document.getElementById('reportType').value;
    const dateRange = document.getElementById('dateRange').value;
    
    if (!type) {
        showToast('Please select a report type', 'error');
        return;
    }
    
    const generateBtn = document.querySelector('button[onclick="generateReport()"]');
    const originalText = generateBtn.textContent;
    generateBtn.textContent = 'Generating...';
    generateBtn.disabled = true;
    
    const dataToSend = {
        type: type,
        dateRange: dateRange
    };
    
    fetch('../apis/reports-api.php?action=generate', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(dataToSend)
    })
    .then(res => res.json())
    .then(result => {
        generateBtn.textContent = originalText;
        generateBtn.disabled = false;
        
        if (result.success) {
            showToast('Report generated successfully!', 'success');
            
            setTimeout(() => {
                if (confirm('Report generated! Would you like to print it now?')) {
                    printReport(result.report_id);
                }
            }, 1000);
            
            fetchReports();
        } else {
            showToast(result.message || 'Error generating report', 'error');
        }
    })
    .catch(err => {
        generateBtn.textContent = originalText;
        generateBtn.disabled = false;
        showToast('Network error generating report', 'error');
        console.error('Error generating report:', err);
    });
}

function viewReport(id) {
    fetch(`../apis/reports-api.php?action=getById&id=${id}`)
        .then(res => res.json())
        .then(report => {
            document.getElementById('viewModalTitle').innerText = 'Report Details';
            document.getElementById('viewModalBody').innerHTML = `
                <p><strong>ID:</strong> #${report.id}</p>
                <p><strong>Type:</strong> ${report.type}</p>
                <p><strong>Date Range:</strong> ${new Date(report.start_date).toLocaleDateString()} - ${new Date(report.end_date).toLocaleDateString()}</p>
                <p><strong>Generated On:</strong> ${new Date(report.created_at).toLocaleString()}</p>
                <p><strong>Generated By:</strong> ${report.generated_by || 'System'}</p>
            `;
            openModal('viewModal');
        });
}

function deleteReport(id) {
    fetch(`../apis/reports-api.php?action=delete&id=${id}`)
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                showToast(result.message, 'success');
                closeModal('deleteModal');
                fetchReports();
            } else {
                showToast(result.message, 'error');
                closeModal('deleteModal');
            }
        })
        .catch(err => showToast('An error occurred while deleting the report.', 'error'));
}

function downloadReport(id) {
    window.open(`../apis/reports-api.php?action=download&id=${id}`, '_blank');
}

function printReport(id) {
    showToast('Preparing report for printing...', 'info');
    
    fetch(`../apis/reports-api.php?action=getById&id=${id}`)
        .then(res => res.json())
        .then(report => {
            if (!report) {
                showToast('Report not found', 'error');
                return;
            }
            
            const printWindow = window.open('', '_blank', 'width=400,height=600');
            
            // Generate POS-compatible HTML
            const posHtml = generatePOSReportHTML(report);
            
            printWindow.document.write(posHtml);
            printWindow.document.close();
            
            // Auto-print for POS systems
            setTimeout(() => {
                printWindow.print();
                // Optional: auto-close after printing
                // setTimeout(() => printWindow.close(), 1000);
            }, 500);
            
        })
        .catch(err => {
            console.error('Error fetching report:', err);
            showToast('Error loading report for printing', 'error');
        });
}

document.addEventListener('DOMContentLoaded', () => {
    fetchReports();
});

window.fetchReports = fetchReports;
window.generateReport = generateReport;
window.viewReport = viewReport;
window.downloadReport = downloadReport;
window.printReport = printReport;