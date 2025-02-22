document.addEventListener('DOMContentLoaded', function () {
    const sendOtpButton = document.getElementById('send-otp');
    const otpInput = document.getElementById('otp');
    const checkDebtButton = document.getElementById('check-debt');
    const paymentOptions = document.getElementById('payment-options');
    const debtInfo = document.getElementById('debt-info');
    const payButton = document.getElementById('pay-btn');
    const paymentForm = document.getElementById('paymentForm');
    let cardNumber = null;
    let generatedOtp = null;
    let countdownInterval = null; // Biến để lưu interval đếm ngược

    // Ẩn phần chọn ngày thanh toán và nút thanh toán ban đầu
    paymentOptions.style.display = "none";
    payButton.style.display = "none";
    debtInfo.style.display = "none";

    // Hàm đếm ngược thời gian
    function startCountdown(seconds) {
        let remainingTime = seconds;
        sendOtpButton.disabled = true; // Vô hiệu hóa nút gửi mã

        // Cập nhật nội dung nút với thời gian đếm ngược
        sendOtpButton.textContent = `Gửi lại sau ${remainingTime}s`;

        countdownInterval = setInterval(() => {
            remainingTime--;
            sendOtpButton.textContent = `Gửi lại sau ${remainingTime}s`;

            // Khi thời gian đếm ngược kết thúc
            if (remainingTime <= 0) {
                clearInterval(countdownInterval); // Dừng đếm ngược
                sendOtpButton.disabled = false; // Kích hoạt lại nút
                sendOtpButton.textContent = "Gửi mã"; // Đặt lại nội dung nút
            }
        }, 1000); // Cập nhật mỗi giây
    }

    // Gửi OTP
    sendOtpButton.addEventListener('click', function () {
        const idNumber = document.getElementById('id-number').value.trim();
        cardNumber = document.getElementById('card-number').value.trim();

        if (!idNumber || !cardNumber) {
            alert('Vui lòng nhập số CMND/CCCD và số thẻ BHXH.');
            return;
        }

        // Gửi yêu cầu OTP
        fetch("http://localhost/php/get-email.php", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ idNumber, cardNumber })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                generatedOtp = data.otp; // Lưu mã OTP từ server
                alert('Mã OTP đã được gửi đến email của bạn.');

                // Bắt đầu đếm ngược 60 giây
                startCountdown(60);
            } else {
                alert(data.message || 'Không tìm thấy thông tin người dùng.');
            }
        })
        .catch(error => {
            console.error('Lỗi:', error);
            alert('Có lỗi xảy ra khi gửi OTP. Vui lòng thử lại.');
        });
    });

    // Kiểm tra nợ sau khi nhập OTP
    checkDebtButton.addEventListener('click', function () {
        const otp = otpInput.value.trim();

        if (!generatedOtp) {
            alert('Vui lòng gửi mã OTP trước.');
            return;
        }

        if (String(otp).trim() !== String(generatedOtp).trim()) {
            alert('Mã OTP không hợp lệ.');
            return;
        }

        alert("Xác thực OTP thành công.");

        // Kiểm tra nợ phí
        fetch("http://localhost/php/check-debt.php", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ cardNumber })
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

                // Hiển thị phần thông tin người dùng
                document.getElementById('user-info').style.display = "block";

                // Hiển thị thông tin nợ
                if (data.debt.amount > 0) {
                    debtInfo.innerHTML = `
                        <h3>Thông báo</h3>
                        <p>Bạn còn nợ ${new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(data.debt.amount)} cho ${data.debt.daysUnpaid} ngày chưa thanh toán.</p>
                        <p>Ngày thanh toán cuối cùng: ${data.lastPaymentDate}</p>
                    `;
                    debtInfo.style.display = "block"; // Hiện thông báo nợ
                    paymentOptions.style.display = "none"; // Ẩn chọn số ngày
                } else {
                    debtInfo.innerHTML = `
                        <h3>Thông báo</h3>
                        <p>Bạn không có nợ phí.</p>
                        <p>Ngày hết hạn bảo hiểm xã hội: ${data.expirationDate}</p>
                    `;
                    debtInfo.style.display = "block"; // Hiện thông báo không có nợ
                    paymentOptions.style.display = "block"; // Hiện chọn số ngày thanh toán
                }
                payButton.style.display = "block"; // Luôn hiện nút thanh toán sau khi kiểm tra nợ
            } else {
                alert(data.message || "Có lỗi xảy ra khi kiểm tra nợ.");
            }
        })
        .catch(error => {
            console.error("Lỗi:", error);
            alert("Có lỗi xảy ra khi kiểm tra nợ.");
        });
    });

    /// Xử lý thanh toán
    paymentForm.addEventListener('submit', function (event) {
        event.preventDefault();

        const daysToPay = document.getElementById('days-to-pay').value;

        fetch("http://localhost/php/thanh-toan.php", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ cardNumber, daysToPay })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {

                // Hiển thị mã QR từ file /images/qr.jpg
                const qrCodeH3  = document.getElementById('qrcodeH3');
                qrCodeH3.textContent = "Mã QR thanh toán";
                const qrCodeDiv = document.getElementById('qrcode');
                qrCodeDiv.innerHTML = `<img src="/images/qr.jpg" alt="Mã QR thanh toán" style="width: 200px; height: 200px;">`;

                // Hiển thị thông tin thanh toán
                const paymentInfo = document.createElement('p');
                paymentInfo.textContent = `Bạn đã thanh toán thành công số tiền ${daysToPay * 10000} VND.`;
                qrCodeDiv.appendChild(paymentInfo);

                // Cập nhật giao diện người dùng
                document.getElementById('payment-options').style.display = 'none';
                document.getElementById('pay-btn').style.display = 'none';
                // Hiển thị thông báo thành công
                alert(data.message);
            } else {
                alert(data.message || 'Có lỗi xảy ra khi thanh toán.');
            }
        })
        .catch(error => {
            console.error("Lỗi:", error);
            alert("Có lỗi xảy ra khi thanh toán.");
        });
    });
});