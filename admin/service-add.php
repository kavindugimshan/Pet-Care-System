<?php
// ============================================================
// Pet Care System - Admin Add Service
// University Web Application Development Project
// Member 4: Admin Management
// ============================================================

require_once '../includes/auth.php';
require_once '../config/db.php';
require_once '../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) { session_start(); }

$errors = $_SESSION['form_errors'] ?? [];
$old    = $_SESSION['form_old']    ?? [];
unset($_SESSION['form_errors'], $_SESSION['form_old']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['service_name']    ?? '');
    $category    = trim($_POST['category']        ?? '');
    $petType     = trim($_POST['target_pet_type'] ?? '');
    $description = trim($_POST['description']     ?? '');
    $priceRaw    = trim($_POST['price']           ?? '');
    $image       = trim($_POST['image']           ?? '');

    $errors = [];
    $old    = compact('name', 'category', 'petType', 'description', 'priceRaw', 'image');

    if ($name        === '') { $errors[] = 'Service name is required.'; }
    if ($category    === '') { $errors[] = 'Category is required.'; }
    if ($petType     === '') { $errors[] = 'Target pet type is required.'; }
    if ($description === '') { $errors[] = 'Description is required.'; }

    $price = filter_var($priceRaw, FILTER_VALIDATE_FLOAT);
    if ($price === false || $price < 0) { $errors[] = 'Price must be a valid positive number.'; }

    if (!$errors) {
        $stmt = $conn->prepare(
            'INSERT INTO services (service_name, category, target_pet_type, description, price, image) VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('ssssds', $name, $category, $petType, $description, $price, $image);
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'Service "' . $name . '" added successfully.'];
            redirect('services.php');
        } else {
            error_log('Service insert failed: ' . $stmt->error);
            $errors[] = 'Database error. Please try again.';
            $stmt->close();
        }
    }
    if ($errors) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_old']    = $old;
        $conn->close();
        redirect('service-add.php');
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Service | Pet Care Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="admin-sidebar-title">🐾 PCS Admin</div>
        <nav><ul>
            <li><a href="dashboard.php">📊 Dashboard</a></li>
            <li><a href="services.php" class="active">🛎️ Manage Services</a></li>
            <li><a href="appointments.php">📅 Appointments</a></li>
        </ul></nav>
        <div class="admin-sidebar-footer">
            <a href="logout.php" class="btn btn-danger btn-sm btn-full">🔒 Logout</a>
        </div>
    </aside>
    <main class="admin-main">
        <div class="admin-top-bar">
            <h1 class="admin-welcome">➕ Add <span>New Service</span></h1>
            <a href="services.php" class="btn btn-ghost btn-sm">← Back to Services</a>
        </div>

        <?php if ($errors): ?>
            <div class="alert alert-error">
                <strong>Please fix:</strong>
                <ul style="margin-top:.5rem;padding-left:1.2rem;">
                    <?php foreach ($errors as $e): ?><li><?php echo sanitize($e); ?></li><?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="card" style="max-width:700px;">
            <form action="service-add.php" method="POST" id="addServiceForm">
                <div class="form-group">
                    <label class="form-label" for="service_name">Service Name <span class="required">*</span></label>
                    <input type="text" id="service_name" name="service_name" class="form-control"
                           value="<?php echo sanitize($old['name'] ?? ''); ?>" required>
                </div>
                <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label" for="category">Category <span class="required">*</span></label>
                        <select id="category" name="category" class="form-control" required>
                            <option value="">— Select —</option>
                            <?php foreach (['Grooming','Veterinary','Boarding','Training','Other'] as $c): ?>
                                <option value="<?php echo $c; ?>" <?php echo ($old['category'] ?? '') === $c ? 'selected' : ''; ?>>
                                    <?php echo $c; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="target_pet_type">Target Pet Type <span class="required">*</span></label>
                        <select id="target_pet_type" name="target_pet_type" class="form-control" required>
                            <option value="">— Select —</option>
                            <?php foreach (['Dog','Cat','Bird','Rabbit','Other'] as $p): ?>
                                <option value="<?php echo $p; ?>" <?php echo ($old['petType'] ?? '') === $p ? 'selected' : ''; ?>>
                                    <?php echo $p; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="description">Description <span class="required">*</span></label>
                    <textarea id="description" name="description" class="form-control" rows="4" required><?php echo sanitize($old['description'] ?? ''); ?></textarea>
                </div>
                <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group">
                        <label class="form-label" for="price">Price (LKR) <span class="required">*</span></label>
                        <input type="number" id="price" name="price" class="form-control"
                               step="0.01" min="0" placeholder="e.g. 3500"
                               value="<?php echo sanitize($old['priceRaw'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="image">Image Filename</label>
                        <input type="text" id="image" name="image" class="form-control"
                               placeholder="e.g. dog-grooming.jpg"
                               value="<?php echo sanitize($old['image'] ?? ''); ?>">
                        <p class="form-text">Upload file to assets/images/ then enter filename here.</p>
                    </div>
                </div>
                <div class="d-flex gap-md">
                    <button type="submit" class="btn btn-primary" id="saveServiceBtn">✅ Save Service</button>
                    <a href="services.php" class="btn btn-ghost">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html>
