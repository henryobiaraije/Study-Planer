<?php

	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	class CardHelper {

		public static function get_next_dew_date_of_card( $card_id ) {
			//f' = max(1300, f - 200) | For again
			//f' = max(1300, f - 150) | For Hard
			//f' = f                  | For Good.
			//f' = max(1300, f + 150) | Easy

			// i1 = m0 * i is the new interval for failed reviews. It can have a minimum.
			// i2 = max(i + 1, (i + d/4) * 1.2 * m) is the new interval for hard reviews.
			// i3 = max(i2 + 1, (i + d/2) * (f / 1000) * m) is the new interval for ok reviews.
			// i4 = max(i3 + 1, (i + d) * (f / 1000) * m * m4) is the new interval for easy reviews.

		}
	}