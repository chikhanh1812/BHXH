document.addEventListener('DOMContentLoaded', function () {
    const personalInfoForm = document.getElementById('personal-info-form');
    const verificationForm = document.getElementById('verification-form');
    const completionForm = document.getElementById('completion-form');
    const nextButton = document.getElementById('next-button');
    const backButton = document.getElementById('back-button');
    const verifyButton = document.getElementById('verify-button');
    const step1 = document.getElementById('step1');
    const step2 = document.getElementById('step2');
    const step3 = document.getElementById('step3');

    // Bước 1: Kiểm tra thông tin cá nhân và chuyển sang bước 2
    nextButton.addEventListener('click', function () {
        // Lấy giá trị từ các trường nhập liệu
        const fullname = document.getElementById('fullname').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const email = document.getElementById('email').value.trim();
        const address = document.getElementById('address').value.trim();
        const dob = document.getElementById('dob').value;
        const gender = document.getElementById('gender').value;
        const idNumber = document.getElementById('id-number').value.trim();

        // Kiểm tra thông tin cá nhân
        if (isValidFullname(fullname) &&
            isValidPhone(phone) &&
            isValidEmail(email) &&
            isValidIdNumber(idNumber) &&
            isValidDob(dob)) {

            // Hiển thị thông tin đã nhập trong bước xác thực
            document.getElementById('display-fullname').textContent = fullname;
            document.getElementById('display-phone').textContent = phone;
            document.getElementById('display-email').textContent = email;
            document.getElementById('display-address').textContent = address;
            document.getElementById('display-dob').textContent = dob;
            document.getElementById('display-gender').textContent = gender === 'male' ? 'Nam' : gender === 'female' ? 'Nữ' : 'Khác';
            document.getElementById('display-id-number').textContent = idNumber;

            // Ẩn form thông tin cá nhân và hiển thị form xác thực
            personalInfoForm.style.display = 'none';
            verificationForm.style.display = 'block';

            // Cập nhật trạng thái bước
            step1.classList.remove('active');
            step2.classList.add('active');
        } else {
            alert('Vui lòng điền đầy đủ và chính xác thông tin cá nhân.');
        }
    });

    // Bước 2: Quay lại bước 1
    backButton.addEventListener('click', function () {
        // Ẩn form xác thực và hiển thị form thông tin cá nhân
        verificationForm.style.display = 'none';
        personalInfoForm.style.display = 'block';

        // Cập nhật trạng thái bước
        step2.classList.remove('active');
        step1.classList.add('active');
    });

    // Bước 2: Xác thực thông tin và gửi dữ liệu đến server
    verifyButton.addEventListener('click', function () {
        // Lấy giá trị từ các trường nhập liệu
        const fullname = document.getElementById('fullname').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const email = document.getElementById('email').value.trim();
        const address = document.getElementById('address').value.trim();
        const dob = document.getElementById('dob').value;
        const gender = document.getElementById('gender').value;
        const idNumber = document.getElementById('id-number').value.trim();

        // Tạo đối tượng dữ liệu để gửi đến server
        const data = {
            fullname: fullname,
            phone: phone,
            email: email,
            address: address,
            dob: dob,
            gender: gender,
            idNumber: idNumber
        };

        // Gửi dữ liệu đến server bằng AJAX
        fetch("http://localhost/php/dang-ky.php", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                // Hiển thị thông báo thành công và Số Thẻ BHXH
                document.getElementById('success-message').textContent = 'Số Thẻ BHXH của bạn là: ' + result.cardNumber;
                verificationForm.style.display = 'none';
                completionForm.style.display = 'block';

                // Cập nhật trạng thái bước
                step2.classList.remove('active');
                step3.classList.add('active');

                // Thêm sự kiện cho nút "Thanh toán ngay"
                document.querySelector('.btn-next').addEventListener('click', function () {
                    const cardNumber = result.cardNumber; // Số thẻ BHXH từ server
                    const idNumber = data.idNumber;       // Số CMND/CCCD từ form
                    window.location.href = `thanh-toan.html?card_number=${result.cardNumber}&id_number=${result.idNumber}}`;
                });
            } else {
                alert(result.message || 'Có lỗi xảy ra khi đăng ký. Vui lòng thử lại.');
            }
        })
        .catch(error => {
            console.error('Lỗi:', error);
            alert('Có lỗi xảy ra khi đăng ký. Vui lòng thử lại.');
        });
    });

    // Các hàm kiểm tra thông tin (giữ nguyên)
    function isValidFullname(fullname) {
        const words = fullname.split(' ');
        return words.length >= 2 && words.every(word => word.length > 0);
    }

    function isValidPhone(phone) {
        const phonePattern = /^\d{10,}$/;
        return phonePattern.test(phone);
    }

    function isValidEmail(email) {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailPattern.test(email);
    }

    function isValidIdNumber(idNumber) {
        const idPattern = /^\d{9}$|^\d{12}$/;
        return idPattern.test(idNumber);
    }

    function isValidDob(dob) {
        const dobDate = new Date(dob);
        const today = new Date();
        const age = today.getFullYear() - dobDate.getFullYear();

        if (today.getMonth() < dobDate.getMonth() || 
            (today.getMonth() === dobDate.getMonth() && today.getDate() < dobDate.getDate())) {
            return age - 1 >= 15;
        }
        return age >= 15;
    }
});