<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class NP_Time_Admin {
    const OPTION_RULES = 'np_time_rules';
    const OPTION_SETTINGS = 'np_time_settings';

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_assets' ] );
        // 当 modal 文案设置更新时，立即注册到多语言插件字符串表，便于快速翻译
        add_action( 'update_option_np_time_modal_settings', [ $this, 'on_modal_settings_updated' ], 10, 3 );
        // 当用户注册设置更新时，注册邮件模板到多语言系统
        add_action( 'update_option_np_time_registration_settings', [ $this, 'on_registration_settings_updated' ], 10, 3 );
        // 拦截设置保存，写入本地文件
        add_filter( 'pre_update_option_np_time_local_postcodes', [ $this, 'intercept_save_local' ], 10, 3 );
        add_filter( 'pre_update_option_np_time_weekday_postcodes', [ $this, 'intercept_save_weekday' ], 10, 3 );
        
        // 产品配送日期设置
        add_action( 'woocommerce_product_options_general_product_data', [ $this, 'product_delivery_date_fields' ] );
        add_action( 'woocommerce_process_product_meta', [ $this, 'save_product_delivery_date_fields' ] );
    }

    public function on_modal_settings_updated( $old_value, $value, $option ) {
        if ( function_exists( 'np_time_register_modal_strings' ) ) {
            // 使用新保存的值注册字符串
            np_time_register_modal_strings( wp_parse_args( (array) $value, np_time_modal_settings_defaults() ) );
        }
    }

    public function on_registration_settings_updated( $old_value, $value, $option ) {
        if ( function_exists( 'np_time_register_registration_strings' ) ) {
            // 注册用户注册邮件模板到多语言系统
            np_time_register_registration_strings();
        }
    }

    public function add_menu() {
        add_menu_page(
            'NP-Time 控制台',
            'NP-Time',
            'manage_options',
            'np-time',
            [ $this, 'render_dashboard_page' ],
            'dashicons-clock',
            58
        );

        add_submenu_page(
            'np-time',
            '配送设置',
            '配送设置',
            'manage_options',
            'np-time-settings',
            [ $this, 'render_page' ]
        );

        add_submenu_page(
            'np-time',
            '小费设置',
            '小费设置',
            'manage_options',
            'np-time-tip-settings',
            [ $this, 'render_tip_page' ]
        );

        add_submenu_page(
            'np-time',
            '弹窗与浮窗设置',
            '弹窗设置',
            'manage_options',
            'np-time-modal-settings',
            [ $this, 'render_modal_settings_page' ]
        );

        add_submenu_page(
            'np-time',
            '优惠券设置',
            '优惠券设置',
            'manage_options',
            'np-time-coupon-settings',
            [ $this, 'render_coupon_settings_page' ]
        );

        // 将默认子菜单重命名为“概览”
        add_submenu_page(
            'np-time',
            '用户注册设置',
            '用户注册设置',
            'manage_options',
            'np-time-registration-settings',
            [ $this, 'render_registration_settings_page' ]
        );

        add_action( 'admin_menu', [ $this, 'rename_default_submenu' ], 100 );
    }

    public function rename_default_submenu() {
        global $submenu;
        if ( isset( $submenu['np-time'][0] ) ) {
            $submenu['np-time'][0][0] = '概览';
        }
    }

    // 旧的 CPT 已废弃，不再注册

    public function register_settings() {
    register_setting( 'np_time_group', self::OPTION_SETTINGS, [ $this, 'sanitize_settings' ] );
    // 仍注册字段以便使用 Settings API 表单，但实际不入库，改为写入文件
    register_setting( 'np_time_group', 'np_time_local_postcodes' );
    register_setting( 'np_time_group', 'np_time_weekday_postcodes' );

    add_settings_section( 'np_time_section_main', '规则与选项', '__return_false', 'np-time-settings' );
    add_settings_field( 'np_time_local_postcodes', '本地配送邮编', [ $this, 'field_local_postcodes' ], 'np-time-settings', 'np_time_section_main' );
    add_settings_field( 'np_time_weekday_postcodes', '按星期几的配送邮编', [ $this, 'field_weekday_postcodes' ], 'np-time-settings', 'np_time_section_main' );
    add_settings_field( self::OPTION_SETTINGS, '其他选项', [ $this, 'field_settings' ], 'np-time-settings', 'np_time_section_main' );

    register_setting( 'np_time_tip_group', 'np_time_tip_enabled', [ $this, 'sanitize_tip_enabled' ] );
    register_setting( 'np_time_tip_group', 'np_time_tip_options', [ $this, 'sanitize_tip_options' ] );
    add_settings_section( 'np_time_tip_section', '小费按钮设置', '__return_false', 'np-time-tip-settings' );
    add_settings_field( 'np_time_tip_enabled', '启用小费功能', [ $this, 'field_tip_enabled' ], 'np-time-tip-settings', 'np_time_tip_section' );
    add_settings_field( 'np_time_tip_options', '结账页小费金额', [ $this, 'field_tip_options' ], 'np-time-tip-settings', 'np_time_tip_section' );

    register_setting( 'np_time_modal_group', 'np_time_modal_settings', [ $this, 'sanitize_modal_settings' ] );
    add_settings_section( 'np_time_modal_section_texts', '弹窗文案', '__return_false', 'np-time-modal-settings' );
    add_settings_field( 'np_time_modal_texts', '弹窗内容', [ $this, 'field_modal_texts' ], 'np-time-modal-settings', 'np_time_modal_section_texts' );
    add_settings_section( 'np_time_modal_section_fab', '浮窗按钮', '__return_false', 'np-time-modal-settings' );
    add_settings_field( 'np_time_fab_settings', '浮窗配置', [ $this, 'field_fab_settings' ], 'np-time-modal-settings', 'np_time_modal_section_fab' );

    // 优惠券设置（使用 modal settings 存储位置，提供单独管理页面）
    register_setting( 'np_time_coupon_group', 'np_time_modal_settings', [ $this, 'sanitize_modal_settings' ] );
    add_settings_section( 'np_time_coupon_section', '优惠券弹窗文案', '__return_false', 'np-time-coupon-settings' );
    add_settings_field( 'np_time_coupon_texts', '优惠券文本', [ $this, 'field_coupon_texts' ], 'np-time-coupon-settings', 'np_time_coupon_section' );
    
    // 用户注册设置
    register_setting( 'np_time_registration_group', 'np_time_registration_settings', [ $this, 'sanitize_registration_settings' ] );
    add_settings_section( 'np_time_registration_section', '自动注册与邮件设置', '__return_false', 'np-time-registration-settings' );
    add_settings_field( 'np_time_registration_main', '注册设置', [ $this, 'field_registration_settings' ], 'np-time-registration-settings', 'np_time_registration_section' );
    }

    // 旧 JSON 规则已废弃，不再展示

    public function field_settings() {
        $defaults = [ 'gate_entire_site' => 1, 'window_days' => 30 ];
        $settings = wp_parse_args( get_option( self::OPTION_SETTINGS, [] ), $defaults );
        $window = isset( $settings['window_days'] ) ? (int) $settings['window_days'] : 30;
        if ( $window <= 0 ) { $window = 30; }
        echo '<div class="np-time-settings">';
        echo '<p style="margin-bottom:8px;">'
            . '<input type="hidden" name="' . esc_attr( self::OPTION_SETTINGS ) . '[gate_entire_site]" value="0" />'
            . '<label><input type="checkbox" name="' . esc_attr( self::OPTION_SETTINGS ) . '[gate_entire_site]" value="1" ' . checked( 1, (int) $settings['gate_entire_site'], false ) . '> 首次进入必须选择配送信息（开启后访客进入网站会自动弹出配送设置并强制完成）</label>'
            . '</p>';
        echo '<p><label>可选日期窗口（天数）： <input type="number" min="7" max="180" step="1" name="' . esc_attr( self::OPTION_SETTINGS ) . '[window_days]" value="' . esc_attr( $window ) . '" style="width:80px;"> <span class="description">用于生成前端可选日期列表，默认 30 天（从明天起算）。</span></label></p>';
        echo '</div>';
    }

    public function sanitize_settings( $value ) {
        $defaults = [ 'gate_entire_site' => 1, 'window_days' => 30 ];
        $value = is_array( $value ) ? $value : [];

        $clean = $defaults;
        $clean['gate_entire_site'] = ! empty( $value['gate_entire_site'] ) ? 1 : 0;

        $window = isset( $value['window_days'] ) ? (int) $value['window_days'] : $defaults['window_days'];
        if ( $window < 7 ) {
            $window = 7;
        } elseif ( $window > 180 ) {
            $window = 180;
        }
        $clean['window_days'] = $window;

        return $clean;
    }

    public function field_tip_options() {
        $options = get_option( 'np_time_tip_options', [ '$1.00', '$3.00', '$5.00' ] );
        if ( ! is_array( $options ) ) {
            $options = array_filter( array_map( 'trim', preg_split( '/[\r\n]+/', (string) $options ) ) );
        }
        if ( empty( $options ) ) {
            $options = [ '$1.00', '$3.00', '$5.00' ];
        }

        $value = implode( "\n", $options );

        echo '<textarea name="np_time_tip_options" rows="6" class="large-text code" style="max-width:480px;">' . esc_textarea( $value ) . '</textarea>';
        echo '<p class="description">每行一个金额，例如 <code>$2.00</code>。保存后将在结账页显示同样顺序的小费按钮。</p>';
    }

    public function field_tip_enabled() {
        $enabled = (int) get_option( 'np_time_tip_enabled', 1 );
        echo '<input type="hidden" name="np_time_tip_enabled" value="0" />';
        echo '<label><input type="checkbox" name="np_time_tip_enabled" value="1" ' . checked( 1, $enabled, false ) . '> 启用结账页小费功能</label>';
        echo '<p class="description">关闭后不会展示小费按钮，也不会向订单添加小费。</p>';
    }

    public function sanitize_tip_enabled( $value ) {
        return ! empty( $value ) ? 1 : 0;
    }

    public function render_dashboard_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        echo '<div class="wrap">';
        echo '<h1>NP-Time 设置</h1>';
        echo '<div class="nav-tab-wrapper" style="margin-bottom: 20px;">';
        echo '<a href="' . esc_url( admin_url( 'admin.php?page=np-time-settings' ) ) . '" class="nav-tab">配送设置</a>';
        echo '<a href="' . esc_url( admin_url( 'admin.php?page=np-time-tip-settings' ) ) . '" class="nav-tab">小费设置</a>';
        echo '<a href="' . esc_url( admin_url( 'admin.php?page=np-time-modal-settings' ) ) . '" class="nav-tab">界面设置</a>';
        echo '<a href="' . esc_url( admin_url( 'admin.php?page=np-time-registration-settings' ) ) . '" class="nav-tab">用户设置</a>';
        echo '</div>';
        echo '</div>';
    }

    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) return;
        echo '<div class="wrap">';
        echo '<h1>NP-Time 配送设置</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields( 'np_time_group' );
        do_settings_sections( 'np-time-settings' );
        submit_button();
        echo '</form>';
        echo '</div>';
    }

    public function render_tip_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        echo '<div class="wrap">';
        echo '<h1>小费设置</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields( 'np_time_tip_group' );
        do_settings_sections( 'np-time-tip-settings' );
        submit_button();
        echo '</form>';
        echo '<p style="max-width:640px;margin-top:24px;">提示：结账页会额外提供“自定义小费”和“残忍拒绝”按钮，此处只需要维护默认金额列表即可。</p>';
        echo '</div>';
    }

    public function render_modal_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        echo '<div class="wrap">';
        echo '<h1>弹窗与浮窗设置</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields( 'np_time_modal_group' );
        do_settings_sections( 'np-time-modal-settings' );
        submit_button();
        echo '</form>';
        echo '</div>';
    }

    public function field_coupon_texts() {
        $defaults = np_time_modal_settings_defaults();
        $settings = wp_parse_args( get_option( 'np_time_modal_settings', [] ), $defaults );

        $coupon_fields = [
            'coupon_button_text' => [ 'label' => '按钮文字', 'type' => 'text' ],
            'coupon_modal_title' => [ 'label' => '弹窗标题', 'type' => 'text' ],
            'coupon_loading_text' => [ 'label' => '加载提示', 'type' => 'text' ],
            'coupon_empty_text' => [ 'label' => '无可用提示', 'type' => 'text' ],
            'coupon_apply_button' => [ 'label' => '使用按钮文字', 'type' => 'text' ],
            'coupon_close_text' => [ 'label' => '关闭按钮文字', 'type' => 'text' ],
            'coupon_login_text' => [ 'label' => '登录提示', 'type' => 'text' ],
            // expiry label key used in JS
            'coupon_expiry_label' => [ 'label' => '有效期标签', 'type' => 'text' ],
            'coupon_min_label_format' => [ 'label' => '满X可用模板（%s 为金额）', 'type' => 'text' ],
            'coupon_copied_clipboard' => [ 'label' => '复制到剪贴板提示', 'type' => 'text' ],
            'coupon_copy_instruction' => [ 'label' => '复制指引（带优惠码）', 'type' => 'text' ],
        ];

        echo '<table class="form-table" role="presentation"><tbody>';
        foreach ( $coupon_fields as $key => $field ) {
            $label = $field['label'];
            $value = isset( $settings[ $key ] ) ? $settings[ $key ] : ( isset( $defaults[ $key ] ) ? $defaults[ $key ] : '' );
            echo '<tr>';
            echo '<th scope="row"><label for="np-time-' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th>';
            echo '<td>';
            if ( 'textarea' === $field['type'] ) {
                echo '<textarea id="np-time-' . esc_attr( $key ) . '" name="np_time_modal_settings[' . esc_attr( $key ) . ']" class="large-text" rows="3">' . esc_textarea( $value ) . '</textarea>';
            } else {
                echo '<input type="text" id="np-time-' . esc_attr( $key ) . '" name="np_time_modal_settings[' . esc_attr( $key ) . ']" value="' . esc_attr( $value ) . '" class="regular-text" />';
            }
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '<p class="description">这里的文本会在“选择优惠券”弹窗中显示，支持自定义。保存后多语言插件可以翻译这些字符串。</p>';
    }

    public function render_coupon_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        echo '<div class="wrap">';
        echo '<h1>优惠券设置</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields( 'np_time_coupon_group' );
        do_settings_sections( 'np-time-coupon-settings' );
        submit_button();
        echo '</form>';
        echo '</div>';
    }

    public function render_registration_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        echo '<div class="wrap">';
        echo '<h1>用户注册设置</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields( 'np_time_registration_group' );
        do_settings_sections( 'np-time-registration-settings' );
        submit_button();
        echo '</form>';
        echo '</div>';
    }

    public function field_modal_texts() {
        $defaults = np_time_modal_settings_defaults();
        $settings = wp_parse_args( get_option( 'np_time_modal_settings', [] ), $defaults );

        $text_fields = [
            'modal_title'            => [ 'label' => '弹窗标题', 'type' => 'text' ],
            'step1_title'           => [ 'label' => '步骤一标题', 'type' => 'text' ],
            'postcode_placeholder'  => [ 'label' => '邮编输入占位符', 'type' => 'text' ],
            'step2_title'           => [ 'label' => '步骤二标题', 'type' => 'text' ],
            'date_label'            => [ 'label' => '本地配送日期标签', 'type' => 'text' ],
            'date_placeholder'      => [ 'label' => '日期下拉默认项', 'type' => 'text' ],
            'date_empty_text'       => [ 'label' => '未输入邮编时的日期提示', 'type' => 'text' ],
            'local_title'           => [ 'label' => '本地配送标题', 'type' => 'text' ],
            'local_hint'            => [ 'label' => '本地配送提示', 'type' => 'textarea' ],
            'local_dates_count_format' => [ 'label' => '本地配送：可选日期计数模板（%s 表示数量）', 'type' => 'text' ],
            // 日期格式化多语言设置
            'month_01'              => [ 'label' => '月份显示：1月', 'type' => 'text' ],
            'month_02'              => [ 'label' => '月份显示：2月', 'type' => 'text' ],
            'month_03'              => [ 'label' => '月份显示：3月', 'type' => 'text' ],
            'month_04'              => [ 'label' => '月份显示：4月', 'type' => 'text' ],
            'month_05'              => [ 'label' => '月份显示：5月', 'type' => 'text' ],
            'month_06'              => [ 'label' => '月份显示：6月', 'type' => 'text' ],
            'month_07'              => [ 'label' => '月份显示：7月', 'type' => 'text' ],
            'month_08'              => [ 'label' => '月份显示：8月', 'type' => 'text' ],
            'month_09'              => [ 'label' => '月份显示：9月', 'type' => 'text' ],
            'month_10'              => [ 'label' => '月份显示：10月', 'type' => 'text' ],
            'month_11'              => [ 'label' => '月份显示：11月', 'type' => 'text' ],
            'month_12'              => [ 'label' => '月份显示：12月', 'type' => 'text' ],
            'weekday_sun'           => [ 'label' => '星期显示：星期日', 'type' => 'text' ],
            'weekday_mon'           => [ 'label' => '星期显示：星期一', 'type' => 'text' ],
            'weekday_tue'           => [ 'label' => '星期显示：星期二', 'type' => 'text' ],
            'weekday_wed'           => [ 'label' => '星期显示：星期三', 'type' => 'text' ],
            'weekday_thu'           => [ 'label' => '星期显示：星期四', 'type' => 'text' ],
            'weekday_fri'           => [ 'label' => '星期显示：星期五', 'type' => 'text' ],
            'weekday_sat'           => [ 'label' => '星期显示：星期六', 'type' => 'text' ],
            'date_weekday_format'   => [ 'label' => '日期星期格式模板（%s月份 %s日期 %s星期）', 'type' => 'text' ],
            'weekday_label'         => [ 'label' => '非本地配送时间标签', 'type' => 'text' ],
            'weekday_placeholder'   => [ 'label' => '时间下拉默认项', 'type' => 'text' ],
            'weekday_empty_text'    => [ 'label' => '未输入邮编时的时间提示', 'type' => 'text' ],
            'nonlocal_title'        => [ 'label' => '非本地配送标题', 'type' => 'text' ],
            'nonlocal_hint'         => [ 'label' => '非本地配送提示', 'type' => 'textarea' ],
            'nonlocal_times_label'  => [ 'label' => '非本地可配送时间标签', 'type' => 'text' ],
            'confirm_button_text'   => [ 'label' => '确认按钮文字', 'type' => 'text' ],
            'loading_text'          => [ 'label' => '加载提示', 'type' => 'text' ],
            'invalid_postcode_text' => [ 'label' => '邮编无效提示', 'type' => 'text' ],
            'no_local_dates_text'   => [ 'label' => '无可选日期提示', 'type' => 'text' ],
            'no_times_text'         => [ 'label' => '无可配送时间提示', 'type' => 'text' ],
            'network_error_text'    => [ 'label' => '网络错误提示', 'type' => 'text' ],
            'missing_date_alert'    => [ 'label' => '缺少日期警告', 'type' => 'text' ],
            'missing_weekday_alert' => [ 'label' => '缺少时间警告', 'type' => 'text' ],
            // 通用显示
            'label_postcode'        => [ 'label' => '标签：配送邮编（含冒号）', 'type' => 'text' ],
            'label_date'            => [ 'label' => '标签：配送日期（含冒号）', 'type' => 'text' ],
            'edit_button_text'      => [ 'label' => '编辑按钮文字', 'type' => 'text' ],
            'not_selected_text'     => [ 'label' => '未选择占位文字', 'type' => 'text' ],
            // 小费相关
            'tip_section_title'          => [ 'label' => '小费区块标题', 'type' => 'text' ],
            'tip_custom_button'          => [ 'label' => '自定义小费按钮', 'type' => 'text' ],
            'tip_refuse_button'          => [ 'label' => '拒绝小费按钮', 'type' => 'text' ],
            'tip_input_placeholder'      => [ 'label' => '自定义金额占位符', 'type' => 'text' ],
            'tip_confirm_button'         => [ 'label' => '自定义金额确认按钮', 'type' => 'text' ],
            'tip_invalid_amount_alert'   => [ 'label' => '无效金额提示', 'type' => 'text' ],
            'tip_cancelled_feedback'     => [ 'label' => '取消小费反馈', 'type' => 'text' ],
            'tip_added_feedback_template'=> [ 'label' => '添加小费反馈模板（%s为金额）', 'type' => 'text' ],
            'tip_fee_name'               => [ 'label' => 'WooCommerce 费用名称', 'type' => 'text' ],
            // 后端提示
            'invalid_choice_message'     => [ 'label' => '后端：选择无效提示', 'type' => 'text' ],
            'invalid_tip_type_message'   => [ 'label' => '后端：小费类型无效', 'type' => 'text' ],
            'wc_notice_require_choice'   => [ 'label' => 'Woo提示：需先选择配送', 'type' => 'text' ],
            'wc_notice_choice_invalid'   => [ 'label' => 'Woo提示：选择不再可用', 'type' => 'text' ],
            'checkout_postcode_mismatch' => [ 'label' => '校验：账单邮编不一致（含%s）', 'type' => 'text' ],
            // 项目名称（无冒号）
            'item_label_postcode'        => [ 'label' => '项目名：配送邮编（无冒号）', 'type' => 'text' ],
            'item_label_date'            => [ 'label' => '项目名：配送日期（无冒号）', 'type' => 'text' ],
            // 其他警告/提示
            'address_postcode_mismatch_alert' => [ 'label' => '前端：地址邮编不一致提示', 'type' => 'text' ],
            'postcode_mismatch_updated_header' => [ 'label' => '前端：邮编不一致列表标题', 'type' => 'text' ],
            'postcode_mismatch_delivery_label' => [ 'label' => '前端：邮编不一致提示-配送邮编标签', 'type' => 'text' ],
            'postcode_mismatch_fix_advice'     => [ 'label' => '前端：邮编不一致提示-修正建议', 'type' => 'text' ],
            'label_shipping_postcode'          => [ 'label' => '标签：收货地址邮编（无冒号）', 'type' => 'text' ],
            'label_billing_postcode'           => [ 'label' => '标签：账单邮编（无冒号）', 'type' => 'text' ],
            'postcode_mismatch_single_template'=> [ 'label' => '前端：单字段不一致模板（含%s）', 'type' => 'text' ],
            'save_failed_fallback'             => [ 'label' => '前端：保存失败兜底文案', 'type' => 'text' ],
            // 优惠券弹窗
            'coupon_button_text'        => [ 'label' => '优惠券：按钮文字', 'type' => 'text' ],
            'coupon_modal_title'        => [ 'label' => '优惠券：弹窗标题', 'type' => 'text' ],
            'coupon_loading_text'       => [ 'label' => '优惠券：加载提示', 'type' => 'text' ],
            'coupon_empty_text'         => [ 'label' => '优惠券：无可用提示', 'type' => 'text' ],
            'coupon_apply_button'       => [ 'label' => '优惠券：使用按钮文字', 'type' => 'text' ],
            'coupon_close_text'         => [ 'label' => '优惠券：关闭按钮文字', 'type' => 'text' ],
            'coupon_login_text'         => [ 'label' => '优惠券：登录提示', 'type' => 'text' ],
            // 产品配送日期相关
            'product_delivery_conflict_message' => [ 'label' => '产品配送：冲突确认消息', 'type' => 'textarea' ],
            'product_delivery_conflict_items_header' => [ 'label' => '产品配送：冲突商品列表标题', 'type' => 'text' ],
            'product_delivery_remove_success' => [ 'label' => '产品配送：移除成功提示', 'type' => 'text' ],
            'product_delivery_remove_failed' => [ 'label' => '产品配送：移除失败提示', 'type' => 'text' ],
            'product_delivery_remove_partial_failed' => [ 'label' => '产品配送：部分移除失败提示', 'type' => 'text' ],
            'product_delivery_not_available_for_date' => [ 'label' => '产品配送：商品不可配送提示（%s商品名 %s日期）', 'type' => 'textarea' ],
            // 系统错误消息
            'missing_required_params' => [ 'label' => '系统错误：缺少必要参数', 'type' => 'text' ],
            'cart_unavailable' => [ 'label' => '系统错误：购物车不可用', 'type' => 'text' ],
            // 弹窗按钮样式
            'modal_button_bg'           => [ 'label' => '弹窗按钮背景颜色', 'type' => 'text' ],
            'modal_button_text_color'   => [ 'label' => '弹窗按钮文字颜色', 'type' => 'text' ],
            'modal_button_border_radius'=> [ 'label' => '弹窗按钮圆角（例如 8px）', 'type' => 'text' ],
            'modal_button_font_family'  => [ 'label' => '弹窗按钮字体（可选）', 'type' => 'text' ],
            'modal_button_font_size'    => [ 'label' => '弹窗按钮字体大小（例如 14px）', 'type' => 'text' ],
        ];

        echo '<table class="form-table" role="presentation"><tbody>';
        foreach ( $text_fields as $key => $field ) {
            $label = $field['label'];
            $value = isset( $settings[ $key ] ) ? $settings[ $key ] : $defaults[ $key ];
            echo '<tr>';
            echo '<th scope="row"><label for="np-time-' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th>';
            echo '<td>';
            if ( 'textarea' === $field['type'] ) {
                echo '<textarea id="np-time-' . esc_attr( $key ) . '" name="np_time_modal_settings[' . esc_attr( $key ) . ']" class="large-text" rows="3">' . esc_textarea( $value ) . '</textarea>';
            } else {
                echo '<input type="text" id="np-time-' . esc_attr( $key ) . '" name="np_time_modal_settings[' . esc_attr( $key ) . ']" value="' . esc_attr( $value ) . '" class="regular-text" />';
            }
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }

    public function field_fab_settings() {
        $defaults = np_time_modal_settings_defaults();
        $settings = wp_parse_args( get_option( 'np_time_modal_settings', [] ), $defaults );

        $position = isset( $settings['fab_position'] ) ? $settings['fab_position'] : $defaults['fab_position'];
        $fab_text = isset( $settings['fab_text'] ) ? $settings['fab_text'] : $defaults['fab_text'];
        $fab_bg   = isset( $settings['fab_bg_color'] ) ? $settings['fab_bg_color'] : $defaults['fab_bg_color'];
        $fab_text_color = isset( $settings['fab_text_color'] ) ? $settings['fab_text_color'] : $defaults['fab_text_color'];
        $fab_icon = isset( $settings['fab_icon'] ) ? $settings['fab_icon'] : $defaults['fab_icon'];
        $fab_icon_id = isset( $settings['fab_icon_id'] ) ? (int) $settings['fab_icon_id'] : 0;

        echo '<table class="form-table" role="presentation"><tbody>';
        echo '<tr><th scope="row">浮窗位置</th><td><select name="np_time_modal_settings[fab_position]">';
        $positions = [ 'left' => '左下角', 'right' => '右下角' ];
        foreach ( $positions as $value => $label ) {
            echo '<option value="' . esc_attr( $value ) . '" ' . selected( $position, $value, false ) . '>' . esc_html( $label ) . '</option>';
        }
        echo '</select></td></tr>';

        echo '<tr><th scope="row"><label for="np-time-fab-text">按钮文字</label></th><td>';
        echo '<input type="text" id="np-time-fab-text" name="np_time_modal_settings[fab_text]" value="' . esc_attr( $fab_text ) . '" class="regular-text" />';
        echo '</td></tr>';

        echo '<tr><th scope="row"><label for="np-time-fab-bg">背景颜色</label></th><td>';
        echo '<input type="text" id="np-time-fab-bg" name="np_time_modal_settings[fab_bg_color]" value="' . esc_attr( $fab_bg ) . '" class="regular-text np-time-color-field" placeholder="#007cba" />';
        echo '</td></tr>';

        echo '<tr><th scope="row"><label for="np-time-fab-text-color">文字颜色</label></th><td>';
        echo '<input type="text" id="np-time-fab-text-color" name="np_time_modal_settings[fab_text_color]" value="' . esc_attr( $fab_text_color ) . '" class="regular-text np-time-color-field" placeholder="#ffffff" />';
        echo '</td></tr>';

        echo '<tr><th scope="row">按钮图标</th><td>';
        echo '<div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">';
        echo '<input type="text" id="np-time-fab-icon" name="np_time_modal_settings[fab_icon]" value="' . esc_attr( $fab_icon ) . '" class="regular-text" placeholder="https://example.com/icon.png" style="max-width:320px;" />';
        echo '<input type="hidden" id="np-time-fab-icon-id" name="np_time_modal_settings[fab_icon_id]" value="' . esc_attr( $fab_icon_id ) . '" />';
        echo '<button type="button" class="button np-time-media-select" data-target="np-time-fab-icon" data-target-id="np-time-fab-icon-id">选择图片</button>';
        echo '<button type="button" class="button-link-delete np-time-media-clear" data-target="np-time-fab-icon" data-target-id="np-time-fab-icon-id">清除</button>';
        echo '<div class="np-time-fab-preview" style="min-height:36px;display:flex;align-items:center;">';
        if ( $fab_icon ) {
            echo '<img src="' . esc_url( $fab_icon ) . '" alt="" style="max-height:36px;border-radius:4px;" />';
        }
        echo '</div>';
        echo '</div>';
        echo '<p class="description">建议上传 32px 的透明背景图标，保存后会显示在按钮文字前。</p>';
        echo '</td></tr>';

        echo '</tbody></table>';
    }

    public function sanitize_modal_settings( $value ) {
        $defaults = np_time_modal_settings_defaults();
        $value = is_array( $value ) ? $value : [];

        $clean = $defaults;

        $text_fields = [
            'modal_title',
            'step1_title',
            'postcode_placeholder',
            'step2_title',
            'date_label',
            'date_placeholder',
            'date_empty_text',
            'weekday_label',
            'weekday_placeholder',
            'weekday_empty_text',
            'confirm_button_text',
            'loading_text',
            'invalid_postcode_text',
            'no_local_dates_text',
            'no_times_text',
            'network_error_text',
            'missing_date_alert',
            'missing_weekday_alert',
            // 新增可配置文本
            'label_postcode',
            'label_date',
            'edit_button_text',
            'not_selected_text',
            'tip_section_title',
            'tip_custom_button',
            'tip_refuse_button',
            'tip_input_placeholder',
            'tip_confirm_button',
            'tip_invalid_amount_alert',
            'tip_cancelled_feedback',
            'tip_added_feedback_template',
            'tip_fee_name',
            'invalid_choice_message',
            'invalid_tip_type_message',
            'wc_notice_require_choice',
            'wc_notice_choice_invalid',
            'checkout_postcode_mismatch',
            'item_label_postcode',
            'item_label_date',
            'address_postcode_mismatch_alert',
            'postcode_mismatch_updated_header',
            'postcode_mismatch_delivery_label',
            'postcode_mismatch_fix_advice',
            'label_shipping_postcode',
            'label_billing_postcode',
            'postcode_mismatch_single_template',
            'save_failed_fallback',
            'coupon_button_text',
            'coupon_modal_title',
            'coupon_loading_text',
            'coupon_empty_text',
            'coupon_apply_button',
            'coupon_close_text',
            'coupon_login_text',
            // 按钮样式字段
            'modal_button_bg',
            'modal_button_text_color',
            'modal_button_border_radius',
            'modal_button_font_family',
            'modal_button_font_size',
            // 产品配送日期相关
            'product_delivery_conflict_items_header',
            'product_delivery_remove_success',
            'product_delivery_remove_failed',
            'product_delivery_remove_partial_failed',
            'missing_required_params',
            'cart_unavailable',
        ];

        foreach ( $text_fields as $field ) {
            if ( isset( $value[ $field ] ) ) {
                $clean[ $field ] = sanitize_text_field( $value[ $field ] );
            }
        }

        $textarea_fields = [ 'local_hint', 'nonlocal_hint', 'product_delivery_conflict_message', 'product_delivery_not_available_for_date' ];
        foreach ( $textarea_fields as $field ) {
            if ( isset( $value[ $field ] ) ) {
                $clean[ $field ] = sanitize_textarea_field( $value[ $field ] );
            }
        }

        $position = isset( $value['fab_position'] ) ? strtolower( $value['fab_position'] ) : $defaults['fab_position'];
        $clean['fab_position'] = in_array( $position, [ 'left', 'right' ], true ) ? $position : $defaults['fab_position'];

        if ( isset( $value['fab_text'] ) ) {
            $clean['fab_text'] = sanitize_text_field( $value['fab_text'] );
        }

        $color_fields = [ 'fab_bg_color', 'fab_text_color' ];
        foreach ( $color_fields as $field ) {
            if ( isset( $value[ $field ] ) ) {
                $color = sanitize_hex_color( $value[ $field ] );
                $clean[ $field ] = $color ? $color : $defaults[ $field ];
            }
        }

        // sanitize modal button colors
        $button_color_fields = [ 'modal_button_bg', 'modal_button_text_color' ];
        foreach ( $button_color_fields as $field ) {
            if ( isset( $value[ $field ] ) ) {
                $color = sanitize_hex_color( $value[ $field ] );
                $clean[ $field ] = $color ? $color : $defaults[ $field ];
            }
        }

        if ( isset( $value['modal_button_border_radius'] ) ) {
            $clean['modal_button_border_radius'] = sanitize_text_field( $value['modal_button_border_radius'] );
        }
        if ( isset( $value['modal_button_font_family'] ) ) {
            $clean['modal_button_font_family'] = sanitize_text_field( $value['modal_button_font_family'] );
        }
        if ( isset( $value['modal_button_font_size'] ) ) {
            $clean['modal_button_font_size'] = sanitize_text_field( $value['modal_button_font_size'] );
        }

        if ( isset( $value['fab_icon'] ) ) {
            $clean['fab_icon'] = esc_url_raw( $value['fab_icon'] );
        }

        if ( isset( $value['fab_icon_id'] ) ) {
            $clean['fab_icon_id'] = absint( $value['fab_icon_id'] );
        }

        if ( function_exists( 'np_time_register_modal_strings' ) ) {
            np_time_register_modal_strings( $clean );
        }

        return $clean;
    }

    public function field_local_postcodes() {
        $codes = $this->read_local_postcodes();
        $codes_count = count( $codes );

        echo '<div id="np-time-local-manager">';
        echo '<p class="description">每个本地邮编独立管理，支持通配符（如 100* 代表以 100 开头的邮编）。</p>';
        echo '<div class="quick-add" style="margin:8px 0;">';
        echo '<input type="text" class="postcode-input" placeholder="添加本地邮编（支持 100*）" style="width:220px;margin-right:6px;" />';
        echo '<button type="button" class="button add-local-postcode">添加</button> ';
        echo '<input type="file" id="np-time-local-csv" accept=".csv" style="margin-left:8px;"> ';
        echo '<button type="button" class="button" id="np-time-local-csv-import">导入CSV</button>';
        echo '</div>';

    // 搜索与删除匹配
    echo '<div class="search-row" style="margin:8px 0;">';
    echo '<input type="text" class="postcode-search" data-scope="local" placeholder="搜索本地邮编..." style="width:220px;margin-right:6px;" />';
    echo '<button type="button" class="button delete-matched" data-scope="local" style="margin-right:6px;">删除匹配项</button>';
    echo '<button type="button" class="button button-secondary clear-all" data-scope="local">清空全部</button>';
    echo '</div>';

        echo '<div class="postcodes-list local" style="max-height:160px;overflow-y:auto;border:1px solid #ddd;padding:8px;background:#fff;">';
        if ( $codes_count > 0 ) {
            foreach ( $codes as $code ) {
                echo '<div class="postcode-item" style="display:inline-block;background:#e8f4fd;padding:3px 6px;margin:2px;border-radius:3px;font-family:monospace;">';
                echo '<span class="postcode-value">' . esc_html( $code ) . '</span> ';
                echo '<button type="button" class="remove-local-postcode" data-code="' . esc_attr( $code ) . '" style="color:red;margin-left:3px;background:none;border:none;cursor:pointer;text-decoration:none;" title="删除" aria-label="删除">&times;</button>';
                echo '</div>';
            }
        } else {
            echo '<em style="color:#999;">暂无本地邮编</em>';
        }
        echo '</div>';
        echo '<input type="hidden" name="np_time_local_postcodes" id="np-time-local-postcodes" value="' . esc_attr( implode( "\n", $codes ) ) . '" />';
        echo '</div>';
        echo '<style>.postcode-item:hover{background:#d4edda!important}.remove-local-postcode:hover{background:#dc3545;color:#fff;border-radius:50%;}</style>';
    }

    // ---------- 文件读写与拦截 ----------
    private function data_dir() {
        return trailingslashit( NP_TIME_PATH . 'data' );
    }

    private function ensure_data_dir() {
        $dir = $this->data_dir();
        if ( ! file_exists( $dir ) ) {
            wp_mkdir_p( $dir );
        }
        // 写入 .htaccess 保护 data 目录
        $ht = trailingslashit( $dir ) . '.htaccess';
        if ( ! file_exists( $ht ) ) {
            $rules  = "Options -Indexes\n";
            $rules .= "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n";
            $rules .= "<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n";
            @file_put_contents( $ht, $rules );
        }
        return $dir;
    }

    private function read_local_postcodes() {
        $file = $this->data_dir() . 'local_postcodes.txt';
        if ( file_exists( $file ) ) {
            $raw = file_get_contents( $file );
            $lines = preg_split( '/\r?\n/', (string) $raw );
            $codes = array_values( array_filter( array_map( 'trim', $lines ) ) );
            return $codes;
        }
        // 兼容首次：从数据库读取一次
        $codes_raw = (string) get_option( 'np_time_local_postcodes', '' );
        $codes = array_values( array_filter( array_map( 'trim', preg_split( '/\r?\n/', $codes_raw ) ) ) );
        return $codes;
    }

    private function write_local_postcodes( $codes_array ) {
        $this->ensure_data_dir();
        $file = $this->data_dir() . 'local_postcodes.txt';
        $content = implode( "\n", array_values( array_unique( array_map( 'trim', (array) $codes_array ) ) ) );
        file_put_contents( $file, $content );
    }

    private function read_weekday_postcodes() {
        $file = $this->data_dir() . 'weekday_postcodes.json';
        if ( file_exists( $file ) ) {
            $json = file_get_contents( $file );
            $data = json_decode( (string) $json, true );
            if ( is_array( $data ) ) return $data;
        }
        // 兼容首次：从数据库读取一次（结构为 [ '0' => "...", '1' => "..." ]）
        $opt = get_option( 'np_time_weekday_postcodes', [] );
        if ( ! is_array( $opt ) ) $opt = [];
        return $opt;
    }

    private function write_weekday_postcodes( $map ) {
        $this->ensure_data_dir();
        $file = $this->data_dir() . 'weekday_postcodes.json';
        // 确保键 0-6 存在，值为字符串（换行分隔或逗号分隔原样保存）
        $norm = [];
        for ( $i = 0; $i <= 6; $i++ ) {
            $v = isset( $map[ (string) $i ] ) ? (string) $map[ (string) $i ] : '';
            $norm[(string)$i] = $v;
        }
        file_put_contents( $file, wp_json_encode( $norm, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
    }

    public function intercept_save_local( $value, $old_value, $option ) {
        // $value 为 textarea 的换行文本
        $lines = preg_split( '/\r?\n/', (string) $value );
        $codes = array_values( array_filter( array_map( 'trim', $lines ) ) );
        $this->write_local_postcodes( $codes );
        // 阻止入库：返回旧值
        return $old_value;
    }

    public function intercept_save_weekday( $value, $old_value, $option ) {
        // $value 为数组：['0' => '...', ..., '6' => '...']
        if ( ! is_array( $value ) ) $value = [];
        $this->write_weekday_postcodes( $value );
        return $old_value;
    }

    public function sanitize_tip_options( $value ) {
        if ( is_string( $value ) ) {
            $items = preg_split( '/[\r\n]+/', $value );
        } elseif ( is_array( $value ) ) {
            $items = $value;
        } else {
            $items = [];
        }

        $clean = [];
        foreach ( $items as $item ) {
            $item = wp_strip_all_tags( (string) $item );
            $item = trim( $item );
            if ( '' === $item ) {
                continue;
            }

            $normalized = str_replace( [',', '$', '￥', '¥', '元', '人民币' ], '', $item );
            $normalized = trim( $normalized );
            if ( is_numeric( $normalized ) ) {
                $amount = (float) $normalized;
                if ( $amount < 0 ) {
                    $amount = 0;
                }
                $item = '$' . number_format( $amount, 2, '.', '' );
            }

            if ( ! in_array( $item, $clean, true ) ) {
                $clean[] = $item;
            }
        }

        if ( empty( $clean ) ) {
            $clean = [ '$1.00', '$3.00', '$5.00' ];
        }

        return $clean;
    }

    public function field_weekday_postcodes() {
        $opt = $this->read_weekday_postcodes();
        if ( ! is_array( $opt ) ) $opt = [];
        
    $days = [ '星期日','星期一','星期二','星期三','星期四','星期五','星期六' ];
        
        echo '<div class="np-time-weekday-manager">';
    echo '<p class="description">为每个星期几设置可配送的邮编（存储在插件 data 目录，方便迁移到生产环境）。非本地邮编用户将看到星期几选择而非日期选择。支持通配符如 100*。</p>';
        
        // 批量导入工具
        echo '<div class="np-time-batch-tools" style="background:#f9f9f9;padding:15px;margin:10px 0;border-radius:5px;">';
        echo '<h4 style="margin-top:0;">批量管理工具</h4>';
        echo '<p><strong>CSV 批量导入：</strong> ';
        echo '<select id="np-time-weekday-target" style="margin:0 5px;">';
        for ( $i = 0; $i <= 6; $i++ ) { 
            echo '<option value="'.$i.'">'.$days[$i].'</option>'; 
        }
        echo '</select> ';
        echo '<input type="file" id="np-time-weekday-csv" accept=".csv" style="margin:0 5px;"> ';
    echo '<button type="button" class="button" id="np-time-import-weekday">导入到指定星期几</button></p>';
        echo '</div>';
        
    // 星期几管理表格
    echo '<table class="form-table np-time-weekday-table" style="width:100%;">';
        for ( $i = 0; $i <= 6; $i++ ) {
            $postcodes = isset( $opt[ (string) $i ] ) ? (string) $opt[ (string) $i ] : '';
            $codes_array = array_filter( array_map( 'trim', preg_split( '/[\r\n,]+/', $postcodes ) ) );
            $codes_count = count( $codes_array );
            
            echo '<tr class="weekday-row" data-day="'.$i.'">';
            echo '<th style="width:80px;vertical-align:top;padding-top:15px;">';
            echo '<strong style="font-size:14px;">' . $days[$i] . '</strong>';
            if ( $codes_count > 0 ) {
                echo '<br><small style="color:#666;">('.$codes_count.'个邮编)</small>';
            }
            echo '</th>';
            echo '<td style="position:relative;">';
            
            // 邮编管理区域
            echo '<div class="postcode-management">';
            
            // 快速添加
            echo '<div class="quick-add" style="margin-bottom:10px;">';
            echo '<input type="text" class="postcode-input" placeholder="添加邮编（支持 100* 通配符）" style="width:200px;margin-right:5px;">';
            echo '<button type="button" class="button add-postcode" data-day="'.$i.'">添加</button>';
            echo '</div>';
            
            // 搜索与删除匹配
            echo '<div class="search-row" style="margin:8px 0;">';
            echo '<input type="text" class="postcode-search" data-scope="weekday" data-day="'.$i.'" placeholder="搜索该星期的邮编..." style="width:220px;margin-right:6px;" />';
            echo '<button type="button" class="button delete-matched" data-scope="weekday" data-day="'.$i.'" style="margin-right:6px;">删除匹配项</button>';
            echo '<button type="button" class="button button-secondary clear-all" data-scope="weekday" data-day="'.$i.'">清空全部</button>';
            echo '</div>';

            // 邮编列表
            echo '<div class="postcodes-list" style="max-height:120px;overflow-y:auto;border:1px solid #ddd;padding:8px;background:#fff;">';
            if ( $codes_count > 0 ) {
                foreach ( $codes_array as $code ) {
                    echo '<div class="postcode-item" style="display:inline-block;background:#e8f4fd;padding:3px 6px;margin:2px;border-radius:3px;font-family:monospace;">';
                    echo '<span class="postcode-value">' . esc_html( $code ) . '</span> ';
                    echo '<button type="button" class="remove-postcode" data-day="'.$i.'" data-code="' . esc_attr( $code ) . '" style="color:red;margin-left:3px;background:none;border:none;cursor:pointer;text-decoration:none;" title="删除" aria-label="删除">&times;</button>';
                    echo '</div>';
                }
            } else {
                echo '<em style="color:#999;">暂无邮编</em>';
            }
            echo '</div>';
            
            // 隐藏的textarea用于提交
            echo '<textarea name="np_time_weekday_postcodes['.$i.']" class="weekday-postcodes-input" style="display:none;">' . esc_textarea( $postcodes ) . '</textarea>';
            
            echo '</div>';
            echo '</td></tr>';
        }
        echo '</table>';
        echo '</div>';
        
        // 添加样式
        echo '<style>
        .np-time-weekday-table .weekday-row:hover { background-color: #f8f9fa; }
        .postcode-item:hover { background-color: #d4edda !important; }
        .remove-postcode:hover { background-color: #dc3545; color: white !important; border-radius: 50%; }
        .quick-add input:focus { border-color: #007cba; box-shadow: 0 0 2px rgba(0,124,186,0.5); }
        </style>';
    }

    public function field_registration_settings() {
        // 获取WooCommerce账户页面的密码重置链接作为默认值
        $default_wc_link = '';
        if ( function_exists( 'wc_get_page_id' ) && function_exists( 'get_permalink' ) ) {
            $myaccount_page_id = wc_get_page_id( 'myaccount' );
            if ( $myaccount_page_id && $myaccount_page_id > 0 ) {
                $default_wc_link = add_query_arg( [
                    'action' => 'rp',
                    'key' => '%key%',
                    'login' => '%login%'
                ], get_permalink( $myaccount_page_id ) );
            }
        }
        
        $defaults = [
            'enable_auto_registration' => 1,
            'custom_password_link' => $default_wc_link,
            'email_subject' => '[%s] 账户创建：设置您的密码',
            'email_content' => "您好，\n\n我们已基于您本次下单使用的邮箱创建了账户：\n\n用户名：%s\n设置密码链接：%s\n\n如果非您本人操作，请忽略此邮件。\n",
        ];
        $settings = wp_parse_args( get_option( 'np_time_registration_settings', [] ), $defaults );
        
        echo '<table class="form-table" role="presentation"><tbody>';
        
        // 启用自动注册
        echo '<tr>';
        echo '<th scope="row">自动注册功能</th>';
        echo '<td>';
        echo '<input type="hidden" name="np_time_registration_settings[enable_auto_registration]" value="0" />';
        echo '<label><input type="checkbox" name="np_time_registration_settings[enable_auto_registration]" value="1" ' . checked( 1, (int) $settings['enable_auto_registration'], false ) . '> 启用下单后自动为未注册邮箱创建账户</label>';
        echo '<p class="description">关闭后不会为游客下单创建账户或发送邮件。</p>';
        echo '</td>';
        echo '</tr>';
        
        // 自定义密码设置链接
        echo '<tr>';
        echo '<th scope="row"><label for="custom-password-link">自定义设置密码链接</label></th>';
        echo '<td>';
        echo '<input type="url" id="custom-password-link" name="np_time_registration_settings[custom_password_link]" value="' . esc_attr( $settings['custom_password_link'] ) . '" class="regular-text" placeholder="' . esc_attr( $default_wc_link ) . '" />';
        echo '<p class="description">默认使用WooCommerce我的账户页面的密码重置链接。如需自定义，请填写完整URL。<br>支持的占位符：%key% (密码重置密钥), %login% (用户名), %email% (邮箱)</p>';
        if ( $default_wc_link ) {
            echo '<p class="description"><strong>当前默认链接：</strong> <code>' . esc_html( $default_wc_link ) . '</code></p>';
        }
        echo '</td>';
        echo '</tr>';
        
        // 邮件主题
        echo '<tr>';
        echo '<th scope="row"><label for="email-subject">邮件主题</label></th>';
        echo '<td>';
        echo '<input type="text" id="email-subject" name="np_time_registration_settings[email_subject]" value="' . esc_attr( $settings['email_subject'] ) . '" class="regular-text" />';
        echo '<p class="description">%s 会被替换为网站名称</p>';
        echo '</td>';
        echo '</tr>';
        
        // 邮件内容
        echo '<tr>';
        echo '<th scope="row"><label for="email-content">邮件内容</label></th>';
        echo '<td>';
        echo '<textarea id="email-content" name="np_time_registration_settings[email_content]" class="large-text" rows="8">' . esc_textarea( $settings['email_content'] ) . '</textarea>';
        echo '<p class="description">支持的占位符：第一个 %s = 用户名，第二个 %s = 设置密码链接</p>';
        echo '</td>';
        echo '</tr>';
        
        echo '</tbody></table>';
    }

    public function sanitize_registration_settings( $value ) {
        // 获取WooCommerce账户页面的密码重置链接作为默认值
        $default_wc_link = '';
        if ( function_exists( 'wc_get_page_id' ) && function_exists( 'get_permalink' ) ) {
            $myaccount_page_id = wc_get_page_id( 'myaccount' );
            if ( $myaccount_page_id && $myaccount_page_id > 0 ) {
                $default_wc_link = add_query_arg( [
                    'action' => 'rp',
                    'key' => '%key%',
                    'login' => '%login%'
                ], get_permalink( $myaccount_page_id ) );
            }
        }
        
        $defaults = [
            'enable_auto_registration' => 1,
            'custom_password_link' => $default_wc_link,
            'email_subject' => '[%s] 账户创建：设置您的密码',
            'email_content' => "您好，\n\n我们已基于您本次下单使用的邮箱创建了账户：\n\n用户名：%s\n设置密码链接：%s\n\n如果非您本人操作，请忽略此邮件。\n",
        ];
        $value = is_array( $value ) ? $value : [];
        $clean = $defaults;
        
        $clean['enable_auto_registration'] = ! empty( $value['enable_auto_registration'] ) ? 1 : 0;
        
        if ( isset( $value['custom_password_link'] ) ) {
            $clean['custom_password_link'] = esc_url_raw( $value['custom_password_link'] );
        }
        
        if ( isset( $value['email_subject'] ) ) {
            $clean['email_subject'] = sanitize_text_field( $value['email_subject'] );
        }
        
        if ( isset( $value['email_content'] ) ) {
            $clean['email_content'] = sanitize_textarea_field( $value['email_content'] );
        }
        
        return $clean;
    }

    // 旧的 CPT 元框已废弃

    public function mb_dates( $post ) {
        wp_nonce_field( 'np_time_rule_save', 'np_time_rule_nonce' );
        $dates = (array) get_post_meta( $post->ID, '_np_time_dates', true );
        $days = (array) get_post_meta( $post->ID, '_np_time_days_of_week', true );
        $mode = ! empty( $dates ) ? 'date' : ( ! empty( $days ) ? 'week' : 'date' );
        echo '<p><strong>选择模式：</strong> '
            . '<label style="margin-right:12px"><input type="radio" name="np_time_mode" value="date" ' . checked( $mode, 'date', false ) . '> 按具体日期</label>'
            . '<label><input type="radio" name="np_time_mode" value="week" ' . checked( $mode, 'week', false ) . '> 按星期几</label>'
            . '</p>';
        echo '<div id="np-time-date-block" style="' . ( $mode === 'date' ? '' : 'display:none' ) . '">';
        echo '<p>具体日期（使用日期选择器添加；支持添加多个）：</p>';
        echo '<div id="np-time-date-picker-wrap">'
            . '<input type="text" id="np-time-date-input" placeholder="选择日期" style="max-width:200px" /> '
            . '<button type="button" class="button" id="np-time-add-date">添加日期</button>'
            . '</div>';
        echo '<ul id="np-time-date-list" style="margin:8px 0;padding-left:18px;list-style:disc;">';
        foreach ( $dates as $d ) {
            echo '<li data-value="' . esc_attr( $d ) . '">' . esc_html( $d ) . ' <a href="#" class="np-time-remove-date">移除</a></li>';
        }
        echo '</ul>';
        echo '<input type="hidden" name="np_time_dates" id="np-time-dates-hidden" value="' . esc_attr( implode( "\n", $dates ) ) . '" />';
        echo '</div>';
    $labels = [ '星期日','星期一','星期二','星期三','星期四','星期五','星期六' ];
        echo '<div id="np-time-days-block" style="' . ( $mode === 'week' ? '' : 'display:none' ) . '">';
    echo '<p id="np-time-days-container">按星期几配送（可多选）：</p><p id="np-time-days-checkboxes">';
        for ( $i = 0; $i <= 6; $i++ ) {
            echo '<label style="margin-right:8px"><input type="checkbox" class="np-time-day" name="np_time_days_of_week[]" value="' . esc_attr( $i ) . '" ' . checked( in_array( $i, array_map( 'intval', $days ), true ), true, false ) . '> ' . esc_html( $labels[$i] ) . '</label>';
        }
        echo '</p>';
        echo '</div>';
    echo '<p class="description">规则：先选择模式（按具体日期 或 按星期几），仅能二选一。</p>';
    }

    public function mb_postcodes( $post ) {
        $codes = (array) get_post_meta( $post->ID, '_np_time_postcodes', true );
        echo '<p>每行一个邮编（支持前缀通配如 100*）。也可导入 CSV：第一行会被忽略。</p>';
        echo '<p><input type="file" id="np-time-csv" accept=".csv"> <button type="button" class="button" id="np-time-csv-import">导入CSV</button></p>';
        echo '<textarea id="np-time-postcodes" name="np_time_postcodes" rows="10" style="width:100%;font-family:monospace;">' . esc_textarea( implode( "\n", $codes ) ) . '</textarea>';
    }

    // 产品元框移除

    public function save_rule_meta( $post_id ) {
        if ( ! isset( $_POST['np_time_rule_nonce'] ) || ! wp_verify_nonce( $_POST['np_time_rule_nonce'], 'np_time_rule_save' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        // Dates (from hidden input, newline-separated)
        $dates_raw = isset( $_POST['np_time_dates'] ) ? (string) wp_unslash( $_POST['np_time_dates'] ) : '';
        $dates = array_filter( array_map( 'trim', preg_split( '/\r?\n/', $dates_raw ) ) );
        $dates = array_values( array_filter( $dates, function( $d ){ return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $d ); } ) );

        // Days of week
        $days = isset( $_POST['np_time_days_of_week'] ) ? array_map( 'intval', (array) $_POST['np_time_days_of_week'] ) : [];
        $days = array_values( array_intersect( $days, [0,1,2,3,4,5,6] ) );

        // Enforce mutual exclusivity: if dates provided, ignore days
        if ( ! empty( $dates ) ) {
            $days = [];
        }

        update_post_meta( $post_id, '_np_time_dates', $dates );
        update_post_meta( $post_id, '_np_time_days_of_week', $days );

        // Postcodes
        $codes_raw = isset( $_POST['np_time_postcodes'] ) ? (string) wp_unslash( $_POST['np_time_postcodes'] ) : '';
        $codes = array_filter( array_map( 'trim', preg_split( '/\r?\n/', $codes_raw ) ) );
        $codes = array_values( array_unique( $codes ) );
        update_post_meta( $post_id, '_np_time_postcodes', $codes );

    // 产品关联已废弃，不再保存
    }

    public function admin_assets( $hook ) {
        // 在 NP-Time 相关页面加载
        $screen = get_current_screen();
        if ( ! $screen ) return;
        if ( false !== strpos( $screen->id, 'np-time' ) ) {
            if ( function_exists( 'wp_enqueue_media' ) ) {
                wp_enqueue_media();
            }
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_script( 'wp-color-picker' );
            wp_enqueue_script( 'jquery-ui-datepicker' );
            $css_path = NP_TIME_PATH . 'assets/np-time-admin.css';
            $js_path  = NP_TIME_PATH . 'assets/np-time-admin.js';
            $css_ver  = file_exists( $css_path ) ? filemtime( $css_path ) : NP_TIME_VERSION;
            $js_ver   = file_exists( $js_path ) ? filemtime( $js_path ) : NP_TIME_VERSION;
            wp_enqueue_style( 'np-time-admin', NP_TIME_URL . 'assets/np-time-admin.css', [], $css_ver );
            wp_enqueue_script( 'np-time-admin', NP_TIME_URL . 'assets/np-time-admin.js', [ 'jquery', 'wp-color-picker' ], $js_ver, true );
        }
        
        // 在产品编辑页面也加载日期选择器
        if ( 'product' === $screen->id ) {
            wp_enqueue_script( 'jquery-ui-datepicker' );
            wp_enqueue_style( 'jquery-ui-datepicker-style', 'https://code.jquery.com/ui/1.12.1/themes/ui-lightness/jquery-ui.css' );
        }
    }

    /**
     * 在产品编辑页面添加配送日期设置字段
     */
    public function product_delivery_date_fields() {
        global $post;
        
        echo '<div class="options_group np-time-delivery-dates">';
        echo '<h4 style="padding: 12px; margin: 0; background: #f7f7f7; border-bottom: 1px solid #ddd;">📅 配送日期设置</h4>';
        echo '<div style="padding: 12px;">';
        
        // 启用配送日期限制
        woocommerce_wp_checkbox( [
            'id' => '_np_delivery_date_enabled',
            'label' => '启用配送日期限制',
            'description' => '勾选后，此产品只能在指定的日期范围内配送',
            'desc_tip' => true,
        ] );
        
        echo '<div class="np-delivery-date-fields" style="display: ' . ( get_post_meta( $post->ID, '_np_delivery_date_enabled', true ) ? 'block' : 'none' ) . ';">';
        
        // 开始日期
        woocommerce_wp_text_input( [
            'id' => '_np_delivery_start_date',
            'label' => '配送开始日期',
            'placeholder' => 'YYYY-MM-DD',
            'description' => '此产品可配送的开始日期',
            'desc_tip' => true,
            'type' => 'date',
            'custom_attributes' => [
                'min' => date( 'Y-m-d' ),
            ],
        ] );
        
        // 结束日期
        woocommerce_wp_text_input( [
            'id' => '_np_delivery_end_date',
            'label' => '配送结束日期',
            'placeholder' => 'YYYY-MM-DD',
            'description' => '此产品可配送的结束日期（留空表示无结束日期）',
            'desc_tip' => true,
            'type' => 'date',
            'custom_attributes' => [
                'min' => date( 'Y-m-d' ),
            ],
        ] );
        
        echo '<p class="description" style="margin-top: 10px; font-style: italic; color: #666;">';
        echo '💡 提示：设置后，用户选择的配送日期必须在此范围内才能购买此产品。如果用户修改配送日期导致此产品不在配送范围内，系统会提示用户移除该产品。';
        echo '</p>';
        
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        // 添加 JavaScript 来控制字段显示/隐藏
        echo '<script type="text/javascript">
        jQuery(document).ready(function($) {
            // 控制配送日期字段的显示/隐藏
            $("#_np_delivery_date_enabled").change(function() {
                if ($(this).is(":checked")) {
                    $(".np-delivery-date-fields").show();
                } else {
                    $(".np-delivery-date-fields").hide();
                }
            }).trigger("change");
        });
        </script>';
    }
    
    /**
     * 保存产品配送日期设置
     */
    public function save_product_delivery_date_fields( $post_id ) {
        // 验证权限
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
        
        // 保存启用状态
        $enabled = isset( $_POST['_np_delivery_date_enabled'] ) ? 'yes' : 'no';
        update_post_meta( $post_id, '_np_delivery_date_enabled', $enabled );
        
        // 如果启用了日期限制，保存日期设置
        if ( 'yes' === $enabled ) {
            $start_date = sanitize_text_field( $_POST['_np_delivery_start_date'] ?? '' );
            $end_date = sanitize_text_field( $_POST['_np_delivery_end_date'] ?? '' );
            
            // 验证日期格式
            if ( $start_date && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $start_date ) ) {
                $start_date = '';
            }
            
            if ( $end_date && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $end_date ) ) {
                $end_date = '';
            }
            
            // 确保结束日期不早于开始日期
            if ( $start_date && $end_date && strtotime( $end_date ) < strtotime( $start_date ) ) {
                $end_date = $start_date;
            }
            
            update_post_meta( $post_id, '_np_delivery_start_date', $start_date );
            update_post_meta( $post_id, '_np_delivery_end_date', $end_date );
        } else {
            // 如果禁用了日期限制，清除日期设置
            delete_post_meta( $post_id, '_np_delivery_start_date' );
            delete_post_meta( $post_id, '_np_delivery_end_date' );
        }
    }
}
