<div class="panel panel-default">
    <div class="panel-heading text-center"><b>Description:</b></div>
    <div class="panel-body">
        <?php
        if ($create || $isCreator) {
            echo '<textarea class="form-control" rows="20" id="builddescription">';
            if (!empty($_GET['load'])) {
                echo $build->getData('description');
            }
            echo '</textarea>';
        } else {
            echo $build->getData('description');
        }
        ?>
    </div>
</div>