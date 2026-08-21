<?php
/**
 * index.php — Customer Service Catalog
 * Pet Care System | Member 2
 *
 * Displays all available pet care services with:
 *   - Dynamic search by service name
 *   - Category filter (loaded dynamically from DB)
 *   - Pet type filter (loaded dynamically from DB)
 *   - Combined search + filters using prepared statements
 *   - Responsive card grid
 *   - Friendly empty-state when no results match
 */

// ── Bootstrap ─────────────────────────────────────────────────────────────────
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// Page meta (used by header.php)
$pageTitle = 'Pet Care Services';
$basePath  = '';                      // root-level page — no path prefix
$extraCss  = ['assets/css/customer.css'];

// ── Read & sanitise GET parameters ────────────────────────────────────────────
$search   = isset($_GET['search'])   ? trim($_GET['search'])   : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$pet_type = isset($_GET['pet_type']) ? trim($_GET['pet_type']) : '';

// ── Load filter options from DB ───────────────────────────────────────────────
$categories = [];
$pet_types  = [];

try {
    // Distinct categories
    $catResult = $conn->query(
        "SELECT DISTINCT category FROM services ORDER BY category"
    );
    if ($catResult) {
        while ($row = $catResult->fetch_assoc()) {
            $categories[] = $row['category'];
        }
    }

    // Distinct pet types
    $petResult = $conn->query(
        "SELECT DISTINCT target_pet_type FROM services ORDER BY target_pet_type"
    );
    if ($petResult) {
        while ($row = $petResult->fetch_assoc()) {
            $pet_types[] = $row['target_pet_type'];
        }
    }
} catch (mysqli_sql_exception $e) {
    error_log('Filter options query failed: ' . $e->getMessage());
    // Non-fatal — we can still show the form with empty dropdowns.
}

// ── Build service query with prepared statements ───────────────────────────────
/**
 * We build the WHERE clause and parameter list dynamically based on
 * which filters are active, then use a single prepared statement.
 * This avoids duplicated SQL blocks and prevents SQL injection.
 */
$conditions = [];
$params     = [];
$types      = '';

if ($search !== '') {
    $conditions[] = 'service_name LIKE ?';
    $params[]     = '%' . $search . '%';
    $types       .= 's';
}

if ($category !== '') {
    $conditions[] = 'category = ?';
    $params[]     = $category;
    $types       .= 's';
}

if ($pet_type !== '') {
    $conditions[] = 'target_pet_type = ?';
    $params[]     = $pet_type;
    $types       .= 's';
}

$sql = 'SELECT id, service_name, category, target_pet_type,
               description, price, image
        FROM services';

if (!empty($conditions)) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}

$sql .= ' ORDER BY category, service_name';

// ── Execute query ─────────────────────────────────────────────────────────────
$services    = [];
$queryError  = false;

try {
    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            throw new RuntimeException('Prepare failed: ' . $conn->error);
        }
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        // No parameters — safe to run directly (no user input in query).
        $result = $conn->query($sql);
    }

    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
} catch (Exception $e) {
    error_log('Service catalog query failed: ' . $e->getMessage());
    $queryError = true;
}

$serviceCount  = count($services);
$filtersActive = ($search !== '' || $category !== '' || $pet_type !== '');
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<!-- ═══════════════════════════════════════════ HERO SECTION -->
<section class="hero" aria-label="Welcome banner">
    <div class="container hero-content">
        <span class="hero-icon" aria-hidden="true">🐾</span>
        <h1 class="hero-title">Professional Pet Care Services</h1>
        <p class="hero-subtitle">
            Discover expert grooming, veterinary and boarding services
            lovingly tailored for your pets.
        </p>
    </div>
</section>

<!-- ════════════════════════════════════════ SEARCH & FILTERS -->
<section class="filter-section" aria-label="Search and filter services">
    <div class="container">
        <form
            id="filterForm"
            method="GET"
            action="index.php"
            role="search"
            aria-label="Filter pet services"
        >
            <div class="filter-form">

                <!-- Search -->
                <div class="filter-group">
                    <label for="search">Search Services</label>
                    <div class="search-wrapper">
                        <span class="search-icon" aria-hidden="true">🔍</span>
                        <input
                            type="text"
                            id="search"
                            name="search"
                            placeholder="e.g. Grooming, Boarding…"
                            value="<?= sanitize($search) ?>"
                            maxlength="100"
                            autocomplete="off"
                        >
                    </div>
                </div>

                <!-- Category -->
                <div class="filter-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option
                                value="<?= sanitize($cat) ?>"
                                <?= ($category === $cat) ? 'selected' : '' ?>
                            >
                                <?= sanitize($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Pet Type -->
                <div class="filter-group">
                    <label for="pet_type">Pet Type</label>
                    <select id="pet_type" name="pet_type">
                        <option value="">All Pet Types</option>
                        <?php foreach ($pet_types as $type): ?>
                            <option
                                value="<?= sanitize($type) ?>"
                                <?= ($pet_type === $type) ? 'selected' : '' ?>
                            >
                                <?= sanitize($type) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="filter-group">
                    <label>&nbsp;</label><!-- spacer to align with inputs -->
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary" id="filterSubmit">
                            Search
                        </button>
                        <?php if ($filtersActive): ?>
                            <a href="index.php" class="btn btn-ghost" id="clearFilters">
                                Clear
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

            </div><!-- /.filter-form -->

            <?php if ($filtersActive): ?>
                <!-- Active filter badges -->
                <div class="active-filters" aria-label="Active filters">
                    <span style="font-size:0.8rem;color:var(--color-text-muted);font-weight:600;">Active:</span>
                    <?php if ($search !== ''): ?>
                        <span class="filter-badge">🔍 "<?= sanitize($search) ?>"</span>
                    <?php endif; ?>
                    <?php if ($category !== ''): ?>
                        <span class="filter-badge">📂 <?= sanitize($category) ?></span>
                    <?php endif; ?>
                    <?php if ($pet_type !== ''): ?>
                        <span class="filter-badge">🐾 <?= sanitize($pet_type) ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </form>
    </div>
</section>

<!-- ═══════════════════════════════════════ SERVICE CATALOG -->
<section class="catalog-section" id="results" aria-label="Service catalog">
    <div class="container">

        <?php if ($queryError): ?>
            <!-- Database error — show friendly message, no technical details -->
            <div class="empty-state">
                <div class="empty-state-icon">⚠️</div>
                <h2 class="empty-state-title">Services Unavailable</h2>
                <p class="empty-state-message">
                    Unable to load services at the moment. Please try again later.
                </p>
            </div>

        <?php elseif ($serviceCount === 0): ?>
            <!-- No results state -->
            <div class="empty-state">
                <div class="empty-state-icon" aria-hidden="true">🔍</div>
                <h2 class="empty-state-title">No Services Found</h2>
                <p class="empty-state-message">
                    No services match your current search or filters.<br>
                    Try adjusting your criteria or browse all services.
                </p>
                <a href="index.php" class="btn btn-primary" id="clearFiltersEmpty">
                    Clear Filters &amp; Browse All
                </a>
            </div>

        <?php else: ?>
            <!-- Results summary -->
            <div class="results-bar">
                <p class="results-count">
                    Showing <strong><?= $serviceCount ?></strong>
                    service<?= $serviceCount !== 1 ? 's' : '' ?>
                    <?php if ($filtersActive): ?>
                        for your search
                    <?php endif; ?>
                </p>
            </div>

            <!-- Service cards grid -->
            <div class="services-grid" data-base-path="<?= sanitize($basePath) ?>">

                <?php foreach ($services as $service): ?>
                    <?php
                    $imgSrc  = resolveServiceImage($service['image'] ?? '', $basePath);
                    $imgAlt  = sanitize($service['service_name']) . ' — ' . sanitize($service['category']);
                    $petIcon = match(strtolower(trim($service['target_pet_type'] ?? ''))) {
                        'dog'   => '🐕',
                        'cat'   => '🐈',
                        default => '🐾',
                    };
                    ?>
                    <article class="service-card" aria-label="<?= sanitize($service['service_name']) ?>">

                        <!-- Image -->
                        <div class="card-image-wrap">
                            <img
                                class="service-img"
                                src="<?= sanitize($imgSrc) ?>"
                                alt="<?= $imgAlt ?>"
                                loading="lazy"
                                onerror="this.onerror=null;this.src='<?= sanitize($basePath) ?>assets/images/placeholder.svg';"
                            >
                            <span class="card-category-ribbon">
                                <?= sanitize($service['category']) ?>
                            </span>
                        </div>

                        <!-- Body -->
                        <div class="card-body">
                            <h2 class="card-title">
                                <?= sanitize($service['service_name']) ?>
                            </h2>

                            <p class="card-meta">
                                <span class="meta-icon" aria-hidden="true"><?= $petIcon ?></span>
                                <?= sanitize($service['target_pet_type']) ?>
                            </p>

                            <p class="card-description">
                                <?= sanitize(truncate($service['description'], 110)) ?>
                            </p>

                            <p class="card-price">
                                <?= formatPrice($service['price']) ?>
                            </p>
                        </div><!-- /.card-body -->

                        <!-- Footer buttons -->
                        <div class="card-footer">
                            <a
                                href="service-details.php?id=<?= (int)$service['id'] ?>"
                                class="btn btn-secondary"
                                id="view-details-<?= (int)$service['id'] ?>"
                            >
                                View Details
                            </a>
                            <a
                                href="customer/booking.php?service_id=<?= (int)$service['id'] ?>"
                                class="btn btn-primary"
                                id="book-now-<?= (int)$service['id'] ?>"
                            >
                                Book Now
                            </a>
                        </div>

                    </article>
                <?php endforeach; ?>

            </div><!-- /.services-grid -->
        <?php endif; ?>

    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
