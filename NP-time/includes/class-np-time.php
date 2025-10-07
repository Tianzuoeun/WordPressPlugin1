<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'np_time_modal_settings_defaults' ) ) {
	function np_time_modal_settings_defaults() {
		$defaults = [
			'modal_title'            => '🚚 选择配送设置',
			'step1_title'           => '第一步：请输入您的邮编',
			'postcode_placeholder'  => '例如： 10001',
			'step2_title'           => '第二步：选择配送方式',
			'date_label'            => '选择配送日期',
			'date_placeholder'      => '请选择配送日期',
			'date_empty_text'       => '请先输入邮编',
			'local_hint'            => '💡 本地配送区域，可选择具体日期',
			'local_title'           => '✅ 本地配送区域',
			'weekday_label'         => '选择配送时间',
			'weekday_placeholder'   => '请选择配送的时间',
			'weekday_empty_text'    => '请先输入邮编',
			'nonlocal_title'        => '🚛 非本地配送区域',
			'nonlocal_hint'         => '📅 非本地配送区域，请选择配送的时间',
			'nonlocal_times_label'  => '可配送时间：',
			'confirm_button_text'   => '确认',
			'loading_text'          => '🔍 正在查询邮编配送选项...',
			'invalid_postcode_text' => '❌ 该邮编暂不支持配送，请检查邮编或联系客服',
			'no_local_dates_text'   => '⚠️ 该本地邮编当前没有可选配送日期',
			'no_times_text'         => '⚠️ 该邮编暂无可配送时间',
			'network_error_text'    => '❌ 网络错误，请重试',
			'missing_date_alert'    => '请完整选择邮编与日期',
			'missing_weekday_alert' => '请完整选择邮编与时间',
			// 通用前端标签/按钮
			'label_postcode'        => '配送邮编：',
			'label_date'            => '配送日期：',
			'edit_button_text'      => '编辑',
			'not_selected_text'     => '未选择',
			// 小费相关（前端+后端）
			'tip_section_title'           => '添加小费',
			'tip_custom_button'           => '自定义小费',
			'tip_refuse_button'           => '残忍拒绝',
			'tip_input_placeholder'       => '输入金额',
			'tip_confirm_button'          => '确认',
			'tip_invalid_amount_alert'    => '请输入有效的小费金额',
			'tip_cancelled_feedback'      => '已取消小费',
			'tip_added_feedback_template'  => '小费 %s 已添加',
			'tip_fee_name'                => '小费',
			// 其他警告/提示
			'address_postcode_mismatch_alert' => '配送地址邮编需与配送设置邮编一致。请修改配送地址或点击“编辑”更新配送邮编。',
			'postcode_mismatch_updated_header' => '配送设置已更新，但发现以下邮编字段与配送邮编不一致：',
			'postcode_mismatch_delivery_label' => '配送邮编：',
			'postcode_mismatch_fix_advice'     => '请修改不一致的邮编字段。',
			'label_shipping_postcode'          => '收货地址邮编',
			'label_billing_postcode'           => '账单邮编',
			'postcode_mismatch_single_template'=> '配送邮编和%s不一致，请修改。%s需与配送设置邮编一致。',
			'save_failed_fallback'             => '保存失败',
			'coupon_button_text'        => '选择可用优惠券',
			'coupon_modal_title'        => '我的优惠券',
			'coupon_loading_text'       => '正在加载优惠券...',
			'coupon_empty_text'         => '暂无可用优惠券',
			'coupon_apply_button'       => '立即使用',
			'coupon_close_text'         => '关闭',
			'coupon_login_text'         => '登录后即可查看和使用您的优惠券。',
			'coupon_expiry_label'       => '有效期：',
			'coupon_min_label_format'   => '满 %s 可用',
			'coupon_copied_clipboard'   => '优惠码已复制到剪贴板，请在结账页粘贴并使用。',
			'coupon_copy_instruction'   => '请复制优惠码并在结账页面粘贴使用： ',
			'local_dates_count_format'  => '可选择 %s 个配送日期',
			// 产品配送日期相关
			'product_delivery_conflict_message' => '您选择的配送日期导致购物车中某些商品无法配送，是否继续并移除这些商品？',
			'product_delivery_conflict_items_header' => '需要移除的商品：',
			'product_delivery_remove_success' => '已成功移除不符合配送日期的商品',
			'product_delivery_remove_failed' => '移除商品失败，请重试',
			'product_delivery_remove_partial_failed' => '部分商品移除失败',
			'product_delivery_not_available_for_date' => '%s在您选择的配送日期（%s）不可配送，请重新选择配送日期或选择其他商品。',
			// 日期格式化相关多语言字符串
			'month_01'                  => '1月',
			'month_02'                  => '2月',
			'month_03'                  => '3月',
			'month_04'                  => '4月',
			'month_05'                  => '5月',
			'month_06'                  => '6月',
			'month_07'                  => '7月',
			'month_08'                  => '8月',
			'month_09'                  => '9月',
			'month_10'                  => '10月',
			'month_11'                  => '11月',
			'month_12'                  => '12月',
			'weekday_sun'               => '星期日',
			'weekday_mon'               => '星期一',
			'weekday_tue'               => '星期二',
			'weekday_wed'               => '星期三',
			'weekday_thu'               => '星期四',
			'weekday_fri'               => '星期五',
			'weekday_sat'               => '星期六',
			'date_weekday_format'       => '%s%s日-%s',
			// 后端与提示
			'invalid_choice_message'      => '邮编或所选日期/时间不支持配送',
			'invalid_tip_type_message'    => '无效的小费类型',
			'wc_notice_require_choice'    => '请先选择配送的日期与邮编。',
			'wc_notice_choice_invalid'    => '当前选择的邮编或日期/时间不再可用，请重新选择。',
			'checkout_postcode_mismatch'  => '配送邮编和账单邮编不一致，请修改。账单邮编（%s）需与配送设置邮编（%s）一致。',
			// 购物车/结账中项目名称（无冒号）
			'item_label_postcode'         => '配送邮编',
			'item_label_date'             => '配送日期',
			'fab_position'          => 'left',
			'fab_text'              => '配送设置',
			'fab_bg_color'          => '#007cba',
			'fab_text_color'        => '#ffffff',
			'fab_icon'              => '',
			'fab_icon_id'           => 0,
			// 按钮样式可配置项（用于弹窗确认按钮与优惠券使用按钮）
			'modal_button_bg'       => '#10b981',
			'modal_button_text_color'=> '#ffffff',
			'modal_button_border_radius'=> '10px',
			'modal_button_font_family'=> '',
			'modal_button_font_size'=> '14px',
			// 用户注册邮件模板
			'registration_email_subject' => '[%s] 账户创建：设置您的密码',
			'registration_email_content' => "您好，\n\n我们已基于您本次下单使用的邮箱创建了账户：\n\n用户名：%s\n设置密码链接：%s\n\n如果非您本人操作，请忽略此邮件。\n",
		];
		return apply_filters( 'np_time_modal_settings_defaults', $defaults );
	}
}

if ( ! function_exists( 'np_time_register_modal_strings' ) ) {
	function np_time_register_modal_strings( $settings ) {
		if ( ! is_array( $settings ) ) {
			return;
		}
		$defaults = np_time_modal_settings_defaults();
		$settings = wp_parse_args( $settings, $defaults );
		foreach ( $settings as $key => $value ) {
			if ( ! is_string( $value ) ) {
				continue;
			}
			// WPML支持
			if ( function_exists( 'icl_register_string' ) ) {
				icl_register_string( 'np-time', 'modal_' . $key, $value );
			} else {
				do_action( 'wpml_register_single_string', 'np-time', 'modal_' . $key, $value );
			}
			// TranslatePress支持
			if ( function_exists( 'trp_register_string' ) ) {
				trp_register_string( $value, 'np-time', 'modal_' . $key, 'modal_strings' );
			} else {
				do_action( 'trp_register_string', $value, 'np-time', 'modal_' . $key, 'modal_strings' );
			}
			// GTranslate支持 - 注册字符串到翻译缓存
			if ( function_exists( 'gtranslate_t' ) ) {
				// 通过调用gtranslate_t来确保字符串被添加到翻译队列
				gtranslate_t( $value );
			}
		}
	}
}

if ( ! function_exists( 'np_time_register_modal_strings_init' ) ) {
	function np_time_register_modal_strings_init() {
		$settings = get_option( 'np_time_modal_settings', [] );
		np_time_register_modal_strings( $settings );
		// 同时注册用户注册邮件字符串
		np_time_register_registration_strings();
	}
	add_action( 'init', 'np_time_register_modal_strings_init' );
}

if ( ! function_exists( 'np_time_register_registration_strings' ) ) {
	function np_time_register_registration_strings() {
		$registration_settings = get_option( 'np_time_registration_settings', [] );
		$defaults = [
			'email_subject' => '[%s] 账户创建：设置您的密码',
			'email_content' => "您好，\n\n我们已基于您本次下单使用的邮箱创建了账户：\n\n用户名：%s\n设置密码链接：%s\n\n如果非您本人操作，请忽略此邮件。\n",
		];
		$settings = wp_parse_args( $registration_settings, $defaults );
		
		$strings_to_register = [
			'email_subject' => $settings['email_subject'],
			'email_content' => $settings['email_content'],
		];
		
		foreach ( $strings_to_register as $key => $value ) {
			if ( ! is_string( $value ) || empty( $value ) ) {
				continue;
			}
			// WPML支持
			if ( function_exists( 'icl_register_string' ) ) {
				icl_register_string( 'np-time', 'registration_' . $key, $value );
			} else {
				do_action( 'wpml_register_single_string', 'np-time', 'registration_' . $key, $value );
			}
			// TranslatePress支持
			if ( function_exists( 'trp_register_string' ) ) {
				trp_register_string( $value, 'np-time', 'registration_' . $key, 'registration_emails' );
			} else {
				do_action( 'trp_register_string', $value, 'np-time', 'registration_' . $key, 'registration_emails' );
			}
			// GTranslate支持
			if ( function_exists( 'gtranslate_t' ) ) {
				gtranslate_t( $value );
			}
		}
	}
}

if ( ! function_exists( 'np_time_get_translated_registration_strings' ) ) {
	function np_time_get_translated_registration_strings() {
		$registration_settings = get_option( 'np_time_registration_settings', [] );
		$defaults = [
			'email_subject' => '[%s] 账户创建：设置您的密码',
			'email_content' => "您好，\n\n我们已基于您本次下单使用的邮箱创建了账户：\n\n用户名：%s\n设置密码链接：%s\n\n如果非您本人操作，请忽略此邮件。\n",
		];
		$settings = wp_parse_args( $registration_settings, $defaults );
		
		// 应用翻译
		$translated = [];
		foreach ( ['email_subject', 'email_content'] as $key ) {
			$value = $settings[$key];
			
			// WPML翻译
			if ( function_exists( 'icl_t' ) ) {
				$value = icl_t( 'np-time', 'registration_' . $key, $value );
			} elseif ( function_exists( 'apply_filters' ) ) {
				$value = apply_filters( 'wpml_translate_single_string', $value, 'np-time', 'registration_' . $key );
			}
			
			// TranslatePress翻译
			if ( function_exists( 'trp_translate' ) ) {
				$value = trp_translate( $value, 'np-time', 'registration_' . $key );
			} elseif ( function_exists( 'apply_filters' ) ) {
				$value = apply_filters( 'trp_translate', $value, 'np-time', 'registration_' . $key );
			}
			
			// GTranslate翻译
			if ( function_exists( 'gtranslate_t' ) ) {
				$value = gtranslate_t( $value );
			}
			
			$translated[$key] = $value;
		}
		
		return $translated;
	}
}

// Frontend UI, AJAX and WooCommerce integration methods
trait NP_Time_Frontend {
	protected function get_modal_settings() {
		$settings = wp_parse_args( get_option( 'np_time_modal_settings', [] ), np_time_modal_settings_defaults() );
		foreach ( $settings as $key => $value ) {
			if ( ! is_string( $value ) ) {
				continue;
			}
			// Always apply translation filters; if filter is not present, apply_filters will return original value.
			$value = apply_filters( 'wpml_translate_single_string', $value, 'np-time', 'modal_' . $key );
			$value = apply_filters( 'trp_translate', $value, 'np-time', 'modal_' . $key );
			$settings[ $key ] = $value;
		}
		return $settings;
	}

	/**
	 * 检测是否处于购物车/结账相关的 WooCommerce AJAX 请求中
	 * 用于在 AJAX 刷新订单摘要时也抑制逐商品显示配送信息
	 */
	protected function is_cart_or_checkout_ajax() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$action = isset( $_REQUEST['wc-ajax'] ) ? (string) $_REQUEST['wc-ajax'] : '';
			if ( $action ) {
				$targets = [
					'get_refreshed_fragments',
					'update_order_review',
					'apply_coupon',
					'remove_coupon',
					'update_shipping_method',
					'checkout',
				];
				return in_array( $action, $targets, true );
			}
		}
		return false;
	}

	/**
	 * 是否处于 Woo Blocks Store API 场景（用于购物车/结账）
	 */
	protected function is_store_api_context() {
		if ( function_exists( 'WC' ) && WC()->cart ) {
			// WooCommerce 9+ 在 Store API 下会将上下文标记为 'store-api'
			if ( property_exists( WC()->cart, 'cart_context' ) && 'store-api' === WC()->cart->cart_context ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * 检测是否处于用户仪表盘的订单列表页面（不包括订单详情页面）
	 */
	protected function is_my_account_orders() {
		// 检查基本函数是否可用
		if ( ! function_exists( 'is_account_page' ) ) {
			return false;
		}
		
		// 必须在我的账户页面
		if ( ! is_account_page() ) {
			return false;
		}
		
		// 检查URL路径来判断是否为订单列表页面
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
		
		// 如果URL包含view-order，说明是订单详情页面，应该显示配送信息
		if ( strpos( $request_uri, '/view-order/' ) !== false ) {
			return false;
		}
		
		// 如果URL包含orders但不包含view-order，说明是订单列表页面，应该隐藏配送信息
		if ( strpos( $request_uri, '/orders' ) !== false ) {
			return true;
		}
		
		// 使用WooCommerce函数作为后备检查
		if ( function_exists( 'is_wc_endpoint_url' ) ) {
			// 明确检查是否在订单端点，但不是查看订单端点
			return is_wc_endpoint_url( 'orders' ) && ! is_wc_endpoint_url( 'view-order' );
		}
		
		return false;
	}

    public function enqueue_assets() {
		$css_path = NP_TIME_PATH . 'assets/np-time.css';
		$js_path  = NP_TIME_PATH . 'assets/np-time.js';
		$css_ver  = file_exists( $css_path ) ? filemtime( $css_path ) : NP_TIME_VERSION;
		$js_ver   = file_exists( $js_path ) ? filemtime( $js_path ) : NP_TIME_VERSION;
		wp_register_style( 'np-time', NP_TIME_URL . 'assets/np-time.css', [], $css_ver );
		wp_register_script( 'np-time', NP_TIME_URL . 'assets/np-time.js', [ 'jquery' ], $js_ver, true );
		
		// 在用户仪表盘页面添加CSS来隐藏配送元数据
		if ( function_exists( 'is_account_page' ) && is_account_page() ) {
			wp_add_inline_style( 'np-time', '
				.woocommerce-account .wc-item-meta { display: none !important; }
				body.woocommerce-account .wc-item-meta { display: none !important; }
			' );
		}
		$settings = wp_parse_args( get_option( 'np_time_settings', [] ), [ 'gate_entire_site' => 1 ] );
        $tip_enabled = (int) get_option( 'np_time_tip_enabled', 1 );
		$valid_choice = 0;
		$choice_payload = null;
		if ( isset( $_COOKIE['np_time_choice'] ) ) {
			$data = json_decode( wp_unslash( $_COOKIE['np_time_choice'] ), true );
			if ( is_array( $data ) && isset( $data['postcode'], $data['date'] ) && NP_Time_Rules::validate_choice( $data['postcode'], $data['date'] ) ) {
				$valid_choice = 1;
				$choice_payload = [ 'postcode' => (string) $data['postcode'], 'date' => (string) $data['date'] ];
			}
		}
		$modal_settings = $this->get_modal_settings();
		// 获取小费配置
		$tip_options = get_option( 'np_time_tip_options', [ '$1.00', '$3.00', '$5.00' ] );
		$saved_tip = isset( $_COOKIE['np_time_tip'] ) ? json_decode( stripslashes( $_COOKIE['np_time_tip'] ), true ) : null;

        // 结账页默认小费为“拒绝”，每次进入结账页重置（不影响用户之后的主动选择）
        if ( $tip_enabled && function_exists( 'is_checkout' ) && is_checkout() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
            $now = current_time( 'timestamp' );
            $midnight = strtotime( 'tomorrow midnight', $now );
            $default_tip = [ 'type' => 'refuse', 'amount' => '0.00', 'timestamp' => $now ];
            setcookie( 'np_time_tip', wp_json_encode( $default_tip ), $midnight, COOKIEPATH, COOKIE_DOMAIN );
            $_COOKIE['np_time_tip'] = wp_json_encode( $default_tip );
            $saved_tip = $default_tip;
        }
        
		wp_localize_script( 'np-time', 'NP_TIME_DATA', [
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'np_time_nonce' ),
			'gate'    => (int) $settings['gate_entire_site'],
			'hasChoice' => $valid_choice,
			'choice' => $choice_payload,
			'tipEnabled' => $tip_enabled,
			'isLoggedIn' => is_user_logged_in() ? 1 : 0,
			'tipOptions' => $tip_options,
            'selectedTip' => $saved_tip ? ( $saved_tip['type'] === 'refuse' ? 'refuse' : $saved_tip['amount'] ) : 'refuse',
            'modalStrings' => $modal_settings,
        ] );
        wp_enqueue_style( 'np-time' );
        wp_enqueue_script( 'np-time' );
    }

	public function render_modal() {
		$modal = $this->get_modal_settings();
		// Build inline CSS variables for modal button styling
		$style_vars = [];
		if ( ! empty( $modal['modal_button_bg'] ) ) {
			$style_vars[] = '--np-time-modal-btn-bg:' . esc_attr( $modal['modal_button_bg'] );
		}
		if ( ! empty( $modal['modal_button_text_color'] ) ) {
			$style_vars[] = '--np-time-modal-btn-color:' . esc_attr( $modal['modal_button_text_color'] );
		}
		if ( ! empty( $modal['modal_button_border_radius'] ) ) {
			$style_vars[] = '--np-time-modal-btn-radius:' . esc_attr( $modal['modal_button_border_radius'] );
		}
		if ( ! empty( $modal['modal_button_font_family'] ) ) {
			$style_vars[] = '--np-time-modal-btn-font-family:' . esc_attr( $modal['modal_button_font_family'] );
		}
		if ( ! empty( $modal['modal_button_font_size'] ) ) {
			$style_vars[] = '--np-time-modal-btn-font-size:' . esc_attr( $modal['modal_button_font_size'] );
		}
		$style_attr = $style_vars ? ' style="' . esc_attr( implode( ';', $style_vars ) ) . '"' : '';
		echo '<div id="np-time-modal" class="np-time-modal" aria-hidden="true"' . $style_attr . '>';
		echo '<div class="np-time-dialog">';
		echo '<h3>' . esc_html( $modal['modal_title'] ) . '</h3>';
		echo '<div class="np-time-form">';
		echo '<div class="np-time-step">';
		echo '<label><strong>' . esc_html( $modal['step1_title'] ) . '</strong><input type="text" id="np-time-postcode" placeholder="' . esc_attr( $modal['postcode_placeholder'] ) . '" style="width:100%;margin-top:5px;"></label>';
		echo '</div>';
		echo '<div id="np-time-step2" class="np-time-step" style="display:none;margin-top:15px;">';
		echo '<strong>' . esc_html( $modal['step2_title'] ) . '</strong>';
		echo '<div id="np-time-date-wrap" style="display:none;margin-top:8px;">';
		echo '<label>' . esc_html( $modal['date_label'] ) . ' <select id="np-time-date" disabled style="width:100%;margin-top:3px;"><option value="">' . esc_html( $modal['date_empty_text'] ) . '</option></select></label>';
		echo '<div class="np-time-hint">' . wp_kses_post( $modal['local_hint'] ) . '</div>';
		echo '</div>';
		echo '<div id="np-time-weekday-wrap" style="display:none;margin-top:8px;">';
		echo '<label>' . esc_html( $modal['weekday_label'] ) . ' <select id="np-time-weekday" disabled style="width:100%;margin-top:3px;"><option value="">' . esc_html( $modal['weekday_empty_text'] ) . '</option></select></label>';
		echo '<div class="np-time-hint">' . wp_kses_post( $modal['nonlocal_hint'] ) . '</div>';
		echo '</div>';
		echo '</div>';
		echo '<div id="np-time-info" class="np-time-info" aria-live="polite"></div>';
		echo '<div class="np-time-actions"><button id="np-time-continue" class="button button-primary">' . esc_html( $modal['confirm_button_text'] ) . '</button></div>';
		echo '</div></div></div>';
	}

	public function render_fab() {
		// 在结账页面不显示FAB按钮
		if ( function_exists( 'is_checkout' ) && is_checkout() ) {
			return;
		}
		
		$modal = $this->get_modal_settings();
		$position = ( isset( $modal['fab_position'] ) && 'right' === $modal['fab_position'] ) ? 'right' : 'left';
		$classes = 'np-time-fab np-time-fab--' . $position;
		$style_parts = [];
		if ( ! empty( $modal['fab_bg_color'] ) ) {
			$style_parts[] = '--np-time-fab-bg:' . esc_attr( $modal['fab_bg_color'] );
			$style_parts[] = 'background:' . esc_attr( $modal['fab_bg_color'] );
		}
		if ( ! empty( $modal['fab_text_color'] ) ) {
			$style_parts[] = '--np-time-fab-color:' . esc_attr( $modal['fab_text_color'] );
			$style_parts[] = 'color:' . esc_attr( $modal['fab_text_color'] );
		}
		if ( 'right' === $position ) {
			$style_parts[] = 'right:16px';
			$style_parts[] = 'left:auto';
		} else {
			$style_parts[] = 'left:16px';
			$style_parts[] = 'right:auto';
		}
		$style = $style_parts ? ' style="' . esc_attr( implode( ';', $style_parts ) ) . '"' : '';
		$has_icon = ! empty( $modal['fab_icon'] );
		if ( $has_icon ) {
			$classes .= ' has-icon';
		}
		echo '<button id="np-time-fab" class="' . esc_attr( $classes ) . '" aria-label="' . esc_attr( $modal['fab_text'] ) . '"' . $style . '>';
		if ( $has_icon ) {
			echo '<span class="np-time-fab-icon"><img src="' . esc_url( $modal['fab_icon'] ) . '" alt="" /></span>';
		}
		echo '<span class="np-time-fab-text">' . esc_html( $modal['fab_text'] ) . '</span>';
		echo '</button>';
	}

	public function ajax_get_options() {
		check_ajax_referer( 'np_time_nonce', 'nonce' );
		$postcode = sanitize_text_field( $_POST['postcode'] ?? '' );
		$rule = NP_Time_Rules::match_postcode_rule( $postcode );
		$local = NP_Time_Rules::is_local_delivery( $postcode );
		$days  = isset( $rule['daysOfWeek'] ) ? array_values( array_map( 'intval', (array) $rule['daysOfWeek'] ) ) : [];
		// 本地邮编：返回“全部未来可选日期”；非本地：按星期几规则计算可选日期
		$window = (int) NP_Time_Rules::get_window_days();
		$dates = $local ? NP_Time_Rules::build_available_dates( [ 'windowDays' => $window ] ) : NP_Time_Rules::build_available_dates( $rule );
		wp_send_json_success( [ 'dates' => $dates, 'daysOfWeek' => $days, 'local' => $local ] );
	}

	public function ajax_save_choice() {
		check_ajax_referer( 'np_time_nonce', 'nonce' );
		$postcode = sanitize_text_field( $_POST['postcode'] ?? '' );
		$date = sanitize_text_field( $_POST['date'] ?? '' );
		$weekday = isset( $_POST['weekday'] ) ? intval( $_POST['weekday'] ) : -1; // 0-6
		// 如果没有传入 date 而传了 weekday，则从明天开始选择最近的该星期几
		if ( empty( $date ) && $weekday >= 0 && $weekday <= 6 ) {
			$now = current_time( 'timestamp' );
			$todayDow = intval( date_i18n( 'w', $now ) );
			$offset = ( ( $weekday - $todayDow + 7 ) % 7 );
			// 确保始终从明天开始，如果计算结果是今天，则推到下一周
			if ( $offset === 0 ) { $offset = 7; }
			// 如果offset小于1，说明计算有误，强制从明天开始
			if ( $offset < 1 ) { $offset = 1; }
			$target = strtotime( "+$offset day", $now );
			$date = date_i18n( 'Y-m-d', $target );
		}
		if ( ! NP_Time_Rules::validate_choice( $postcode, $date ) ) {
			$modal = $this->get_modal_settings();
			$msg = isset( $modal['invalid_choice_message'] ) ? $modal['invalid_choice_message'] : '邮编或所选日期/时间不支持配送';
			wp_send_json_error( [ 'message' => $msg ], 400 );
		}
		
		// 检查购物车中是否有产品在新的配送日期下不可用
		$items_to_remove = NP_Time_Rules::get_cart_items_to_remove_for_delivery_date( $date );
		
		$choice = [ 'postcode' => $postcode, 'date' => $date ];
		$now = current_time( 'timestamp' );
		$midnight = strtotime( 'tomorrow midnight', $now );
		setcookie( 'np_time_choice', wp_json_encode( $choice ), $midnight, COOKIEPATH, COOKIE_DOMAIN );
		$_COOKIE['np_time_choice'] = wp_json_encode( $choice );
		
		// 如果有需要移除的产品，返回警告信息
		if ( ! empty( $items_to_remove ) ) {
			$modal = $this->get_modal_settings();
			$conflict_message = isset( $modal['product_delivery_conflict_message'] ) ? $modal['product_delivery_conflict_message'] : '您选择的配送日期导致购物车中某些商品无法配送，是否继续并移除这些商品？';
			$response = [
				'choice' => $choice,
				'cart_conflicts' => $items_to_remove,
				'message' => $conflict_message
			];
			wp_send_json_success( $response );
		}
		
		wp_send_json_success( $choice );
	}

	public function ajax_save_tip() {
		check_ajax_referer( 'np_time_nonce', 'nonce' );
		// 小费功能关闭时，强制返回拒绝状态，避免前端报错
		if ( ! (int) get_option( 'np_time_tip_enabled', 1 ) ) {
			$now = current_time( 'timestamp' );
			$midnight = strtotime( 'tomorrow midnight', $now );
			$tip_data = [ 'type' => 'refuse', 'amount' => '0.00', 'timestamp' => $now ];
			setcookie( 'np_time_tip', wp_json_encode( $tip_data ), $midnight, COOKIEPATH, COOKIE_DOMAIN );
			$_COOKIE['np_time_tip'] = wp_json_encode( $tip_data );
			wp_send_json_success( $tip_data );
		}

    $tip_type = sanitize_text_field( $_POST['tip_type'] ?? '' );
    $tip_amount = sanitize_text_field( $_POST['tip_amount'] ?? '' );
		
		// 验证小费类型
		if ( ! in_array( $tip_type, [ 'preset', 'custom', 'refuse' ], true ) ) {
			$modal = $this->get_modal_settings();
			$msg = isset( $modal['invalid_tip_type_message'] ) ? $modal['invalid_tip_type_message'] : '无效的小费类型';
			wp_send_json_error( [ 'message' => $msg ], 400 );
		}
		
		// 保存小费选择到会话或cookie
		$tip_data = [
			'type' => $tip_type,
			'amount' => $tip_amount,
			'timestamp' => current_time( 'timestamp' )
		];
		
		// 保存到cookie，有效期到明天
		$now = current_time( 'timestamp' );
		$midnight = strtotime( 'tomorrow midnight', $now );
		setcookie( 'np_time_tip', wp_json_encode( $tip_data ), $midnight, COOKIEPATH, COOKIE_DOMAIN );
		$_COOKIE['np_time_tip'] = wp_json_encode( $tip_data );
		
		// 如果在WooCommerce环境中，添加小费到购物车
		if ( class_exists( 'WooCommerce' ) && WC()->cart ) {
			$this->add_tip_to_cart( $tip_type, $tip_amount );
		}
		
		wp_send_json_success( $tip_data );
	}

	public function ajax_get_user_coupons() {
		check_ajax_referer( 'np_time_nonce', 'nonce' );
		
		$user_id = is_user_logged_in() ? get_current_user_id() : 0;
		$email = '';
		
		// 获取用户邮箱
		if ( function_exists( 'WC' ) && WC()->customer ) {
			$email = (string) WC()->customer->get_billing_email();
		}
		if ( ! $email && is_user_logged_in() ) {
			$user = wp_get_current_user();
			$email = $user ? (string) $user->user_email : '';
		}
		
		// 如果没有邮箱，直接返回空
		if ( ! $email ) {
			wp_send_json_success( [ 'coupons' => [] ] );
		}
		
		$coupons = [];
		$seen = [];
		
		if ( ! class_exists( 'WC_Coupon' ) ) {
			wp_send_json_success( [ 'coupons' => [] ] );
		}

		// 确保购物车已加载
		if ( function_exists( 'wc_load_cart' ) ) {
			wc_load_cart();
		}
		if ( function_exists( 'WC' ) && WC()->cart instanceof WC_Cart ) {
			WC()->cart->calculate_totals();
		}

		// 获取当前购物车小计
		$cart_subtotal = 0;
		if ( function_exists( 'WC' ) && WC()->cart ) {
			$cart_subtotal = WC()->cart->get_subtotal();
		}

		// 获取所有优惠券
		$all_coupons = [];
		if ( function_exists( 'wc_get_coupons' ) ) {
			try {
				$all_coupons = wc_get_coupons( [ 
					'limit' => -1, 
					'orderby' => 'date', 
					'order' => 'DESC' 
				] );
			} catch ( Exception $e ) {
				$all_coupons = [];
			}
		} else {
			// 备用方案：直接查询优惠券
			$posts = get_posts( [ 
				'post_type' => 'shop_coupon', 
				'posts_per_page' => -1, 
				'post_status' => 'publish' 
			] );
			if ( $posts ) {
				foreach ( $posts as $p ) {
					try {
						$all_coupons[] = new WC_Coupon( $p->ID );
					} catch ( Exception $e ) {
						// 忽略错误
					}
				}
			}
		}

		// 创建折扣对象用于验证
		$discounts = null;
		if ( class_exists( 'WC_Discounts' ) && function_exists( 'WC' ) && WC()->cart instanceof WC_Cart ) {
			$discounts = new WC_Discounts( WC()->cart );
		}

		// 过滤优惠券
		foreach ( $all_coupons as $coupon ) {
			if ( ! $coupon instanceof WC_Coupon ) {
				continue;
			}
			
			$code = $coupon->get_code();
			if ( ! $code || isset( $seen[ $code ] ) ) {
				continue;
			}
			
			// 关键修改：只显示在 allowed emails 中包含用户邮箱的优惠券
			$email_restrictions = $coupon->get_email_restrictions();
			
			// 如果没有设置邮箱限制，跳过此优惠券
			if ( empty( $email_restrictions ) ) {
				continue;
			}
			
			// 检查用户邮箱是否在允许列表中
			$email_allowed = false;
			$user_email_lower = strtolower( $email );
			foreach ( $email_restrictions as $allowed_email ) {
				$allowed_email_lower = strtolower( trim( $allowed_email ) );
				
				// 支持通配符匹配（如 *@domain.com）
				if ( strpos( $allowed_email_lower, '*' ) !== false ) {
					$pattern = str_replace( 
						[ '.', '*' ], 
						[ '\.', '.*' ], 
						$allowed_email_lower 
					);
					if ( preg_match( '/^' . $pattern . '$/i', $user_email_lower ) ) {
						$email_allowed = true;
						break;
					}
				} elseif ( $allowed_email_lower === $user_email_lower ) {
					$email_allowed = true;
					break;
				}
			}
			
			// 如果邮箱不在允许列表中，跳过
			if ( ! $email_allowed ) {
				continue;
			}
			
			// 检查其他限制条件
			// 检查使用次数限制
			if ( $coupon->get_usage_limit() > 0 && 
				$coupon->get_usage_count() >= $coupon->get_usage_limit() ) {
				continue;
			}
			
			// 检查每个用户的使用限制
			if ( $coupon->get_usage_limit_per_user() > 0 && $user_id ) {
				$user_usage = $this->get_coupon_usage_for_user( $coupon, $user_id, $email );
				if ( $user_usage >= $coupon->get_usage_limit_per_user() ) {
					continue;
				}
			}
			
			// 检查过期日期
			$date_expires = $coupon->get_date_expires();
			if ( $date_expires && $date_expires->getTimestamp() < current_time( 'timestamp', true ) ) {
				continue;
			}

			// 准备优惠券数据
			$payload = $this->prepare_coupon_payload( $coupon );
			if ( empty( $payload ) ) {
				continue;
			}

			// 添加是否可用标记
			$min_amount = $coupon->get_minimum_amount();
			$is_usable = true;
			$unusable_reason = '';

			// 检查最低消费金额
			if ( $min_amount > 0 && $cart_subtotal < $min_amount ) {
				$is_usable = false;
				$unusable_reason = sprintf( '差 %s 可用', 
					html_entity_decode( wp_strip_all_tags( wc_price( $min_amount - $cart_subtotal ) ) ) 
				);
			}

			// 使用 WC_Discounts 进行其他验证
			if ( $is_usable && class_exists( 'WC_Discounts' ) && WC()->cart ) {
				$discounts = new WC_Discounts( WC()->cart );
				try {
					$result = $discounts->is_coupon_valid( $coupon );
					if ( is_wp_error( $result ) ) {
						// 如果错误不是因为最低金额，则标记为不可用
						$error_code = $result->get_error_code();
						if ( $error_code !== 'invalid_coupon_minimum_amount' ) {
							$is_usable = false;
							$unusable_reason = $result->get_error_message();
						}
					}
				} catch ( Exception $e ) {
					// 忽略验证错误，保持显示
				}
			}

			// 添加可用性信息到返回数据
			$payload['is_usable'] = $is_usable;
			$payload['unusable_reason'] = $unusable_reason;
			$payload['cart_subtotal'] = $cart_subtotal;
			
			$seen[ $code ] = true;
			$coupons[] = $payload;
		}
		
		wp_send_json_success( [ 
			'coupons' => $coupons,
			'cart_subtotal' => $cart_subtotal 
		] );
	}

	public function ajax_remove_cart_conflicts() {
		check_ajax_referer( 'np_time_nonce', 'nonce' );
		
		$postcode = sanitize_text_field( $_POST['postcode'] ?? '' );
		$date = sanitize_text_field( $_POST['date'] ?? '' );
		$cart_keys = isset( $_POST['cart_keys'] ) ? (array) $_POST['cart_keys'] : [];
		
		if ( ! $postcode || ! $date ) {
			wp_send_json_error( [ 'message' => '缺少必要参数' ], 400 );
		}
		
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			wp_send_json_error( [ 'message' => '购物车不可用' ], 400 );
		}
		
		$removed_items = [];
		$failed_removals = [];
		
		foreach ( $cart_keys as $cart_key ) {
			$cart_key = sanitize_text_field( $cart_key );
			$cart_item = WC()->cart->get_cart_item( $cart_key );
			
			if ( $cart_item ) {
				$product = wc_get_product( $cart_item['product_id'] );
				$removed_items[] = [
					'name' => $product ? $product->get_name() : '未知商品',
					'quantity' => $cart_item['quantity']
				];
				
				// 移除购物车项
				if ( ! WC()->cart->remove_cart_item( $cart_key ) ) {
					$failed_removals[] = $cart_key;
				}
			}
		}
		
		// 保存新的配送选择
		$choice = [ 'postcode' => $postcode, 'date' => $date ];
		$now = current_time( 'timestamp' );
		$midnight = strtotime( 'tomorrow midnight', $now );
		setcookie( 'np_time_choice', wp_json_encode( $choice ), $midnight, COOKIEPATH, COOKIE_DOMAIN );
		$_COOKIE['np_time_choice'] = wp_json_encode( $choice );
		
		if ( ! empty( $failed_removals ) ) {
			$modal = $this->get_modal_settings();
			$error_message = isset( $modal['product_delivery_remove_partial_failed'] ) ? $modal['product_delivery_remove_partial_failed'] : '部分商品移除失败';
			wp_send_json_error( [
				'message' => $error_message,
				'removed' => $removed_items,
				'failed' => $failed_removals
			], 500 );
		}
		
		$modal = $this->get_modal_settings();
		$success_message = isset( $modal['product_delivery_remove_success'] ) ? $modal['product_delivery_remove_success'] : '已成功移除不符合配送日期的商品';
		
		wp_send_json_success( [
			'choice' => $choice,
			'removed_items' => $removed_items,
			'message' => $success_message
		] );
	}

	protected function get_coupon_usage_for_user( WC_Coupon $coupon, $user_id, $email ) {
		$counts = 0;
		$used_by = $coupon->get_used_by();
		if ( $used_by ) {
			$lower_email = $email ? strtolower( $email ) : '';
			foreach ( $used_by as $used ) {
				if ( (int) $used === (int) $user_id && $user_id ) {
					$counts++;
				} elseif ( $lower_email && strtolower( $used ) === $lower_email ) {
					$counts++;
				}
			}
		}
		return $counts;
	}

	protected function prepare_coupon_payload( $coupon ) {
		if ( ! $coupon instanceof WC_Coupon ) {
			return [];
		}
		$amount_display = $this->format_coupon_amount_for_display( $coupon );
		$description    = wp_strip_all_tags( $coupon->get_description() );
		$expiry         = '';
		$date_expires   = $coupon->get_date_expires();
		if ( $date_expires ) {
			$expiry = $date_expires->date_i18n( get_option( 'date_format' ) );
		}
		$min_raw      = $coupon->get_minimum_amount();
		$max_raw      = $coupon->get_maximum_amount();
		$min_display  = $min_raw ? html_entity_decode( wp_strip_all_tags( wc_price( $min_raw ) ) ) : '';
		$max_display  = $max_raw ? html_entity_decode( wp_strip_all_tags( wc_price( $max_raw ) ) ) : '';
		return [
			'code'           => $coupon->get_code(),
			'amount_display' => $amount_display,
			'amount_raw'     => $coupon->get_amount(),
			'amount_type'    => $coupon->get_discount_type(),
			'description'    => $description,
			'expiry'         => $expiry,
			'minimum_amount_raw' => $min_raw,
			'minimum_amount_display' => $min_display,
			'maximum_amount_raw' => $max_raw,
			'maximum_amount_display' => $max_display,
		];
	}

	protected function format_coupon_amount_for_display( $coupon ) {
		if ( ! $coupon instanceof WC_Coupon ) {
			return '';
		}
		$amount = $coupon->get_amount();
		$type   = $coupon->get_discount_type();
		if ( in_array( $type, [ 'percent', 'percent_product' ], true ) ) {
			return rtrim( rtrim( wc_format_decimal( $amount, 2 ), '0' ), '.' ) . '%';
		}
		return html_entity_decode( wp_strip_all_tags( wc_price( $amount ) ) );
	}

	private function add_tip_to_cart( $tip_type, $tip_amount ) {
		// 移除之前的小费
		$this->remove_tip_from_cart();
		
		if ( $tip_type === 'refuse' || empty( $tip_amount ) || $tip_amount === '$0.00' ) {
			return;
		}
		
		// 解析金额
		$amount = floatval( str_replace( '$', '', $tip_amount ) );
		if ( $amount > 0 ) {
			// 添加小费作为费用
			$modal = $this->get_modal_settings();
			$label = isset( $modal['tip_fee_name'] ) ? $modal['tip_fee_name'] : '小费';
			WC()->cart->add_fee( $label, $amount );
		}
	}

	private function remove_tip_from_cart() {
		// 移除之前添加的小费费用
		if ( WC()->cart && WC()->cart->fees_api() ) {
			$modal = $this->get_modal_settings();
			$label = isset( $modal['tip_fee_name'] ) ? $modal['tip_fee_name'] : '小费';
			$fees = WC()->cart->get_fees();
			foreach ( $fees as $fee_key => $fee ) {
				if ( $fee->name === $label ) {
					unset( $fees[ $fee_key ] );
				}
			}
			WC()->cart->fees_api()->set_fees( $fees );
		}
	}

	public function maybe_hook_woocommerce() {
		if ( class_exists( 'WooCommerce' ) ) {
			add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'wc_require_choice' ], 10, 3 );
			add_filter( 'woocommerce_add_cart_item_data', [ $this, 'wc_attach_choice_to_item' ], 10, 3 );
			// 使用较高优先级，便于在其他插件可能添加元数据后进行移除
			add_filter( 'woocommerce_get_item_data', [ $this, 'wc_display_item_data' ], 999, 2 );
			// 将购物车项目数据保存到订单项目元数据
			add_action( 'woocommerce_checkout_create_order_line_item', [ $this, 'save_cart_item_data_to_order_item' ], 10, 4 );
			// 控制订单项目元数据的显示
			add_filter( 'woocommerce_order_item_display_meta_key', [ $this, 'maybe_hide_order_item_meta_key' ], 999, 3 );
			add_filter( 'woocommerce_order_item_display_meta_value', [ $this, 'format_order_item_meta_value' ], 999, 3 );
			add_filter( 'woocommerce_display_item_meta', [ $this, 'maybe_hide_entire_item_meta' ], 999, 3 );
			add_filter( 'woocommerce_hidden_order_itemmeta', [ $this, 'hide_delivery_meta_in_my_account' ], 10, 1 );
			add_filter( 'woocommerce_order_item_get_formatted_meta_data', [ $this, 'filter_order_item_meta_data' ], 10, 2 );
			// 小费功能
			add_action( 'woocommerce_cart_calculate_fees', [ $this, 'wc_add_tip_fee' ] );
			
			// 订单处理 - 保存配送信息到订单元数据
			add_action( 'woocommerce_checkout_order_processed', [ $this, 'save_delivery_info_to_order' ], 10, 1 );
			
			// 订单详情页面显示配送信息
			add_action( 'woocommerce_order_details_after_order_table', [ $this, 'display_delivery_info_in_order_details' ], 10, 1 );
			
			// 感谢页面显示配送信息
			add_action( 'woocommerce_thankyou', [ $this, 'display_delivery_info_on_thankyou' ], 20, 1 );
			// 感谢页面：若下单邮箱未注册账户，则自动注册并发送设置密码邮件
			add_action( 'woocommerce_thankyou', [ $this, 'maybe_register_user_for_order' ], 5, 1 );
			
			// 邮件中显示配送信息
			add_action( 'woocommerce_email_order_details', [ $this, 'display_delivery_info_in_email' ], 15, 4 );
			
			// 后台订单详情显示配送信息
			add_action( 'woocommerce_admin_order_data_after_billing_address', [ $this, 'display_delivery_info_in_admin' ], 10, 1 );
			
			// 保存后台编辑的配送信息
			add_action( 'woocommerce_process_shop_order_meta', [ $this, 'save_admin_delivery_info' ], 10, 2 );
		}
	}

	/**
	 * 若订单为游客下单且邮箱未注册，则自动创建用户并发送设置密码邮件
	 */
	public function maybe_register_user_for_order( $order_id ) {
		if ( ! $order_id ) return;
		$order = wc_get_order( $order_id );
		if ( ! $order ) return;

		// 获取注册设置，检查是否启用自动注册功能
		$registration_settings = get_option( 'np_time_registration_settings', [] );
		$enable_auto_registration = isset( $registration_settings['enable_auto_registration'] ) ? (int) $registration_settings['enable_auto_registration'] : 1;
		
		// 如果功能未启用，则跳过
		if ( ! $enable_auto_registration ) {
			return;
		}

		// 已有关联用户则跳过
		if ( $order->get_user_id() ) {
			return;
		}

		$email = trim( (string) $order->get_billing_email() );
		$first_name = trim( (string) $order->get_billing_first_name() );
		$last_name  = trim( (string) $order->get_billing_last_name() );
		if ( ! $email || ! is_email( $email ) ) {
			return;
		}
		// 邮箱已存在则跳过
		if ( email_exists( $email ) ) {
			return;
		}

		// 生成唯一用户名（基于邮箱前缀）
		list( $base_user, ) = explode( '@', $email, 2 );
		$base_user = sanitize_user( $base_user, true );
		if ( strlen( $base_user ) < 3 ) {
			$base_user = 'user_' . wp_generate_password( 6, false, false );
		}
		$username = $base_user;
		$suffix = 1;
		while ( username_exists( $username ) ) {
			$username = $base_user . '_' . $suffix;
			$suffix++;
		}

		// 临时随机密码（用户将通过邮件链接设置新密码）
		$temp_password = wp_generate_password( 20, true, true );
		$user_id = wp_create_user( $username, $temp_password, $email );
		if ( is_wp_error( $user_id ) ) {
			return;
		}

		// 设为 customer 角色，并保存姓名
		wp_update_user( [ 'ID' => $user_id, 'role' => 'customer', 'first_name' => $first_name, 'last_name' => $last_name ] );

		// 关联订单到新用户
		try {
			$order->set_customer_id( $user_id );
			$order->add_order_note( sprintf( '已基于邮箱 %s 自动创建账户（用户ID：%d）。', $email, $user_id ) );
			$order->save();
		} catch ( \Throwable $e ) {}

		// 生成设置密码链接并发送邮件
		$user = get_user_by( 'id', $user_id );
		if ( $user && ! is_wp_error( $user ) ) {
			$key = get_password_reset_key( $user );
			if ( ! is_wp_error( $key ) ) {
				// 获取设置中的自定义链接
				$custom_link = isset( $registration_settings['custom_password_link'] ) ? trim( $registration_settings['custom_password_link'] ) : '';
				
				if ( $custom_link ) {
					// 使用自定义链接，支持占位符替换
					$reset_url = str_replace(
						[ '%key%', '%login%', '%email%' ],
						[ $key, rawurlencode( $user->user_login ), rawurlencode( $email ) ],
						$custom_link
					);
				} else {
					// 优先使用WooCommerce我的账户页面链接，如果不可用则使用WordPress默认链接
					if ( function_exists( 'wc_get_page_id' ) && function_exists( 'get_permalink' ) ) {
						$myaccount_page_id = wc_get_page_id( 'myaccount' );
						if ( $myaccount_page_id && $myaccount_page_id > 0 ) {
							$reset_url = add_query_arg( [
								'action' => 'rp',
								'key'    => $key,
								'login'  => rawurlencode( $user->user_login ),
							], get_permalink( $myaccount_page_id ) );
						} else {
							// 备用：使用WordPress默认密码重置链接
							$reset_url = add_query_arg( [
								'action' => 'rp',
								'key'    => $key,
								'login'  => rawurlencode( $user->user_login ),
							], wp_login_url() );
						}
					} else {
						// 备用：使用WordPress默认密码重置链接
						$reset_url = add_query_arg( [
							'action' => 'rp',
							'key'    => $key,
							'login'  => rawurlencode( $user->user_login ),
						], wp_login_url() );
					}
				}

				$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
				
				// 获取翻译后的邮件模板
				if ( function_exists( 'np_time_get_translated_registration_strings' ) ) {
					$translated_templates = np_time_get_translated_registration_strings();
					$subject_template = $translated_templates['email_subject'];
					$content_template = $translated_templates['email_content'];
				} else {
					// 回退到配置设置
					$subject_template = isset( $registration_settings['email_subject'] ) ? $registration_settings['email_subject'] : '[%s] 账户创建：设置您的密码';
					$content_template = isset( $registration_settings['email_content'] ) ? $registration_settings['email_content'] : "您好，\n\n我们已基于您本次下单使用的邮箱创建了账户：\n\n用户名：%s\n设置密码链接：%s\n\n如果非您本人操作，请忽略此邮件。\n";
				}
				
				$subject = sprintf( $subject_template, $blogname );
				$message = sprintf( $content_template, $user->user_login, $reset_url );
				
				// 发送邮件
				$mail_sent = wp_mail( $email, $subject, $message );
				
				// 记录邮件发送结果到订单备注
				if ( $mail_sent ) {
					$order->add_order_note( sprintf( '已向 %s 发送账户激活邮件。', $email ) );
				} else {
					$order->add_order_note( sprintf( '向 %s 发送账户激活邮件失败，请手动联系用户。', $email ) );
				}
				$order->save();
			}
		}
	}

    public function wc_add_tip_fee() {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            return;
        }

        // 只在结账页面添加小费，不在购物车页面添加
        if ( ! is_checkout() ) {
            return;
        }

        // 未启用小费功能则直接跳过
        if ( ! (int) get_option( 'np_time_tip_enabled', 1 ) ) {
            return;
        }

        // 获取保存的小费信息
        $saved_tip = isset( $_COOKIE['np_time_tip'] ) ? json_decode( stripslashes( $_COOKIE['np_time_tip'] ), true ) : null;
        
        if ( ! $saved_tip || $saved_tip['type'] === 'refuse' ) {
            return;
        }
		
		$amount = floatval( str_replace( '$', '', $saved_tip['amount'] ) );
		if ( $amount > 0 ) {
			$modal = $this->get_modal_settings();
			$label = isset( $modal['tip_fee_name'] ) ? $modal['tip_fee_name'] : '小费';
			WC()->cart->add_fee( $label, $amount );
		}
	}

	public function get_choice() {
		if ( isset( $_COOKIE['np_time_choice'] ) ) {
			$data = json_decode( wp_unslash( $_COOKIE['np_time_choice'] ), true );
			if ( is_array( $data ) && isset( $data['postcode'], $data['date'] ) ) {
				if ( NP_Time_Rules::validate_choice( $data['postcode'], $data['date'] ) ) {
					return $data;
				}
			}
		}
		return null;
	}

	public function wc_require_choice( $passed, $product_id, $quantity ) {
		$choice = $this->get_choice();
		if ( ! $choice ) {
			$modal = $this->get_modal_settings();
			$txt = isset( $modal['wc_notice_require_choice'] ) ? $modal['wc_notice_require_choice'] : '请先选择配送的日期与邮编。';
			wc_add_notice( $txt, 'error' );
			return false;
		}
		if ( ! NP_Time_Rules::validate_choice( $choice['postcode'], $choice['date'] ) ) {
			$modal = $this->get_modal_settings();
			$txt = isset( $modal['wc_notice_choice_invalid'] ) ? $modal['wc_notice_choice_invalid'] : '当前选择的邮编或日期/时间不再可用，请重新选择。';
			wc_add_notice( $txt, 'error' );
			return false;
		}
		
		// 检查产品是否在选择的配送日期下可用
		if ( ! NP_Time_Rules::is_product_available_on_date( $product_id, $choice['date'] ) ) {
			$product = wc_get_product( $product_id );
			$product_name = $product ? $product->get_name() : '该商品';
			$modal = $this->get_modal_settings();
			$template = isset( $modal['product_delivery_not_available_for_date'] ) ? $modal['product_delivery_not_available_for_date'] : '%s在您选择的配送日期（%s）不可配送，请重新选择配送日期或选择其他商品。';
			wc_add_notice( sprintf( $template, $product_name, $choice['date'] ), 'error' );
			return false;
		}
		
		return $passed;
	}

	public function wc_attach_choice_to_item( $cart_item_data, $product_id, $variation_id ) {
		$choice = $this->get_choice();
		if ( $choice ) {
			$cart_item_data['np_time'] = $choice;
		}
		return $cart_item_data;
	}

	public function wc_display_item_data( $item_data, $cart_item ) {
		// 购物车/结账（含其 AJAX 刷新）不在产品项内显示，若其它过滤器已加入则移除
		// 同时在用户仪表盘的订单列表页面也不显示配送信息
		if ( ( function_exists('is_cart') && is_cart() ) || ( function_exists('is_checkout') && is_checkout() ) || $this->is_cart_or_checkout_ajax() || $this->is_store_api_context() || $this->is_my_account_orders() ) {
			if ( is_array( $item_data ) ) {
				$modal = $this->get_modal_settings();
				$label_pc = isset( $modal['item_label_postcode'] ) ? (string) $modal['item_label_postcode'] : '配送邮编';
				$label_date = isset( $modal['item_label_date'] ) ? (string) $modal['item_label_date'] : '配送日期';
				$item_data = array_values( array_filter( $item_data, function( $row ) use ( $label_pc, $label_date ) {
					$name = is_array( $row ) && isset( $row['name'] ) ? (string) $row['name'] : '';
					return ! in_array( $name, [ $label_pc, $label_date ], true );
				} ) );
			}
			return $item_data;
		}
		if ( isset( $cart_item['np_time'] ) && is_array( $cart_item['np_time'] ) ) {
			$c = $cart_item['np_time'];
			$modal = $this->get_modal_settings();
			$label_pc = isset( $modal['item_label_postcode'] ) ? (string) $modal['item_label_postcode'] : '配送邮编';
			$label_date = isset( $modal['item_label_date'] ) ? (string) $modal['item_label_date'] : '配送日期';
			
			// 使用格式化的日期显示
			$formatted_date = $this->format_saved_delivery_date( $c['date'] );
			
			$item_data[] = [ 'name' => $label_pc, 'value' => esc_html( $c['postcode'] ) ];
			$item_data[] = [ 'name' => $label_date, 'value' => esc_html( $formatted_date ) ];
		}
		return $item_data;
	}

	/**
	 * 将购物车项目数据保存到订单项目元数据
	 */
	public function save_cart_item_data_to_order_item( $item, $cart_item_key, $values, $order ) {
		if ( isset( $values['np_time'] ) && is_array( $values['np_time'] ) ) {
			$choice = $values['np_time'];
			$modal = $this->get_modal_settings();
			$label_pc = isset( $modal['item_label_postcode'] ) ? (string) $modal['item_label_postcode'] : '配送邮编';
			$label_date = isset( $modal['item_label_date'] ) ? (string) $modal['item_label_date'] : '配送日期';
			
			// 使用格式化的日期显示
			$formatted_date = $this->format_saved_delivery_date( $choice['date'] );
			
			$item->add_meta_data( $label_pc, $choice['postcode'] );
			$item->add_meta_data( $label_date, $formatted_date );
		}
	}

	/**
	 * 控制订单项目元数据的显示 - 在用户仪表盘页面隐藏配送信息（包括订单列表和订单详情）
	 */
	public function maybe_hide_order_item_meta_key( $display_key, $meta, $item ) {
		$modal = $this->get_modal_settings();
		$label_pc = isset( $modal['item_label_postcode'] ) ? (string) $modal['item_label_postcode'] : '配送邮编';
		$label_date = isset( $modal['item_label_date'] ) ? (string) $modal['item_label_date'] : '配送日期';
		
		// 检查是否为配送相关的元数据
		if ( in_array( $display_key, [ $label_pc, $label_date ], true ) ) {
			// 多种方式检测是否在用户仪表盘页面
			$is_my_account = false;
			
			// 方式1: 使用WooCommerce函数检测
			if ( function_exists( 'is_account_page' ) && is_account_page() ) {
				$is_my_account = true;
			}
			
			// 方式2: URL检测
			$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
			if ( strpos( $request_uri, '/my-account/' ) !== false ) {
				$is_my_account = true;
			}
			
			// 方式3: 检查当前页面模板
			if ( function_exists( 'wc_get_page_id' ) ) {
				global $post;
				if ( $post && $post->ID === wc_get_page_id( 'myaccount' ) ) {
					$is_my_account = true;
				}
			}
			
			if ( $is_my_account ) {
				return false; // 在用户仪表盘页面隐藏wc-item-meta中的配送信息
			}
		}
		
		return $display_key;
	}

	/**
	 * 格式化订单项目元数据的显示值
	 */
	public function format_order_item_meta_value( $display_value, $meta, $item ) {
		// 保持原始值，因为在save_cart_item_data_to_order_item中已经格式化过了
		return $display_value;
	}

	/**
	 * 完全隐藏用户仪表盘页面的订单项配送元数据
	 */
	public function maybe_hide_entire_item_meta( $html, $item, $args ) {
		// 检查是否在用户仪表盘页面
		$is_my_account = false;
		
		if ( function_exists( 'is_account_page' ) && is_account_page() ) {
			$is_my_account = true;
		}
		
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
		if ( strpos( $request_uri, '/my-account/' ) !== false ) {
			$is_my_account = true;
		}
		
		if ( $is_my_account ) {
			$modal = $this->get_modal_settings();
			$label_pc = isset( $modal['item_label_postcode'] ) ? (string) $modal['item_label_postcode'] : '配送邮编';
			$label_date = isset( $modal['item_label_date'] ) ? (string) $modal['item_label_date'] : '配送日期';
			
			// 从HTML中移除配送相关的元数据
			$html = preg_replace('/<li[^>]*>\s*<strong[^>]*>' . preg_quote($label_pc, '/') . '.*?<\/li>/s', '', $html);
			$html = preg_replace('/<li[^>]*>\s*<strong[^>]*>' . preg_quote($label_date, '/') . '.*?<\/li>/s', '', $html);
			
			// 如果ul元素变为空，完全移除它
			$html = preg_replace('/<ul[^>]*class="wc-item-meta"[^>]*>\s*<\/ul>/s', '', $html);
		}
		
		return $html;
	}

	/**
	 * 在用户仪表盘页面隐藏配送相关的订单项元数据
	 */
	public function hide_delivery_meta_in_my_account( $hidden_meta ) {
		// 检查是否在用户仪表盘页面
		$is_my_account = false;
		
		if ( function_exists( 'is_account_page' ) && is_account_page() ) {
			$is_my_account = true;
		}
		
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
		if ( strpos( $request_uri, '/my-account/' ) !== false ) {
			$is_my_account = true;
		}
		
		if ( $is_my_account ) {
			$modal = $this->get_modal_settings();
			$label_pc = isset( $modal['item_label_postcode'] ) ? (string) $modal['item_label_postcode'] : '配送邮编';
			$label_date = isset( $modal['item_label_date'] ) ? (string) $modal['item_label_date'] : '配送日期';
			
			// 将配送相关的元数据键添加到隐藏列表中
			$hidden_meta[] = $label_pc;
			$hidden_meta[] = $label_date;
		}
		
		return $hidden_meta;
	}

	/**
	 * 直接过滤订单项元数据数组，移除配送相关数据
	 */
	public function filter_order_item_meta_data( $formatted_meta, $item ) {
		// 检查是否在用户仪表盘页面
		$is_my_account = false;
		
		if ( function_exists( 'is_account_page' ) && is_account_page() ) {
			$is_my_account = true;
		}
		
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
		if ( strpos( $request_uri, '/my-account/' ) !== false ) {
			$is_my_account = true;
		}
		
		if ( $is_my_account ) {
			$modal = $this->get_modal_settings();
			$label_pc = isset( $modal['item_label_postcode'] ) ? (string) $modal['item_label_postcode'] : '配送邮编';
			$label_date = isset( $modal['item_label_date'] ) ? (string) $modal['item_label_date'] : '配送日期';
			
			// 过滤掉配送相关的元数据
			$filtered_meta = [];
			foreach ( $formatted_meta as $meta ) {
				if ( ! in_array( $meta->key, [ $label_pc, $label_date ], true ) ) {
					$filtered_meta[] = $meta;
				}
			}
			return $filtered_meta;
		}
		
		return $formatted_meta;
	}

	/**
	 * 在订单详情页面显示配送信息
	 */
	public function display_delivery_info_in_order_details( $order ) {
		// 只在用户仪表盘的订单详情页面显示
		if ( ! is_account_page() ) {
			return;
		}

		// 确保是订单详情页面（包含view-order）
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '';
		if ( strpos( $request_uri, '/view-order/' ) === false ) {
			return;
		}

		// 获取订单中的配送信息
		$delivery_info = null;
		$items = $order->get_items();
		
		foreach ( $items as $item ) {
			$modal = $this->get_modal_settings();
			$label_date = isset( $modal['item_label_date'] ) ? (string) $modal['item_label_date'] : '配送日期';
			$label_pc = isset( $modal['item_label_postcode'] ) ? (string) $modal['item_label_postcode'] : '配送邮编';
			
			$delivery_date = $item->get_meta( $label_date );
			$delivery_postcode = $item->get_meta( $label_pc );
			
			if ( $delivery_date || $delivery_postcode ) {
				$delivery_info = [
					'date' => $delivery_date,
					'postcode' => $delivery_postcode,
					'date_label' => $label_date,
					'postcode_label' => $label_pc
				];
				break; // 找到第一个有配送信息的商品就停止
			}
		}

		// 如果找到配送信息，显示它
		if ( $delivery_info ) {
			echo '<div class="np-time-order-delivery-info" style="margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 6px; border-left: 4px solid #2196F3;">';
			echo '<h3 style="margin: 0 0 15px 0; color: #333; font-size: 16px;">配送信息</h3>';
			
			if ( $delivery_info['date'] ) {
				echo '<p style="margin: 5px 0;"><strong>' . esc_html( $delivery_info['date_label'] ) . ':</strong> ' . esc_html( $delivery_info['date'] ) . '</p>';
			}
			
			if ( $delivery_info['postcode'] ) {
				echo '<p style="margin: 5px 0;"><strong>' . esc_html( $delivery_info['postcode_label'] ) . ':</strong> ' . esc_html( $delivery_info['postcode'] ) . '</p>';
			}
			
			echo '</div>';
		}
	}
}

class NP_Time_Plugin {
	use NP_Time_Frontend;
	/** @var NP_Time_Plugin */
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Load textdomain (optional)
		add_action( 'init', [ $this, 'load_textdomain' ] );
		// Register shortcode
		add_action( 'init', [ $this, 'register_shortcodes' ] );

		// Admin
		if ( is_admin() ) {
			require_once NP_TIME_PATH . 'includes/Admin/class-np-time-admin.php';
			new NP_Time_Admin();
		}

		// Frontend assets and UI
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'wp_footer', [ $this, 'render_modal' ] );
		add_action( 'wp_footer', [ $this, 'render_fab' ] );

		// AJAX endpoints
		add_action( 'wp_ajax_np_time_get_options', [ $this, 'ajax_get_options' ] );
		add_action( 'wp_ajax_nopriv_np_time_get_options', [ $this, 'ajax_get_options' ] );
		add_action( 'wp_ajax_np_time_save_choice', [ $this, 'ajax_save_choice' ] );
		add_action( 'wp_ajax_nopriv_np_time_save_choice', [ $this, 'ajax_save_choice' ] );
		add_action( 'wp_ajax_np_time_save_tip', [ $this, 'ajax_save_tip' ] );
		add_action( 'wp_ajax_nopriv_np_time_save_tip', [ $this, 'ajax_save_tip' ] );
		add_action( 'wp_ajax_np_time_get_user_coupons', [ $this, 'ajax_get_user_coupons' ] );
		add_action( 'wp_ajax_nopriv_np_time_get_user_coupons', [ $this, 'ajax_get_user_coupons' ] );
		add_action( 'wp_ajax_np_time_remove_cart_conflicts', [ $this, 'ajax_remove_cart_conflicts' ] );
		add_action( 'wp_ajax_nopriv_np_time_remove_cart_conflicts', [ $this, 'ajax_remove_cart_conflicts' ] );

		// WooCommerce integration if available
		add_action( 'init', [ $this, 'maybe_hook_woocommerce' ] );
		
		// 产品显示过滤
		add_filter( 'woocommerce_product_is_visible', [ $this, 'filter_product_visibility' ], 10, 2 );
		add_filter( 'woocommerce_variation_is_visible', [ $this, 'filter_variation_visibility' ], 10, 4 );
		// 在购物车 totals 表格下方插入配送信息和编辑按钮
		add_action( 'woocommerce_after_cart_totals', [ $this, 'render_cart_delivery_info' ] );
		// 在结账页订单摘要前插入统一配送信息
		add_action( 'woocommerce_review_order_before_cart_contents', [ $this, 'render_checkout_delivery_info' ] );
		// 结账邮编与配送邮编一致性校验
		add_action( 'woocommerce_after_checkout_validation', [ $this, 'wc_validate_checkout_postcode' ], 10, 2 );
	}
	/**
	 * 格式化配送日期显示，支持多语言
	 */
	private function format_delivery_date_display( $choice, $modal ) {
		if ( empty( $choice['date'] ) ) {
			return isset( $modal['not_selected_text'] ) ? $modal['not_selected_text'] : '未选择';
		}

		$date_value = $choice['date'];
		$postcode = $choice['postcode'] ?? '';

		// 如果是数字，可能是星期几的索引，需要查找对应的日期
		if ( is_numeric( $date_value ) ) {
			$weekday_index = intval( $date_value );
			
			// 查找从明天开始7天内符合这个星期几的日期，确保不包含今天和之前的日期
			$today = new DateTime();
			for ( $i = 1; $i <= 7; $i++ ) {
				$future_date = clone $today;
				$future_date->add( new DateInterval( "P{$i}D" ) );
				if ( $future_date->format( 'w' ) == $weekday_index ) {
					// 找到了符合条件的日期，格式化显示
					return $this->format_date_with_weekday( $future_date, $modal );
				}
			}
			
			// 如果没找到，降级到星期显示
			$weekday_names = [
				$this->get_translated_modal_string( 'weekday_sun', '星期日' ),
				$this->get_translated_modal_string( 'weekday_mon', '星期一' ),
				$this->get_translated_modal_string( 'weekday_tue', '星期二' ),
				$this->get_translated_modal_string( 'weekday_wed', '星期三' ),
				$this->get_translated_modal_string( 'weekday_thu', '星期四' ),
				$this->get_translated_modal_string( 'weekday_fri', '星期五' ),
				$this->get_translated_modal_string( 'weekday_sat', '星期六' ),
			];
			return $weekday_names[ $weekday_index ] ?? $date_value;
		}

		// 如果是日期字符串，尝试解析
		$date_obj = DateTime::createFromFormat( 'Y-m-d', $date_value );
		if ( ! $date_obj ) {
			// 尝试其他常见格式
			$date_obj = DateTime::createFromFormat( 'm-d', $date_value );
			if ( ! $date_obj ) {
				return $date_value; // 无法解析，返回原值
			}
		}

		// 检查日期是否是今天或过去的日期，如果是则调整到下一个符合条件的日期
		$today = new DateTime();
		if ( $date_obj <= $today ) {
			$weekday_index = intval( $date_obj->format( 'w' ) );
			// 从明天开始查找下一个符合条件的日期
			for ( $i = 1; $i <= 7; $i++ ) {
				$future_date = clone $today;
				$future_date->add( new DateInterval( "P{$i}D" ) );
				if ( $future_date->format( 'w' ) == $weekday_index ) {
					return $this->format_date_with_weekday( $future_date, $modal );
				}
			}
		}

		return $this->format_date_with_weekday( $date_obj, $modal );
	}

	/**
	 * 格式化已保存的配送日期显示
	 */
	private function format_saved_delivery_date( $date_value ) {
		if ( empty( $date_value ) ) {
			return $date_value;
		}

		$modal = $this->get_modal_settings();
		
		// 如果是数字，可能是星期几的索引，需要查找对应的日期
		if ( is_numeric( $date_value ) ) {
			$weekday_index = intval( $date_value );
			
			// 查找从明天开始7天内符合这个星期几的日期，确保不包含今天和之前的日期
			$today = new DateTime();
			for ( $i = 1; $i <= 7; $i++ ) {
				$future_date = clone $today;
				$future_date->add( new DateInterval( "P{$i}D" ) );
				if ( $future_date->format( 'w' ) == $weekday_index ) {
					// 找到了符合条件的日期，格式化显示
					return $this->format_date_with_weekday( $future_date, $modal );
				}
			}
			
			// 如果没找到，降级到星期显示
			$weekday_names = [
				$this->get_translated_modal_string( 'weekday_sun', '星期日' ),
				$this->get_translated_modal_string( 'weekday_mon', '星期一' ),
				$this->get_translated_modal_string( 'weekday_tue', '星期二' ),
				$this->get_translated_modal_string( 'weekday_wed', '星期三' ),
				$this->get_translated_modal_string( 'weekday_thu', '星期四' ),
				$this->get_translated_modal_string( 'weekday_fri', '星期五' ),
				$this->get_translated_modal_string( 'weekday_sat', '星期六' ),
			];
			return $weekday_names[ $weekday_index ] ?? $date_value;
		}

		// 如果是日期字符串，尝试解析
		$date_obj = DateTime::createFromFormat( 'Y-m-d', $date_value );
		if ( ! $date_obj ) {
			// 尝试其他常见格式
			$date_obj = DateTime::createFromFormat( 'm-d', $date_value );
			if ( ! $date_obj ) {
				return $date_value; // 无法解析，返回原值
			}
		}

		// 检查日期是否是今天或过去的日期，如果是则调整到下一个符合条件的日期
		$today = new DateTime();
		if ( $date_obj <= $today ) {
			$weekday_index = intval( $date_obj->format( 'w' ) );
			// 从明天开始查找下一个符合条件的日期
			for ( $i = 1; $i <= 7; $i++ ) {
				$future_date = clone $today;
				$future_date->add( new DateInterval( "P{$i}D" ) );
				if ( $future_date->format( 'w' ) == $weekday_index ) {
					return $this->format_date_with_weekday( $future_date, $modal );
				}
			}
		}

		return $this->format_date_with_weekday( $date_obj, $modal );
	}

	/**
	 * 格式化日期和星期显示
	 */
	private function format_date_with_weekday( $date_obj, $modal ) {
		$month_keys = [
			'month_01', 'month_02', 'month_03', 'month_04', 'month_05', 'month_06',
			'month_07', 'month_08', 'month_09', 'month_10', 'month_11', 'month_12'
		];
		$weekday_keys = [
			'weekday_sun', 'weekday_mon', 'weekday_tue', 'weekday_wed',
			'weekday_thu', 'weekday_fri', 'weekday_sat'
		];

		$month_index = (int) $date_obj->format( 'n' ) - 1; // 1-12 转为 0-11
		$weekday_index = (int) $date_obj->format( 'w' ); // 0=星期日
		$day = $date_obj->format( 'j' ); // 不补零的日期

		$month_name = $this->get_translated_modal_string( $month_keys[ $month_index ], ( $month_index + 1 ) . '月' );
		$weekday_name = $this->get_translated_modal_string( $weekday_keys[ $weekday_index ], '星期' );

		$format_template = $this->get_translated_modal_string( 'date_weekday_format', '%s%s日-%s' );
		
		return sprintf( $format_template, $month_name, $day, $weekday_name );
	}

	/**
	 * 获取翻译后的模态框字符串
	 */
	private function get_translated_modal_string( $key, $fallback ) {
		$modal_settings = $this->get_modal_settings();
		return $modal_settings[ $key ] ?? $fallback;
	}

	/**
	 * 结账页统一配送信息
	 */
	public function render_checkout_delivery_info() {
		if ( ! is_checkout() ) return;
		$choice = $this->get_choice();
		$modal = $this->get_modal_settings();
		$label_pc = isset( $modal['label_postcode'] ) ? $modal['label_postcode'] : '配送邮编：';
		$label_date = isset( $modal['label_date'] ) ? $modal['label_date'] : '配送日期：';
		$edit_text = isset( $modal['edit_button_text'] ) ? $modal['edit_button_text'] : '编辑';
		$not_sel = isset( $modal['not_selected_text'] ) ? $modal['not_selected_text'] : '未选择';
		
		// 使用格式化后的日期显示
		$formatted_date = $this->format_delivery_date_display( $choice, $modal );
		
		echo '<tr class="np-time-checkout-row"><td colspan="2" style="padding:10px 0 !important;text-align:left !important;">';
		echo '<div class="wc-block-components-totals-item__label">';
		echo '<div class="np-time-row"><span class="np-time-pc"><strong>' . esc_html( $label_pc ) . '</strong>' . esc_html( $choice['postcode'] ?? $not_sel ) . '</span></div>';
		echo '<div class="np-time-row"><span class="np-time-date"><strong>' . esc_html( $label_date ) . '</strong>' . esc_html( $formatted_date ) . '</span></div>';
		echo '</div>';
		echo '</td></tr>';
	}
	/**
	 * 在购物车 totals 区块插入配送邮编和日期，并添加编辑按钮
	 */
	public function render_cart_delivery_info() {
		if ( ! is_cart() ) return;
		$choice = $this->get_choice();
		$modal = $this->get_modal_settings();
		$label_pc = isset( $modal['label_postcode'] ) ? $modal['label_postcode'] : '配送邮编：';
		$label_date = isset( $modal['label_date'] ) ? $modal['label_date'] : '配送日期：';
		$edit_text = isset( $modal['edit_button_text'] ) ? $modal['edit_button_text'] : '编辑';
		$not_sel = isset( $modal['not_selected_text'] ) ? $modal['not_selected_text'] : '未选择';
		
		// 使用格式化后的日期显示
		$formatted_date = $this->format_delivery_date_display( $choice, $modal );
		
		echo '<div id="np-time-totals-info" style="margin-top:16px;padding:0 0 30px;border:0 solid #da010100;border-radius:6px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;text-align:left;background:#f9f9f900;">';
		echo '<div class="np-time-info-text" style="display:flex;flex-direction:column;gap:6px;">';
		echo '<div class="np-time-row"><span class="np-time-pc"><strong>' . esc_html( $label_pc ) . '</strong>' . esc_html( $choice['postcode'] ?? $not_sel ) . '</span></div>';
		echo '<div class="np-time-row"><span class="np-time-date"><strong>' . esc_html( $label_date ) . '</strong>' . esc_html( $formatted_date ) . '</span></div>';
		echo '</div>';
		// 在结账页面不显示编辑按钮
		if ( ! ( function_exists('is_checkout') && is_checkout() ) ) {
			echo '<div class="np-time-info-actions" style="margin-left:auto;">';
			echo '<button type="button" class="np-time-edit-btn" style="border:none;background:none;color:#007cba;cursor:pointer;text-decoration:underline;padding:0;">' . esc_html( $edit_text ) . '</button>';
			echo '</div>';
		}
		echo '</div>';
		// 弹窗已在 wp_footer 渲染
	}

	/**
	 * 结账页后端校验：结账地址邮编必须与配送设置邮编一致
	 */
	public function wc_validate_checkout_postcode( $data, $errors ) {
		if ( ! is_checkout() ) return;
		$choice = $this->get_choice();
		if ( ! $choice || empty( $choice['postcode'] ) ) return;
		$posted_pc = '';
		// 优先取收货邮编，其次账单邮编
		if ( ! empty( $_POST['ship_to_different_address'] ) && '1' === $_POST['ship_to_different_address'] ) {
			$posted_pc = sanitize_text_field( $_POST['shipping_postcode'] ?? '' );
		}
		if ( empty( $posted_pc ) ) {
			$posted_pc = sanitize_text_field( $_POST['shipping_postcode'] ?? $_POST['billing_postcode'] ?? '' );
		}
		if ( $posted_pc && strcasecmp( trim( $posted_pc ), trim( (string) $choice['postcode'] ) ) !== 0 ) {
			$modal = $this->get_modal_settings();
			$template = isset( $modal['checkout_postcode_mismatch'] ) ? (string) $modal['checkout_postcode_mismatch'] : '配送邮编和账单邮编不一致，请修改。账单邮编（%s）需与配送设置邮编（%s）一致。';
			$errors->add( 'np_time_pc_mismatch', sprintf( $template, esc_html( $posted_pc ), esc_html( $choice['postcode'] ) ) );
		}
	}

	/**
	 * 在 block/cart 兼容下方插入（如 totals 区块无效时）
	 */
public function render_cart_delivery_info_block() {
    if ( ! is_cart() ) return;
    $choice = $this->get_choice();
    $modal = $this->get_modal_settings();
    $label_pc = isset( $modal['label_postcode'] ) ? $modal['label_postcode'] : '配送邮编：';
    $label_date = isset( $modal['label_date'] ) ? $modal['label_date'] : '配送日期：';
    $edit_text = isset( $modal['edit_button_text'] ) ? $modal['edit_button_text'] : '编辑';
    $not_sel = isset( $modal['not_selected_text'] ) ? $modal['not_selected_text'] : '未选择';
    echo '<div class="np-time-cart-block" style="margin:16px 0;">';
    echo '<div class="wc-block-components-totals-item__label">';
    echo '<div class="np-time-row"><span class="np-time-pc"><strong>' . esc_html( $label_pc ) . '</strong>' . esc_html( $choice['postcode'] ?? $not_sel ) . '</span></div>';
    // 在结账页面不显示编辑按钮
    if ( function_exists('is_checkout') && is_checkout() ) {
        echo '<div class="np-time-row"><span class="np-time-date"><strong>' . esc_html( $label_date ) . '</strong>' . esc_html( $choice['date'] ?? $not_sel ) . '</span></div>';
    } else {
        // 在结账页面不显示编辑按钮
    if ( function_exists('is_checkout') && is_checkout() ) {
        echo '<div class="np-time-row"><span class="np-time-date"><strong>' . esc_html( $label_date ) . '</strong>' . esc_html( $choice['date'] ?? $not_sel ) . '</span></div>';
    } else {
        echo '<div class="np-time-row"><span class="np-time-date"><strong>' . esc_html( $label_date ) . '</strong>' . esc_html( $choice['date'] ?? $not_sel ) . '</span><button type="button" class="np-time-edit-btn">' . esc_html( $edit_text ) . '</button></div>';
    }
    }
    echo '</div>';
    echo '</div>';
}

	public function load_textdomain() {
		load_plugin_textdomain( 'np-time', false, dirname( plugin_basename( NP_TIME_FILE ) ) . '/languages' );
	}

	public function register_shortcodes() {
		add_shortcode( 'np_time_now', [ $this, 'shortcode_time_now' ] );
	}

	public function shortcode_time_now( $atts = [], $content = null, $tag = '' ) {
		$atts = shortcode_atts( [
			'format' => get_option( 'time_format', 'H:i:s' ),
			'date_format' => get_option( 'date_format', 'Y-m-d' ),
			'show_date' => 'yes',
		], $atts, $tag );

		$timestamp = current_time( 'timestamp' );
		$time_str  = date_i18n( $atts['format'], $timestamp );

		if ( 'yes' === strtolower( $atts['show_date'] ) ) {
			$date_str = date_i18n( $atts['date_format'], $timestamp );
			return sprintf( '%s %s', esc_html( $date_str ), esc_html( $time_str ) );
		}

		return esc_html( $time_str );
	}

	/**
	 * 保存配送信息到订单元数据
	 */
	public function save_delivery_info_to_order( $order_id ) {
		$choice = $this->get_choice();
		if ( ! $choice || ! isset( $choice['postcode'], $choice['date'] ) ) {
			return;
		}

		// 获取订单对象，兼容HPOS
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		// 保存配送邮编和配送日期到订单元数据
		$order->update_meta_data( '_np_delivery_postcode', sanitize_text_field( $choice['postcode'] ) );
		$order->update_meta_data( '_np_delivery_date', sanitize_text_field( $choice['date'] ) );

		// 也保存完整的配送选择信息，便于后续扩展
		$order->update_meta_data( '_np_delivery_info', $choice );
		
		// 保存到订单
		$order->save();
	}

	/**
	 * 在感谢页面显示配送信息
	 */
public function display_delivery_info_on_thankyou( $order_id ) {
		if ( ! $order_id ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$postcode = $order->get_meta( '_np_delivery_postcode' );
		$date = $order->get_meta( '_np_delivery_date' );

		if ( ! $postcode || ! $date ) {
			return;
		}

		echo '<div class="np-delivery-info-thankyou" style="margin: 20px 0; padding: 15px; background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 5px;">';
		echo '<h3 style="margin-top: 0; color: #333;">配送信息</h3>';
    $modal = $this->get_modal_settings();
    $label_pc = isset( $modal['label_postcode'] ) ? $modal['label_postcode'] : '配送邮编：';
    $label_date = isset( $modal['label_date'] ) ? $modal['label_date'] : '配送日期：';
    
    // 使用格式化的日期显示
    $formatted_date = $this->format_saved_delivery_date( $date );
    
    echo '<p><strong>' . esc_html( $label_pc ) . '</strong> ' . esc_html( $postcode ) . '</p>';
    echo '<p><strong>' . esc_html( $label_date ) . '</strong> ' . esc_html( $formatted_date ) . '</p>';
		echo '</div>';
	}

	/**
	 * 在邮件中显示配送信息
	 */
public function display_delivery_info_in_email( $order, $sent_to_admin, $plain_text, $email ) {
		if ( ! $order ) {
			return;
		}

		$postcode = $order->get_meta( '_np_delivery_postcode' );
		$date = $order->get_meta( '_np_delivery_date' );

		if ( ! $postcode || ! $date ) {
			return;
		}

		$modal = $this->get_modal_settings();
		$formatted_date = $this->format_saved_delivery_date( $date );
		
		if ( $plain_text ) {
			echo "\n" . "配送信息:\n";
            $label_pc_txt = isset( $modal['label_postcode'] ) ? $modal['label_postcode'] : '配送邮编：';
            $label_date_txt = isset( $modal['label_date'] ) ? $modal['label_date'] : '配送日期：';
            echo $label_pc_txt . ' ' . $postcode . "\n";
            echo $label_date_txt . ' ' . $formatted_date . "\n\n";
		} else {
			echo '<div style="margin: 20px 0; padding: 15px; background: #f8f9fa; border: 1px solid #e0e0e0;">';
			echo '<h3 style="margin-top: 0; color: #333;">配送信息</h3>';
            $label_pc = isset( $modal['label_postcode'] ) ? $modal['label_postcode'] : '配送邮编：';
            $label_date = isset( $modal['label_date'] ) ? $modal['label_date'] : '配送日期：';
            echo '<p><strong>' . esc_html( $label_pc ) . '</strong> ' . esc_html( $postcode ) . '</p>';
            echo '<p><strong>' . esc_html( $label_date ) . '</strong> ' . esc_html( $formatted_date ) . '</p>';
			echo '</div>';
		}
	}

	/**
	 * 在后台订单详情页面显示配送信息
	 */
public function display_delivery_info_in_admin( $order ) {
		if ( ! $order ) {
			return;
		}

		$postcode = $order->get_meta( '_np_delivery_postcode' );
		$date = $order->get_meta( '_np_delivery_date' );

		if ( ! $postcode || ! $date ) {
			return;
		}

		echo '<div class="order_data_column" style="width: 32%; float: left; margin-right: 1%;">';
		echo '<h3>配送信息</h3>';
		echo '<div class="address">';
    $modal = $this->get_modal_settings();
    $label_pc = isset( $modal['label_postcode'] ) ? $modal['label_postcode'] : '配送邮编：';
    $label_date = isset( $modal['label_date'] ) ? $modal['label_date'] : '配送日期：';
    
    // 使用格式化的日期显示
    $formatted_date = $this->format_saved_delivery_date( $date );
    
    echo '<p><strong>' . esc_html( $label_pc ) . '</strong><br>' . esc_html( $postcode ) . '</p>';
    echo '<p><strong>' . esc_html( $label_date ) . '</strong><br>' . esc_html( $formatted_date ) . '</p>';
		echo '</div>';
		echo '</div>';
	}

	/**
	 * 保存后台编辑的配送信息
	 */
	public function save_admin_delivery_info( $order_id, $post ) {
		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			return;
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		// 保存配送邮编
		if ( isset( $_POST['_np_delivery_postcode'] ) ) {
			$postcode = sanitize_text_field( $_POST['_np_delivery_postcode'] );
			$order->update_meta_data( '_np_delivery_postcode', $postcode );
		}

		// 保存配送日期
		if ( isset( $_POST['_np_delivery_date'] ) ) {
			$date = sanitize_text_field( $_POST['_np_delivery_date'] );
			$order->update_meta_data( '_np_delivery_date', $date );
		}

		// 更新完整配送信息
		if ( isset( $_POST['_np_delivery_postcode'], $_POST['_np_delivery_date'] ) ) {
			$delivery_info = [
				'postcode' => sanitize_text_field( $_POST['_np_delivery_postcode'] ),
				'date' => sanitize_text_field( $_POST['_np_delivery_date'] )
			];
			$order->update_meta_data( '_np_delivery_info', $delivery_info );
		}

		$order->save();
	}

	/**
	 * 过滤产品可见性，根据当前配送日期隐藏不可用的产品
	 */
	public function filter_product_visibility( $visible, $product_id ) {
		// 在管理后台不进行过滤
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $visible;
		}

		// 只在有配送选择时进行过滤
		$choice = $this->get_choice();
		if ( ! $choice || empty( $choice['date'] ) ) {
			return $visible; // 没有配送选择时，显示所有产品
		}

		// 检查产品是否在配送日期下可用
		if ( ! NP_Time_Rules::is_product_available_on_date( $product_id, $choice['date'] ) ) {
			return false;
		}

		return $visible;
	}

	/**
	 * 过滤变体可见性
	 */
	public function filter_variation_visibility( $visible, $variation_id, $product_id, $variation ) {
		// 在管理后台不进行过滤
		if ( is_admin() && ! wp_doing_ajax() ) {
			return $visible;
		}

		// 只在有配送选择时进行过滤
		$choice = $this->get_choice();
		if ( ! $choice || empty( $choice['date'] ) ) {
			return $visible;
		}

		// 检查变体产品是否在配送日期下可用
		if ( ! NP_Time_Rules::is_product_available_on_date( $variation_id, $choice['date'] ) ) {
			return false;
		}

		return $visible;
	}


}

// ----------------- Frontend / Rules / Woo -----------------

class NP_Time_Rules {
	/**
	 * 统一的可选窗口天数，默认 30 天。
	 * 如需后台可配，可在 np_time_settings 增加 window_days 字段。
	 */
	public static function get_window_days() {
		$settings = get_option( 'np_time_settings', [] );
		$window = isset( $settings['window_days'] ) ? intval( $settings['window_days'] ) : 30;
		return max( 1, $window );
	}

	// 旧的 CPT 规则已废弃

	protected static function get_weekday_postcodes() {
		// 从插件 data 目录读取
		$file = NP_TIME_PATH . 'data/weekday_postcodes.json';
		$opt = [];
		if ( file_exists( $file ) ) {
			$json = file_get_contents( $file );
			$dat = json_decode( (string) $json, true );
			if ( is_array( $dat ) ) $opt = $dat;
		} else {
			// 兼容老数据：尝试从数据库读取一次
			$opt = get_option( 'np_time_weekday_postcodes', [] );
		}
		if ( ! is_array( $opt ) ) $opt = [];
		$out = [];
		for ( $i = 0; $i <= 6; $i++ ) {
			$raw = isset( $opt[ (string) $i ] ) ? (string) $opt[ (string) $i ] : '';
			$parts = preg_split( '/[\r\n,]+/', $raw );
			$out[$i] = array_values( array_filter( array_map( 'trim', $parts ) ) );
		}
		return $out;
	}

	protected static function get_local_postcodes() {
		$file = NP_TIME_PATH . 'data/local_postcodes.txt';
		if ( file_exists( $file ) ) {
			$raw = file_get_contents( $file );
			$lines = preg_split( '/\r?\n/', (string) $raw );
			$codes = array_values( array_filter( array_map( 'trim', $lines ) ) );
			return $codes;
		}
		// 兼容老数据：尝试从数据库读取一次
		$raw = (string) get_option( 'np_time_local_postcodes', '' );
		$lines = preg_split( '/\r?\n/', $raw );
		$codes = array_values( array_filter( array_map( 'trim', $lines ) ) );
		return $codes;
	}

	public static function is_local_delivery( $postcode ) {
		foreach ( self::get_local_postcodes() as $pattern ) {
			$regex = str_replace( '*', '.*', preg_quote( $pattern, '/' ) );
			if ( preg_match( '/^' . $regex . '$/i', (string) $postcode ) ) return true;
		}
		return false;
	}

	public static function match_postcode_rule( $postcode ) {
		// 使用设置页中的“按星期几邮编映射”
		$weekdayMap = self::get_weekday_postcodes();
		$matchedDays = [];
		for ( $dow = 0; $dow <= 6; $dow++ ) {
			foreach ( $weekdayMap[$dow] ?? [] as $pattern ) {
				$regex = str_replace( '*', '.*', preg_quote( $pattern, '/' ) );
				if ( preg_match( '/^' . $regex . '$/i', (string) $postcode ) ) {
					$matchedDays[] = $dow;
					break;
				}
			}
		}
		if ( $matchedDays ) {
			$matchedDays = array_values( array_unique( $matchedDays ) );
			return [ 'daysOfWeek' => $matchedDays ];
		}
		// 未匹配到按星期几规则时，返回空数组（非本地将无可选星期几）
		return [];
	}

	public static function build_available_dates( $rule ) {
		$days = (int) ( $rule['windowDays'] ?? self::get_window_days() );
		$daysOfWeek = isset( $rule['daysOfWeek'] ) ? (array) $rule['daysOfWeek'] : [];
		// 归一化星期几编码，兼容 0-6 与 1-7（7 表示周日 -> 0）
		$daysOfWeek = array_values( array_unique( array_map( function( $n ) {
			$n = (int) $n;
			return $n % 7; // 0-6，7 会变为 0
		}, $daysOfWeek ) ) );
		$specificDates = isset( $rule['dates'] ) ? (array) $rule['dates'] : [];
		$out = [];
		$today = current_time( 'timestamp' );
		// 从“明天”开始构建，排除当天
		for ( $i = 1; $i <= $days; $i++ ) {
			$ts = strtotime( "+$i day", $today );
			$dow = (int) date_i18n( 'w', $ts );
			$date = date_i18n( 'Y-m-d', $ts );
			$ok = true;
			if ( $daysOfWeek ) {
				$ok = in_array( $dow, $daysOfWeek, true );
			}
			if ( $specificDates ) {
				$ok = in_array( $date, $specificDates, true ) || $ok;
			}
			if ( $ok ) $out[] = $date;
		}
		return array_values( array_unique( $out ) );
	}

	public static function validate_choice( $postcode, $date ) {
		$postcode = (string) $postcode; $date = (string) $date;
		if ( empty( $postcode ) || empty( $date ) ) return false;
		if ( self::is_local_delivery( $postcode ) ) {
			$dates = self::build_available_dates( [ 'windowDays' => self::get_window_days() ] );
			return in_array( $date, $dates, true );
		}
		$rule = self::match_postcode_rule( $postcode );
		$dates = self::build_available_dates( $rule );
		return in_array( $date, $dates, true );
	}

	/**
	 * 检查产品是否在指定的配送日期下可用
	 * @param int $product_id 产品ID
	 * @param string $delivery_date 配送日期 (Y-m-d 格式)
	 * @return bool
	 */
	public static function is_product_available_on_date( $product_id, $delivery_date ) {
		$product_id = (int) $product_id;
		
		// 检查产品是否启用了配送日期限制
		$date_enabled = get_post_meta( $product_id, '_np_delivery_date_enabled', true );
		if ( 'yes' !== $date_enabled ) {
			return true; // 没有启用日期限制，产品总是可用
		}
		
		$start_date = get_post_meta( $product_id, '_np_delivery_start_date', true );
		$end_date = get_post_meta( $product_id, '_np_delivery_end_date', true );
		
		$selected_timestamp = strtotime( $delivery_date );
		
		// 检查开始日期
		if ( $start_date ) {
			$start_timestamp = strtotime( $start_date );
			if ( $selected_timestamp < $start_timestamp ) {
				return false;
			}
		}
		
		// 检查结束日期
		if ( $end_date ) {
			$end_timestamp = strtotime( $end_date );
			if ( $selected_timestamp > $end_timestamp ) {
				return false;
			}
		}
		
		return true;
	}

	/**
	 * 获取购物车中因配送日期变更而需要移除的产品
	 * @param string $new_delivery_date 新的配送日期
	 * @return array 需要移除的购物车键和产品信息
	 */
	public static function get_cart_items_to_remove_for_delivery_date( $new_delivery_date ) {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return [];
		}
		
		$items_to_remove = [];
		
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product_id = isset( $cart_item['variation_id'] ) && $cart_item['variation_id'] 
				? $cart_item['variation_id'] 
				: $cart_item['product_id'];
				
			if ( ! self::is_product_available_on_date( $product_id, $new_delivery_date ) ) {
				$product = wc_get_product( $product_id );
				if ( $product ) {
					$items_to_remove[] = [
						'cart_key' => $cart_item_key,
						'product_id' => $product_id,
						'product_name' => $product->get_name(),
						'quantity' => $cart_item['quantity']
					];
				}
			}
		}
		
		return $items_to_remove;
	}

	public static function get_product_rules() {
		$json = get_option( 'np_time_product_rules', '' );
		$data = json_decode( $json, true );
		if ( ! is_array( $data ) ) return [ 'items' => [] ];
		$data['items'] = isset( $data['items'] ) && is_array( $data['items'] ) ? $data['items'] : [];
		return $data;
	}

	public static function validate_for_product( $product_id, $postcode, $date ) {
		$product_id = (int) $product_id;
		$date = (string) $date;
		
		// 首先检查产品级别的配送日期设置（新增功能）
		$date_enabled = get_post_meta( $product_id, '_np_delivery_date_enabled', true );
		if ( 'yes' === $date_enabled ) {
			$start_date = get_post_meta( $product_id, '_np_delivery_start_date', true );
			$end_date = get_post_meta( $product_id, '_np_delivery_end_date', true );
			
			$selected_timestamp = strtotime( $date );
			
			// 检查开始日期
			if ( $start_date ) {
				$start_timestamp = strtotime( $start_date );
				if ( $selected_timestamp < $start_timestamp ) {
					return false;
				}
			}
			
			// 检查结束日期
			if ( $end_date ) {
				$end_timestamp = strtotime( $end_date );
				if ( $selected_timestamp > $end_timestamp ) {
					return false;
				}
			}
			
			// 如果产品设置了日期范围，还需要通过基础的邮编规则验证
			if ( ! self::validate_choice( $postcode, $date ) ) {
				return false;
			}
			
			return true;
		}
		
		// 检查旧的产品规则系统（保持兼容性）
		$rules = self::get_product_rules();
		foreach ( $rules['items'] as $item ) {
			$ids = array_map( 'intval', (array) ( $item['productIds'] ?? [] ) );
			if ( in_array( $product_id, $ids, true ) ) {
				$allowed = [];
				$window = 60; // fallback window
				$today = current_time( 'timestamp' );
				$daysOfWeek = (array) ( $item['daysOfWeek'] ?? [] );
				$specific = (array) ( $item['dates'] ?? [] );
				for ( $i = 0; $i < $window; $i++ ) {
					$ts = strtotime( "+$i day", $today );
					$dow = (int) date_i18n( 'w', $ts );
					$d = date_i18n( 'Y-m-d', $ts );
					$ok = true;
					if ( $daysOfWeek ) $ok = in_array( $dow, $daysOfWeek, true );
					if ( $specific ) $ok = in_array( $d, $specific, true ) || $ok;
					if ( $ok ) $allowed[] = $d;
				}
				return in_array( $date, array_unique( $allowed ), true );
			}
		}
		// fallback to postcode-level rule
		return self::validate_choice( $postcode, $date );
	}


}

if ( ! function_exists( 'wp_get_session_token' ) ) {
	// ensure session-like storage via cookies
}

// removed duplicate trait and temp subclass
