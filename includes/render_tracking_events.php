<script type="text/javascript">
<?php foreach($this->events_queue as $event): ?>
metrilo.event("<?= $event['event']; ?>", <?= json_encode($event['params']); ?>);
<?php endforeach; ?>
</script>