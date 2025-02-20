document.addEventListener('DOMContentLoaded', function () {
    const adminLookupForm = document.getElementById('adminLookupForm');
    const adminSearchInput = document.getElementById('admin-search-input');
    const adminLookupResult = document.getElementById('adminLookupResult');
    const adminSearchMessage = document.getElementById('admin-search-message');
    const adminMedicalRecordsList = document.getElementById('adminMedicalRecordsList');

    adminLookupForm.addEventListener('submit', function (event) {
        event.preventDefault();

        const searchValue = adminSearchInput.value.trim();

        if (!searchValue) {
            alert('Vui lòng nhập số thẻ BHXH hoặc số CMND/CCCD.');
            return;
        }

        // Gửi yêu cầu tra cứu đến server
        fetch("http://localhost/php/admin-lookup.php", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                'search-input': searchValue
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Ẩn thông báo không tìm thấy
                adminSearchMessage.style.display = 'none';

                // Hiển thị thông tin người dùng
                document.getElementById('admin-result-name').textContent = data.user.fullname;
                document.getElementById('admin-result-card-number').textContent = data.user.card_number;
                document.getElementById('admin-result-id-number').textContent = data.user.id_number;
                document.getElementById('admin-result-address').textContent = data.user.address;
                document.getElementById('admin-result-phone').textContent = data.user.phone;
                document.getElementById('admin-result-dob').textContent = data.user.dob;
                document.getElementById('admin-result-registration-date').textContent = new Date(data.user.registration_date).toLocaleDateString();

                // Xử lý trạng thái tài khoản
                const statusElement = document.getElementById('admin-result-status');
                if (data.user.status == 'inactive') {
                    statusElement.textContent = 'Cần thanh toán phí dịch vụ';
                } else {
                    statusElement.textContent = 'Đang hoạt động';
                }

                // Hiển thị hồ sơ khám bệnh trong bảng
                adminMedicalRecordsList.innerHTML = '';
                if (data.medicalRecords.length > 0) {
                    data.medicalRecords.forEach(record => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${record.hospital_name}</td>
                            <td>${record.disease}</td>
                            <td>${new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(record.hospital_fee)}</td>
                            <td>${record.is_paid ? 'Có' : 'Không'}</td>
                            <td>${record.is_reimbursed ? 'Có' : 'Không'}</td>
                            <td>${new Date(record.admission_date).toLocaleDateString()}</td>
                            <td>${new Date(record.discharge_date).toLocaleDateString()}</td>
                        `;
                        adminMedicalRecordsList.appendChild(row);
                    });
                } else {
                    adminMedicalRecordsList.innerHTML = '<tr><td colspan="7">Không có hồ sơ khám bệnh nào.</td></tr>';
                }

                // Hiển thị kết quả tra cứu
                adminLookupResult.style.display = 'block';
            } else {
                // Ẩn kết quả tra cứu và hiển thị thông báo không tìm thấy
                adminLookupResult.style.display = 'none';
                adminSearchMessage.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Lỗi:', error);
            alert('Có lỗi xảy ra khi tra cứu. Vui lòng thử lại.');
        });
    });
});