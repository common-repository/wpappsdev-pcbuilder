<section class="wpadpcbu-pcbuilder alignwide">
	<?php do_action( 'wpadpcbu_builder_page_section_top', $components, $items ); ?>
	<?php if ( wpadpcbu_process()->builder->breadcrumb_enable() ) { ?>
	<div class="wpadpcbu-breadcrumb">
		<div class="wpadpcbu-row">
			<div class="wpadpcbu-half wpadpcbu-mobile-full wpadpcbu-align-center">
				<ul class="wpadpcbu-nav">
					<li><a href="<?php echo esc_url( site_url() ); ?>" title="<?php _e( 'Home', 'wpappsdev-pcbuilder' ); ?>"><i class="dashicons dashicons-admin-multisite"></i></a></li>
					<li><a class="bclink" data-page="builder" href="#"><?php echo esc_attr( wpadpcbu_process()->builder->menu_title() ); ?></a></li>
				</ul>
				<div class="clear"></div>
			</div>
			<div class="wpadpcbu-half wpadpcbu-mobile-full wpadpcbu-align-center wpadpcbu-justify-end">
				<p class="page-heading"><?php echo esc_attr( wpadpcbu_process()->builder->builder_title() ); ?></p>
			</div>
		</div>
	</div>
	<?php } ?>

	<?php if ( wpadpcbu_process()->builder->actions_enable() ) { ?>
	<div class="wpadpcbu-actions">
		<div class="wpadpcbu-row">
			<div class="wpadpcbu-half wpadpcbu-mobile-full">
				<div class="right-button">
					<?php
					foreach ( wpadpcbu_process()->builder->builder_actions() as $action_class => $action ) {
						echo sprintf( '<a class="pc-builder-button %s" href="%s" title="%s"><i class="%s"></i>%s</a>', esc_attr( $action_class ), esc_url( $action['href'] ), esc_attr( $action['name'] ), esc_attr( $action['icon-class'] ), esc_attr( $action['name'] ) );
					}
					?>
				</div>
			</div>
			<div class="wpadpcbu-half wpadpcbu-mobile-full wpadpcbu-justify-end">
				<div class="top-total-amount">
					<span class="amount"><?php echo wc_clean( $total ); ?></span>
				</div>
			</div>
		</div>
	</div>
	<?php } ?>

	<?php do_action( 'wpadpcbu_builder_page_before_table', $components, $items ); ?>

	<div class="wpadpcbu-component" id="wpadpcbu-component">
		<div class="wpadpcbu-component-table">
			<table class="table table-striped has-background table-responsive-sm" style="background-color: #ffff;">
				<thead>
					<tr>
						<?php foreach ( $columns as $key => $name ) { ?>
							<?php echo sprintf( '<th scope="col">%s</th>', esc_attr( $name ) ); ?>
						<?php } ?>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $components as $component ) { ?>
						<?php
							$key           = "CI{$component['term_id']}";
							$has_component = isset( $items[ $key ] ) ? 'has-component' : 'fixed-height';
						?>
						<tr id = "componentid-<?php echo wc_clean( $component['term_id'] ); ?>" class="component-item-row <?php echo wc_clean( $has_component ); ?>">
							<?php foreach ( $columns as $col_key => $name ) { ?>
								<?php if( $col_key == 'component') { ?>
									<th class="component-name" scope="row">
										<div class="component-name-div">
											<?php if( 'has-component' == $has_component ) { ?>
												<?php echo sprintf( '<img src="%s" class="" alt="" loading="lazy">', esc_url( wp_get_attachment_url( get_post_thumbnail_id( $items[ $key ]['id'] ) ) ) ); ?>
											<?php } else { ?>
												<?php echo isset( $component['image'] ) ? wp_kses( $component['image'], wpadpcbu_allowed_html() ) : ''; ?>
											<?php } ?>
											<div class="component-name-warp">
												<span><?php echo wc_clean( $component['name'] ); ?></span>
												<?php if( $component['required'] ) { ?>
													<span class="required-span">Required</span>
												<?php } ?>
											</div>
										</div>
									</th>
								<?php } elseif ( $col_key == 'image' ) { ?>
									<td class="component-product-image">
										<?php if ( isset( $items[ $key ] ) ) { ?>
											<div class="product-image">
												<a target="_blank" href="<?php echo esc_url( get_permalink( $items[ $key ]['id'] ) ); ?>">
												<?php echo sprintf( '<img width="80" height="80" src="%s" class="attachment-80x80 size-80x80" alt="" loading="lazy">', esc_url( wp_get_attachment_url( get_post_thumbnail_id( $items[ $key ]['id'] ) ) ) ); ?>
												</a>
											</div>
										<?php } ?>
									</td>
								<?php } elseif ( $col_key == 'name' ) { ?>
									<td class="component-product-name">
										<?php
										if ( isset( $items[ $key ] ) ) {
											echo wc_clean( $items[ $key ]['name'] );
										}
										?>
									</td>
								<?php } elseif ( $col_key == 'price' ) { ?>
									<td class="component-product-price">
										<?php
										if ( isset( $items[ $key ] ) ) {
											echo wc_clean( $items[ $key ]['fprice'] );
										}
										?>
									</td>
								<?php } elseif ( $col_key == 'action' ) { ?>
									<td class="component-product-action">
										<div class="component-actions">
											<?php if ( isset( $items[ $key ] ) ) { ?>
												<a class="wpadpcbu-search-product pc-builder-button" data-componentid = "<?php echo wc_clean( $component['term_id'] ); ?>" href="#" title="<?php esc_attr_e( 'Change Product', 'wpappsdev-pcbuilder' ); ?>">
													<i class="dashicons dashicons-update"></i>
													<span><?php esc_attr_e( 'Change', 'wpappsdev-pcbuilder' ); ?></span>
												</a>
												<a class="wpadpcbu-remove-product pc-builder-button" data-componentid = "<?php echo wc_clean( $component['term_id'] ); ?>" href="#" title="<?php esc_attr_e( 'Remove Product', 'wpappsdev-pcbuilder' ); ?>">
													<i class="dashicons dashicons-trash"></i>
													<span><?php esc_attr_e( 'Remove', 'wpappsdev-pcbuilder' ); ?></span>
												</a>
											<?php } else { ?>
												<a class="wpadpcbu-search-product pc-builder-button" data-componentid = "<?php echo wc_clean( $component['term_id'] ); ?>" href="#" title="<?php esc_attr_e( 'Search Product', 'wpappsdev-pcbuilder' ); ?>">
													<i class="dashicons dashicons-search"></i>
													<span><?php esc_attr_e( 'Choose', 'wpappsdev-pcbuilder' ); ?></span>
												</a>
											<?php } ?>
										</div>
									</td>
								<?php } else {
									do_action( 'wpadpcbu_component_item_row_data', $key, $component, $items );
								}
							}
							?>
						</tr>
					<?php } ?>
					<tr class="total-amount">
						<td colspan="2" class="amount-label text-right"><b><?php _e( 'Total:', 'wpappsdev-pcbuilder' ); ?></b></td>
						<td colspan="2" class="wpadpcbu-total"><?php echo wc_clean( $total ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	<?php do_action( 'wpadpcbu_builder_page_section_bottom', $components, $items ); ?>

</section>
