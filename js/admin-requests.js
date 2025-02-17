// Tải danh sách yêu cầu trợ cấp
function loadRequests() {
    fetch("http://localhost/php/get-requests.php")
        .then(response => {
            if (!response.ok) {
                throw new Error('Lỗi kết nối: ' + response.statusText);
            }
            return response.text(); // Đọc dữ liệu dưới dạng text trước
        })
        .then(text => {
            try {
                const data = JSON.parse(text); // Cố gắng phân tích JSON
                if (data.success) {
                    const requestsList = document.getElementById('requests-list');
                    requestsList.innerHTML = '';
                    data.requests.forEach(request => {
                        if (request.status === 'pending') { // Chỉ hiển thị yêu cầu có trạng thái "pending"
                            const requestItem = document.createElement('div');
                            requestItem.className = 'request-item';
                            requestItem.innerHTML = `
                                <h4>Yêu cầu từ: ${request.fullname}</h4>
                                <p>CCCD/CMND: ${request.id_number}</p>
                                <p>Số thẻ BHXH: ${request.insurance_number}</p>
                                <p>Loại trợ cấp: ${getRequestType(request.request_type)}</p>
                                <p class="status-${request.status}">Trạng thái: ${getStatusText(request.status)}</p>
                                <button id='duyet' onclick="approveRequest(${request.id})">Duyệt</button>
                                <button id='tuchoi' onclick="rejectRequest(${request.id})">Từ chối</button>
                            `;
                            requestsList.appendChild(requestItem);
                        }
                    });
                } else {
                    console.error('Không thể lấy danh sách yêu cầu.');
                }
            } catch (error) {
                console.error('Lỗi phân tích JSON:', error);
                console.error('Dữ liệu trả về:', text);
            }
        })
        .catch(error => {
            console.error('Lỗi:', error);
        });
}

// Chuyển đổi loại trợ cấp từ tiếng Anh sang tiếng Việt
function getRequestType(type) {
    switch (type) {
        case 'accident':
            return 'Tai nạn lao động, bệnh nghề nghiệp';
        case 'retired':
            return 'Đã về hưu';
        case 'unemployed':
            return 'Nghỉ việc hơn 1 năm';
        case 'abroad':
            return 'Định cư nước ngoài';
        case 'illness':
            return 'Bệnh hiểm nghèo';
        default:
            return type;
    }
}

// Chuyển đổi trạng thái từ tiếng Anh sang tiếng Việt
function getStatusText(status) {
    switch (status) {
        case 'approved':
            return 'Đã xử lí';
        case 'rejected':
            return 'Đã từ chối';
        case 'pending':
            return 'Đang xử lí';
        default:
            return status;
    }
}

// Duyệt yêu cầu
function approveRequest(id) {
    if (confirm('Bạn có chắc chắn muốn duyệt yêu cầu này không?')) {
        fetch(`http://localhost/php/approve-request.php?id=${id}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Yêu cầu đã được duyệt.');
                loadRequests(); // Tải lại danh sách yêu cầu sau khi duyệt
            } else {
                alert(data.message || 'Có lỗi xảy ra khi duyệt yêu cầu.');
            }
        })
        .catch(error => {
            console.error('Lỗi:', error);
            alert('Có lỗi xảy ra khi duyệt yêu cầu.');
        });
    }
}

// Từ chối yêu cầu
function rejectRequest(id) {
    if (confirm('Bạn có chắc chắn muốn từ chối yêu cầu này không?')) {
        fetch(`http://localhost/php/reject-request.php?id=${id}`, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Yêu cầu đã bị từ chối.');
                loadRequests(); // Tải lại danh sách yêu cầu sau khi từ chối
            } else {
                alert(data.message || 'Có lỗi xảy ra khi từ chối yêu cầu.');
            }
        })
        .catch(error => {
            console.error('Lỗi:', error);
            alert('Có lỗi xảy ra khi từ chối yêu cầu.');
        });
    }
}

// Tìm kiếm yêu cầu
function searchRequests() {
    const searchValue = document.getElementById('search-input').value;
    fetch(`http://localhost/php/search-requests.php?search=${searchValue}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const requestsList = document.getElementById('requests-list');
                requestsList.innerHTML = '';
                data.requests.forEach(request => {
                    const requestItem = document.createElement('div');
                    requestItem.className = 'request-item';
                    requestItem.innerHTML = `
                        <h4>Yêu cầu từ: ${request.fullname}</h4>
                        <p>CCCD/CMND: ${request.id_number}</p>
                        <p>Số thẻ BHXH: ${request.card_number}</p>
                        <p>Loại trợ cấp: ${getRequestType(request.request_type)}</p>
                        <p class="status-${request.status}">Trạng thái: ${getStatusText(request.status)}</p>
                        ${request.status === 'pending' ? `
                            <button id='duyet' onclick="approveRequest(${request.id})">Duyệt</button>
                            <button id='tuchoi' onclick="rejectRequest(${request.id})">Từ chối</button>
                        ` : ''}
                    `;
                    requestsList.appendChild(requestItem);
                });
            } else {
                console.error('Không thể tìm kiếm yêu cầu.');
            }
        })
        .catch(error => {
            console.error('Lỗi:', error);
        });
}

// Tải danh sách yêu cầu khi trang được tải
loadRequests();