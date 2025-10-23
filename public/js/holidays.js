document.addEventListener('DOMContentLoaded', function () {
    // Validate day selection based on month
    const monthSelect = document.querySelector('select[name="month"]');
    const daySelect = document.querySelector('select[name="day"]');

    if (monthSelect && daySelect) {
        monthSelect.addEventListener('change', function () {
            updateDayOptions(this.value);
        });

        // Initialize with current month
        updateDayOptions(monthSelect.value);
    }

    function updateDayOptions(month) {
        const daysInMonth = new Date(new Date().getFullYear(), month, 0).getDate();
        const currentDay = daySelect.value;

        // Save current selection if it's still valid
        let newSelectedDay = currentDay <= daysInMonth ? currentDay : daysInMonth;

        // Regenerate day options
        daySelect.innerHTML = '';
        for (let day = 1; day <= daysInMonth; day++) {
            const option = document.createElement('option');
            option.value = day;
            option.textContent = day;
            if (day == newSelectedDay) {
                option.selected = true;
            }
            daySelect.appendChild(option);
        }
    }

    // Show success message if present
    if (document.querySelector('.alert-success')) {
        setTimeout(() => {
            document.querySelector('.alert-success').style.display = 'none';
        }, 5000);
    }
});