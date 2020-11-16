<script type="text/javascript">
    <?php
    foreach($this->events_queue as $event) {
        echo html_entity_decode($event);
    }
    ?>
</script>