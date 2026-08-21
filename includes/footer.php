<?php
/**
 * Shared Footer Include
 * Pet Care System
 *
 * Outputs the closing </main>, the site footer, script tags, and </body></html>.
 *
 * Variables that can be set before including this file:
 *   $basePath   (string) — relative path prefix for asset links, e.g. '../'
 *   $extraJs    (array)  — additional JS file paths relative to the base path
 */

$basePath = isset($basePath) ? $basePath : '';
$year     = date('Y');
?>

</main><!-- /main#main-content -->

<!-- ═══════════════════════════════════════════════════════════ FOOTER -->
<footer class="site-footer" role="contentinfo">
    <div class="container footer-inner">
        <div class="footer-brand">
            <span class="logo-icon" aria-hidden="true">🐾</span>
            <span class="logo-text">Pet<strong>Care</strong></span>
        </div>
        <p class="footer-tagline">Professional care for your beloved pets.</p>
        <p class="footer-copy">
            &copy; <?= $year ?> Pet Care System. All rights reserved.
        </p>
    </div>
</footer>
<!-- ══════════════════════════════════════════════════════════ /FOOTER -->

<!-- Scripts -->
<script src="<?= $basePath ?>assets/js/catalog.js" defer></script>

<?php if (isset($extraJs)): ?>
    <?php foreach ((array)$extraJs as $js): ?>
        <script src="<?= htmlspecialchars($basePath . $js, ENT_QUOTES, 'UTF-8') ?>" defer></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>
</html>
