<script type="text/javascript">
    metrilo.identify("<?php echo $this->identify_call_data; ?>");
    <?php
        if ($this->user_tags) {
            echo "window.metrilo.applyTags($this->user_tags)";
        }
    ?>
</script>
