<script type="text/javascript">
    (function() {
        const f = function () {
            metrilo.identify("<?php echo $this->identify_call_data; ?>");
            <?php
            if ($this->user_tags) {
                echo "window.metrilo.applyTags($this->user_tags);";
            }
            ?>
        };
        if (typeof window.metrilo !== 'undefined') {
            f();
        } else {
            window.metriloQueue = window.metriloQueue || [];
            window.metriloQueue.push(f);
        }
    })();
</script>
