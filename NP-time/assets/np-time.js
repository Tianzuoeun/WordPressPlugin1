(function($){
  if (typeof NP_TIME_DATA !== 'undefined' && NP_TIME_DATA) {
    if (typeof NP_TIME_DATA.gate !== 'undefined') {
      NP_TIME_DATA.gate = Number(NP_TIME_DATA.gate) === 1;
    }
    if (typeof NP_TIME_DATA.tipEnabled !== 'undefined') {
      NP_TIME_DATA.tipEnabled = Number(NP_TIME_DATA.tipEnabled) === 1;
    }
    if (typeof NP_TIME_DATA.hasChoice !== 'undefined') {
      NP_TIME_DATA.hasChoice = Number(NP_TIME_DATA.hasChoice) === 1;
    }
    if (typeof NP_TIME_DATA.isLoggedIn !== 'undefined') {
      NP_TIME_DATA.isLoggedIn = Number(NP_TIME_DATA.isLoggedIn) === 1;
    }
  }
  var TIP_STORAGE_KEY = 'npTipCheckoutSelection';

  function isCheckoutPage() {
    return $('form.checkout, .wp-block-woocommerce-checkout, .wc-block-components-checkout').length > 0;
  }

  function getStoredTipSelection() {
    if (typeof window.sessionStorage === 'undefined') {
      return null;
    }
    try {
      var raw = window.sessionStorage.getItem(TIP_STORAGE_KEY);
      if (!raw) {
        return null;
      }
      var data = JSON.parse(raw);
      if (!data || typeof data.type === 'undefined') {
        return null;
      }
      return data;
    } catch (err) {
      return null;
    }
  }

  function storeTipSelection(type, amount) {
    if (typeof window.sessionStorage === 'undefined') {
      return;
    }
    try {
      window.sessionStorage.setItem(
        TIP_STORAGE_KEY,
        JSON.stringify({ type: type, amount: amount, timestamp: Date.now() })
      );
    } catch (err) {}
  }

  function clearStoredTipSelection() {
    if (typeof window.sessionStorage === 'undefined') {
      return;
    }
    try {
      window.sessionStorage.removeItem(TIP_STORAGE_KEY);
    } catch (err) {}
  }

  var pendingPrefill = null; // {postcode, date}
  var MODAL_STRINGS = (typeof NP_TIME_DATA !== 'undefined' && NP_TIME_DATA && NP_TIME_DATA.modalStrings) ? NP_TIME_DATA.modalStrings : {};

  // Helper function to get cookie value
  function getCookie(name) {
    var value = "; " + document.cookie;
    var parts = value.split("; " + name + "=");
    if (parts.length === 2) return parts.pop().split(";").shift();
    return null;
  }

  function getString(key, fallback){
    var value = MODAL_STRINGS && MODAL_STRINGS[key];
    if (typeof value === 'string' && value.length) {
      return value;
    }
    return fallback;
  }
  function formatString(key, fallback){
    var args = Array.prototype.slice.call(arguments, 2);
    var s = getString(key, fallback);
    var i = 0;
    return s.replace(/%s/g, function(){ return (i < args.length) ? args[i++] : ''; });
  }

  function applyTipSelectionUI(selection) {
    if (!selection) {
      return;
    }
    $('.np-tip-btn').removeClass('np-tip-selected');
    if (selection.type === 'custom') {
      $('.np-tip-btn.np-tip-custom').addClass('np-tip-selected');
      $('.np-custom-tip-input').show();
      if (selection.amount) {
        var numeric = parseFloat(String(selection.amount).replace(/[^0-9.]/g, ''));
        if (!isNaN(numeric)) {
          $('#np-custom-tip-amount').val(numeric.toFixed(2));
        }
      }
    } else if (selection.type === 'refuse') {
      $('.np-tip-btn.np-tip-refuse').addClass('np-tip-selected');
      $('.np-custom-tip-input').hide();
    } else {
      var selector = '.np-tip-btn[data-tip="' + selection.amount + '"]';
      var $target = $(selector);
      if ($target.length) {
        $target.addClass('np-tip-selected');
      }
      $('.np-custom-tip-input').hide();
    }
  }

  function reapplyStoredTipSelection() {
    if (!NP_TIME_DATA || !NP_TIME_DATA.tipEnabled) {
      return;
    }
    var stored = getStoredTipSelection();
    if (!stored) {
      return;
    }

    NP_TIME_DATA.tipType = stored.type;
    NP_TIME_DATA.tipAmount = stored.amount;
    NP_TIME_DATA.selectedTip = stored.type === 'refuse' ? 'refuse' : stored.amount;

    var attempt = 0;
    function apply() {
      var $buttons = $('.np-tip-btn');
      if ($buttons.length === 0 && attempt < 10) {
        attempt++;
        setTimeout(apply, 150);
        return;
      }
      if ($buttons.length > 0) {
        applyTipSelectionUI(stored);
      }
      if (stored.type !== 'refuse') {
        saveTipChoice(stored.type, stored.amount);
      } else {
        storeTipSelection('refuse', '0.00');
        setTimeout(restoreTipSelection, 50);
      }
    }
    apply();
  }

  function ensureCouponModal() {
    var $overlay = $('#np-time-coupon-overlay');
    if ($overlay.length) {
      return $overlay;
    }
    var title = getString('coupon_modal_title', '我的优惠券');
    var closeText = getString('coupon_close_text', '关闭');
    var loadingText = getString('coupon_loading_text', '正在加载优惠券...');

    var modalHtml = '' +
      '<div id="np-time-coupon-overlay" class="np-time-coupon-overlay" aria-hidden="true">' +
        '<div class="np-time-coupon-modal" role="dialog" aria-modal="true" aria-label="' + title + '">' +
          '<button type="button" class="np-time-coupon-close" aria-label="' + closeText + '">×</button>' +
          '<h3 class="np-time-coupon-heading">' + title + '</h3>' +
          '<div class="np-time-coupon-body">' +
            '<div class="np-time-coupon-status">' + loadingText + '</div>' +
            '<div class="np-time-coupon-list" role="list"></div>' +
          '</div>' +
          '<div class="np-time-coupon-footer">' +
            '<button type="button" class="button np-time-coupon-close-btn">' + closeText + '</button>' +
          '</div>' +
        '</div>' +
      '</div>';
    $('body').append(modalHtml);
    return $('#np-time-coupon-overlay');
  }

  function renderCouponList(coupons) {
    var $overlay = ensureCouponModal();
    var $status = $overlay.find('.np-time-coupon-status');
    var $list = $overlay.find('.np-time-coupon-list');
    $list.empty();

    if (!coupons || !coupons.length) {
        $status.text(getString('coupon_empty_text', '暂无可用优惠券')).show();
        return;
    }

    $status.hide();
    coupons.forEach(function(coupon) {
        var $card = $('<div/>', {
            'class': 'np-time-coupon-card' + (!coupon.is_usable ? ' coupon-disabled' : ''),
            role: 'listitem'
        });

        var $left = $('<div/>', { 'class': 'np-time-coupon-left' });
        var $amount = $('<div/>', { 'class': 'np-time-coupon-amount' }).text(coupon.amount_display || coupon.amount_raw || '');
        var $code = $('<div/>', { 'class': 'np-time-coupon-code' }).text((coupon.code || '').toUpperCase());

        var $meta = $('<div/>', { 'class': 'np-time-coupon-meta' });
        if (coupon.description) {
            $meta.append($('<p/>').text(coupon.description));
        }
        
        // 显示最低消费要求
        var minAmountRaw = parseFloat(coupon.minimum_amount_raw || 0);
        if (!isNaN(minAmountRaw) && minAmountRaw > 0) {
            var minLabel = coupon.minimum_amount_display || coupon.minimum_amount_raw;
            var $minRequirement = $('<p/>', { 'class': 'np-time-coupon-limit' });
            
            if (!coupon.is_usable && coupon.unusable_reason) {
                // 如果不可用，显示差额
                $minRequirement.html('<span style="color: #ff6b6b;">满 ' + minLabel + ' 可用（' + coupon.unusable_reason + '）</span>');
            } else {
                $minRequirement.text(formatString('coupon_min_label_format', '满 %s 可用', minLabel));
            }
            $meta.append($minRequirement);
        }

        $left.append($amount, $code, $meta);

        var $right = $('<div/>', { 'class': 'np-time-coupon-actions' });
        
        // 显示有效期
        var expiryText = coupon.expiry ? coupon.expiry : '—';
        $right.append($('<div/>', { 'class': 'np-time-coupon-expiry' }).text(getString('coupon_expiry_label', '有效期：') + expiryText));

        var $bottomWrap = $('<div/>', { 'class': 'np-time-coupon-bottom' });
        
        // 根据可用性设置按钮
        if (coupon.is_usable) {
            var $apply = $('<button/>', {
                type: 'button',
                'class': 'np-time-coupon-apply',
                'data-code': coupon.code
            }).text(getString('coupon_apply_button', '立即使用'));
            $bottomWrap.append($apply);
        } else {
            var $disabled = $('<button/>', {
                type: 'button',
                'class': 'np-time-coupon-apply-disabled',
                disabled: true
            }).text('未满足条件');
            $bottomWrap.append($disabled);
        }
        
        $right.append($bottomWrap);
        $card.append($left, $right);
        $list.append($card);
    });
  }

  function loadCoupons() {
    var $overlay = ensureCouponModal();
    var $status = $overlay.find('.np-time-coupon-status');
    var $list = $overlay.find('.np-time-coupon-list');
    $overlay.addClass('is-loading');
    $status.text(getString('coupon_loading_text', '正在加载优惠券...')).show();
    $list.empty();

    return $.post(NP_TIME_DATA.ajaxUrl, {
      action: 'np_time_get_user_coupons',
      nonce: NP_TIME_DATA.nonce
    }).done(function(res) {
      if (res && res.success && res.data) {
        renderCouponList(res.data.coupons || []);
      } else {
        var msg = (res && res.data && res.data.message) ? res.data.message : getString('coupon_empty_text', '暂无可用优惠券');
        $status.text(msg).show();
      }
    }).fail(function() {
      $status.text(getString('coupon_empty_text', '暂无可用优惠券')).show();
    }).always(function() {
      $overlay.removeClass('is-loading');
    });
  }

  function openCouponModal() {
    var $overlay = ensureCouponModal();
    $overlay.attr('aria-hidden', 'false').addClass('is-visible');
    $('body').addClass('np-time-prevent-scroll');

    loadCoupons();
  }

  function closeCouponModal() {
    $('#np-time-coupon-overlay').attr('aria-hidden', 'true').removeClass('is-visible is-loading');
    $('body').removeClass('np-time-prevent-scroll');
  }

  function applyCouponFromModal(code) {
    code = String(code || '').trim();
    if (!code) {
      return;
    }
    var $form = $('form.checkout_coupon');
    if ($form.length) {
      var $toggleLink = $('.woocommerce-form-coupon-toggle .showcoupon');
      if ($toggleLink.length && !$form.is(':visible')) {
        $toggleLink.trigger('click');
      }
      var $input = $form.find('input[name="coupon_code"]');
      if (!$input.length) {
        $input = $('<input/>', { type: 'text', name: 'coupon_code', style: 'display:none;' }).appendTo($form);
      }
      $input.val(code);
      closeCouponModal();
      setTimeout(function() {
        $form.trigger('submit');
      }, 120);
      return;
    }

    // 回退方案：尝试查找页面任何 coupon_code 输入并提交其父表单或点击附近的应用按钮
    var $anyInput = $('input[name="coupon_code"]').filter(':visible').first();
    if ($anyInput.length) {
      $anyInput.val(code);
      var $closestForm = $anyInput.closest('form');
      if ($closestForm.length) {
        closeCouponModal();
        setTimeout(function() { $closestForm.trigger('submit'); }, 120);
        return;
      }
      // 查找附近的按钮
      var $nearBtn = $anyInput.siblings('button, input[type="submit"]').first();
      if ($nearBtn.length) {
        closeCouponModal();
        setTimeout(function() { $nearBtn.trigger('click'); }, 120);
        return;
      }
    }

    // 最后手动复制到剪贴板并提示用户
    try {
      navigator.clipboard.writeText(code);
      alert(getString('coupon_copied_clipboard', '优惠码已复制到剪贴板，请在结账页粘贴并使用。'));
    } catch (e) {
      alert(getString('coupon_copy_instruction', '请复制优惠码并在结账页面粘贴使用： ') + code);
    }
    closeCouponModal();
  }

  // 改进版：更健壮的注入逻辑，处理主题或 Blocks 布局差异
  function initCouponPicker() {
    if (!isCheckoutPage()) {
      return;
    }
    if ($('#np-time-coupon-trigger').length) {
      return;
    }

    var btnText = getString('coupon_button_text', '选择可用优惠券');

    // 尝试多个放置点，按优先级从上到下
    var placements = [
      { sel: '.woocommerce-form-coupon-toggle', method: 'after' },
      { sel: '.woocommerce-form-coupon', method: 'after' },
      { sel: 'form.checkout .woocommerce-checkout-payment', method: 'before' },
      { sel: '.woocommerce-checkout-review-order-table', method: 'before' },
      { sel: '.wc-block-components-order-meta, .wc-block-components-totals-wrapper', method: 'prepend' },
      { sel: 'form.checkout', method: 'prepend' },
      { sel: 'body', method: 'append' }
    ];

    var inserted = false;
    for (var i = 0; i < placements.length; i++) {
      var p = placements[i];
      try {
        var $target = $(p.sel);
        if (!$target || $target.length === 0) continue;

        var $btn = $('<button/>', {
          type: 'button',
          id: 'np-time-coupon-trigger',
          'class': 'button np-time-coupon-trigger'
        }).text(btnText);

        if (p.method === 'after') {
          $target.first().after($btn);
        } else if (p.method === 'before') {
          $target.first().before($btn);
        } else if (p.method === 'prepend') {
          $target.first().prepend($btn);
        } else {
          $target.first().append($btn);
        }

        $btn.on('click', function() { openCouponModal(); });
        inserted = true;
        break;
      } catch (e) {
        // ignore and try next
      }
    }

    if (!inserted) {
      // 最后一招：直接 append 到 body
      var $btn = $('<button/>', {
        type: 'button',
        id: 'np-time-coupon-trigger',
        'class': 'button np-time-coupon-trigger'
      }).text(btnText).appendTo('body');
      $btn.on('click', function() { openCouponModal(); });
    }
  }

  // 检查所有邮编是否匹配配送邮编
  function checkAllPostcodesMatch() {
    if (!NP_TIME_DATA || !NP_TIME_DATA.choice || !NP_TIME_DATA.choice.postcode) {

      return;
    }
    
    var deliveryPostcode = String(NP_TIME_DATA.choice.postcode).trim();
    var mismatchedFields = [];
    

    
    // 检查所有邮编字段（排除配送设置弹窗中的字段）
    $('input[name="billing_postcode"], input[name="shipping_postcode"], input#billing-postcode, input#shipping-postcode, input[autocomplete="postal-code"]').not('#np-time-postcode').each(function() {
      var $field = $(this);
      var fieldValue = $field.val().trim();
      var fieldName = $field.attr('name') || $field.attr('id') || 'unknown';
      
      if (fieldValue && fieldValue.toLowerCase() !== deliveryPostcode.toLowerCase()) {
        var isShipping = fieldName.toLowerCase().includes('shipping');
        var fieldType = isShipping ? '收货地址邮编' : '账单邮编';
        
        mismatchedFields.push({
          field: $field,
          fieldName: fieldName,
          fieldType: fieldType,
          value: fieldValue
        });
        
        // 标红边框
        $field.css('border-color', '#e74c3c');
        

      } else if (fieldValue) {
        // 清除边框颜色
        $field.css('border-color', '');
      }
    });
    
    // 如果有不匹配的字段，显示提示
    if (mismatchedFields.length > 0) {
      var message = getString('postcode_mismatch_updated_header','配送设置已更新，但发现以下邮编字段与配送邮编不一致：') + '\n\n';
      mismatchedFields.forEach(function(item) {
        message += '• ' + item.fieldType + '：' + item.value + '\n';
      });
      message += '\n' + getString('postcode_mismatch_delivery_label','配送邮编：') + deliveryPostcode + '\n\n' + getString('postcode_mismatch_fix_advice','请修改不一致的邮编字段。');
      
      setTimeout(function() {
        alert(message);
      }, 500);
    } else {

    }
  }

  // 重新加载配送信息显示
  function reloadDeliveryInfo() {

    
    // 移除所有现有的配送信息元素和小费元素
    $('.np-time-checkout-row, .np-time-checkout-block, .np-tip-row, .np-tip-block, #np-time-totals-info').remove();

    
    // 使用智能等待机制重新注入配送信息
    function waitForTableAndReload(attempt) {
      attempt = attempt || 0;
      var $table = $('table.woocommerce-checkout-review-order-table');
      var $subtotalRow = $table.find('tr.cart-subtotal');
      
      if ($table.length && $subtotalRow.length && attempt < 5) {

        ensureCartDeliveryInfoInjected();
        updateTotalsInfoDisplay();
      } else if (attempt < 5) {

        setTimeout(function() {
          waitForTableAndReload(attempt + 1);
        }, 200);
      } else {

        ensureCartDeliveryInfoInjected();
        updateTotalsInfoDisplay();
      }
    }
    
    // 稍微延迟后开始检测，确保DOM更新完成
    setTimeout(function() {
      waitForTableAndReload();
    }, 100);
    

  }

  function updateTotalsInfoDisplay(){
    try{

      
      var pcTxt = (NP_TIME_DATA && NP_TIME_DATA.choice && NP_TIME_DATA.choice.postcode) ? NP_TIME_DATA.choice.postcode : getString('not_selected_text','未选择');
      var dTxt  = (NP_TIME_DATA && NP_TIME_DATA.choice && NP_TIME_DATA.choice.date) ? NP_TIME_DATA.choice.date : getString('not_selected_text','未选择');
      

      
      // 更新购物车页面的配送信息显示
      var $cartTotalsInfo = $('#np-time-totals-info');
      if ($cartTotalsInfo.length) {
        $cartTotalsInfo.find('.np-time-pc').html('<strong>' + getString('label_postcode','配送邮编：') + '</strong> ' + pcTxt);
        $cartTotalsInfo.find('.np-time-date').html('<strong>' + getString('label_date','配送日期：') + '</strong> ' + dTxt);
      }
      // 结账页行
      $('.np-time-checkout-row div:first-child').html('<strong>' + getString('label_postcode','配送邮编：') + '</strong>'+pcTxt);
      $('.np-time-checkout-row div:last-child span').html('<strong>' + getString('label_date','配送日期：') + '</strong>'+dTxt);
      // Blocks 结账页面
      $('.np-time-checkout-block div:first-child').html('<strong>' + getString('label_postcode','配送邮编：') + '</strong>'+pcTxt);
      $('.np-time-checkout-block div:last-child span').html('<strong>' + getString('label_date','配送日期：') + '</strong>'+dTxt);
      



    }catch(e){
      console.error('NP Time: 更新显示时出错:', e);
    }
  }

  function ensureCartDeliveryInfoInjected(){
    // 早期检查：如果在用户仪表盘的订单页面，直接返回
    var isMyAccountOrdersPage = window.location.href.indexOf('/my-account/orders') !== -1 || 
                                window.location.href.indexOf('/my-account/view-order') !== -1 ||
                                $('.woocommerce-account .woocommerce-orders-table').length > 0 ||
                                $('.woocommerce-account .woocommerce-order').length > 0 ||
                                $('.woocommerce-MyAccount-content').length > 0;
    
    if (isMyAccountOrdersPage) {
      return; // 在用户仪表盘页面不注入配送信息
    }
    
    var inserted = false;
    function inject(){
      // 检查是否在结账页面（有结账相关元素）
      var isCheckoutPage = $('form.checkout, .wp-block-woocommerce-checkout, .wc-block-components-checkout').length > 0;
      
      // 检查是否在用户仪表盘的订单页面
      var isMyAccountOrdersPage = window.location.href.indexOf('/my-account/orders') !== -1 || 
                                  window.location.href.indexOf('/my-account/view-order') !== -1 ||
                                  $('.woocommerce-account .woocommerce-orders-table').length > 0 ||
                                  $('.woocommerce-account .woocommerce-order').length > 0;
      
      // 购物车页面：只显示配送信息，不显示小费
      // 但不在用户仪表盘订单页面显示
      if (!isCheckoutPage && !isMyAccountOrdersPage) {

        
        // 只清除结账相关元素，保留购物车配送信息
        $('.np-time-checkout-row, .np-time-checkout-block, .np-tip-row, .np-tip-block').remove();
        
        // 检查购物车表格
        var $cartTotals = $('.cart_totals').first();
        var $cartTable = $cartTotals.find('table.shop_table.shop_table_responsive').first();
        if (!$cartTable.length) {
          $cartTable = $('.woocommerce-cart-form table.shop_table.shop_table_responsive').first();
        }
        if (!$cartTable.length) {
          $cartTable = $('table.shop_table.shop_table_responsive').first();
        }

        var $existingInfo = $('#np-time-totals-info').first();

        // 如果购物车配送信息已存在，则强制移动到表格下方
        if ($existingInfo.length) {

          $existingInfo.detach();
          if ($cartTable.length) {
            $existingInfo.insertAfter($cartTable);
          } else if ($cartTotals.length) {
            $cartTotals.append($existingInfo);
          } else {
            $('.woocommerce-cart-form').first().append($existingInfo);
          }
          inserted = true;
          return;
        }

        // 如果购物车配送信息不存在且有表格，则添加
        if (!$existingInfo.length && $cartTable.length) {
          var pcTxt = (NP_TIME_DATA && NP_TIME_DATA.choice && NP_TIME_DATA.choice.postcode) ? NP_TIME_DATA.choice.postcode : getString('not_selected_text','未选择');
          var dTxt  = (NP_TIME_DATA && NP_TIME_DATA.choice && NP_TIME_DATA.choice.date) ? NP_TIME_DATA.choice.date : getString('not_selected_text','未选择');
          
          var cartDeliveryBlockHtml = '' +
            '<div id="np-time-totals-info" style="margin-top:16px;padding:0 0 30px;border:0 solid #da010100;border-radius:6px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;text-align:left;background:#f9f9f900;">' +
              '<div class="np-time-info-text" style="display:flex;flex-direction:column;gap:6px;">' +
                '<div class="np-time-row"><span class="np-time-pc"><strong>' + getString('label_postcode','配送邮编：') + '</strong>' + pcTxt + '</span></div>' +
                '<div class="np-time-row"><span class="np-time-date"><strong>' + getString('label_date','配送日期：') + '</strong>' + dTxt + '</span></div>' +
              '</div>' +
              (isCheckoutPage ? '' : 
                '<div class="np-time-info-actions" style="margin-left:auto;">' +
                  '<button type="button" class="np-time-edit-btn" style="border:none;background:none;color:#007cba;cursor:pointer;text-decoration:underline;padding:0;">' + getString('edit_button_text','编辑') + '</button>' +
                '</div>'
              ) +
            '</div>';

          var $newBlock = $(cartDeliveryBlockHtml);
          if ($cartTable.length) {
            $newBlock.insertAfter($cartTable);
          } else if ($cartTotals.length) {
            $cartTotals.append($newBlock);
          } else {
            $('.woocommerce-cart-form').first().append($newBlock);
          }

          inserted = true;
        } else if ($existingInfo.length) {

          inserted = true;
        } else {

        }
        return;
      }
      
      // 结账页面：先清除任何已存在的配送信息行和小费行，防止重复
      $('.np-time-checkout-row, .np-time-checkout-block, .np-tip-row, .np-tip-block, #np-time-totals-info').remove();
      
      // 经典结账模板：在cart-subtotal行下方插入配送信息行（若不存在）
      if(!$('tr.np-time-checkout-row').length){
        var $table = $('table.woocommerce-checkout-review-order-table');
        if($table.length){
          var pcTxt2 = (NP_TIME_DATA && NP_TIME_DATA.choice && NP_TIME_DATA.choice.postcode) ? NP_TIME_DATA.choice.postcode : getString('not_selected_text','未选择');
          var dTxt2  = (NP_TIME_DATA && NP_TIME_DATA.choice && NP_TIME_DATA.choice.date) ? NP_TIME_DATA.choice.date : getString('not_selected_text','未选择');
          
          // 只在结账页面且开启小费功能时显示小费选择
          var tipRowHtml = '';
          if (isCheckoutPage && NP_TIME_DATA && NP_TIME_DATA.tipEnabled) {
            // 获取小费选项配置
            var tipOptions = (NP_TIME_DATA && NP_TIME_DATA.tipOptions) ? NP_TIME_DATA.tipOptions : ['$1.00', '$3.00', '$5.00'];
            var selectedTip = (NP_TIME_DATA && NP_TIME_DATA.selectedTip) ? NP_TIME_DATA.selectedTip : 'refuse';
            
            // 生成小费选项按钮HTML
            var tipButtonsHtml = '';
            for (var i = 0; i < tipOptions.length; i++) {
              var isSelected = selectedTip === tipOptions[i] ? 'np-tip-selected' : '';
              tipButtonsHtml += '<button type="button" class="np-tip-btn ' + isSelected + '" data-tip="' + tipOptions[i] + '">' + tipOptions[i] + '</button>';
            }
            
            tipRowHtml = ''+
              '<tr class="np-tip-row"><td colspan="2" style="padding:10px 0;text-align:left !important;">'+
                '<div style="margin-bottom:8px;"><strong>' + getString('tip_section_title','添加小费') + '</strong></div>'+
                '<div class="np-tip-options" style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">'+
                  tipButtonsHtml +
                  '<button type="button" class="np-tip-btn np-tip-custom ' + (selectedTip === 'custom' ? 'np-tip-selected' : '') + '" data-tip="custom">' + getString('tip_custom_button','自定义小费') + '</button>'+
                  '<button type="button" class="np-tip-btn np-tip-refuse ' + (selectedTip === 'refuse' ? 'np-tip-selected' : '') + '" data-tip="refuse">' + getString('tip_refuse_button','残忍拒绝') + '</button>'+
                '</div>'+
                '<div class="np-custom-tip-input" style="margin-top:10px;display:' + (selectedTip === 'custom' ? 'block' : 'none') + ';">'+
                  '<input type="number" id="np-custom-tip-amount" placeholder="' + getString('tip_input_placeholder','输入金额') + '" step="0.01" min="0" style="padding:8px;border:1px solid #ddd;border-radius:20px;width:120px;">'+
                  '<button type="button" id="np-custom-tip-confirm" style="margin-left:8px;padding:8px 15px;background:#8bc34a;color:white;border:none;border-radius:20px;cursor:pointer;">' + getString('tip_confirm_button','确认') + '</button>'+
                '</div>'+
              '</td></tr>';
          }
          
          // 配送信息行 - 结账页面不显示编辑按钮
          var deliveryRow = ''+
            '<tr class="np-time-checkout-row"><td colspan="2" style="padding:10px 0;text-align:left !important;">'+
              '<div style="margin-bottom:4px;text-align:left;"><strong>' + getString('label_postcode','配送邮编：') + '</strong>'+pcTxt2+'</div>'+
              '<div style="text-align:left;">'+
                '<span><strong>' + getString('label_date','配送日期：') + '</strong>'+dTxt2+'</span>'+
              '</div>'+
            '</td></tr>';
          
          // 输出表格结构用于调试

          $table.find('tr').each(function(index) {
            var $row = $(this);
            var classes = $row.attr('class') || 'no-class';
            var text = $row.find('td, th').first().text().trim().substring(0, 30);

          });
          
          // 查找cart-subtotal行，先插入配送信息行
          var $subtotalRow = $table.find('tr.cart-subtotal');
          if ($subtotalRow.length) {

            $subtotalRow.after(deliveryRow);
            inserted = true;

            
            // 然后在配送信息行下方插入小费行（如果存在）
            if (tipRowHtml) {
              var $deliveryRow = $subtotalRow.next('tr.np-time-checkout-row');
              if ($deliveryRow.length) {
                $deliveryRow.after(tipRowHtml);

              }
            }
            
            // 验证插入是否成功
            var $insertedRow = $subtotalRow.next('tr.np-time-checkout-row');
            if ($insertedRow.length) {

            } else {

            }
          } else {
            // 尝试其他可能的小计行class名称
            var $subtotalAlternatives = $table.find('tr[class*="subtotal"], tr[class*="小计"], tr td:contains("小计"), tr td:contains("Subtotal")').closest('tr');
            if ($subtotalAlternatives.length) {

              $subtotalAlternatives.last().after(deliveryRow);
              
              // 然后插入小费行（如果存在）
              if (tipRowHtml) {
                var $deliveryRow = $subtotalAlternatives.last().next('tr.np-time-checkout-row');
                if ($deliveryRow.length) {
                  $deliveryRow.after(tipRowHtml);
                }
              }
              inserted = true;
            } else {
              // 如果找不到小计行，尝试order-total行
              var $orderTotalRow = $table.find('tr.order-total, tr[class*="total"], tr td:contains("总计"), tr td:contains("Total")').closest('tr');
              if ($orderTotalRow.length) {

                $orderTotalRow.first().before(deliveryRow);
                
                // 然后插入小费行（如果存在）
                if (tipRowHtml) {
                  var $deliveryRow = $orderTotalRow.prev('tr.np-time-checkout-row');
                  if ($deliveryRow.length) {
                    $deliveryRow.after(tipRowHtml);
                  }
                }
                inserted = true;
              } else {
                // 备选方案：插入到tbody的末尾

                $table.find('tbody').append(deliveryRow);
                
                // 然后插入小费行（如果存在）
                if (tipRowHtml) {
                  $table.find('tbody').append(tipRowHtml);
                }
                inserted = true;
              }
            }
          }
        }
      }
      
      // Blocks 结账页面：注入到 totals 区域（如果还没注入）
      if(!$('tr.np-time-checkout-row').length && !$('div.np-time-checkout-block').length && isCheckoutPage){
        var $checkoutTotals = $('.wc-block-components-order-meta, .wc-block-components-totals-wrapper').first();
        if($checkoutTotals.length && !$checkoutTotals.find('.np-time-checkout-block').length){
          var pcTxt3 = (NP_TIME_DATA && NP_TIME_DATA.choice && NP_TIME_DATA.choice.postcode) ? NP_TIME_DATA.choice.postcode : getString('not_selected_text','未选择');
          var dTxt3  = (NP_TIME_DATA && NP_TIME_DATA.choice && NP_TIME_DATA.choice.date) ? NP_TIME_DATA.choice.date : getString('not_selected_text','未选择');
          
          // 配送信息块（总是显示）- 结账页面不显示编辑按钮
          var deliveryBlockHtml = ''+
            '<div class="np-time-checkout-block" style="margin-bottom:8px;padding:8px 15px;border-bottom:1px solid #eee;text-align:left;">'+
              '<div style="margin-bottom:4px;text-align:left;"><strong>' + getString('label_postcode','配送邮编：') + '</strong>'+pcTxt3+'</div>'+
              '<div style="text-align:left;">'+
                '<span><strong>' + getString('label_date','配送日期：') + '</strong>'+dTxt3+'</span>'+
              '</div>'+
            '</div>';
          
          // 小费选择块（只在结账页面显示且功能开启）
          var tipBlockHtml = '';
          if (NP_TIME_DATA && NP_TIME_DATA.tipEnabled) {
            var tipOptions = (NP_TIME_DATA && NP_TIME_DATA.tipOptions) ? NP_TIME_DATA.tipOptions : ['$1.00', '$3.00', '$5.00'];
            var selectedTip = (NP_TIME_DATA && NP_TIME_DATA.selectedTip) ? NP_TIME_DATA.selectedTip : 'refuse';
            var tipButtonsHtml = '';
            for (var i = 0; i < tipOptions.length; i++) {
              var isSelected = selectedTip === tipOptions[i] ? 'np-tip-selected' : '';
              tipButtonsHtml += '<button type="button" class="np-tip-btn ' + isSelected + '" data-tip="' + tipOptions[i] + '">' + tipOptions[i] + '</button>';
            }
            tipBlockHtml = ''+
              '<div class="np-tip-block" style="padding:15px;text-align:left;">'+
                '<div style="margin-bottom:8px;"><strong>' + getString('tip_section_title','添加小费') + '</strong></div>'+
                '<div class="np-tip-options" style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">'+
                  tipButtonsHtml +
                  '<button type="button" class="np-tip-btn np-tip-custom ' + (selectedTip === 'custom' ? 'np-tip-selected' : '') + '" data-tip="custom">' + getString('tip_custom_button','自定义小费') + '</button>'+
                  '<button type="button" class="np-tip-btn np-tip-refuse ' + (selectedTip === 'refuse' ? 'np-tip-selected' : '') + '" data-tip="refuse">' + getString('tip_refuse_button','残忍拒绝') + '</button>'+
                '</div>'+
                '<div class="np-custom-tip-input" style="margin-top:10px;display:' + (selectedTip === 'custom' ? 'block' : 'none') + ';">'+
                  '<input type="number" id="np-custom-tip-amount" placeholder="' + getString('tip_input_placeholder','输入金额') + '" step="0.01" min="0" style="padding:8px;border:1px solid #ddd;border-radius:20px;width:120px;">'+
                  '<button type="button" id="np-custom-tip-confirm" style="margin-left:8px;padding:8px 15px;background:#8bc34a;color:white;border:none;border-radius:20px;cursor:pointer;">' + getString('tip_confirm_button','确认') + '</button>'+
                '</div>'+
              '</div>';
          }
          var blockHtml = deliveryBlockHtml + tipBlockHtml;
          $checkoutTotals.prepend(blockHtml);
        }
      }
    }
    // 立即尝试
    inject();
    if(inserted) return;
    
    // Blocks 可能异步渲染，使用 MutationObserver 监听
    // 但要确保只在表格结构完整时才注入
    var observer = new MutationObserver(function(){
      var $table = $('table.woocommerce-checkout-review-order-table');
      var $subtotalRow = $table.find('tr.cart-subtotal');
      
      // 只有当检测到完整表格结构时才注入
      if ($table.length && $subtotalRow.length && !$('tr.np-time-checkout-row').length) {

        inject();
      }
      
      if($('tr.np-time-checkout-row').length || $('div.np-time-checkout-block').length){ 
        observer.disconnect(); 
      }
    });
    observer.observe(document.body, { childList:true, subtree:true });
  }
  function openModal(){
    $('#np-time-modal').attr('aria-hidden','false');
    
    // 设置输入框提示文本
    $('#np-time-postcode').attr('placeholder', getString('postcode_placeholder', '请输入完整邮编'));
    
    // 预填用户上次选择（无 setTimeout 竞态）
    try {
      if (NP_TIME_DATA && NP_TIME_DATA.choice) {
        var ch = NP_TIME_DATA.choice;
        if (ch.postcode) {
          $('#np-time-postcode').val(ch.postcode);
          pendingPrefill = { postcode: String(ch.postcode||''), date: String(ch.date||'') };
          loadOptions();
        }
      }
    } catch(e) {}
  }
  function closeModal(){ $('#np-time-modal').attr('aria-hidden','true'); }

  function renderInfo(data){
    var info='';
    if(data.local){
      info += '<div style="color:#198754;">'+getString('local_title','✅ 本地配送区域')+'</div>';
      var dateCount = (data.dates||[]).length;
      if(dateCount > 0) {
        info += '<div style="color:#666;font-size:12px;">' + formatString('local_dates_count_format', '可选择 %s 个配送日期', dateCount) + '</div>';
      }
    } else {
      info += '<div style="color:#0d6efd;">'+getString('nonlocal_title','🚛 非本地配送区域')+'</div>';
      if(Array.isArray(data.daysOfWeek) && data.daysOfWeek.length > 0){
        // 生成带日期的可配送时间列表
        var today = new Date();
        var timesList = [];
        
        // 查找接下来7天内符合条件的日期
        for (var i = 0; i < 7; i++) {
          var futureDate = new Date(today);
          futureDate.setDate(today.getDate() + i);
          var dayOfWeek = futureDate.getDay();
          
          if (data.daysOfWeek.indexOf(dayOfWeek) !== -1 || data.daysOfWeek.indexOf(String(dayOfWeek)) !== -1) {
            timesList.push(formatDateDisplay(futureDate, dayOfWeek));
          }
        }
        
        // 如果没有找到具体日期，降级到原来的显示方式
        if (timesList.length === 0) {
          var names=['星期日','星期一','星期二','星期三','星期四','星期五','星期六'];
          var list=data.daysOfWeek.map(function(d){
            var n=parseInt(d,10); if(isNaN(n)) return d; var idx=(n===0?0:(n%7)); return names[idx]||d;
          }).join('、');
          info += '<div style="color:#666;font-size:12px;">'+getString('nonlocal_times_label','可配送时间：')+list+'</div>';
        } else {
          info += '<div style="color:#666;font-size:12px;">'+getString('nonlocal_times_label','可配送时间：')+'<br>'+timesList.join('<br>')+'</div>';
        }
      }
    }
    $('#np-time-info').html(info);
  }

  // 格式化日期显示，支持多语言
  function formatDateDisplay(date, dayOfWeek) {
    var monthNames = [
      getString('month_01', '1月'),
      getString('month_02', '2月'),
      getString('month_03', '3月'),
      getString('month_04', '4月'),
      getString('month_05', '5月'),
      getString('month_06', '6月'),
      getString('month_07', '7月'),
      getString('month_08', '8月'),
      getString('month_09', '9月'),
      getString('month_10', '10月'),
      getString('month_11', '11月'),
      getString('month_12', '12月')
    ];
    var weekdayNames = [
      getString('weekday_sun', '星期日'),
      getString('weekday_mon', '星期一'), 
      getString('weekday_tue', '星期二'),
      getString('weekday_wed', '星期三'),
      getString('weekday_thu', '星期四'),
      getString('weekday_fri', '星期五'),
      getString('weekday_sat', '星期六')
    ];
    
    var month = monthNames[date.getMonth()];
    var day = date.getDate();
    var weekday = weekdayNames[dayOfWeek];
    
    return formatString('date_weekday_format', '%s%s日-%s 可配送', month, day, weekday);
  }

  function populateWeekdays(days){
    var $w=$('#np-time-weekday').prop('disabled',false).empty();
    $w.append($('<option/>',{value:'',text:getString('weekday_placeholder','请选择配送的时间')}));
    var names=['星期日','星期一','星期二','星期三','星期四','星期五','星期六'];
    
    // 计算从明天开始的7天日期，确保不包含今天和之前的日期
    var today = new Date();
    var dateOptions = [];
    for (var i = 1; i <= 7; i++) {
      var futureDate = new Date(today);
      futureDate.setDate(today.getDate() + i);
      var dayOfWeek = futureDate.getDay();
      if ((days || []).indexOf(dayOfWeek) !== -1 || (days || []).indexOf(String(dayOfWeek)) !== -1) {
        dateOptions.push({
          value: dayOfWeek,
          date: futureDate,
          text: formatDateDisplay(futureDate, dayOfWeek)
        });
      }
    }
    
    // 如果有具体日期选项，使用带日期的显示
    if (dateOptions.length > 0) {
      dateOptions.forEach(function(option) {
        $w.append($('<option/>',{value:option.value,text:option.text}));
      });
    } else {
      // 降级到原来的显示方式
      (days||[]).forEach(function(d){
        var n=parseInt(d,10); if(isNaN(n)) return; var idx=(n===0?0:(n%7));
        $w.append($('<option/>',{value:idx,text:names[idx]}));
      });
    }
  }

  function loadOptions(){
    var postcode = $('#np-time-postcode').val().trim();
    if(!postcode){ 
      $('#np-time-step2').hide();
      $('#np-time-info').html('');
      return; 
    }
    
    // 检查邮编长度，如果太短则不查询
    if(postcode.length < 5) {
      $('#np-time-step2').hide();
      $('#np-time-info').html('<div style="color:#666;">' + getString('postcode_too_short','请输入完整的邮编（至少5位数字）') + '</div>');
      return;
    }
    
    // 清空之前的信息，显示加载状态
    $('#np-time-info').html('<div style="color:#666;">' + getString('loading_text','🔍 正在查询邮编配送选项...') + '</div>');
    $('#np-time-step2').hide(); // 隐藏第二步直到查询完成
    
    $.post(NP_TIME_DATA.ajaxUrl, { action:'np_time_get_options', nonce:NP_TIME_DATA.nonce, postcode:postcode })
    .done(function(res){
      if(!res || !res.success){ 
        $('#np-time-info').html('<div style="color:#d63384;">' + getString('invalid_postcode_text','❌ 该邮编暂不支持配送，请检查邮编或联系客服') + '</div>');
        $('#np-time-step2').hide();
        return; 
      }
      var data=res.data||{};
      renderInfo(data);
      var $dateWrap=$('#np-time-date-wrap');
      var $wdWrap=$('#np-time-weekday-wrap');
      var $step2 = $('#np-time-step2');
      var currentPc = $('#np-time-postcode').val().trim();
      
      if(data.local){
        // 本地：选择日期
        $wdWrap.hide();
        $('#np-time-weekday')
          .prop('disabled',true)
          .empty()
          .append($('<option/>',{value:'',text:getString('weekday_empty_text','请先输入邮编')}))
          .val('');
        var dates=data.dates||[]; 
        var $date=$('#np-time-date').prop('disabled',false).empty();
        $date.append($('<option/>',{value:'',text:getString('date_placeholder','请选择配送日期')}));
        dates.forEach(function(d){ $date.append($('<option/>',{value:d,text:d})); });
        $dateWrap.show();
        // 预填：如果 pending 与当前邮编一致，并且包含该日期
        if (pendingPrefill && pendingPrefill.postcode === currentPc && pendingPrefill.date && dates.indexOf(pendingPrefill.date) !== -1) {
          $('#np-time-date').val(pendingPrefill.date);
        }
        if(dates.length === 0) {
          $('#np-time-info').html('<div style="color:#fd7e14;">' + getString('no_local_dates_text','⚠️ 该本地邮编当前没有可选配送日期') + '</div>');
        }
      }else{
  // 非本地：选择时间
        $dateWrap.hide();
        $('#np-time-date')
          .prop('disabled',true)
          .empty()
          .append($('<option/>',{value:'',text:getString('date_empty_text','请先输入邮编')}))
          .val('');
        var weekdays = data.daysOfWeek||[];
        if(weekdays.length > 0) {
          populateWeekdays(weekdays);
          $wdWrap.show();
          // 预填：从日期计算星期几，若在可选列表中则设值
          if (pendingPrefill && pendingPrefill.postcode === currentPc && pendingPrefill.date) {
            var d = new Date(pendingPrefill.date.replace(/-/g,'/'));
            if (!isNaN(d.getTime())) {
              var dow = d.getDay();
              if (weekdays.indexOf(dow) !== -1 || weekdays.indexOf(String(dow)) !== -1) {
                $('#np-time-weekday').val(String(dow));
              }
            }
          }
        } else {
          $('#np-time-info').html('<div style="color:#fd7e14;">' + getString('no_times_text','⚠️ 该邮编暂无可配送时间') + '</div>');
          $('#np-time-step2').hide();
          return;
        }
      }
      $step2.show();
      // 清理 pending（仅当匹配当前邮编时）
      if (pendingPrefill && pendingPrefill.postcode === currentPc) {
        pendingPrefill = null;
      }
    })
    .fail(function(){
      $('#np-time-info').html('<div style="color:#d63384;">' + getString('network_error_text','❌ 网络错误，请重试') + '</div>');
      $('#np-time-step2').hide();
    });
  }

  // 防抖变量
  var postcodeDebounceTimer = null;
  var lastPostcodeQuery = '';
  
  // 防抖查询函数
  function debouncedLoadOptions() {
    if (postcodeDebounceTimer) {
      clearTimeout(postcodeDebounceTimer);
    }
    
    postcodeDebounceTimer = setTimeout(function() {
      var postcode = $('#np-time-postcode').val().trim();
      
      // 避免重复查询相同的邮编
      if (postcode === lastPostcodeQuery) {
        return;
      }
      
      if (postcode.length >= 5) { // 增加最小长度要求
        lastPostcodeQuery = postcode;
        loadOptions();
      } else if (postcode.length === 0) {
        // 如果清空了输入，隐藏第二步和清空状态
        $('#np-time-step2').hide();
        $('#np-time-info').html('');
        lastPostcodeQuery = '';
      } else if (postcode.length > 0 && postcode.length < 5) {
        // 显示长度不足提示
        $('#np-time-step2').hide();
        $('#np-time-info').html('<div style="color:#666;">' + getString('postcode_too_short','请输入完整的邮编（至少5位数字）') + '</div>');
      }
    }, 1000); // 1秒延迟，给用户更多时间输入
  }
  
  $(document).on('change','#np-time-postcode', function() {
    // change事件立即触发（失去焦点时）
    var postcode = $(this).val().trim();
    if (postcode.length >= 5 && postcode !== lastPostcodeQuery) {
      if (postcodeDebounceTimer) {
        clearTimeout(postcodeDebounceTimer);
        postcodeDebounceTimer = null;
      }
      lastPostcodeQuery = postcode;
      loadOptions();
    } else if (postcode.length === 0) {
      $('#np-time-step2').hide();
      $('#np-time-info').html('');
      lastPostcodeQuery = '';
    } else if (postcode.length > 0 && postcode.length < 5) {
      $('#np-time-step2').hide();
      $('#np-time-info').html('<div style="color:#666;">' + getString('postcode_too_short','请输入完整的邮编（至少5位数字）') + '</div>');
    }
  });
  
  $(document).on('keyup input','#np-time-postcode', debouncedLoadOptions);

  $(document).on('click','#np-time-continue', function(){
    var postcode=$('#np-time-postcode').val().trim();
    var localMode = $('#np-time-date-wrap').is(':visible');
    var payload={ action:'np_time_save_choice', nonce:NP_TIME_DATA.nonce, postcode:postcode };
    


    
    if(localMode){
      var date=$('#np-time-date').val();
      if(!postcode || !date){ alert(getString('missing_date_alert','请完整选择邮编与日期')); return; }
      payload.date=date;

    }else{
      var wd=$('#np-time-weekday').val();
      if(!postcode || wd===''){ alert(getString('missing_weekday_alert','请完整选择邮编与时间')); return; }
      payload.weekday=wd;

    }
    

    $.post(NP_TIME_DATA.ajaxUrl, payload)
    .done(function(res){ 

      
      if(res && res.success){ 
        // 检查是否有购物车冲突
        if (res.data && res.data.cart_conflicts && res.data.cart_conflicts.length > 0) {
          showCartConflictDialog(res.data);
          return;
        }
        
        console.log('NP-Time: 保存成功，响应数据:', res);
        
        closeModal(); 
        NP_TIME_DATA.hasChoice = true; 
        
        // 设置sessionStorage标记，以便页面刷新后记住选择状态
        if (typeof(Storage) !== "undefined") {
          sessionStorage.setItem('np_time_has_choice', '1');
          console.log('NP-Time: 已设置sessionStorage标记');
        }
        
        // 更新配送选择数据 - 保留现有数据，只更新返回的字段
        var choice = res.data.choice;
        var dateChanged = false;
        
        console.log('NP-Time: 更新前的choice数据:', NP_TIME_DATA.choice);
        console.log('NP-Time: 服务器返回的完整数据:', res.data);
        console.log('NP-Time: 解析后的choice数据:', choice);
        
        if (choice) {
          if (!NP_TIME_DATA.choice) {
            NP_TIME_DATA.choice = {};
          }
          
          if (choice.postcode) {
            NP_TIME_DATA.choice.postcode = String(choice.postcode);
          }
          
          if (choice.date) {
            // 检查日期是否发生了变化
            var oldDate = NP_TIME_DATA.choice ? NP_TIME_DATA.choice.date : null;
            var newDate = String(choice.date);
            if (oldDate && oldDate !== newDate) {
              dateChanged = true;
              console.log('NP-Time: 日期发生变化，从', oldDate, '到', newDate);
            } else {
              console.log('NP-Time: 日期没有变化或首次设置，日期:', newDate);
            }
            NP_TIME_DATA.choice.date = newDate;
          }
        }
        
        // 如果日期发生了变化，刷新页面以更新产品列表
        if (dateChanged) {
          // 确保Cookie已保存，延长等待时间
          setTimeout(function() {
            // 重新设置hasChoice标志以确保状态正确
            if (typeof(Storage) !== "undefined") {
              sessionStorage.setItem('np_time_has_choice', '1');
            }
            window.location.reload();
          }, 1000);
          return;
        }
        
        // 如果日期没有变化，只重新加载配送信息显示
        setTimeout(function() {
          reloadDeliveryInfo();
          
          // 检查billing和shipping邮编是否与新的配送邮编一致
          checkAllPostcodesAfterDeliveryUpdate();
        }, 200);
      } else { 
        console.log('NP Time: 保存失败', res);
        alert((res && res.data && res.data.message) || getString('save_failed_fallback','保存失败')); 
      } 
    })
    .fail(function(xhr){ var msg=getString('save_failed_fallback','保存失败'); try{ var r=JSON.parse(xhr.responseText); if(r && r.data && r.data.message) msg=r.data.message; }catch(e){} alert(msg); });
  });

  $(document).on('click','#np-time-fab, .np-time-edit-btn', function(e){ 
    // 在结账页面禁用配送选择浮窗
    if ($('body').hasClass('woocommerce-checkout') || $('.woocommerce-checkout').length > 0) {
      e.preventDefault();
      return false;
    }
    openModal(); 
  });

  function guardAction(e){
    if (NP_TIME_DATA && NP_TIME_DATA.gate && !NP_TIME_DATA.hasChoice) {
      e.preventDefault();
      e.stopImmediatePropagation();
      openModal();
      return false;
    }
  }
  $(document).on('click', 'a.checkout-button, .checkout-button, button[name="proceed"]', guardAction);
  $(document).on('submit', 'form.checkout, form.cart', guardAction);

  // 结账前端邮编一致性校验（防止 Blocks 无后端钩子时遗漏）
  function getCheckoutPostcode(){
    var shipDiff = $('input#ship-to-different-address').is(':checked') || $('input[name="ship_to_different_address"]').val()==='1';
    var $ship = $('input[name="shipping_postcode"],input#shipping-postcode');
    var $bill = $('input[name="billing_postcode"],input#billing-postcode');
    // 优先读取可见的收货邮编，其次账单邮编
    var shipping = $.trim(($ship.filter(':visible').val()||$ship.val()||'').toString());
    var billing  = $.trim(($bill.filter(':visible').val()||$bill.val()||'').toString());
    var val = '';
    if (shipDiff && shipping) val = shipping; else val = shipping || billing;
    if (!val) {
      // Blocks 兜底：找第一个邮编输入框
      var $any = $('.wp-block-woocommerce-checkout, .wc-block-components-checkout, form').find('input[autocomplete="postal-code"]:visible').first();
      if ($any.length) val = $.trim(($any.val()||'').toString());
    }
    return val;
  }
  function enforceCheckoutPostcodeMatch(e){
    if (!NP_TIME_DATA || !NP_TIME_DATA.choice || !NP_TIME_DATA.choice.postcode) return true;
    var desired = String(NP_TIME_DATA.choice.postcode||'').trim();
    var posted = String(getCheckoutPostcode()||'').trim();
    if (posted && desired && posted.toLowerCase() !== desired.toLowerCase()){
      if (e && e.preventDefault){ e.preventDefault(); e.stopImmediatePropagation(); }
      alert(getString('address_postcode_mismatch_alert','配送地址邮编需与配送设置邮编一致。请修改配送地址或点击“编辑”更新配送邮编。'));
      try{ $('.np-time-edit-btn').first().focus(); }catch(_){}
      return false;
    }
    return true;
  }
  // 捕获阶段的 click 提前拦截（优先级高于 React 事件）
  document.addEventListener('click', function(e){
    var t = e.target;
    if (!t) return;
    var sel = [
      '.wc-block-components-checkout-place-order-button',
      '.wp-block-woocommerce-checkout-place-order-button button',
      '.wc-block-components-button[type="submit"]',
      '.wp-block-woocommerce-checkout-place-order-block button',
      '#place_order',
      'button[name="place_order"]',
      'button[type="submit"][form="checkout"]'
    ].join(',');
    if (t.closest && t.closest(sel)){
      if (!enforceCheckoutPostcodeMatch(e)) return false;
    }
  }, true);

  // 实时监控账单邮编输入
  function monitorBillingPostcode() {
    // 防止重复初始化
    if (window.npPostcodeMonitorInitialized) {

      return;
    }
    window.npPostcodeMonitorInitialized = true;
    

    
    var lastErrorField = null; // 记录最后一个出错的字段
    var hasShownAlert = {}; // 记录每个字段是否已显示过弹窗
    
    // 显示邮编不匹配错误 - 使用弹窗
    function showPostcodeError($field, message) {
      var fieldKey = $field.attr('name') || $field.attr('id') || 'unknown';
      var currentValue = $field.val().trim();
      var alertKey = fieldKey + '_' + currentValue;
      
      // 如果同一个字段的同一个值已经弹过窗，就不再弹
      if (hasShownAlert[alertKey]) {

        $field.css('border-color', '#e74c3c');
        return;
      }
      
      console.log('NP Time: 显示邮编错误弹窗', message);
      alert(message);
      hasShownAlert[alertKey] = true;
      lastErrorField = $field;
      $field.css('border-color', '#e74c3c');
      
      // 5秒后清除该字段的弹窗记录，允许再次弹窗（如果用户再次输入相同错误值）
      setTimeout(function() {
        delete hasShownAlert[alertKey];
      }, 5000);
    }
    
    // 清除错误样式
    function clearErrorStyle($field) {
      $field.css('border-color', '');
    }
    
    // 检查邮编是否匹配
    function checkPostcodeMatch($field, triggerAlert) {
      var fieldName = $field.attr('name') || $field.attr('id') || '';

      
      if (!NP_TIME_DATA || !NP_TIME_DATA.choice || !NP_TIME_DATA.choice.postcode) {

        return;
      }
      
      var inputPostcode = $field.val().trim();
      var savedPostcode = String(NP_TIME_DATA.choice.postcode || '').trim();
      

      
      if (inputPostcode && savedPostcode && inputPostcode.toLowerCase() !== savedPostcode.toLowerCase()) {
        if (triggerAlert) {
          // 判断是billing还是shipping字段
          var isShipping = fieldName.toLowerCase().includes('shipping');
          var fieldType = isShipping ? getString('label_shipping_postcode','收货地址邮编') : getString('label_billing_postcode','账单邮编');
          var message = formatString('postcode_mismatch_single_template','配送邮编和%s不一致，请修改。%s需与配送设置邮编一致。', fieldType, fieldType);
          showPostcodeError($field, message);
        } else {
          // 只改变边框颜色，不弹窗
          $field.css('border-color', '#e74c3c');
        }
      } else if (inputPostcode) {
        clearErrorStyle($field);
      }
    }
    
    // 通用监控函数
    function attachMonitor($field) {
      if ($field.data('np-monitored')) return;
      $field.data('np-monitored', true);
      

      
      // 先移除可能存在的旧事件监听器，避免重复
      $field.off('.nptime');
      
      // input事件：只改变边框颜色，不弹窗
      $field.on('input.nptime keyup.nptime', function() {
        checkPostcodeMatch($(this), false);
      });
      
      // blur事件：弹窗提示
      $field.on('blur.nptime', function() {
        checkPostcodeMatch($(this), true);
      });
    }
    
    // 定义所有可能的邮编字段选择器（排除配送设置弹窗中的邮编输入框）
    var postcodeSelectors = [
      'input[name="billing_postcode"]:not(#np-time-postcode)',
      'input[name="shipping_postcode"]:not(#np-time-postcode)', 
      'input#billing-postcode:not(#np-time-postcode)',
      'input#shipping-postcode:not(#np-time-postcode)',
      'input#billing_postcode:not(#np-time-postcode)',
      'input#shipping_postcode:not(#np-time-postcode)',
      'input[autocomplete="postal-code"]:not(#np-time-postcode)',
      'input.shipping-postcode:not(#np-time-postcode)',
      'input.billing-postcode:not(#np-time-postcode)',
      'input[name*="postcode"]:not(#np-time-postcode)',
      'input[id*="postcode"]:not(#np-time-postcode)'
    ].join(', ');
    

    
    // 立即监控已存在的字段
    $(postcodeSelectors).each(function() {
      attachMonitor($(this));
    });
    
    // 定时检查新增的输入框（处理复杂的动态生成场景）
    if (!window.npPostcodeInterval) {
      window.npPostcodeInterval = setInterval(function() {
        $(postcodeSelectors).each(function() {
          attachMonitor($(this));
        });
        
        // 如果页面被卸载，清除定时器
        if (!document.body.contains(document.querySelector('body'))) {
          clearInterval(window.npPostcodeInterval);
          window.npPostcodeInterval = null;
        }
      }, 2000);
      
      // 页面卸载时清理
      $(window).on('beforeunload.nptime', function() {
        if (window.npPostcodeInterval) {
          clearInterval(window.npPostcodeInterval);
          window.npPostcodeInterval = null;
        }
      });
    }
  }

  $(function(){ 
    var checkoutPage = isCheckoutPage();
    if (!NP_TIME_DATA || !NP_TIME_DATA.tipEnabled) {
      clearStoredTipSelection();
    } else if (checkoutPage) {
      reapplyStoredTipSelection();
    } else {
      clearStoredTipSelection();
    }

    var hasChoiceFlag = false;
    if (typeof NP_TIME_DATA !== 'undefined' && NP_TIME_DATA) {
      hasChoiceFlag = !!NP_TIME_DATA.hasChoice;
    }

    if (NP_TIME_DATA && NP_TIME_DATA.gate) {
      if (!hasChoiceFlag) {

        NP_TIME_DATA.hasChoice = false;
        openModal();
      } else {

      }
    }

    initCouponPicker();
    
    // 恢复滚动位置（如果是因为小费选择刷新的页面）
    var savedScrollPosition = sessionStorage.getItem('npTimeScrollPosition');
    if (savedScrollPosition) {

      setTimeout(function() {
        $(window).scrollTop(parseInt(savedScrollPosition));
        sessionStorage.removeItem('npTimeScrollPosition');
      }, 100);
    }
    
    // 延迟启动邮编监控，确保页面完全加载
    setTimeout(function() {

      monitorBillingPostcode();
    }, 500);
    
    // 多次尝试注入配送信息，确保可靠性
    function initializeDeliveryInfo() {

      ensureCartDeliveryInfoInjected();
    }
    
    // 立即尝试
    initializeDeliveryInfo();
    
    // DOM完全准备后尝试
    setTimeout(initializeDeliveryInfo, 100);
    
    // 稍后再次尝试
    setTimeout(initializeDeliveryInfo, 300);
    
    // 页面完全加载后再次确保配送信息注入
    $(window).on('load', function() {
      // 等待更长时间确保WooCommerce表格结构稳定
      function waitForTableAndInject(attempt) {
        attempt = attempt || 0;
        var $table = $('table.woocommerce-checkout-review-order-table');
        var $subtotalRow = $table.find('tr.cart-subtotal');
        
        if ($table.length && $subtotalRow.length && attempt < 10) {

          // 先清除任何可能已存在的配送信息行
          $('.np-time-checkout-row, .np-time-checkout-block').remove();
          // 注入到正确位置
          ensureCartDeliveryInfoInjected();
          updateTotalsInfoDisplay();
        } else if (attempt < 10) {

          setTimeout(function() {
            waitForTableAndInject(attempt + 1);
          }, 300);
        } else {

          ensureCartDeliveryInfoInjected();
          updateTotalsInfoDisplay();
        }
      }
      
      setTimeout(function() {

        waitForTableAndInject();
        

        monitorBillingPostcode();
      }, 500);
    });
    
    // 小费选择功能
    if (NP_TIME_DATA && NP_TIME_DATA.tipEnabled) {
      setupTipSelection();
    }
    
    // 监听WooCommerce结账更新事件，确保配送信息和小费选择在更新后仍然显示
    $(document).on('updated_checkout', function() {

      // 延迟执行，确保WooCommerce完全更新完毕
      setTimeout(function() {
        ensureCartDeliveryInfoInjected();
        initCouponPicker();
        // 恢复小费选择状态
        if (NP_TIME_DATA && NP_TIME_DATA.tipEnabled) {
          setTimeout(function() {
            restoreTipSelection();
          }, 50);
        } else {
          $('.np-tip-row, .np-tip-block').remove();
        }
      }, 200);
    });
    
    // 监听购物车更新事件
  $(document).on('updated_cart_totals', function() {

      setTimeout(function() {
        ensureCartDeliveryInfoInjected();
        initCouponPicker();
      }, 100);
    });
  });

  $(document).on('click', '.np-time-coupon-close, .np-time-coupon-close-btn', function(e) {
    e.preventDefault();
    closeCouponModal();
  });

  $(document).on('click', '#np-time-coupon-overlay', function(e) {
    if (e.target === this) {
      closeCouponModal();
    }
  });

  $(document).on('click', '.np-time-coupon-apply', function(e) {
    e.preventDefault();
    applyCouponFromModal($(this).data('code'));
  });

  $(document).on('keydown', function(e) {
    if (e.key === 'Escape' && $('#np-time-coupon-overlay').hasClass('is-visible')) {
      closeCouponModal();
    }
  });

  // 恢复小费选择状态
  function restoreTipSelection() {
    if (!NP_TIME_DATA || !NP_TIME_DATA.tipEnabled) {
      return;
    }
    // 优先使用最新的备份状态
    var selectionData = null;
    if (window.lastTipSelection && (Date.now() - window.lastTipSelection.timestamp) < 10000) {
      selectionData = window.lastTipSelection;

    } else if (NP_TIME_DATA && NP_TIME_DATA.selectedTip) {
      selectionData = {
        selectedButton: NP_TIME_DATA.selectedTip,
        type: NP_TIME_DATA.tipType,
        amount: NP_TIME_DATA.selectedTip
      };

    }
    
    if (selectionData) {
      // 等待小费按钮存在
      var restoreAttempt = 0;
      function attemptRestore() {
        if ($('.np-tip-btn').length > 0) {
          $('.np-tip-btn').removeClass('np-tip-selected');
          
          if (selectionData.selectedButton === 'refuse') {
            $('.np-tip-btn.np-tip-refuse').addClass('np-tip-selected');
          } else if (selectionData.type === 'custom') {
            $('.np-tip-btn.np-tip-custom').addClass('np-tip-selected');
          } else {
            $('.np-tip-btn[data-tip="' + selectionData.selectedButton + '"]').addClass('np-tip-selected');
          }
          

        } else if (restoreAttempt < 5) {
          restoreAttempt++;
          setTimeout(attemptRestore, 200);
        }
      }
      
      attemptRestore();
    }
  }

  // 智能页面刷新 - 保持语言和URL参数
  function refreshPageSafely() {

    
    // 获取当前完整的URL（包含所有参数、锚点等）
    var currentUrl = window.location.href;
    
    // 保存滚动位置
    var scrollTop = $(window).scrollTop();
    sessionStorage.setItem('npTimeScrollPosition', scrollTop);
    


    
    // 检查是否有语言参数（常见的多语言插件模式）
    var urlParams = new URLSearchParams(window.location.search);
    var hasLangParam = urlParams.has('lang') || urlParams.has('language') || 
                       urlParams.has('locale') || urlParams.has('wpml_lang') || 
                       urlParams.has('polylang') || urlParams.has('loco') ||
                       urlParams.has('translatepress') || urlParams.has('weglot') ||
                       urlParams.has('gtranslate') || urlParams.has('ml');
    
    // 检查URL是否包含语言路径（如 /en/, /zh/, /de/, /zh-cn/ 等）
    var hasLangPath = /\/[a-z]{2,3}(-[a-zA-Z]{2,4})?\//i.test(window.location.pathname);
    
    // 检查是否有语言子域名（如 en.site.com, zh.site.com）
    var hasLangSubdomain = /^[a-z]{2,3}(-[a-zA-Z]{2,4})?\./.test(window.location.hostname);
    
    // 检查cookie中的语言设置
    var hasLangCookie = document.cookie.indexOf('wp-wpml_current_language=') !== -1 ||
                        document.cookie.indexOf('pll_language=') !== -1 ||
                        document.cookie.indexOf('weglot_language=') !== -1;
    
    if (hasLangParam || hasLangPath || hasLangSubdomain || hasLangCookie) {


      // 使用当前完整URL刷新，保持所有语言设置
      window.location.href = currentUrl;
    } else {
      // 没有明显的语言设置，使用简单刷新

      window.location.reload();
    }
  }
  
  // 小费选择功能
  function setupTipSelection() {
    // 使用事件委托处理动态添加的小费按钮
    $(document).on('click', '.np-tip-btn', function(e) {
      e.preventDefault();
      e.stopPropagation();
      
      var $btn = $(this);
      var tipValue = $btn.data('tip');
      
      // 移除所有选中状态
      $('.np-tip-btn').removeClass('np-tip-selected');
      
      // 添加选中状态到当前按钮
      $btn.addClass('np-tip-selected');
      
      if (tipValue === 'custom') {
        // 显示自定义输入
        $('.np-custom-tip-input').show();
        $('#np-custom-tip-amount').focus();
      } else {
        // 隐藏自定义输入
        $('.np-custom-tip-input').hide();
        
        if (tipValue === 'refuse') {

          saveTipChoice('refuse', '0.00');
        } else {

          saveTipChoice('preset', tipValue);
        }
      }
      
      return false;
    });
    
    // 自定义小费确认
    $(document).on('click', '#np-custom-tip-confirm', function(e) {
      e.preventDefault();
      e.stopPropagation();
      
      var customAmount = $('#np-custom-tip-amount').val();
      if (customAmount && parseFloat(customAmount) >= 0) {

        var formattedAmount = '$' + parseFloat(customAmount).toFixed(2);
        saveTipChoice('custom', formattedAmount);
        $('.np-custom-tip-input').hide();
      } else {
        alert(getString('tip_invalid_amount_alert','请输入有效的小费金额'));
      }
      
      return false;
    });
    
    // 回车键确认自定义小费
    $(document).on('keypress', '#np-custom-tip-amount', function(e) {
      if (e.which === 13) {
        $('#np-custom-tip-confirm').click();
      }
    });
  }
  
  // 保存小费选择
  function saveTipChoice(type, amount) {

    
    // 更新全局数据
    if (!NP_TIME_DATA) NP_TIME_DATA = {};
    NP_TIME_DATA.selectedTip = type === 'refuse' ? 'refuse' : amount;
    NP_TIME_DATA.tipType = type;
    NP_TIME_DATA.tipAmount = amount;
    
    // 设置更新标志，防止过度刷新
    window.npTipUpdating = true;
    
    // 发送到后端保存
    return $.ajax({
      url: NP_TIME_DATA.ajaxUrl,
      type: 'POST',
      data: {
        action: 'np_time_save_tip',
        nonce: NP_TIME_DATA.nonce,
        tip_type: type,
        tip_amount: amount
      },
      success: function(response) {
        if (response.success) {

          
          // 显示用户反馈
          var feedbackText = type === 'refuse' ? 
            getString('tip_cancelled_feedback','已取消小费') : 
            formatString('tip_added_feedback_template','小费 %s 已添加', amount);

          
          // 更新全局数据
          NP_TIME_DATA.selectedTip = type === 'refuse' ? 'refuse' : amount;
          
          // 不刷新页面，直接更新UI显示
          
          // 保存当前选择状态，防止在更新过程中丢失
          window.lastTipSelection = {
            type: type,
            amount: amount,
            selectedButton: $('.np-tip-btn.np-tip-selected').attr('data-tip'),
            timestamp: Date.now()
          };

          if (isCheckoutPage()) {
            storeTipSelection(type, amount);
          } else {
            clearStoredTipSelection();
          }
          
          // 触发结账区域更新，让WooCommerce重新计算总价
          $('body').trigger('update_checkout');
          

          
        } else {
          console.error('NP Time: 保存小费失败:', response.data);
        }
      },
      error: function() {
        console.error('NP Time: 保存小费请求失败');
        // 清除更新标志
        window.npTipUpdating = false;
      }
    });
  }

  // 页面加载时清理用户仪表盘的配送信息元素
  $(document).ready(function() {
    // 检查页面刷新后的选择状态
    if (typeof(Storage) !== "undefined" && sessionStorage.getItem('np_time_has_choice') === '1') {
      // 如果sessionStorage显示用户已选择，更新NP_TIME_DATA状态
      if (NP_TIME_DATA) {
        NP_TIME_DATA.hasChoice = true;
        console.log('NP-Time: Restored choice state from sessionStorage');
      }
      // 不清除sessionStorage标记，让它在会话期间持续存在
    }
    
    // 初始检查并显示模态框（如果需要）
    setTimeout(function() {
      console.log('NP-Time: 开始初始检查...');
      console.log('NP-Time: NP_TIME_DATA.gate =', NP_TIME_DATA ? NP_TIME_DATA.gate : 'undefined');
      console.log('NP-Time: NP_TIME_DATA.hasChoice =', NP_TIME_DATA ? NP_TIME_DATA.hasChoice : 'undefined');
      
      if (NP_TIME_DATA && NP_TIME_DATA.gate && !NP_TIME_DATA.hasChoice) {
        console.log('NP-Time: Gate检查失败，进行Cookie检查...');
        
        // 额外的cookie检查，防止PHP端检查失败
        var cookieValue = getCookie('np_time_choice');
        console.log('NP-Time: Cookie值 =', cookieValue);
        
        if (cookieValue) {
          try {
            // 尝试直接解析，如果失败再尝试URL解码
            var data;
            try {
              data = JSON.parse(cookieValue);
              console.log('NP-Time: Cookie直接解析成功');
            } catch (e1) {
              console.log('NP-Time: Cookie直接解析失败，尝试URL解码');
              data = JSON.parse(decodeURIComponent(cookieValue));
              console.log('NP-Time: Cookie URL解码后解析成功');
            }
            
            console.log('NP-Time: 解析后的数据 =', data);
            
            if (data && data.postcode && data.date) {
              // 基本格式检查：邮编不为空，日期格式正确
              var postcode = String(data.postcode).trim();
              var date = String(data.date).trim();
              var datePattern = /^\d{4}-\d{2}-\d{2}$/;
              
              console.log('NP-Time: 提取的邮编 =', postcode);
              console.log('NP-Time: 提取的日期 =', date);
              console.log('NP-Time: 日期格式检查 =', datePattern.test(date));
              
              if (postcode && date && datePattern.test(date)) {
                console.log('NP-Time: JS验证通过，设置hasChoice=true');
                NP_TIME_DATA.hasChoice = true;
                
                // 更新sessionStorage
                if (typeof(Storage) !== "undefined") {
                  sessionStorage.setItem('np_time_has_choice', '1');
                  console.log('NP-Time: 已设置sessionStorage标记');
                }
                
                return; // 不显示模态框
              } else {
                console.log('NP-Time: JS验证失败');
              }
            } else {
              console.log('NP-Time: Cookie数据格式错误或缺少字段');
            }
          } catch (e) {
            console.log('NP-Time: Cookie解析错误:', e);
          }
        } else {
          console.log('NP-Time: 没有找到Cookie');
        }
        
        // 如果确实没有有效选择，显示模态框
        console.log('NP-Time: 没有找到有效选择，将显示模态框');
        openModal();
      } else {
        if (!NP_TIME_DATA) {
          console.log('NP-Time: NP_TIME_DATA未定义');
        } else if (!NP_TIME_DATA.gate) {
          console.log('NP-Time: Gate未启用');
        } else if (NP_TIME_DATA.hasChoice) {
          console.log('NP-Time: hasChoice=true，不需要显示模态框');
        }
      }
    }, 500); // 增加延迟到500毫秒，给页面更多时间加载
    
    var isMyAccountPage = window.location.href.indexOf('/my-account/') !== -1 || 
                          $('.woocommerce-account').length > 0 ||
                          $('.woocommerce-MyAccount-content').length > 0;
    
    if (isMyAccountPage) {
      // 移除可能存在的配送信息元素
      $('#np-time-totals-info, .np-time-checkout-row, .np-time-checkout-block').remove();
      
      // 移除订单项中的配送元数据
      $('.woocommerce-account .wc-item-meta').remove();
      
      // 定时检查并移除可能动态生成的元素
      var checkAndRemove = function() {
        $('.woocommerce-account .wc-item-meta').remove();
      };
      
      // 页面加载后继续检查
      setTimeout(checkAndRemove, 500);
      setTimeout(checkAndRemove, 1000);
      setTimeout(checkAndRemove, 2000);
    }
  });

  // 显示购物车冲突对话框
  function showCartConflictDialog(data) {
    var conflicts = data.cart_conflicts || [];
    var choice = data.choice || {};
    var message = data.message || getString('product_delivery_conflict_message', '您选择的配送日期导致购物车中某些商品无法配送，是否继续并移除这些商品？');
    
    if (conflicts.length === 0) {
      return;
    }
    
    // 构建冲突商品列表
    var itemsList = conflicts.map(function(item) {
      return '• ' + item.product_name + ' (数量: ' + item.quantity + ')';
    }).join('\n');
    
    var itemsHeader = getString('product_delivery_conflict_items_header', '需要移除的商品：');
    var fullMessage = message + '\n\n' + itemsHeader + '\n' + itemsList;
    
    if (confirm(fullMessage)) {
      // 用户确认移除冲突商品
      var cart_keys = conflicts.map(function(item) {
        return item.cart_key;
      });
      
      removeCartConflicts(choice.postcode, choice.date, cart_keys);
    }
    // 如果用户取消，不执行任何操作，保持弹窗打开
  }
  
  // 移除购物车中冲突的商品
  function removeCartConflicts(postcode, date, cartKeys) {
    var payload = {
      action: 'np_time_remove_cart_conflicts',
      nonce: NP_TIME_DATA.nonce,
      postcode: postcode,
      date: date,
      cart_keys: cartKeys
    };
    
    $.post(NP_TIME_DATA.ajaxUrl, payload)
    .done(function(res) {
      if (res && res.success) {
        closeModal();
        NP_TIME_DATA.hasChoice = true;
        
        // 设置sessionStorage标记
        if (typeof(Storage) !== "undefined") {
          sessionStorage.setItem('np_time_has_choice', '1');
        }
        
        // 更新配送选择数据
        if (res.data && res.data.choice) {
          if (!NP_TIME_DATA.choice) {
            NP_TIME_DATA.choice = {};
          }
          
          if (res.data.choice.postcode) {
            NP_TIME_DATA.choice.postcode = String(res.data.choice.postcode);
          }
          
          if (res.data.choice.date) {
            NP_TIME_DATA.choice.date = String(res.data.choice.date);
          }
        }
        
        // 显示成功消息
        var successMsg = res.data.message || getString('product_delivery_remove_success', '已成功移除不符合配送日期的商品');
        alert(successMsg);
        
        // 刷新页面以更新产品列表和购物车显示
        setTimeout(function() {
          window.location.reload();
        }, 1000);
        
      } else {
        console.log('NP Time: 移除冲突商品失败', res);
        var errorMsg = (res && res.data && res.data.message) || getString('product_delivery_remove_failed', '移除商品失败，请重试');
        alert(errorMsg);
      }
    })
    .fail(function(xhr) {
      var msg = getString('product_delivery_remove_failed', '移除商品失败，请重试');
      try {
        var r = JSON.parse(xhr.responseText);
        if (r && r.data && r.data.message) {
          msg = r.data.message;
        }
      } catch(e) {}
      alert(msg);
    });
  }

  // 在页面加载时检查存储的日期
  $(document).ready(function() {
      // 检查存储的选择是否过期
      if (NP_TIME_DATA && NP_TIME_DATA.choice) {
          var selectedDate = NP_TIME_DATA.choice.date;
          if (selectedDate) {
              var today = new Date();
              today.setHours(0,0,0,0);
              var selected = new Date(selectedDate.replace(/-/g,'/'));
              
              // 如果选择的日期是今天或之前，强制重新选择
              if (selected <= today) {
                  console.log('NP-Time: 选择的日期已过期，需要重新选择');
                  NP_TIME_DATA.hasChoice = false;
                  NP_TIME_DATA.choice = null;
                  // 清除本地存储
                  localStorage.removeItem('np_time_choice');
                  sessionStorage.removeItem('np_time_choice');
                  // 如果启用了gate，显示选择弹窗
                  if (NP_TIME_DATA.gate) {
                      openModal();
                  }
              }
          }
      }
  });

})(jQuery);
