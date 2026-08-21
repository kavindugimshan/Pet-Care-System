<?php
// ============================================================
// Pet Care System - Customer Service Catalog (Homepage)
// University Web Application Development Project
// ============================================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// ── Search & Filter ──────────────────────────────────────────
$search   = trim($_GET['search']   ?? '');
$category = trim($_GET['category'] ?? '');
$petType  = trim($_GET['pet_type'] ?? '');

// Build dynamic WHERE clause with prepared statement
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
if ($petType !== '') {
    $conditions[] = 'target_pet_type = ?';
    $params[]     = $petType;
    $types       .= 's';
}

$where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
$sql   = "SELECT id, service_name, category, target_pet_type, description, price, image
          FROM services $where ORDER BY id ASC";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log('Catalog prepare failed: ' . $conn->error);
    $services = [];
} else {
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result   = $stmt->get_result();
    $services = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// ── Dynamic filter options ────────────────────────────────────
$catResult = $conn->query("SELECT DISTINCT category FROM services ORDER BY category");
$categories = $catResult ? $catResult->fetch_all(MYSQLI_ASSOC) : [];

$petResult  = $conn->query("SELECT DISTINCT target_pet_type FROM services ORDER BY target_pet_type");
$petTypes   = $petResult ? $petResult->fetch_all(MYSQLI_ASSOC) : [];

$conn->close();

$totalServices = count($services);
$hasFilters    = $search !== '' || $category !== '' || $petType !== '';

$pageTitle = 'Pet Care Services';
require_once __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="/assets/css/customer.css">
<script src="/assets/js/catalog.js" defer></script>

<!-- ── Hero Section ─────────────────────────────────────────── -->
<section class="hero">
    <div class="container hero-content">
        <span class="hero-icon" aria-hidden="true">🐾</span>
        <h1 class="hero-title">Professional Pet Care Services</h1>
        <p class="hero-subtitle">
            Trusted grooming, veterinary checkups, and boarding services for your beloved companions.
        </p>
    </div>
</section>

<!-- ── Search & Filter ──────────────────────────────────────── -->
<section class="filter-section" id="services">
    <div class="container">
        <form id="filterForm" action="/index.php" method="GET" class="filter-form">
            <!-- Search -->
            <div class="filter-group">
                <label for="search">Search Services</label>
                <div class="search-wrapper">
                    <span class="search-icon" aria-hidden="true">🔍</span>
                    <input
                        type="text"
                        id="search"
                        name="search"
                        class="form-control"
                        placeholder="e.g. Grooming, Vet…"
                        value="<?php echo sanitize($search); ?>"
                    >
                </div>
            </div>

            <!-- Category filter -->
            <div class="filter-group">
                <label for="category">Category</label>
                <select id="category" name="category" class="form-control">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo sanitize($cat['category']); ?>"
                            <?php echo ($category === $cat['category']) ? 'selected' : ''; ?>>
                            <?php echo sanitize($cat['category']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Pet type filter -->
            <div class="filter-group">
                <label for="pet_type">Pet Type</label>
                <select id="pet_type" name="pet_type" class="form-control">
                    <option value="">All Pet Types</option>
                    <?php foreach ($petTypes as $pt): ?>
                        <option value="<?php echo sanitize($pt['target_pet_type']); ?>"
                            <?php echo ($petType === $pt['target_pet_type']) ? 'selected' : ''; ?>>
                            <?php echo sanitize($pt['target_pet_type']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Buttons -->
            <div class="filter-group filter-actions">
                <label style="visibility:hidden">Go</label>
                <button type="submit" class="btn btn-primary" id="searchBtn">Search</button>
                <?php if ($hasFilters): ?>
                    <a href="/index.php" class="btn btn-ghost" id="clearFilters">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Active filter badges -->
        <?php if ($hasFilters): ?>
        <div class="active-filters">
            <span style="font-size:.8rem;color:var(--color-text-muted);font-weight:600;">Active:</span>
            <?php if ($search !== ''): ?>
                <span class="filter-badge">🔍 <?php echo sanitize($search); ?></span>
            <?php endif; ?>
            <?php if ($category !== ''): ?>
                <span class="filter-badge">📂 <?php echo sanitize($category); ?></span>
            <?php endif; ?>
            <?php if ($petType !== ''): ?>
                <span class="filter-badge">🐾 <?php echo sanitize($petType); ?></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ── Service Catalog Grid ──────────────────────────────────── -->
<section class="catalog-section" id="results">
    <div class="container">
        <!-- Results bar -->
        <div class="results-bar">
            <p class="results-count">
                Showing <strong><?php echo $totalServices; ?></strong>
                service<?php echo $totalServices !== 1 ? 's' : ''; ?>
                <?php if ($hasFilters): ?>
                    matching your search
                <?php endif; ?>
            </p>
        </div>

        <!-- Service cards -->
        <div class="services-grid" data-base-path="/">
            <?php if ($totalServices === 0): ?>
                <div class="empty-state">
                    <div class="empty-state-icon" aria-hidden="true">🐾</div>
                    <h2 class="empty-state-title">No Services Found</h2>
                    <p class="empty-state-message">
                        <?php if ($hasFilters): ?>
                            No services match your current search or filters.
                        <?php else: ?>
                            No services available yet. Please check back later.
                        <?php endif; ?>
                    </p>
                    <?php if ($hasFilters): ?>
                        <a href="/index.php" class="btn btn-primary" id="clearFiltersEmpty">View All Services</a>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <?php foreach ($services as $service):
                    $imgSrc  = 'assets/images/' . ($service['image'] ? basename($service['image']) : 'placeholder.svg');
                    $petIcon = match(strtolower(trim($service['target_pet_type']))) {
                        'dog'   => '🐕',
                        'cat'   => '🐈',
                        default => '🐾',
                    };
                ?>
                <article class="service-card" id="service-card-<?php echo (int)$service['id']; ?>">
                    <!-- Image -->
                    <div class="card-image-wrap">
                        <img
                            class="service-img"
                            src="/<?php echo sanitize($imgSrc); ?>"
                            alt="<?php echo sanitize($service['service_name']); ?>"
                            onerror="this.onerror=null;this.src='/assets/images/placeholder.svg';"
                        >
                        <span class="card-category-ribbon">
                            <?php echo sanitize($service['category']); ?>
                        </span>
                    </div>

                    <!-- Body -->
                    <div class="card-body">
                        <h2 class="card-title"><?php echo sanitize($service['service_name']); ?></h2>
                        <p class="card-meta">
                            <span class="meta-icon" aria-hidden="true"><?php echo $petIcon; ?></span>
                            <?php echo sanitize($service['target_pet_type']); ?>
                        </p>
                        <p class="card-description">
                            <?php echo sanitize(truncateText($service['description'], 110)); ?>
                        </p>
                        <p class="card-price"><?php echo formatPrice((float)$service['price']); ?></p>
                    </div>

                    <!-- Footer buttons -->
                    <div class="card-footer">
                        <a href="/service-details.php?id=<?php echo (int)$service['id']; ?>"
                           class="btn btn-secondary"
                           id="details-<?php echo (int)$service['id']; ?>">
                            View Details
                        </a>
                        <a href="/customer/booking.php?service_id=<?php echo (int)$service['id']; ?>"
                           class="btn btn-primary"
                           id="book-<?php echo (int)$service['id']; ?>">
                            Book Now
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
