    <!-- =========================
         JS DINÁMICOS POR VISTA
    ========================== -->
    <?php if (isset($js) && is_array($js)): ?>
        <?php foreach ($js as $file): ?>
            <script src="/public/assets/js/<?= $file ?>" defer></script>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>
