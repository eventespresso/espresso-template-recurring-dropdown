<?php
/* ---- Default recurring-dropdown template ---- */
?>

<table width="100%">
	<tr>
		<th><?php _e('Event Name', 'event_espresso') ?></th>
		<th><?php _e('Venue', 'event_espresso') ?></th>
		<th><?php _e('Time', 'event_espresso') ?></th>
		<th><?php _e('Cost', 'event_espresso') ?></th>
		<th><?php _e('Register', 'event_espresso') ?></th>
	</tr>
	<?php
		foreach ($events as $event){
			$this_event_id			= $event->id;
			$member_only			= !empty($event->member_only) ? $event->member_only : '';
			$event_meta				= unserialize($event->event_meta);
			$externalURL 			= $event->externalURL;
			$registration_url 		= !empty($externalURL) ? $externalURL : espresso_reg_url($event->id);
			if ( has_filter( 'filter_hook_espresso_get_num_available_spaces' ) ){
				$open_spots		= apply_filters('filter_hook_espresso_get_num_available_spaces', $event->id); //Available in 3.1.37
			}else{
				$open_spots		= get_number_of_attendees_reg_limit($event->id, 'number_available_spaces');
			}
			$recurrence_id			= $event->recurrence_id;
			$allow_overflow			= $event->allow_overflow;
			$overflow_event_id		= $event->overflow_event_id;
			$externalURL 			= $event->externalURL;
			$registration_url 		= !empty($externalURL) ? $externalURL : espresso_reg_url($event->id);
			$event_status 			= event_espresso_get_status($event->id);

			/* group recurring events */
			$is_new_event_type = $recurrence_id == 0 || !isset($recurrence_ids_array[$recurrence_id]);

			if ($is_new_event_type){
				$events_type_index++;
				$events_of_same_type[$events_type_index] = array();
			}
			if (!isset($recurrence_ids_array[$recurrence_id]))
					$recurrence_ids_array[$recurrence_id] = $events_type_index;

			$event_data = array(
				'event_id'			=> $event->id,
				'event_name'		=> stripslashes_deep($event->event_name),
				'venue_title'		=> $event->venue_name,
				'start_time'		=> $event->start_time,
				'price'				=> $event->event_cost,
				'event_desc'		=> $event->event_desc,
				'start_date'		=> $event->start_date,
				'end_date'			=> $event->end_date,
				'reg_start_date'	=> $event->registration_start,
				'reg_end_date'		=> $event->registration_end,
				'reg_limit'			=> $event->reg_limit,
				'registration_url'	=> $registration_url,
				'recurrence_id'		=> $recurrence_id,
				'overflow_event_id'	=> $event->overflow_event_id,
				'allow_overflow'	=> $event->allow_overflow
			);

			if ($recurrence_id == 0) {
				array_push($events_of_same_type[$events_type_index], $event_data);
			} else {
				array_push($events_of_same_type[$recurrence_ids_array[$recurrence_id]], $event_data);
			}

			$last_recurrence_id = $recurrence_id;

		}
		foreach ($events_of_same_type as $events_group) {
			$first_event_instance	= $events_group[0];

			?>
			<tr id="event_data-<?php echo $first_event_instance['event_id']?>" class="event_data subpage_excerpt r event-data-display event-list-display">
				<td id="event_title-<?php echo $first_event_instance['event_id']?>" class="event_title"><?php echo stripslashes_deep($first_event_instance['event_name'])?></td>
				<td id="venue_title-<?php echo $first_event_instance['venue_title']?>" class="venue_title"><?php echo stripslashes_deep($first_event_instance['venue_title'])?></td>
				<td id="start_time-<?php echo $first_event_instance['start_time']?>" class="start_time"><?php echo stripslashes_deep($first_event_instance['start_time'])?></td>
				<td id="price-<?php echo $first_event_instance['price']?>" class="price"><?php echo $org_options['currency_symbol'].stripslashes_deep($first_event_instance['price'])?></td>
				<?php
				//Group the recurring events

				if (count($events_group) > 1){
			?>
					<td><input type="button" value="<?php echo $button_text; ?>" data-dropdown="#date_picker_<?php echo $first_event_instance['event_id']?>">
					<div class="dropdown-menu has-tip has-scroll" id="date_picker_<?php echo $first_event_instance['event_id']?>">
						<ul>
						<?php
							foreach ($events_group as $e){

								//$num_attendees = apply_filters('filter_hook_espresso_get_num_attendees', $e['event_id']);
								$num_attendees = get_number_of_attendees_reg_limit($e['event_id'], 'num_attendees');

								echo '<li>';

								if ($num_attendees >= $e['reg_limit']){
									echo '<span class="error">';
								}else{
									echo '<a href="'.$e['registration_url'].'">';
								}

								if ($e['start_date'] != $e['end_date']){
									echo event_date_display($e['start_date'], get_option('date_format')).'–'.event_date_display($e['end_date'], get_option('date_format'));
								}else{
									echo event_date_display($e['start_date'], get_option('date_format'));
								}


								//var_dump($e);
								if ($num_attendees >= $e['reg_limit']){
									echo ' - '.__('Sold Out', 'event_espresso').'</span>';
									if ($e['allow_overflow'] == 'Y'){
										$overflow_url = espresso_reg_url($e['overflow_event_id']);
										echo '[ <a class="waitlist" href="'.$overflow_url.'">'.__('Join Waiting List').'</a> ]';
									}
								}
								elseif (event_espresso_get_status($e['event_id']) == 'NOT_ACTIVE'){
									echo ' '.__('Closed', 'event_espresso').'</span>';

								}else{
									echo '</a>';
								}
								if($num_attendees < $e['reg_limit']) {
									$params = array(
										//REQUIRED, the id of the event that needs to be added to the cart
										'event_id' => $e['event_id'],
										//REQUIRED, Anchor of the link, can use text or image
										//'anchor' => __("Add to Cart", 'event_espresso'),
										'anchor' => '<img src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/cart_add.png" />',
										//REQUIRED, if not available at this point, use the next line before this array declaration
										// $event_name = get_event_field('event_name', EVENTS_DETAIL_TABLE, ' WHERE id = ' . $event_id);
										'event_name' => $e['event_name'],
										//OPTIONAL, will place this term before the link
										//'separator' => __(" or ", 'event_espresso')
									);

									$check_reg_date = strtotime($e['reg_start_date']);
									$check_reg_end_date = strtotime($e['reg_end_date']);
									$check_cur_date = strtotime( date('Y-m-d') );

									if($check_cur_date < $check_reg_date || $check_cur_date > $check_reg_end_date) {
										echo '<img style="float: right;" src="' . EVENT_ESPRESSO_PLUGINFULLURL . 'images/error.png" />';
									}
									else {
										echo $cart_link = event_espresso_cart_link($params);
									}

								}

								echo '</li>';
							}
						?>
						</ul>
					</div></td>
		<?php
				}else{
					//$num_attendees = apply_filters('filter_hook_espresso_get_num_attendees', $first_event_instance['event_id']);
					$num_attendees = get_number_of_attendees_reg_limit($first_event_instance['event_id'], 'num_attendees');
					if ($num_attendees >= $events_group[0]['reg_limit']){ ?>
						<td><p><span class="error"><?php _e('Sold Out', 'event_espresso'); ?></span>
						<?php
						if ($first_event_instance['allow_overflow'] == 'Y'){
							echo '[ <a href="'.espresso_reg_url($first_event_instance['overflow_event_id']).'">'.__('Join Waiting List').'</a> ]';
						}
						?>
						</p>
						</td>
		<?php
					}
					elseif (event_espresso_get_status($first_event_instance['event_id']) == 'NOT_ACTIVE'){ ?>
						<td class="event-links">
						<?php echo ' '.__('Closed', 'event_espresso').'</span>'; ?>
						</td>
					<?php
					}else{ ?>
						<td class="event-links"><a href="<?php echo $first_event_instance['registration_url']; ?>" title="<?php echo stripslashes_deep($first_event_instance['event_name'])?>">
						<?php _e('Register', 'event_espresso'); ?>
						</a>
						</td>
		<?php
					}

				}
	?>
			</tr>
	<?php
		}
	?>
</table>

<script>jQuery(".dropdown-menu").appendTo("body");</script>