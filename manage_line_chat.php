<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['department_id']) == "") {
    header("Location: index.php");
    exit();
} else {
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <style>
        .chat-container {
            display: flex;
            height: calc(100vh - 200px);
            min-height: 500px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
            border: 1px solid #e3e6f0;
        }
        .chat-users-list {
            width: 30%;
            min-width: 250px;
            border-right: 1px solid #e3e6f0;
            display: flex;
            flex-direction: column;
            background: #f8f9fc;
        }
        .chat-window {
            width: 70%;
            display: flex;
            flex-direction: column;
            background: #fff;
        }
        .chat-search {
            padding: 15px;
            border-bottom: 1px solid #e3e6f0;
            background: #fff;
        }
        .users-scroll {
            flex: 1;
            overflow-y: auto;
        }
        .user-item {
            padding: 12px 15px;
            border-bottom: 1px solid #eaecf4;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
        }
        .user-item:hover {
            background: #eaecf4;
        }
        .user-item.active {
            background: #4e73df;
            color: #fff !important;
        }
        .user-item.active .text-muted, .user-item.active .small {
            color: rgba(255,255,255,0.8) !important;
        }
        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #eaecf4;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: bold;
            color: #4e73df;
            margin-right: 12px;
            border: 2px solid #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .user-item.active .user-avatar {
            background: #fff;
            color: #4e73df;
        }
        .user-info {
            flex: 1;
            min-width: 0;
        }
        .user-name {
            font-weight: bold;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .last-msg {
            font-size: 0.8rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .chat-header {
            padding: 15px 20px;
            border-bottom: 1px solid #e3e6f0;
            background: #f8f9fc;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .chat-body {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f4f6f9;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .message-bubble {
            max-width: 70%;
            padding: 10px 14px;
            border-radius: 18px;
            font-size: 0.95rem;
            line-height: 1.4;
            position: relative;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .message-incoming {
            background: #fff;
            color: #333;
            align-self: flex-start;
            border-bottom-left-radius: 4px;
        }
        .message-outgoing {
            background: #4e73df;
            color: #fff;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }
        .message-time {
            font-size: 0.7rem;
            opacity: 0.7;
            margin-top: 4px;
            text-align: right;
        }
        .message-incoming .message-time {
            color: #858796;
        }
        .message-outgoing .message-time {
            color: rgba(255,255,255,0.8);
        }
        .chat-footer {
            padding: 15px 20px;
            border-top: 1px solid #e3e6f0;
            background: #fff;
        }
        .unread-badge {
            min-width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #e74a3b;
            color: #fff;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 8px;
            font-weight: bold;
        }
        .img-msg {
            max-width: 200px;
            border-radius: 8px;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .img-msg:hover {
            opacity: 0.9;
        }
        @media (max-width: 768px) {
            .chat-container {
                flex-direction: column;
                height: calc(100vh - 120px);
            }
            .chat-users-list {
                width: 100%;
                height: 40%;
                border-right: none;
                border-bottom: 1px solid #e3e6f0;
            }
            .chat-window {
                width: 100%;
                height: 60%;
            }
        }
    </style>
</head>
<body id="page-top">
<div id="wrapper">
    <?php include('includes/Side-Bar.php'); ?>
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <?php include('includes/Top-Bar.php'); ?>
            <div class="container-fluid" id="container-wrapper">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800"><i class="fab fa-line text-success"></i> <?php echo urldecode($_GET['s'] ?? 'ห้องแชต LINE OA') ?></h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                        <li class="breadcrumb-item"><?php echo urldecode($_GET['m'] ?? 'ระบบ LINE OA') ?></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo urldecode($_GET['s'] ?? 'ห้องแชต LINE OA') ?></li>
                    </ol>
                </div>

                <div class="chat-container mb-4">
                    <!-- Left Sidebar: User list -->
                    <div class="chat-users-list">
                        <div class="chat-search">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fa fa-search text-muted"></i></span>
                                </div>
                                <input type="text" class="form-control border-left-0" id="search_user" placeholder="ค้นหาชื่อ หรือ บ้านเลขที่...">
                            </div>
                        </div>
                        <div class="users-scroll" id="users_container">
                            <div class="text-center py-4 text-muted"><i class="fa fa-spinner fa-spin"></i> กำลังโหลดรายการสนทนา...</div>
                        </div>
                    </div>

                    <!-- Right Sidebar: Active Chat Window -->
                    <div class="chat-window">
                        <div class="chat-header" id="chat_header_pane">
                            <div class="text-muted">กรุณาเลือกผู้สนทนาจากแถบด้านซ้ายเพื่อเริ่มต้นห้องแชต</div>
                        </div>
                        <div class="chat-body" id="chat_body_pane">
                            <div class="m-auto text-center text-muted">
                                <i class="fab fa-line text-success" style="font-size: 5rem; opacity: 0.2;"></i>
                                <p class="mt-3">เลือกสมาชิกเพื่อเริ่มต้นพูดคุยและตอบคำถามแบบเรียลไทม์</p>
                            </div>
                        </div>
                        <div class="chat-footer d-none" id="chat_footer_pane">
                            <form id="send_msg_form">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="reply_input" placeholder="พิมพ์ข้อความตอบกลับที่นี่..." autocomplete="off">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary px-4"><i class="fa fa-paper-plane"></i> ส่ง</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Zoom Image Modal -->
        <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content bg-dark">
                    <div class="modal-header border-0 text-white pb-0">
                        <button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">&times;</button>
                    </div>
                    <div class="modal-body text-center pt-0">
                        <img id="modalImage" src="" class="img-fluid rounded" style="max-height: 80vh;">
                    </div>
                </div>
            </div>
        </div>

        <?php include('includes/Footer.php'); ?>
    </div>
</div>

<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/myadmin.min.js"></script>

<script>
    window.activeUserId = '';
    window.activeUserName = '';
    window.userListCache = [];

    $(document).ready(function () {
        // Load user list
        loadUserList();

        // Search filter
        $('#search_user').on('input', function() {
            filterUserList();
        });

        // Click user item to load chat
        $(document).on('click', '.user-item', function () {
            let userId = $(this).data('userid');
            let displayName = $(this).data('displayname');
            let house = $(this).data('house') || '';
            let fname = $(this).data('fname') || '';
            let lname = $(this).data('lname') || '';
            let phone = $(this).data('phone') || '';

            $('.user-item').removeClass('active');
            $(this).addClass('active');
            $(this).find('.unread-badge').remove(); // Clear client-side unread badge

            openChatSession(userId, displayName, house, fname, lname, phone);
        });

        // Form send message
        $('#send_msg_form').on('submit', function (e) {
            e.preventDefault();
            let text = $('#reply_input').val().trim();
            if (text === '' || window.activeUserId === '') return;

            // Optimistic rendering in chat body
            let timestamp = new Date().toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' }) + ' ' + new Date().toLocaleDateString('th-TH', { day: 'numeric', month: 'short' });
            let tempHtml = `<div class="message-bubble message-outgoing">
                <div>${text}</div>
                <div class="message-time">${timestamp}</div>
            </div>`;
            $('#chat_body_pane').append(tempHtml);
            let body = $('#chat_body_pane');
            body.scrollTop(body[0].scrollHeight);

            $('#reply_input').val('');

            $.ajax({
                url: 'model/manage_line_chat_process.php',
                type: 'POST',
                data: {
                    action: 'SEND_REPLY',
                    line_user_id: window.activeUserId,
                    message_text: text
                },
                dataType: 'json',
                success: function (response) {
                    if (response.status === 'success') {
                        // Reload chat history to get actual DB state
                        loadChatHistory(window.activeUserId);
                        // Refresh user list snippet
                        loadUserList(true);
                    } else {
                        alertify.error(response.message);
                    }
                },
                error: function () {
                    alertify.error('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                }
            });
        });

        // Click image message to zoom
        $(document).on('click', '.img-msg', function () {
            let src = $(this).attr('src');
            $('#modalImage').attr('src', src);
            $('#imageModal').modal('show');
        });

        // Real-time polling loop every 5 seconds
        setInterval(function() {
            loadUserList(true); // Quiet reload
            if (window.activeUserId !== '') {
                loadChatHistory(window.activeUserId, true); // Quiet reload history
            }
        }, 5000);
    });

    function loadUserList(isQuiet = false) {
        $.ajax({
            url: 'model/manage_line_chat_process.php',
            type: 'POST',
            data: { action: 'GET_USERS' },
            dataType: 'json',
            success: function (users) {
                window.userListCache = users;
                renderUserList(users);
            }
        });
    }

    function renderUserList(users) {
        let container = $('#users_container');
        let html = '';

        if (users.length > 0) {
            users.forEach(u => {
                let activeClass = (u.line_user_id === window.activeUserId) ? 'active' : '';
                let avatarChar = u.line_display_name ? u.line_display_name.charAt(0).toUpperCase() : 'L';
                
                let detailText = '';
                if (u.house_number) {
                    detailText = `บ้านเลขที่: ${u.house_number}`;
                    if (u.first_name) {
                        detailText += ` | คุณ${u.first_name}`;
                    }
                } else {
                    detailText = 'ยังไม่ลงทะเบียนบ้าน';
                }

                let unreadHtml = '';
                if (u.unread_count > 0 && u.line_user_id !== window.activeUserId) {
                    unreadHtml = `<div class="unread-badge">${u.unread_count}</div>`;
                }

                html += `<div class="user-item ${activeClass}" 
                             data-userid="${u.line_user_id}" 
                             data-displayname="${escapeHtml(u.line_display_name)}"
                             data-house="${escapeHtml(u.house_number)}"
                             data-fname="${escapeHtml(u.first_name)}"
                             data-lname="${escapeHtml(u.last_name)}"
                             data-phone="${escapeHtml(u.phone)}">
                    <div class="user-avatar">${avatarChar}</div>
                    <div class="user-info">
                        <div class="user-name text-dark font-weight-bold d-flex align-items-center justify-content-between">
                            <span>${escapeHtml(u.line_display_name)}</span>
                            <small class="text-muted font-weight-normal" style="font-size: 0.7rem;">${formatTimeShort(u.created_at)}</small>
                        </div>
                        <div class="text-muted small text-truncate" style="font-size: 0.75rem; margin-bottom: 2px;">${detailText}</div>
                        <div class="last-msg text-muted text-truncate font-italic">${escapeHtml(u.message_text)}</div>
                    </div>
                    ${unreadHtml}
                </div>`;
            });
        } else {
            html = '<div class="text-center py-4 text-muted">ไม่พบข้อมูลการสนทนา</div>';
        }

        container.html(html);
        filterUserList(); // Re-apply search filter if any text is typed
    }

    function filterUserList() {
        let query = $('#search_user').val().toLowerCase().trim();
        if (query === '') {
            $('.user-item').show();
            return;
        }

        $('.user-item').each(function() {
            let name = $(this).data('displayname').toLowerCase();
            let house = $(this).data('house').toLowerCase();
            let fname = $(this).data('fname').toLowerCase();
            let lname = $(this).data('lname').toLowerCase();

            if (name.includes(query) || house.includes(query) || fname.includes(query) || lname.includes(query)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    }

    function openChatSession(userId, displayName, house, fname, lname, phone) {
        window.activeUserId = userId;
        window.activeUserName = displayName;

        // Render header details
        let headerHtml = `<div class="d-flex align-items-center">
            <div class="user-avatar bg-primary text-white" style="width: 40px; height: 40px; font-size: 1rem;">${displayName.charAt(0).toUpperCase()}</div>
            <div>
                <h6 class="m-0 font-weight-bold text-dark">${escapeHtml(displayName)}</h6>
                <small class="text-muted">
                    ${house ? `<i class="fa fa-home"></i> บ้านเลขที่: ${escapeHtml(house)}` : '<i class="fa fa-home"></i> ยังไม่ผูกบ้าน'} 
                    ${fname ? ` | คุณ${escapeHtml(fname)} ${escapeHtml(lname)}` : ''}
                    ${phone ? ` | <i class="fa fa-phone"></i> ${escapeHtml(phone)}` : ''}
                </small>
            </div>
        </div>
        <div>
            <button class="btn btn-outline-secondary btn-sm" onclick="loadChatHistory('${userId}')"><i class="fa fa-refresh"></i> รีเฟรช</button>
        </div>`;
        $('#chat_header_pane').html(headerHtml);

        // Show footer and clear input
        $('#chat_footer_pane').removeClass('d-none');
        $('#reply_input').val('').focus();

        // Load messages
        loadChatHistory(userId);
    }

    function loadChatHistory(userId, isQuiet = false) {
        if (!isQuiet) {
            $('#chat_body_pane').html('<div class="m-auto text-center text-muted"><i class="fa fa-spinner fa-spin fa-2x"></i><p class="mt-2">กำลังโหลดบทสนทนา...</p></div>');
        }

        $.ajax({
            url: 'model/manage_line_chat_process.php',
            type: 'POST',
            data: {
                action: 'GET_CHAT_HISTORY',
                line_user_id: userId
            },
            dataType: 'json',
            success: function (history) {
                let html = '';
                if (history.length > 0) {
                    history.forEach(msg => {
                        let isOutgoing = (msg.group_id === 'OUTGOING');
                        let bubbleClass = isOutgoing ? 'message-outgoing' : 'message-incoming';
                        
                        let contentHtml = '';
                        if (msg.message_type === 'image') {
                            contentHtml = `<img src="line_oa/checkin/uploads/${msg.photo_path}" class="img-fluid img-msg img-thumbnail">`;
                        } else {
                            contentHtml = `<div>${escapeHtml(msg.message_text).replace(/\n/g, '<br>')}</div>`;
                        }

                        html += `<div class="message-bubble ${bubbleClass}">
                            ${contentHtml}
                            <div class="message-time">${formatTimeLong(msg.created_at)}</div>
                        </div>`;
                    });
                } else {
                    html = '<div class="m-auto text-muted">เริ่มบทสนทนากับสมาชิกรายนี้</div>';
                }
                
                // Get scroll height before rendering to see if we should scroll
                let body = $('#chat_body_pane');
                let scrollAtBottom = (body[0].scrollHeight - body.scrollTop() - body.outerHeight() < 50);

                body.html(html);

                // Scroll to bottom on initial load or if we were already at the bottom
                if (!isQuiet || scrollAtBottom) {
                    body.scrollTop(body[0].scrollHeight);
                }
            }
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function formatTimeShort(dateStr) {
        if (!dateStr) return '';
        let d = new Date(dateStr.replace(/-/g, "/"));
        let now = new Date();
        if (d.toDateString() === now.toDateString()) {
            return d.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' });
        }
        return d.toLocaleDateString('th-TH', { day: 'numeric', month: 'short' });
    }

    function formatTimeLong(dateStr) {
        if (!dateStr) return '';
        let d = new Date(dateStr.replace(/-/g, "/"));
        let time = d.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' });
        let date = d.toLocaleDateString('th-TH', { day: 'numeric', month: 'short', year: '2-digit' });
        return time + ' | ' + date;
    }
</script>
</body>
</html>
<?php } ?>
