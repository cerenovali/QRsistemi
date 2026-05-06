</main>

<footer class="bg-dark text-secondary mt-5 py-4">
    <div class="container text-center">
        <p class="mb-1 text-white fw-semibold"><i class="bi bi-qr-code-scan me-1"></i>QR Sistemi</p>
        <p class="small mb-0">BGT 132 — Yazılım Geliştirme Teknolojileri Final Projesi | PHP + MySQL + Bootstrap</p>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- jsQR (kamera QR okuma) -->
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.js"></script>

<?php if (!empty($sayfaJs ?? '')): ?>
<script>
<?= $sayfaJs ?>
</script>
<?php endif; ?>

</body>
</html>
