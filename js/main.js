
const currentDateElement = document.getElementById('current-date');

// Hàm để lấy thứ, ngày, tháng, năm hiện tại
function getCurrentDate() {
        const days = ["Chủ Nhật", "Thứ Hai", "Thứ Ba", "Thứ Tư", "Thứ Năm", "Thứ Sáu", "Thứ Bảy"];
        const months = [
                "01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"
        ];

        const now = new Date();
        const dayOfWeek = days[now.getDay()]; // Lấy thứ
        const day = String(now.getDate()).padStart(2, '0'); // Lấy ngày
        const month = months[now.getMonth()]; // Lấy tháng
        const year = now.getFullYear(); // Lấy năm

        // Định dạng ngày tháng: "Thứ X, ngày DD/MM/YYYY"
        return `${dayOfWeek}, ngày ${day}/${month}/${year}`;
    }

    // Cập nhật ngày tháng vào phần tử
    currentDateElement.textContent = getCurrentDate();
