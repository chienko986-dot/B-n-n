<?php require_once PATH_VIEW . 'partials/header.php'; ?>

<?php
if (isset($view)) {
    require_once PATH_VIEW . $view . '.php';
}
?>

<?php require_once PATH_VIEW . 'partials/footer.php'; ?>
