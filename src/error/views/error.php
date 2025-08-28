<?php

?>
<h2><?= $title ?></h2>
<p>Страница: <a href="<?= $back_url ?>"><?= $back_url ?></a></p>
<p><?= $description ?></p>
<pre><?= $error_info ?></pre>
<pre><?= $error_id ?></pre>
<pre><?php
    if (!empty($error)) {
        print_r($error->asArray());
    } ?>
    </pre>