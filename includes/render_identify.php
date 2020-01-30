<script type="text/javascript">
metrilo.identify("<?php echo $this->identify_call_data['id']; ?>", <?php echo json_encode($this->identify_call_data['params']); ?>, '<?php echo $_COOKIE['cbuid'] ?>');
</script>
