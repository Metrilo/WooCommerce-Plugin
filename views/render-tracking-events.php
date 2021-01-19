<script type="text/javascript">
    (function() {
        const f = function () {
            <?php
            foreach($this->events_queue as $event) {
                echo html_entity_decode($event);
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
