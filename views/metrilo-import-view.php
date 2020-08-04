<div class="welcome-panel">
    <div style="float: left; width: 120px; text-align: center; padding-top: 30px;">
        <img src="https://cdn.metrilo.com/logo-m.png" />
    </div>
    <div style="float: left;">
        <h3>Importing your historical data to Metrilo</h3>
        <p>
            This tool helps you sync all your data (customers, categories, products and orders) to Metrilo and can take <strong>up to 20 minutes</strong> to complete. <br />
            It will not affect your website's performance at all since it sends your data to your Metrilo account in small chunks.  <br /><br />
            Make sure to <strong>not close this page</strong> while importing. Coffee, maybe?
        </p>
        <?php if($this->importing): ?>
            <script type="text/javascript">
                jQuery.noConflict();

                var dataOptions = {
                    action: 'metrilo_import',
                    customersChunks: <?php echo $this->get_customer_chunks(); ?>,
                    categoriesChunks: <?php echo $this->get_category_chunks(); ?>,
                    productsChunks: <?php echo $this->get_product_chunks(); ?>,
                    ordersChunks: <?php echo $this->get_order_chunks(); ?>,
                    importType: 'customers',
                    percentage: 100,
                    retryAttempts: 3
                };

                function sync_chunk(chunkId, importType) {
                    dataOptions.chunkId = chunkId;
                    if (importType == 'customers' && chunkId == 0) {
                        jQuery('#metrilo_import_step').text('Importing customers');
                    }

                    var progress_percents = Math.round(chunkId * this.dataOptions.percentage);
                    this.update_importing_message('Please wait... ' + progress_percents + '% done', true);

                    this.ajax_post_with_retry('<?php echo admin_url('admin-ajax.php'); ?>', this.dataOptions, function() {
                        var newChunkId = chunkId + 1;

                        switch (importType) {
                            case 'customers':
                                this.import_type(newChunkId, dataOptions, 'customers', 'categories');
                                break;
                            case 'categories':
                                this.import_type(newChunkId, dataOptions, 'categories', 'deletedProducts');
                                break;
                            case 'deletedProducts':
                                this.import_type(newChunkId, dataOptions, 'deletedProducts', 'products');
                                break;
                            case 'products':
                                this.import_type(newChunkId, dataOptions, 'products', 'orders');
                                break;
                            case 'orders':
                                this.import_type(newChunkId, dataOptions, 'orders', null);
                                break;
                            default:
                                return false;
                        }
                    });
                }

                function ajax_post_with_retry(url, data, callback) {
                    if(dataOptions.retryAttempts !== 0) {
                        jQuery.post(url, data, function(response) {
                            callback();
                        }).fail(function () {
                            dataOptions.retryAttempts -= 1;
                            setTimeout(function() {
                                this.ajax_post_with_retry(url, data, callback);
                            }, 5000);
                        })
                    } else {
                        dataOptions.retryAttempts = 3;
                        this.sync_chunk(data.chunkId + 1, data.importType, true);
                    }
                }

                function import_type(newChunkId, data, current, next) {
                    if (data[`${current}Chunks`] > 0) {
                        data.percentage = (100 / data[`${current}Chunks`]);
                    }
                    var hasMoreChunks = newChunkId < data[`${current}Chunks`];

                    if(hasMoreChunks) {
                        this.sync_chunk(newChunkId, data.importType, false);
                    } else {
                        if(current == 'orders') {
                            this.update_importing_message("<span style='color: green;'>" + 'Done! Please expect up to 30 minutes for your historical data to appear in Metrilo.' + "</span>", true);
                            jQuery('#metrilo_import_step').hide();
                        } else {
                            jQuery('#metrilo_import_step').text((`Importing ${next}`));
                            dataOptions.importType = next;
                            this.sync_chunk(0, dataOptions.importType, false);
                        }
                    }
                }

                function update_importing_message(message, show_loader) {
                    if (show_loader) {
                        jQuery('#metrilo_import_status').html(message);
                    }
                }

                sync_chunk(0, 'customers');
            </script>
            <strong id="metrilo_import_step"></strong>
            <strong id="metrilo_import_status"></strong>
        <?php else: ?>
            <a href="<?php echo admin_url('tools.php?page=metrilo-import&import=1') ?>" class="button"><strong>Import</strong></a>
        <?php endif; ?>
    </div>
    <br style="clear: both;" />
    <br />
</div>
<div style="color: #888; font-size: 11px; padding: 5px;">
    If you encounter any issues, let us know at <a href="mailto:support@metrilo.com">support@metrilo.com</a>. We'll be happy to assist you!
</div>
