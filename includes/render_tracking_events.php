<script type="text/javascript">
<?php foreach($this->events_queue as $event): ?>
	<?php if($event['method'] == 'track'): ?>
	metrilo.event("<?php echo $event['event']; ?>", <?php echo html_entity_decode(json_encode($event['params'])); ?>);
	<?php endif; ?>
	<?php if($event['method'] == 'pageview'): ?>
	metrilo.pageview();
	<?php endif; ?>
<?php endforeach; ?>
</script>
