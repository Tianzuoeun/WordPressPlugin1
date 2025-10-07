add_filter('woocommerce_can_reduce_order_stock', function($can_reduce, $order) {
    // 从新插件的Cookie读取配送信息
    $delivery_date = null;
    $source = '';
    
    // 尝试从np_time_choice cookie读取（新插件格式）
    if (isset($_COOKIE['np_time_choice'])) {
        $choice_data = json_decode(stripslashes($_COOKIE['np_time_choice']), true);
        if (is_array($choice_data) && isset($choice_data['date'])) {
            $delivery_date = sanitize_text_field($choice_data['date']);
            $source = 'np_time_plugin';
        }
    }
    
    // 如果cookie中没有，尝试从订单meta中读取（新插件保存的格式）
    if (!$delivery_date) {
        $delivery_date = $order->get_meta('_np_delivery_date');
        if ($delivery_date) {
            $source = 'order_meta';
        }
    }
    
    $order_id = $order->get_id();
    $tomorrow = date('Y-m-d', current_time('timestamp') + DAY_IN_SECONDS);
    
    error_log("[NP Time库存控制] 订单ID: {$order_id}");
    error_log("[NP Time库存控制] 配送日期: " . ($delivery_date ?: '无'));
    error_log("[NP Time库存控制] 数据来源: " . $source);
    error_log("[NP Time库存控制] 明天日期: {$tomorrow}");
    
    if ($delivery_date) {
        // 保存配送信息到订单（使用新插件的meta key格式）
        $order->update_meta_data('_np_delivery_date', $delivery_date);
        $order->update_meta_data('_np_delivery_date_source', $source);
        
        // 如果从cookie中获取到了邮编信息，也一并保存
        if (isset($choice_data['postcode'])) {
            $order->update_meta_data('_np_delivery_postcode', sanitize_text_field($choice_data['postcode']));
        }
        
        $order->save();
        
        // 处理可能的日期格式差异
        $delivery_date_only = substr($delivery_date, 0, 10);
        
        if ($delivery_date_only === $tomorrow) {
            error_log("[NP Time库存控制] ✅ 配送日期是明天，扣减库存");
            return true;
        } else {
            error_log("[NP Time库存控制] ❌ 配送日期不是明天（{$delivery_date_only}），不扣减库存");
            return false;
        }
    }
    
    error_log("[NP Time库存控制] ⚠️ 未找到配送日期，使用默认库存逻辑");
    return $can_reduce;
}, 10, 2);