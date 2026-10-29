<?php $publicPath = '/hotel-booking-system/public'; ?>
<header>
    <div class="header-content">
        <div class="container">
            <div class="row">
                <div class="col-12 col-md-4">
                     <a href="<?php echo $publicPath; ?>/index.php" class="site-brand-link">
                    <div class="site-brand">
                       <img src="<?php echo $publicPath; ?>/assets/images/hotel-citystar-logo.jpg" alt="Hotel CityStar Logo"
                            title="Hotel CityStar Logo" class="site-logo">
                        <h1 class="site-title">Hotel CityStar</h1>
                    </div>
                    </a>
                </div>
                <div class="col-12 col-md-4">
                    <ul class="nav">
                        <li class="nav-item"><a href="<?php echo $publicPath; ?>/index.php">Kezdőlap</a></li>
                        <li class="nav-item"><a href="<?php echo $publicPath; ?>/pages/rooms/list.php">Szobák</a></li>
                        <li class="nav-item"><a href="<?php echo $publicPath; ?>/pages/services.php">Szolgáltatások</a></li>
                        <li class="nav-item"><a href="<?php echo $publicPath; ?>/pages/about.php">Rólunk</a></li>
                        <li class="nav-item"><a href="<?php echo $publicPath; ?>/pages/contact.php">Kapcsolat</a></li>
                    </ul>
                </div>
                <div class="col-12 col-md-4 header-actions">
                    <div class="header-form">
                        <a href="<?php echo $publicPath; ?>/pages/auth/login.php" class="btn login-button nav-button" id="login-button">Bejelentkezés</a>
                        <a href="<?php echo $publicPath; ?>/pages/auth/register.php" class="btn register-button nav-button" id="register-button">Regisztráció</a>
                        <a href="<?php echo $publicPath; ?>/pages/profile.php" class="btn profile-button nav-button profile-button" id="profile-button" hidden>Profil</a>
                        <a href="<?php echo $publicPath; ?>/pages/auth/logout.php" class="btn profile-button nav-button profile-button" id="logout-button" hidden>Kijelentkezés</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>