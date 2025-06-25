<?php
$stmt = $pdo->prepare("SELECT * FROM contact_info WHERE is_visible = 1");
$stmt->execute();
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM maps WHERE is_active = 1 LIMIT 1");
$stmt->execute();
$map = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<footer class="bg-light py-3">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5>Liên hệ</h5>
                <?php foreach ($contacts as $contact): ?>
                    <a href="<?php echo htmlspecialchars($contact['link']); ?>" target="_blank">
                        <img src="<?php echo htmlspecialchars($contact['contact']); ?>" alt="Contact" width="30">
                    </a>
                <?php endforeach; ?>
            </div>
            <div class="col-md-4">
                <h5>Bản đồ</h5>
                <?php if ($map): ?>
                    <?php echo $map['description_vi']; ?>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <h5>Thông tin</h5>
                <p>Kaiadmin - Cung cấp sản phẩm và dịch vụ chất lượng cao.</p>
            </div>
        </div>
    </div>
</footer>