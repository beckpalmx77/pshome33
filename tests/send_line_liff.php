<script src="https://static.line-scdn.net/liff/edge/2/sdk.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    const LIFF_ID = "YOUR_LIFF_ID"; // 👉 เปลี่ยนตรงนี้เป็น LIFF ID ของคุณ

    $(document).ready(function () {
        liff.init({ liffId: LIFF_ID }).then(() => {
            if (!liff.isLoggedIn()) {
                liff.login();
            }
        }).catch(err => {
            console.error("LIFF Init Error:", err);
        });

        // ✅ ฟอร์มโอนเงิน
        $("#transfer_form").on("submit", function (event) {
            event.preventDefault();

            let period_month_start = parseInt($("#period_month_start").val());
            let period_month_to = parseInt($("#period_month_to").val());

            if (period_month_start > period_month_to) {
                alertify.error("กรุณาตรวจสอบเดือนเริ่มต้นและเดือนสิ้นสุดให้ถูกต้อง");
                return;
            }

            let formData = new FormData(this);
            formData.append('period_month_start', document.getElementById('period_month_start').value);
            formData.append('period_month_to', document.getElementById('period_month_to').value);
            formData.append('payment_type', document.getElementById('payment_type').value);

            $.ajax({
                url: "model/manage_payment_transfer.php",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                success: function (response) {
                    if (response == 1) {
                        alertify.success("โอนเงินและส่ง Slip สำเร็จ");
                        $("#transfer_form")[0].reset();
                        $("#preview_image").hide().attr("src", "");

                        // ✅ ส่งข้อความกลับ LINE OA
                        if (liff.isInClient()) {
                            liff.getProfile().then(profile => {
                                const message = `📤 แจ้งการโอนเงินเรียบร้อยแล้ว!\n👤 ผู้ใช้: ${profile.displayName}\n📅 เดือน: ${period_month_start} - ${period_month_to}`;
                                liff.sendMessages([{ type: "text", text: message }])
                                    .then(() => {
                                        liff.closeWindow();
                                    })
                                    .catch(err => {
                                        console.error("ส่งข้อความล้มเหลว:", err);
                                        alertify.error("ส่งข้อความกลับ LINE ไม่สำเร็จ");
                                        liff.closeWindow();
                                    });
                            });
                        } else {
                            alertify.success("ไม่ได้เปิดใน LINE App (ข้อความจะไม่ถูกส่ง)");
                        }

                    } else {
                        alertify.error("ไม่สามารถบันทึกข้อมูลได้");
                    }
                },
                error: function () {
                    alertify.error("เกิดข้อผิดพลาดในการส่งข้อมูล");
                }
            });
        });
    });
</script>

