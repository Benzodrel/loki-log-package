<?php

?>
    <h2><?= $title ?></h2>
    <p>Страница: <a href="<?= $back_url ?>"><?= $back_url ?></a></p>
    <p><?= $description ?></p>
    <pre><?= $error_info ?></pre>

    <pre><?php print_r( '$error->asArray()' ); ?></pre>