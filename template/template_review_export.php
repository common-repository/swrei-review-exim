<div class="wrap woocommerce">	
	<div class="">
		<h3> <?php _e("Export Review and Review Meta","swrei-review-exim") ?> </h3>
		<div class="">
			<form method="post" id="reviewexport_mainform" action="" enctype="multipart/form-data">					
				<div class="form_fields">		
					<input type="submit" id="review_export_call" name="review_export_call" class="button button-primary" value="<?php _e("Export","swrei-review-exim") ?>">
					<?php wp_nonce_field( 'review_export_nonce', 'review_export_nonce' ); ?>
				</div>
			</form>			
		</div>
		<br>
		<hr>
		<br>
		<div class="">
			<h3><?php _e("Import Review and Review Meta","swrei-review-exim") ?> </h3>
			<form method="post" id="reviewimport_mainform" action="" enctype="multipart/form-data">					
				<span><?php _e("Select Exported CSV file","swrei-review-exim") ?> </span>		
				<div class="form_fields">					
					<input type="file" id="review_import_file" name="review_import_file" class="" required />
				</div>
				<div class="form_fields">
					<?php wp_nonce_field( 'review_import_nonce', 'review_import_nonce' ); ?>		
					<input type="submit" id="review_import_call" name="review_import_call" class="button button-primary" value="<?php _e("Import","swrei-review-exim") ?>" />
				</div>
			</form>
		</div>
	</div>	
</div>