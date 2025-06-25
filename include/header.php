<?php
$stmt = $pdo->prepare("SELECT * FROM logos WHERE is_active = 1 LIMIT 1");
$stmt->execute();
$logo = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM categories WHERE display_position LIKE '%menu_main%' AND is_active = 1 ORDER BY position ASC");
$stmt->execute();
$menu_main = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <!-- Logo -->
        <a class="navbar-brand" href="/">
            <?php if ($logo): ?>
                <img src="<?php echo htmlspecialchars($logo['desktop_image']); ?>" alt="<?php echo htmlspecialchars($logo['title_vi']); ?>" height="<?php echo $logo['height'] ?? 50; ?>">
            <?php else: ?>
                Kaiadmin
            <?php endif; ?>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Menu -->
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/">Trang chủ</a>
                </li>
                <?php foreach ($menu_main as $item): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/<?php echo htmlspecialchars($item['slug_vi']); ?>" target="<?php echo htmlspecialchars($item['link_target']); ?>">
                            <?php echo htmlspecialchars($item['title_vi']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <!-- Tìm kiếm và icon -->
            <div class="d-flex align-items-center">
                <form class="form-inline mr-3" action="/search" method="GET">
                    <input class="form-control" type="search" name="q" placeholder="Tìm kiếm..." aria-label="Search" value="<?php echo htmlspecialchars($search_query); ?>">
                    <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
                </form>
                <a href="/cart" class="mx-2"><i class="fas fa-shopping-cart"></i></a>
                <a href="/wishlist" class="mx-2"><i class="fas fa-heart"></i></a>
                <a href="/compare" class="mx-2"><i class="fas fa-balance-scale"></i></a>
            </div>
        </div>
    </div>
</nav>