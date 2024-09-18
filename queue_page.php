<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // The processing is handled by process_queue.php via AJAX
    exit();
}

$services = $conn->query("SELECT * FROM services WHERE service_id");

// ตรวจสอบวันที่ล่าสุดที่มีการเปลี่ยนแปลง
$today_date = date('Y-m-d');
$last_checked_date = isset($_SESSION['last_checked_date']) ? $_SESSION['last_checked_date'] : '';

if ($today_date !== $last_checked_date) {
    $conn->query("UPDATE queue SET status = 'Not Coming' WHERE status = 'Waiting' OR status = 'Called'");
    $_SESSION['last_checked_date'] = $today_date;
}
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าจองคิว</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Animate.css -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        html,
        body {
            height: 100%;
        }

        body {
            background-color: #f2f2f2;
            /* สีเทาอ่อน */
            color: #333;
            font-family: 'Roboto', sans-serif;
            /* ฟอนต์หลัก */
            display: flex;
            flex-direction: column;
        }

        main {
            flex: 1;
        }

        .navbar-custom {
            background-color: #fcbf05;
            /* สีเหลืองหลัก */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            /* เพิ่มเงาให้กับ Navbar */
        }

        .navbar-custom .navbar-brand,
        .navbar-custom .nav-link {
            color: #333;
            /* สีข้อความใน Navbar */
            font-weight: 500;
        }

        .navbar-custom .nav-link:hover {
            color: #333;
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
        }

        .headex h1 {
            color: #333;
            text-align: center;
            border-radius: 10px;
            padding: 20px;
            background-color: #fcbf05;
            /* สีเหลืองหลัก */
            font-weight: 700;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            /* เพิ่มเงาให้กับหัวข้อ */
        }

        .card {
            background-color: #FFFFFF;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            /* เพิ่มเงาให้กับ Card */
        }

        .btn-custom {
            background-color: #fcbf05;
            /* สีเหลืองหลัก */
            border-color: #fcbf05;
            color: #333;
            font-weight: 500;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .btn-custom:hover {
            background-color: #e0a800;
            /* สีเหลืองเข้มเมื่อ hover */
            border-color: #e0a800;
            color: #fff;
        }

        #calledQueue {
            font-weight: bold;
            color: #333;
        }

        .list-group-item {
            background-color: #F8F9FA;
            border-color: #DDD;
            border-radius: 5px;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }

        .service-button {
            margin-bottom: 10px;
            width: 100%;
            font-weight: 500;
            padding: 10px 15px;
            font-size: 1rem;
            color: #000;
            /* ตัวหนังสือสีดำ */
            background-color: #fcbf05;
            /* พื้นหลังสีเหลือง */
            border: 1px solid #fcbf05;
            /* ขอบสีเหลือง */
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .service-button:hover {
            background-color: #e0a800;
            /* สีเหลืองเข้มเมื่อ hover */
            color: #fff;
            /* ตัวหนังสือสีขาวเมื่อ hover */
        }

        .service-button.active {
            background-color: #e0a800;
            /* สีเหลืองเข้มเมื่อถูกเลือก */
            color: #fff;
            /* ตัวหนังสือสีขาวเมื่อถูกเลือก */
            border-color: #e0a800;
        }

        /* เพิ่มการจัดการฟอนต์สำหรับหัวข้อ */
        h2 {
            font-weight: 700;
            color: #333;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .container {
                margin-top: 20px;
            }

            .headex h1 {
                font-size: 1.5rem;
                padding: 15px;
            }

            h2 {
                font-size: 1.25rem;
            }

            .service-button {
                font-size: 0.9rem;
                padding: 8px 12px;
            }
            .btn-signin {
                padding: 6px 12px;
                font-size: 0.9rem;
            }
        }

        /* Footer Styles */
        footer {
            background-color: #222;
            color: #fff;
            padding: 20px 0;
            box-shadow: 0 -4px 8px rgba(0, 0, 0, 0.3);
        }

        .footer-logo {
            width: 80px;
            /* ปรับขนาดโลโก้ */
            margin-right: 15px;
            /* เพิ่มช่องว่างระหว่างโลโก้กับข้อความ */
        }

        .footer-info p {
            margin: 0;
            line-height: 1.5;
            /* จัดระยะห่างบรรทัด */
        }

        .footer-address p {
            margin: 5px 0;
            font-size: 14px;
            color: #aaa;
        }

        /* ปรับปรุงสไตล์ปุ่ม Sign in */
        .btn-signin {
            padding: 8px 16px;
            border-radius: 5px;
            font-weight: 500;
            transition: background-color 0.3s ease, color 0.3s ease, transform 0.3s ease;
        }

        /* สไตล์เมื่อ hover */
        .btn-signin:hover {
            background-color: #e0a800;
            /* สีเหลืองเข้มเมื่อ hover */
            color: #fff;
            /* ตัวหนังสือสีขาวเมื่อ hover */
            transform: translateY(-2px);
            /* เพิ่มเอฟเฟกต์การยกขึ้นเล็กน้อย */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            /* เพิ่มเงาเมื่อ hover */
        }

        /* สไตล์เมื่อ active */
        .btn-signin:active {
            background-color: #cc9200;
            /* สีเหลืองเข้มกว่าเมื่อคลิก */
            transform: translateY(0);
            /* รีเซ็ตการยกขึ้น */
            box-shadow: none;
            /* เอาเงาออกเมื่อคลิก */
        }

    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom shadow">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">ห้องวิชาการคณะวิทยาศาสตร์</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav"
                aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon" style="color: #333;"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link btn btn-custom btn-signin" href="login.php">
                            <i class="fas fa-sign-in-alt me-2"></i> Sign in
                        </a>

                    </li>
                </ul>
            </div>
        </div>
    </nav>


    <!-- Main Content -->
    <main class="container mt-5 animate__animated animate__fadeIn">
        <div class="headex mb-4">
            <h1>จองคิวห้องวิชาการ</h1>
        </div>
        <div class="row">
            <!-- ลงทะเบียน -->
            <div class="col-lg-6 col-md-12 mb-4">
                <div class="card p-4 animate__animated animate__zoomIn shadow">
                    <h2>ลงทะเบียน</h2>
                    <form id="queue-form">
                        <div class="mb-3">
                            <label for="student_id" class="form-label">รหัสนิสิต <i class="fas fa-info-circle" data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="กรอกรหัสนิสิตของคุณ"></i></label>
                            <input type="text" id="student_id" name="student_id" class="form-control" maxlength="8" required>
                        </div>
                        <div class="mb-3">
                            <label for="full_name" class="form-label">ชื่อ-นามสกุล <i class="fas fa-info-circle" data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="กรอกชื่อและนามสกุลของคุณ"></i></label>
                            <input type="text" id="full_name" name="full_name" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">บริการที่ต้องการ <i class="fas fa-info-circle" data-bs-toggle="popover" data-bs-content="เลือกบริการที่คุณต้องการใช้งานจากรายการนี้"></i></label>
                            <div class="d-flex flex-column">
                                <?php
                                $services = $conn->query("SELECT DISTINCT s.service_id, s.service_name 
                                      FROM services s
                                      JOIN serviceemployee se ON s.service_id = se.service_id
                                      WHERE s.service_name != 'admin'");

                                while ($row = $services->fetch_assoc()) :
                                ?>
                                    <button type="button" class="service-button" data-service-id="<?php echo $row['service_id']; ?>">
                                        <?php echo htmlspecialchars($row['service_name']); ?>
                                    </button>
                                <?php
                                endwhile;
                                ?>
                            </div>
                            <input type="hidden" id="service_id" name="service_id" required>
                        </div>
                        <button type="submit" class="btn btn-custom w-100" disabled>ยืนยันการจองคิว</button>
                    </form>
                </div>
            </div>
            <!-- คิวที่ถูกเรียก -->
            <div class="col-lg-6 col-md-12 mb-4">
                <div class="card p-4 mb-3 animate__animated animate__zoomIn shadow">
                    <h2>คิวที่ถูกเรียก </h2>
                    <div id="calledQueue" class="fs-4">ไม่มีคิวที่ถูกเรียก</div>
                </div>
                <!-- รายการคิวทั้งหมด -->
                <div class="card p-4 animate__animated animate__zoomIn shadow">
                    <h2>รายการคิวทั้งหมด </h2>
                    <ul class="list-group" id="queueList">
                        <!-- Queue data will be inserted here by JavaScript -->
                    </ul>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 d-flex align-items-center mb-3 mb-md-0">
                    <img src="nu.png" alt="NU Logo" class="footer-logo" />
                    <div class="footer-info">
                        <p>มหาวิทยาลัยนเรศวร</p>
                        <p>Naresuan University</p>
                    </div>
                </div>
                <div class="col-md-6 footer-address text-md-end">
                    <p>ที่อยู่: 99 หมู่ 9 ตำบล ท่าโพธิ์ อำเภอเมือง จังหวัด พิษณุโลก 65000</p>
                    <p>โทรศัพท์: 055-963112 | โทรสาร: 055-963113</p>
                    <p>Email: saraban_sci@nu.ac.th</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center">
                <div class="modal-body">
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                    <h5 class="mb-2" id="successModalMessage">คุณได้คิวที่ <span id="queueNumber"></span></h5>
                    <button type="button" class="btn btn-custom" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center">
                <div class="modal-body">
                    <i class="fas fa-times-circle fa-4x text-danger mb-3"></i>
                    <h5 class="mb-2">ดูเหมือนบางอย่างผิดพลาด</h5>
                    <button type="button" class="btn btn-custom" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const studentIdField = document.getElementById('student_id');
            const fullNameField = document.getElementById('full_name');
            const submitButton = document.querySelector('button[type="submit"]');
            const queueList = document.getElementById('queueList');
            const calledQueueBox = document.getElementById('calledQueue');
            const serviceButtons = document.querySelectorAll('.service-button');
            const serviceIdInput = document.getElementById('service_id');
            const successModalMessage = document.getElementById('successModalMessage');
            const queueNumberSpan = document.getElementById('queueNumber');

            let selectedServiceId = null;
            let queueReadCounts = {}; // To track the number of times each queue is read
            const maxReads = 2; // Maximum number of reads per queue

            // Handle service button selection
            serviceButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    serviceButtons.forEach(btn => btn.classList.remove('active'));

                    // Add active class to the clicked button
                    this.classList.add('active');

                    // Set the selected service ID
                    selectedServiceId = this.getAttribute('data-service-id');
                    serviceIdInput.value = selectedServiceId;

                    // Enable the submit button if student ID is valid
                    if (studentIdField.value.length === 8) {
                        submitButton.disabled = false;
                    }
                });
            });

            studentIdField.addEventListener('input', function() {
                fetchFullName();
                if (studentIdField.value.length === 8 && selectedServiceId) {
                    studentIdField.classList.add('border-success');
                    submitButton.disabled = false;
                } else {
                    studentIdField.classList.remove('border-success');
                    submitButton.disabled = true;
                }
            });

            function fetchFullName() {
                const studentId = studentIdField.value;
                if (studentId.length === 8) {
                    fetch('fetch_name.php?student_id=' + studentId)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                fullNameField.value = data.full_name;
                                if (selectedServiceId) {
                                    submitButton.disabled = false;
                                }
                            } else {
                                fullNameField.value = "ไม่พบข้อมูล";
                                submitButton.disabled = true;
                                showErrorModal(data.message);
                            }
                        });
                } else {
                    fullNameField.value = "";
                    submitButton.disabled = true;
                }
            }

            document.getElementById('queue-form').addEventListener('submit', function(event) {
                event.preventDefault();
                const formData = new FormData(this);

                fetch('process_queue.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.type === 'success') {
                            // Set the queue number in the modal
                            queueNumberSpan.textContent = data.queue_number;
                            showSuccessModal();
                            this.reset();
                            submitButton.disabled = true;
                            fullNameField.value = "";
                            // Reset service buttons
                            serviceButtons.forEach(btn => btn.classList.remove('active'));
                            serviceIdInput.value = "";
                            selectedServiceId = null;
                            fetchQueueData();
                        } else {
                            showErrorModal(data.message);
                        }
                    });
            });

            function fetchQueueData() {
                fetch('fetch_queue.php')
                    .then(response => response.json())
                    .then(data => {
                        queueList.innerHTML = '';
                        let calledQueues = [];
                        data.forEach(queue => {
                            const listItem = document.createElement('li');
                            listItem.classList.add('list-group-item');
                            listItem.textContent = `คิวที่ ${queue.daily_queue_number}: ${queue.full_name} - ${queue.service_name}`;
                            queueList.appendChild(listItem);

                            if (queue.status === "Called") {
                                calledQueues.push(queue);
                            }
                        });

                        if (calledQueues.length > 0) {
                            calledQueueBox.innerHTML = ''; // Clear previous content
                            calledQueues.forEach(queue => {
                                const text = `ขอเชิญคิวที่ : ${queue.daily_queue_number} ${queue.full_name} ไป ${queue.service_name}`;
                                const p = document.createElement('p');
                                p.textContent = text;
                                calledQueueBox.appendChild(p);

                                // Initialize or update read count for this queue ID
                                if (!queueReadCounts[queue.queue_id]) {
                                    queueReadCounts[queue.queue_id] = 0;
                                }

                                if (queueReadCounts[queue.queue_id] < maxReads) {
                                    const utterance = new SpeechSynthesisUtterance();
                                    utterance.lang = 'th-TH'; // Thai language
                                    utterance.text = text;
                                    window.speechSynthesis.speak(utterance);

                                    queueReadCounts[queue.queue_id]++;
                                }
                            });
                        } else {
                            calledQueueBox.textContent = "ไม่มีคิวที่ถูกเรียก";
                        }
                    });
            }

            fetchQueueData();
            setInterval(fetchQueueData, 2000);

            // Initialize tooltips and popovers
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        });

        function showSuccessModal() {
            const successModalElement = document.getElementById('successModal');
            if (successModalElement) { // ตรวจสอบว่า element มีอยู่
                const successModal = new bootstrap.Modal(successModalElement);
                successModal.show();
            } else {
                console.error('Success modal element not found!');
            }
        }

        function showErrorModal(message = 'เกิดข้อผิดพลาดในการจองคิว') {
            const errorModalElement = document.getElementById('errorModal');
            if (errorModalElement) { // ตรวจสอบว่า element มีอยู่
                const errorModal = new bootstrap.Modal(errorModalElement);
                // Optionally, update the message dynamically
                const errorModalMessage = errorModalElement.querySelector('h5');
                if (errorModalMessage) {
                    errorModalMessage.textContent = message;
                }
                errorModal.show();
            } else {
                console.error('Error modal element not found!');
            }
        }
    </script>
</body>

</html>