function generatePayslip(qr_employee_id) {
    const payPeriodRange = document.querySelector('[name="pay_period_range"]').value;
    const payPeriodMonth = document.querySelector('[name="pay_period_month"]').value;



    window.open(`../../views/employees/generate_payslip.php?employee_id=${qr_employee_id}&pay_period_range=${encodeURIComponent(payPeriodRange)}&pay_period_month=${encodeURIComponent(payPeriodMonth)}`, '_blank', 'width=800,height=600');
}
