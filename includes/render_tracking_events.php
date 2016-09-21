<script type="text/javascript">
<?php foreach($this->events_queue as $event): ?>
	<?php if($event['method'] == 'track'): ?>
	metrilo.event("<?php echo $event['event']; ?>", <?php echo json_encode($event['params']); ?>);
	<?php endif; ?>
	<?php if($event['method'] == 'pageview'): ?>
	metrilo.pageview();
	<?php endif; ?>
<?php endforeach; ?>
</script>
<?php if ($this->has_events_in_cookie): ?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$.get("<?php echo add_query_arg('metrilo_clear', 1); ?>", function(response) {  });
	});
	</script>
<?php endif; ?>
