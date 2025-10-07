(function($){
  var selectedDates = new Set();
  function collectFromList(){
    selectedDates = new Set();
    $('#np-time-date-list li').each(function(){ selectedDates.add($(this).attr('data-value')); });
  }
  function syncHidden(){
    var values=[]; $('#np-time-date-list li').each(function(){ values.push($(this).attr('data-value')); });
    $('#np-time-dates-hidden').val(values.join('\n'));
  }
  function toggleModeUI(){
    var mode = $('input[name="np_time_mode"]:checked').val()||'date';
    if(mode==='date'){
      $('#np-time-date-block').show();
      $('#np-time-days-block').hide();
      $('#np-time-days-checkboxes .np-time-day').prop('checked', false);
    }else{
      $('#np-time-date-block').hide();
      $('#np-time-days-block').show();
      $('#np-time-date-list').empty();
      collectFromList();
      syncHidden();
    }
  }
  function ensureDatepicker(){
    var $inp = $('#np-time-date-input');
    if($inp.length && !$inp.data('hasDatepicker')){
      collectFromList();
      $inp.datepicker({
        dateFormat: 'yy-mm-dd',
        minDate: 1, // 禁用当天
        showOtherMonths: true,
        selectOtherMonths: true,
        beforeShowDay: function(date){
          var d = $.datepicker.formatDate('yy-mm-dd', date);
          var sel = selectedDates.has(d);
          return [true, sel ? 'selected' : ''];
        },
        onSelect: function(dateText){
          if(selectedDates.has(dateText)){
            // 取消选择
            selectedDates.delete(dateText);
            $('#np-time-date-list li[data-value="'+dateText+'"]').remove();
          }else{
            // 新增
            selectedDates.add(dateText);
            if($('#np-time-date-list li[data-value="'+dateText+'"]').length===0){
              $('#np-time-date-list').append('<li data-value="'+dateText+'">'+dateText+' <a href="#" class="np-time-remove-date">移除</a></li>');
            }
          }
          syncHidden();
          // 保持日历显示，便于连续多选
          setTimeout(function(){ $inp.focus(); $inp.datepicker('refresh'); }, 0);
        },
        showButtonPanel: false
      });
    }
  }

  $(function(){ ensureDatepicker(); toggleModeUI(); });

  $(document).on('change','input[name="np_time_mode"]', function(){ toggleModeUI(); ensureDatepicker(); });

  // 支持从列表移除时同步状态与高亮
  $(document).on('click','.np-time-remove-date', function(e){
    e.preventDefault();
    var $li = $(this).closest('li');
    var v = $li.attr('data-value');
    $li.remove();
    selectedDates.delete(v);
    syncHidden();
    var $inp = $('#np-time-date-input');
    if($inp.data('hasDatepicker')){ $inp.datepicker('refresh'); }
  });

  function parseCSV(file, cb){
    if(!file) return;
    var reader=new FileReader();
    reader.onload=function(e){
      var text=e.target.result||'';
      var rows=text.split(/\r?\n/).slice(1); // skip header
      var items=[]; rows.forEach(function(r){ r.split(',').forEach(function(c){ c=c.trim(); if(c) items.push(c); }); });
      cb(items);
    };
    reader.readAsText(file);
  }

  // 导入规则邮编（CPT）
  $(document).on('click','#np-time-csv-import',function(){
    var f=$('#np-time-csv')[0].files[0];
    parseCSV(f,function(list){
      var ta=$('#np-time-postcodes');
      var cur=ta.val().trim();
      var merged=(cur? cur+'\n' : '') + list.join('\n');
      ta.val(merged);
    });
  });

  // 导入本地邮编（设置页）
  $(document).on('click','#np-time-local-csv-import,#np-time-import-local',function(){
    var f=$('#np-time-local-csv')[0] && $('#np-time-local-csv')[0].files[0];
    if(!f){ alert('请选择CSV文件'); return; }
    parseCSV(f,function(list){
      // 合并到隐藏域
      var ta=$('#np-time-local-postcodes');
      var cur=(ta.val()||'').trim();
      var merged=(cur? cur+'\n' : '') + list.join('\n');
      ta.val(merged);
      // 刷新chips
      refreshLocalList();
      alert('已导入 '+list.length+' 条本地邮编');
    });
  });

  // 本地邮编：添加/删除
  function refreshLocalList(){
    var ta=$('#np-time-local-postcodes');
    if(!ta.length) return;
    var content=(ta.val()||'').trim();
    var codes = content? content.split(/[\r\n,]+/).map(function(c){return c.trim();}).filter(Boolean) : [];
    var box=$('#np-time-local-manager .postcodes-list.local');
    box.empty();
    if(codes.length){
      codes.forEach(function(code){
        var item=$('<div class="postcode-item" style="display:inline-block;background:#e8f4fd;padding:3px 6px;margin:2px;border-radius:3px;font-family:monospace;"></div>');
        item.append('<span class="postcode-value">'+escapeHtml(code)+'</span> ');
        item.append('<a href="#" class="remove-local-postcode" data-code="'+escapeAttr(code)+'" style="color:red;text-decoration:none;margin-left:3px;" title="删除">&times;</a>');
        box.append(item);
      });
    }else{
      box.append('<em style="color:#999;">暂无本地邮编</em>');
    }
  }

  $(document).on('click','.add-local-postcode',function(){
    var input = $('#np-time-local-manager .postcode-input');
    var code = input.val().trim();
    if(!code){ alert('请输入邮编'); return; }
    var ta=$('#np-time-local-postcodes');
    var existing=(ta.val()||'').trim();
    var arr = existing? existing.split(/\r?\n/).map(function(c){return c.trim();}).filter(Boolean) : [];
    if(arr.indexOf(code)!==-1){ alert('该邮编已存在'); return; }
    arr.push(code);
    ta.val(arr.join('\n'));
    input.val('');
    refreshLocalList();
  });

  $(document).on('click','.remove-local-postcode',function(e){
    e.stopImmediatePropagation();
    var code=String($(this).attr('data-code'));
    var ta=$('#np-time-local-postcodes');
    var arr = (ta.val()||'').trim().split(/[\r\n,]+/).map(function(c){return c.trim();}).filter(Boolean);
    arr = arr.filter(function(c){ return c!==code; });
    ta.val(arr.join('\n'));
    refreshLocalList();
    return false;
  });

  // 页面初始化时刷新本地列表
  $(function(){ refreshLocalList(); });

  $(function(){
    if ($.fn.wpColorPicker) {
      $('.np-time-color-field').wpColorPicker({
        change: function(event, ui){
          $(event.target).val(ui.color.toString());
        },
        clear: function(event){
          var $input = $(event.target);
          var def = $input.attr('placeholder') || '';
          if (def) {
            $input.val(def).trigger('change');
          }
        }
      });
    }
  });

  // 导入到按周几的邮编（设置页）
  $(document).on('click','#np-time-import-weekday',function(){
    var f=$('#np-time-weekday-csv')[0] && $('#np-time-weekday-csv')[0].files[0];
    var target=$('#np-time-weekday-target').val();
    if (!f) { alert('请选择CSV文件'); return; }
    parseCSV(f,function(list){
      var ta=$('textarea[name="np_time_weekday_postcodes['+target+']"]');
      var cur=ta.val().trim();
      var merged=(cur? cur+'\n' : '') + list.join('\n');
      ta.val(merged);
      // 刷新界面显示
      refreshWeekdayDisplay(target);
  alert('成功导入 '+list.length+' 个邮编到'+['星期日','星期一','星期二','星期三','星期四','星期五','星期六'][target]);
    });
  });

  // 新增邮编管理功能
  function refreshWeekdayDisplay(day) {
    var ta = $('textarea[name="np_time_weekday_postcodes['+day+']"]');
    var content = ta.val().trim();
    var codes = content ? content.split(/[\r\n,]+/).map(function(c){ return c.trim(); }).filter(Boolean) : [];
    var row = $('.weekday-row[data-day="'+day+'"]');
    var listDiv = row.find('.postcodes-list');
    
    listDiv.empty();
    if (codes.length > 0) {
      codes.forEach(function(code) {
        var item = $('<div class="postcode-item" style="display:inline-block;background:#e8f4fd;padding:3px 6px;margin:2px;border-radius:3px;font-family:monospace;"></div>');
        item.append('<span class="postcode-value">'+escapeHtml(code)+'</span> ');
        item.append('<a href="#" class="remove-postcode" data-day="'+day+'" data-code="'+escapeAttr(code)+'" style="color:red;text-decoration:none;margin-left:3px;" title="删除">&times;</a>');
        listDiv.append(item);
      });
      row.find('th small').text('('+codes.length+'个邮编)');
    } else {
      listDiv.append('<em style="color:#999;">暂无邮编</em>');
      row.find('th small').text('');
    }
  }

  function escapeHtml(text) {
    return $('<div>').text(text).html();
  }
  
  function escapeAttr(text) {
    return text.replace(/[&<>"']/g, function(m) {
      return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m];
    });
  }

  // 添加邮编
  $(document).on('click', '.add-postcode', function() {
    var day = $(this).data('day');
    var input = $(this).siblings('.postcode-input');
    var code = input.val().trim();
    if (!code) { alert('请输入邮编'); return; }
    
    var ta = $('textarea[name="np_time_weekday_postcodes['+day+']"]');
    var current = ta.val().trim();
    var codes = current ? current.split(/[\r\n,]+/).map(function(c){ return c.trim(); }).filter(Boolean) : [];
    
    if (codes.indexOf(code) !== -1) {
      alert('该邮编已存在');
      return;
    }
    
    codes.push(code);
    ta.val(codes.join('\n'));
    input.val('');
    refreshWeekdayDisplay(day);
  });

  // 删除邮编
  $(document).on('click', '.remove-postcode', function(e) {
    e.stopImmediatePropagation();
    var day = String($(this).attr('data-day'));
    var code = String($(this).attr('data-code'));
    
    var ta = $('textarea[name="np_time_weekday_postcodes['+day+']"]');
    var codes = ta.val().trim().split(/[\r\n,]+/).map(function(c){ return c.trim(); }).filter(Boolean);
    codes = codes.filter(function(c) { return c !== code; });
    
    ta.val(codes.join('\n'));
    refreshWeekdayDisplay(day);
    return false;
  });

  // 搜索过滤（本地与星期几通用）
  $(document).on('input', '.postcode-search', function(){
    var q = $(this).val().toLowerCase();
    var scope = $(this).data('scope'); // local | weekday
    var container;
    if(scope==='local'){
      container = $('#np-time-local-manager .postcodes-list.local');
    }else{
      var day = $(this).data('day');
      container = $('.weekday-row[data-day="'+day+'"]').find('.postcodes-list');
    }
    container.find('.postcode-item').each(function(){
      var text = $(this).find('.postcode-value').text().toLowerCase();
      $(this).toggle(text.indexOf(q)!==-1);
    });
  });

  // 删除匹配项（根据当前搜索框过滤结果）
  $(document).on('click', '.delete-matched', function(){
    var scope = $(this).data('scope');
    var day = $(this).data('day');
    var searchInput;
    var container;
    if(scope==='local'){
      searchInput = $('#np-time-local-manager .postcode-search[data-scope="local"]');
      container = $('#np-time-local-manager .postcodes-list.local');
      var ta = $('#np-time-local-postcodes');
      var all = (ta.val()||'').trim().split(/[\r\n,]+/).map(function(c){return c.trim();}).filter(Boolean);
      // 收集可见项并删除
      var toDelete = [];
      container.find('.postcode-item:visible .postcode-value').each(function(){ toDelete.push($(this).text().trim()); });
      var left = all.filter(function(c){ return toDelete.indexOf(c)===-1; });
      ta.val(left.join('\n'));
      refreshLocalList();
      // 清空搜索
      searchInput.val('');
    }else{
      var row = $('.weekday-row[data-day="'+day+'"]');
      searchInput = row.find('.postcode-search[data-scope="weekday"]');
      container = row.find('.postcodes-list');
      var ta2 = $('textarea[name="np_time_weekday_postcodes['+day+']"]');
      var all2 = (ta2.val()||'').trim();
      var arr = all2? all2.split(/[\r\n,]+/).map(function(c){return c.trim();}).filter(Boolean) : [];
      var toDelete2 = [];
      container.find('.postcode-item:visible .postcode-value').each(function(){ toDelete2.push($(this).text().trim()); });
      arr = arr.filter(function(c){ return toDelete2.indexOf(c)===-1; });
      ta2.val(arr.join('\n'));
      refreshWeekdayDisplay(day);
      searchInput.val('');
    }
  });

  // 清空全部（双重确认）
  $(document).on('click', '.clear-all', function(){
    var scope = $(this).data('scope');
    var day = $(this).data('day');
    if(!confirm('确定要清空全部吗？')) return;
    if(!confirm('再次确认：该操作不可撤销，是否继续？')) return;
    if(scope==='local'){
      var ta=$('#np-time-local-postcodes');
      ta.val('');
      refreshLocalList();
      $('#np-time-local-manager .postcode-search').val('');
    }else{
      var ta2=$('textarea[name="np_time_weekday_postcodes['+day+']"]');
      ta2.val('');
      refreshWeekdayDisplay(day);
      $('.weekday-row[data-day="'+day+'"]').find('.postcode-search').val('');
    }
  });

  // 回车键添加邮编
  $(document).on('keypress', '.postcode-input', function(e) {
    if (e.which === 13) {
      $(this).siblings('.add-postcode').click();
    }
  });

  // 初始化显示
  $(function() {
    $('.weekday-row').each(function() {
      var day = $(this).data('day');
      if (day !== undefined) {
        refreshWeekdayDisplay(day);
      }
    });
  });

  function updateFabPreview(url){
    var $preview = $('.np-time-fab-preview');
    if(!$preview.length) return;
    $preview.empty();
    if(url){
      var img = $('<img>', { src: url, alt: '', style: 'max-height:36px;border-radius:4px;' });
      $preview.append(img);
    }
  }

  $(document).on('change', '#np-time-fab-icon', function(){
    updateFabPreview($.trim($(this).val()));
  });

  $(document).on('click', '.np-time-media-select', function(e){
    e.preventDefault();
    if (typeof wp === 'undefined' || !wp.media) {
      alert('当前环境不支持媒体库');
      return;
    }

    var target = $(this).data('target');
    var targetId = $(this).data('target-id');
    var frame = wp.media({
      title: '选择图标',
      button: { text: '使用此图标' },
      multiple: false
    });

    frame.on('select', function(){
      var attachment = frame.state().get('selection').first().toJSON();
      if(target){
        var $input = $('#' + target);
        $input.val(attachment.url).trigger('change');
      }
      if(targetId){
        $('#' + targetId).val(attachment.id);
      }
      updateFabPreview(attachment.url);
    });

    frame.open();
  });

  $(document).on('click', '.np-time-media-clear', function(e){
    e.preventDefault();
    var target = $(this).data('target');
    var targetId = $(this).data('target-id');
    if(target){
      $('#' + target).val('').trigger('change');
    }
    if(targetId){
      $('#' + targetId).val('');
    }
    updateFabPreview('');
  });

  // CSV import（保持不变）
  $(document).on('click', '#np-time-csv-import', function(){
    var input = document.getElementById('np-time-csv');
    if(!input || !input.files || !input.files[0]){ alert('请选择CSV文件'); return; }
    var file = input.files[0];
    var reader = new FileReader();
    reader.onload = function(){
      var text = reader.result || '';
      var lines = text.split(/\r?\n/); if(lines.length>0){ lines.shift(); }
      var codes = lines.map(function(l){ return (l||'').split(',')[0].trim(); }).filter(Boolean);
      var $ta = $('#np-time-postcodes');
      var existing = $ta.val().trim();
      var joined = (existing? existing+'\n' : '') + codes.join('\n');
      $ta.val(joined);
      alert('已导入 '+codes.length+' 条邮编');
    };
    reader.readAsText(file);
  });

  $(document).on('click', '#np-time-local-csv-import', function(){
    var input = document.getElementById('np-time-local-csv');
    if(!input || !input.files || !input.files[0]){ alert('请选择CSV文件'); return; }
    var file = input.files[0];
    var reader = new FileReader();
    reader.onload = function(){
      var text = reader.result || '';
      var lines = text.split(/\r?\n/); if(lines.length>0){ lines.shift(); }
      var codes = lines.map(function(l){ return (l||'').split(',')[0].trim(); }).filter(Boolean);
      var $ta = $('#np-time-local-postcodes');
      var existing = $ta.val().trim();
      var joined = (existing? existing+'\n' : '') + codes.join('\n');
      $ta.val(joined);
      alert('已导入 '+codes.length+' 条本地邮编');
    };
    reader.readAsText(file);
  });
})(jQuery);
