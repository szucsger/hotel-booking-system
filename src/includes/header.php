<?php $publicPath = '/hotel-booking-system/public'; ?>
<header>
    <div class="header-content">
        <div class="container">
            <div class="row">
                <div class="col-12 col-md-3">
                    <a href="<?php echo $publicPath; ?>/index.php" class="site-brand-link">
                    <div class="site-brand">
                        <img src="<?php echo $publicPath; ?>/assets/images/hotel-citystar-logo.jpg" alt="Hotel CityStar Logo"
                            title="Hotel CityStar Logo" class="site-logo">
                        <h1 class="site-title">Hotel CityStar</h1>
                    </div>
                    </a>
                </div>
                <div class="col-12 col-md-6">
                    <ul class="nav">
                        <li class="nav-item"><a href="<?php echo $publicPath; ?>/index.php">Kezdőlap</a></li>
                        <li class="nav-item"><a href="<?php echo $publicPath; ?>/pages/rooms/list.php">Szobák</a></li>
                        <li class="nav-item"><a href="<?php echo $publicPath; ?>/pages/services.php">Szolgáltatások</a></li>
                        <li class="nav-item"><a href="<?php echo $publicPath; ?>/pages/about.php">Rólunk</a></li>
                        <li class="nav-item"><a href="<?php echo $publicPath; ?>/pages/contact.php">Kapcsolat</a></li>
                    </ul>
                </div>
                <div class="col-12 col-md-3 header-actions">
                    <div class="header-form">
                        <?php if (isset($_SESSION['user']) && is_array($_SESSION['user'])): ?>
                            <a href="<?php echo $publicPath; ?>/pages/guest/guest.php" class="btn profile-button nav-button profile-button" id="profile-button">Profil</a>
                            <form action="<?php echo $publicPath; ?>/pages/auth/logout.php" method="post">
                                <input type="hidden" name="csrf_token"
                                    value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit" class="btn profile-button nav-button profile-button" id="logout-button">Kijelentkezés</button>
                            </form>
                        <?php else: ?>
                            <a href="<?php echo $publicPath; ?>/pages/auth/login.php" class="btn login-button nav-button" id="login-button">Bejelentkezés</a>
                            <a href="<?php echo $publicPath; ?>/pages/auth/register.php" class="btn register-button nav-button" id="register-button">Regisztráció</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>