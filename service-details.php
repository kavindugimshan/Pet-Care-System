<?php
/**
 * service-details.php — Service Detail Page
 * Pet Care System | Member 2
 *
 * Accepts: ?id=<positive integer>
 *
 * - Validates and sanitises the ID parameter
 * - Fetches the service using a prepared statement
 * - Displays full service information
 * - Links to customer/booking.php?service_id=ID  (Member 3)
 * - Shows a safe error page for invalid or missing IDs
 */

// ── Bootstrap ─────────────────────────────────────────────────────────────────
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$basePath = '';  // root-level page
$extraCss = ['assets/css/customer.css'];

// ── Validate the ID parameter ─────────────────────────────────────────────────
/**
 * Accept only a positive integer.
 * filter_var with FILTER_VALIDATE_INT returns false or the int.
 * We also reject zero and negatives.
 */
$rawId     = isset($_GET['id']) ? $_GET['id'] : '';
$serviceId = filter_var($rawId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

// If validation fails, $serviceId === false.
$service      = null;
$fetchError   = false;
$invalidId    = ($serviceId === false);

if (!$invalidId) {
    // ── Fetch service via prepared statement ──────────────────────────────────
    try {
        $stmt = $conn->prepare(
            'SELECT id, service_name, category, target_pet_type,
                    description, price, image
             FROM   services
             WHERE  id = ?
             LIMIT  1'
        );

        if ($stmt === false) {
            throw new RuntimeException('Prepare failed: ' . $conn->error);
        }

        $stmt->bind_param('i', $serviceId);
        $stmt->execute();
        $result  = $stmt->get_result();
        $service = $result->fetch_assoc(); // null if not found
        $stmt->close();

    } catch (Exception $e) {
        error_log('Service details fetch failed: ' . $e->getMessage());
        $fetchError = true;
    }
}

// ── Set page title ────────────────────────────────────────────────────────────
if ($service) {
    $pageTitle = sanitize($service['service_name']);
} elseif ($invalidId) {
    $pageTitle = 'Invalid Service';
} elseif ($fetchError) {
    $pageTitle = 'Error';
} else {
    $pageTitle = 'Service Not Found';
}
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<section class="details-section">
    <div class="container">

        <?php if ($fetchError): ?>
            <!-- ── Database error ─────────────────────────── -->
            <div class="details-not-found">
                <div class="details-not-found-icon" aria-hidden="true">⚠️</div>
                <h1 class="details-not-found-title">Something Went Wrong</h1>
                <p class="details-not-found-message">
                    Unable to load service details at the moment.<br>
                    Please try again later.
                </p>
                <a href="index.php" class="btn btn-primary" id="errorBackHome">
                    ← Back to Services
                </a>
            </div>

        <?php elseif ($invalidId || $service === null): ?>
            <!-- ── Not found / invalid ID ─────────────────── -->
            <div class="details-not-found">
                <div class="details-not-found-icon" aria-hidden="true">🔍</div>
                <h1 class="details-not-found-title">Service Not Found</h1>
                <p class="details-not-found-message">
                    <?php if ($invalidId): ?>
                        The service ID provided is not valid.<br>
                    <?php else: ?>
                        The service you are looking for does not exist or may have been removed.<br>
                    <?php endif; ?>
                    Please browse our available services below.
                </p>
                <a href="index.php" class="btn btn-primary" id="notFoundBackHome">
                    ← Browse All Services
                </a>
            </div>

        <?php else: ?>
            <!-- ── Service found — render full details ─────── -->
            <?php
            $imgSrc  = resolveServiceImage($service['image'] ?? '', $basePath);
            $imgAlt  = sanitize($service['service_name']) . ' service image';
            $petIcon = match(strtolower(trim($service['target_pet_type'] ?? ''))) {
                'dog'   => '🐕',
                'cat'   => '🐈',
                default => '🐾',
            };
            ?>

            <!-- Back link -->
            <div class="details-back">
                <a href="index.php" class="btn btn-ghost btn-sm" id="backToServices">
                    ← Back to Services
                </a>
            </div>

            <!-- Details card -->
            <div class="details-card">

                <!-- ── Image panel ──────────────────────────── -->
                <div class="details-image-panel">
                    <img
                        class="service-img"
                        src="<?= sanitize($imgSrc) ?>"
                        alt="<?= $imgAlt ?>"
                        onerror="this.onerror=null;this.src='<?= sanitize($basePath) ?>assets/images/placeholder.svg';"
                    >
                    <span class="details-category-badge">
                        <?= sanitize($service['category']) ?>
                    </span>
                </div>

                <!-- ── Info panel ───────────────────────────── -->
                <div class="details-info-panel">

                    <h1 class="details-title">
                        <?= sanitize($service['service_name']) ?>
                    </h1>

                    <!-- Metadata grid -->
                    <div class="details-meta-grid">
                        <div class="details-meta-item">
                            <p class="details-meta-label">Category</p>
                            <p class="details-meta-value">
                                📂 <?= sanitize($service['category']) ?>
                            </p>
                        </div>
                        <div class="details-meta-item">
                            <p class="details-meta-label">Suitable For</p>
                            <p class="details-meta-value">
                                <?= $petIcon ?> <?= sanitize($service['target_pet_type']) ?>
                            </p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <p class="details-description-label">About This Service</p>
                        <p class="details-description-text">
                            <?= nl2br(sanitize($service['description'])) ?>
                        </p>
                    </div>

                    <!-- Price -->
                    <div class="details-price-block">
                        <span class="details-price-label">Service Price</span>
                        <span class="details-price-value">
                            <?= formatPrice($service['price']) ?>
                        </span>
                    </div>

                    <!-- CTA Buttons -->
                    <div class="details-actions">
                        <a
                            href="customer/booking.php?service_id=<?= (int)$service['id'] ?>"
                            class="btn btn-primary btn-lg"
                            id="book-service-<?= (int)$service['id'] ?>"
                        >
                            📅 Book This Service
                        </a>
                        <a
                            href="index.php"
                            class="btn btn-secondary"
                            id="backToServicesList"
                        >
                            ← Back to Services
                        </a>
                    </div>

                </div><!-- /.details-info-panel -->
            </div><!-- /.details-card -->

        <?php endif; ?>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
