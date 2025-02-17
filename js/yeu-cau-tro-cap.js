document.addEventListener('DOMContentLoaded', function () {
    const sendOtpButton = document.getElementById('send-otp');
    const otpInput = document.getElementById('otp');
    const checkDebtButton = document.getElementById('check-debt');
    const requestOptions = document.getElementById('request-options');
    const requestDetails = document.getElementById('request-details');
    const submitButton = document.getElementById('submit-btn');
    const userInfo = document.getElementById('user-info');
    let generatedOtp = null;
    let cardNumber = null;

    // Ẩn các phần form ban đầu
    requestOptions.style.display = "none";
    requestDetails.style.display = "none";
    submitButton.style.display = "none";
    userInfo.style.display = "none";

    // Gửi OTP
    sendOtpButton.addEventListener('click', function () {
        const idNumber = document.getElementById('id-number').value.trim();
        cardNumber = document.getElementById('card-number').value.trim();
    
        if (!idNumber || !cardNumber) {
            alert('Vui lòng nhập số CMND/CCCD và số thẻ BHXH.');
            return;
        }
    
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
            } else {
                alert(data.message || 'Không tìm thấy thông tin người dùng.');
            }
        })
        .catch(error => {
            console.error('Lỗi:', error);
            alert('Có lỗi xảy ra khi gửi OTP. Vui lòng thử lại.');
        });
    });

    // Kiểm tra OTP và hiển thị form yêu cầu trợ cấp
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

        // Kiểm tra thông tin người dùng
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
                const resultName = document.getElementById('result-name');
                const resultCardNumber = document.getElementById('result-card-number');
                const resultIdNumber = document.getElementById('result-id-number');
                const resultAddress = document.getElementById('result-address');
                const resultPhone = document.getElementById('result-phone');
                const resultDob = document.getElementById('result-dob');
                const resultRegistrationDate = document.getElementById('result-registration-date');

                if (resultName && resultCardNumber && resultIdNumber && resultAddress && resultPhone && resultDob && resultRegistrationDate) {
                    resultName.textContent = data.user.fullname;
                    resultCardNumber.textContent = data.user.card_number;
                    resultIdNumber.textContent = data.user.id_number;
                    resultAddress.textContent = data.user.address;
                    resultPhone.textContent = data.user.phone;
                    resultDob.textContent = data.user.dob;
                    resultRegistrationDate.textContent = new Date(data.user.registration_date).toLocaleDateString();
                } else {
                    console.error('Không tìm thấy một hoặc nhiều phần tử DOM.');
                }

                // Hiển thị phần thông tin người dùng
                userInfo.style.display = "block";

                // Hiển thị form yêu cầu trợ cấp
                requestOptions.style.display = "block";
                submitButton.style.display = "block";
                requestDetails.style.display = "block";
            } else {
                alert(data.message || "Có lỗi xảy ra khi kiểm tra thông tin.");
            }
        })
        .catch(error => {
            console.error("Lỗi:", error);
            alert("Có lỗi xảy ra khi kiểm tra thông tin.");
        });
    });

    // Xử lý khi chọn loại trợ cấp
    document.getElementById('request-type').addEventListener('change', function () {
        const requestType = this.value;
        const requestDetails = document.getElementById('request-details');

        // Hiển thị các trường nhập liệu tương ứng
        switch (requestType) {
            case 'accident':
                requestDetails.innerHTML = `
                    <div class="form-group">
                        <label for="company-name">Tên cơ quan/ công ty:</label>
                        <input type="text" id="company-name" name="company-name" required>
                    </div>
                    <div class="form-group">
                        <label for="company-address">Địa chỉ:</label>
                        <input type="text" id="company-address" name="company-address" required>
                    </div>
                    <div class="form-group">
                        <label for="incident-date">Thời gian mất khả năng làm việc:</label>
                        <input type="date" id="incident-date" name="incident-date" required>
                    </div>
                    <div class="form-group">
                        <label for="reason">Lý do cụ thể:</label>
                        <textarea id="reason" name="reason" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="proof">Giấy xác nhận mất khả năng làm việc:</label>
                        <input type="file" id="proof" name="proof" accept="image/*, application/pdf" required>
                    </div>
                `;
                break;
            case 'retired':
                requestDetails.innerHTML = `
                    <div class="form-group">
                        <label for="company-name">Tên cơ quan/ công ty:</label>
                        <input type="text" id="company-name" name="company-name" required>
                    </div>
                    <div class="form-group">
                        <label for="company-address">Địa chỉ:</label>
                        <input type="text" id="company-address" name="company-address" required>
                    </div>
                    <div class="form-group">
                        <label for="incident-date">Thời gian về hưu:</label>
                        <input type="date" id="incident-date" name="incident-date" required>
                    </div>
                    <div class="form-group">
                        <label for="proof">Giấy xác nhận quá tuổi làm việc:</label>
                        <input type="file" id="proof" name="proof" accept="image/*, application/pdf" required>
                    </div>
                `;
                break;
            case 'unemployed':
                requestDetails.innerHTML = `
                    <div class="form-group">
                            <label for="company-name">Tên cơ quan/ công ty:</label>
                            <input type="text" id="company-name" name="company-name" required>
                        </div>
                        <div class="form-group">
                            <label for="company-address">Địa chỉ:</label>
                            <input type="text" id="company-address" name="company-address" required>
                        </div>
                        <div class="form-group">
                            <label for="incident-date">Thời gian nghĩ việc:</label>
                            <input type="date" id="incident-date" name="incident-date" required>
                        </div>
                        <div class="form-group">
                            <label for="reason">Lý do cụ thể:</label>
                            <textarea id="reason" name="reason" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="proof">Giấy xác nhận nghĩ việc từ công ty:</label>
                            <input type="file" id="proof" name="proof" accept="image/*, application/pdf" required>
                        </div>
                    `;
                break;
            case 'abroad':
                requestDetails.innerHTML = `
                    <div class="form-group">
                        <label for="company-address">Nơi di cư:</label>
                        <input type="text" id="company-address" name="company-address" required>
                    </div>
                    <div class="form-group">
                        <label for="incident-date">Thời gian di cư:</label>
                        <input type="date" id="incident-date" name="incident-date" required>
                    </div>
                    <div class="form-group">
                        <label for="reason">Lý do cụ thể:</label>
                        <textarea id="reason" name="reason" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="proof">Giấy xác nhận định cư nước ngoài:</label>
                        <input type="file" id="proof" name="proof" accept="image/*, application/pdf" required>
                    </div>
                `;
                break;
            case 'illness':
                requestDetails.innerHTML = `
                    <div class="form-group">
                        <label for="company-name">Tên cơ quan/ công ty:</label>
                        <input type="text" id="company-name" name="company-name" required>
                    </div>
                    <div class="form-group">
                        <label for="company-address">Địa chỉ:</label>
                        <input type="text" id="company-address" name="company-address" required>
                    </div>
                    <div class="form-group">
                        <label for="incident-date">Thời gian mất khả năng làm việc:</label>
                        <input type="date" id="incident-date" name="incident-date" required>
                    </div>
                    <div class="form-group">
                        <label for="reason">Lý do cụ thể:</label>
                        <textarea id="reason" name="reason" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="proof">Giấy xác nhận bệnh lí:</label>
                        <input type="file" id="proof" name="proof" accept="image/*, application/pdf" required>
                    </div>
                `;
                break;
        }

    });

    // Xử lý gửi yêu cầu trợ cấp
    document.getElementById('requestForm').addEventListener('submit', function (event) {
        event.preventDefault();

        const requestType = document.getElementById('request-type').value;
        const formData = new FormData(this);

        fetch("http://localhost/php/submit-request.php", {
            method: 'POST',
            body: formData
        })
        .then(response => response.json()) // Đảm bảo server trả về JSON
        .then(data => {
            if (data.success) {
                alert(data.message); // Hiển thị thông báo thành công
            } else {
                alert(data.message); // Hiển thị thông báo lỗi từ server
            }
        })
        .catch(error => {
            alert('Yêu cầu của bạn đã được gửi, hãy kiểm tra email của bạn!');
        });
    });
});