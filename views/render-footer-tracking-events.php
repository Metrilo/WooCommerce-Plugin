<?php if ($this->has_events_in_cookie): ?>
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded",function() {
            const url         = "<?php echo add_query_arg('metrilo_clear', 1); ?>";
            const httpRequest = new XMLHttpRequest();
            httpRequest.open('GET', url);
            httpRequest.send();
        });
    </script>
<?php endif; ?>
