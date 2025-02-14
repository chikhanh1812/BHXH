document.addEventListener('DOMContentLoaded', function () {
    const lookupForm = document.getElementById('lookupForm');
    const lookupResult = document.getElementById('lookupResult');
    const medicalRecordsList = document.getElementById('medicalRecordsList');

    lookupForm.addEventListener('submit', function (event) {
        event.preventDefault();

        const cardNumber = document.getElementById('card-number').value.trim();
        const idNumber = document.getElementById('id-number').value.trim();

        // Gửi yêu cầu tra cứu đến server
        fetch("http://localhost/php/tra-cuu.php", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                'card-number': cardNumber,
                'id-number': idNumber
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Hiển thị thông tin người dùng
                document.getElementById('result-name').textContent = data.user.fullname;
                document.getElementById('result-card-number').textContent = data.user.card_number;
                document.getElementById('result-id-number').textContent = data.user.id_number;
                document.getElementById('result-address').textContent = data.user.address;
                document.getElementById('result-phone').textContent = data.user.phone;
                document.getElementById('result-dob').textContent = data.user.dob;
                document.getElementById('result-registration-date').textContent = new Date(data.user.registration_date).toLocaleDateString();
            
                // Xử lý trạng thái tài khoản
                const statusElement = document.getElementById('result-status');
                if (data.user.status == 'inactive') {
                    // Tạo một nút có thể click được
                    const paymentButton = document.createElement('a');
                    paymentButton.textContent = 'Cần thanh toán phí dịch vụ';
                    paymentButton.href = `thanh-toan.html?card_number=${data.user.card_number}&id_number=${data.user.id_number}`; // Chuyển hướng đến trang thanh toán với thông tin đã điền sẵn
                    paymentButton.className = 'payment-button'; // Thêm class để tạo kiểu CSS
                    statusElement.innerHTML = ''; // Xóa nội dung cũ
                    statusElement.appendChild(paymentButton); // Thêm nút vào phần tử
                } else {
                    statusElement.textContent = 'Đang hoạt động';
                }

                // Hiển thị hồ sơ khám bệnh trong bảng
                medicalRecordsList.innerHTML = '';
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
                        medicalRecordsList.appendChild(row);
                    });
                } else {
                    medicalRecordsList.innerHTML = '<tr><td colspan="7">Không có hồ sơ khám bệnh nào.</td></tr>';
                }

                // Hiển thị kết quả tra cứu
                lookupResult.style.display = 'block';
            } else {
                alert(data.message || 'Không tìm thấy thông tin người dùng.');
            }
        })
        .catch(error => {
            console.error('Lỗi:', error);
            alert('Có lỗi xảy ra khi tra cứu. Vui lòng thử lại.');
        });
    });
});