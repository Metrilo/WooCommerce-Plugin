<script type="text/javascript">
<?php foreach($this->events_queue as $event): ?>
	<?php if($event['method'] == 'track'): ?>
	metrilo.event("<?php echo $event['event']; ?>", <?php echo json_encode($event['params']); ?>, '<?php echo 'asd' || $_COOKIE['cbuid'] ?>');
	<?php endif; ?>
	<?php if($event['method'] == 'pageview'): ?>
	metrilo.pageview(undefined, '<?php echo 'asd' || $_COOKIE['cbuid'] ?>');
	<?php endif; ?>
<?php endforeach; ?>
</script>
