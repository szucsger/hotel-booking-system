<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendég</title>
    <link rel="stylesheet" href="../../assets/bootstrap-5.3.8-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/main.css">
    <link rel="stylesheet" href="../../css/components.css">
    <link rel="stylesheet" href="../../css/variables.css">
</head>

<body>
    <header class="reception-header py-4">
        <div class="container reception-header-inner">
            <div class="reception-topline">
                <div> Vendég <span id="current-day"></span>
                </div>
                <div class="reception-header-row">
                    <div class="reception-title-group">
                        <h3 class="mb-0"><span id="time-of-day">Jó reggelt </span></h3>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </header>

<main class="container my-4 reception-body">

    <!-- Vendég gyorsmenü -->
<div class="row g-4">

    <!-- 1. Profilom -->
    <div class="col-12 col-md-6">
        <div class="info-card h-100">
            <i class="bi bi-person-circle fs-1"></i>
            <p class="info-card-label">Profilom</p>
            <p class="info-card-description">
                Személyes adataim megtekintése és módosítása.
            </p>
        </div>
    </div>

    <!-- 2. Foglalásaim -->
    <div class="col-12 col-md-6">
        <div class="info-card h-100">
            <i class="bi bi-calendar-check fs-1"></i>
            <p class="info-card-label">Foglalásaim</p>
            <p class="info-card-description">
                Korábbi és aktuális foglalásaim megtekintése.
            </p>
        </div>
    </div>

    <!-- 3. Új foglalás -->
    <div class="col-12 col-md-6">
        <div class="info-card h-100">
            <i class="bi bi-plus-circle fs-1"></i>
            <p class="info-card-label">Új foglalás</p>
            <p class="info-card-description">
                Új szoba lefoglalása a szállodában.
            </p>
        </div>
    </div>

    <!-- 4. Kijelentkezés -->
    <div class="col-12 col-md-6">
        <div class="info-card h-100">
            <i class="bi bi-box-arrow-right fs-1"></i>
            <p class="info-card-label">Kijelentkezés</p>
            <p class="info-card-description">
                Kilépés a vendégfiókból.
            </p>
        </div>
    </div>

</div>
<!-- Következő foglalásom -->
<div class="row mt-4">
    <div class="col-12">

        <div class="info-card h-100">

            <p class="info-card-label">
                <i class="bi bi-calendar-event me-2"></i>
                Következő foglalásom
            </p>

            <?php if ($next_booking): ?>

                <div class="row mt-3">

                    <div class="col-md-3">
                        <h4>
                            <?php echo htmlspecialchars($next_booking['room_number']); ?>
                        </h4>
                        <p class="info-card-description">Szobaszám</p>
                    </div>

                    <div class="col-md-3">
                        <h4>
                            <?php echo htmlspecialchars($next_booking['check_in_date']); ?>
                        </h4>
                        <p class="info-card-description">Érkezés</p>
                    </div>

                    <div class="col-md-3">
                        <h4>
                            <?php echo htmlspecialchars($next_booking['check_out_date']); ?>
                        </h4>
                        <p class="info-card-description">Távozás</p>
                    </div>

                    <div class="col-md-3">
                        <h4>
                            <?php echo number_format($next_booking['total_price'], 0, ',', ' '); ?> Ft
                        </h4>
                        <p class="info-card-description">Végösszeg</p>
                    </div>

                </div>

            <?php else: ?>

                <p class="info-card-description mt-3">
                    Jelenleg nincs következő foglalásod.
                </p>

            <?php endif; ?>

        </div>

    </div>
</div>