<?php if ($this->has_events_in_cookie): ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $.get("<?php echo add_query_arg('metrilo_clear', 1); ?>", function(response) {  });
        });
        
        document.addEventListener("DOMContentLoaded",function() {
            console.log(123);
            var httpRequest = new XMLHttpRequest();
            httpRequest.onreadystatechange = function (data) {
                // code
            }
            httpRequest.open('GET', url);
            httpRequest.send();
        });
    </script>
<?php endif; ?>
