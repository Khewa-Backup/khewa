$(document).ready(function(){

  var specific_price_fields = {
    selected: {'filter_fields': false, 'update': false},
    id_specific_price_selected: {'filter_fields': false, 'update': false},
    selected_num: {'filter_fields': 0, 'update': 0},
    fields_ids: ['id_specific_price',
                  'specific_price',
                  'specific_price_reduction',
                  'specific_price_reduction_type',
                  'specific_price_from',
                  'specific_price_to',
                  'specific_price_from_quantity',
                  'specific_price_id_group'
    ],
    setParameters: function(page) {
        $(".updateproducts #"+page+" .selected_fields li").each(function(e) {
            var field_id = $(this).attr("data-value");

            if ($.inArray(field_id, specific_price_fields.fields_ids) !== -1) {
                specific_price_fields.selected[page] = true;
                specific_price_fields.id_specific_price_selected[page] = true;
                specific_price_fields.selected_num[page]++;
            }
        });
    },
    onAdd: function(page, field_id, tab) {
        if (field_id === "id_specific_price") {
            specific_price_fields.id_specific_price_selected[page] = true;
            specific_price_fields.selected[page] = true;
            specific_price_fields.selected_num[page]++;
        } else if ($.inArray(field_id, specific_price_fields.fields_ids) !== -1 && field_id !== "id_specific_price") {
            specific_price_fields.selected_num[page]++;
            specific_price_fields.selected[page] = true;
            var id_specific_price_name = $("li[data-value='id_specific_price']").data("name");
            
            if (specific_price_fields.id_specific_price_selected[page] === false) {
                specific_price_fields.id_specific_price_selected[page] = true;
                specific_price_fields.selected_num[page]++;
                var data_hint = "This value is automatically added when any of the special_price fields is chosen, and can be removed only if none of the special_price fields is added";
                var specific_price_id_field = "<li data-tab='"+tab+"' data-name='"+id_specific_price_name+"' data-value='id_specific_price' class='isset_hint' data-hint='"+data_hint+"'><i class='icon-info icon-info-fields'></i>"+id_specific_price_name+" <i class='icon-arrows icon-arrows-select-fields'></i></li>";
                $('.updateproducts #'+page+'  .block_selected_fields .selected_fields').append(specific_price_id_field);

                $(".block_base_fields [data-value='id_specific_price'][data-tab='"+tab+"']").remove();
            }
        } else {
          return false;
        }
    },
    onAddAll: function(page) {
      specific_price_fields.selected_num[page] = specific_price_fields.fields_ids.length;
      specific_price_fields.selected[page] = true;
      specific_price_fields.id_specific_price_selected[page] = true;
    },
    onDelete: function(page, field_id) {
        if (field_id === "id_specific_price" && specific_price_fields.selected_num[page] === 1) {
            specific_price_fields.selected_num[page]--;
            specific_price_fields.id_specific_price_selected[page] = false;
            specific_price_fields.selected[page] = false;
        } else {
            if ($.inArray(field_id, specific_price_fields.fields_ids) !== -1) {
                specific_price_fields.selected_num[page]--;
            }
        }
    },
    onDeleteAll: function(page) {
        specific_price_fields.selected_num[page] = 0;
        specific_price_fields.selected[page] = false;
        specific_price_fields.id_specific_price_selected[page] = false;
    }
  };

  specific_price_fields.setParameters("filter_fields");
  specific_price_fields.setParameters("update");

  tabActive('#export');


  $(document).on('click', '.alert-danger-export li a', function(){
    var tab = $(this).attr('data-tab');
    var field = $(this).attr('data-field');
    activeErrorTab(tab, field);
  });


  $(document).on('click', '.updateproducts .add_base_filds_all', function(e){
    var base_tab = $('.updateproducts .nav-tabs li.active a').attr('href');
    var tab = $(base_tab+' .list-group-item.active').attr('data-tab');
    var page = $(this).attr('data-page');

    $('.updateproducts #'+page+' .field_list_'+tab+'  .block_base_fields li').each(function(e) {
      var el = $(this).clone().removeClass('checked').append('<i class="icon-arrows icon-arrows-select-fields"></i>');
      $('.updateproducts #'+page+'  .block_selected_fields .selected_fields').append(el[0]);
      $(this).remove();
    });

    if (tab == "exportTabPrices" || tab == "updateTabPrices") {
      specific_price_fields.onAddAll(page);
    }
  });


  $(document).on('click', '.updateproducts .add_base_filds', function(e){
    var base_tab = $('.updateproducts .nav-tabs li.active a').attr('href');
    var tab = $(base_tab+' .list-group-item.active').attr('data-tab');
    var page = $(this).attr('data-page');

    $('.updateproducts #'+page+'  .field_list_'+tab+'  .block_base_fields li.checked').each(function(e) {
      var field_id = $(this).attr("data-value");
      specific_price_fields.onAdd(page, field_id, tab);

      var el = $(this).clone().removeClass('checked').append('<i class="icon-arrows icon-arrows-select-fields"></i>');
      $('.updateproducts #'+page+'  .block_selected_fields .selected_fields').append(el[0]);
      $(this).remove();
    });
  });

  $(document).on('click', '.updateproducts .remove_base_filds_all', function(e){
    var page = $(this).attr('data-page');

    $('.updateproducts #'+page+'  .selected_fields li').each(function(e) {
      if(!$(this).hasClass('disable_fields')) {
        var tab =  $(this).attr('data-tab');
        var el = $(this).clone().removeClass('checked');
        $('.updateproducts #'+page+' .field_list_'+tab+' .block_base_fields').append(el[0]);
        $(this).remove();
      }
    });

    $('.updateproducts #'+page+' .block_base_fields li .icon-arrows-select-fields').remove();

    specific_price_fields.onDeleteAll(page);
  });


  $(document).on('click', '.updateproducts .remove_base_filds', function(e){
    var page = $(this).attr('data-page');
    $('.updateproducts #'+page+'  .selected_fields li.checked').each(function(e) {
      if(!$(this).hasClass('disable_fields')){
        var tab = $(this).attr('data-tab');
        var el = $(this).clone().removeClass('checked');
        var field_id = $(this).attr("data-value");

          if (field_id === "id_specific_price" && specific_price_fields.selected_num[page] > 1) {
              return;
          }
          specific_price_fields.onDelete(page, field_id);

        $('.updateproducts  #'+page+' .field_list_'+tab+' .block_base_fields').append(el[0]);
        $(this).remove();
      }
    });

    $('.updateproducts  #'+page+' .block_base_fields li .icon-arrows-select-fields').remove();
  });

  $(document).on('keyup', '.updateproducts .search_base_fields', function(){
    var self = $(this);
    var base_tab = $('.updateproducts .nav-tabs li.active a').attr('href');
    var tab = $(base_tab+' .list-group-item.active').attr('data-tab');
    var page = $(this).attr('data-page');
    $('.updateproducts #'+page+'  .field_list_'+tab+' .block_base_fields li').each(function(){
      if( $(this).text().toLowerCase().indexOf(self.val().toLowerCase()) >= 0 ){
        $(this).show();
      }
      else{
        $(this).hide();
      }
    });
  });

  $(document).on('keyup', '.updateproducts .search_selected_fields', function(){
    var self = $(this);
    var page = $(this).attr('data-page');
    $('.updateproducts #'+page+'  .selected_fields li').each(function(){
      if( $(this).text().toLowerCase().indexOf(self.val().toLowerCase()) >= 0 ){
        $(this).show();
      }
      else{
        $(this).hide();
      }
    });
  });

  $("#filter_fields .fields_list .list-group-item").live('click', function(){
    if(!$(this).hasClass('active')){
      var tab = $(this).attr('data-tab');
      $('#filter_fields .block_all_fields .field_list_base').removeClass('active');
      $('#filter_fields .block_all_fields .field_list_'+tab).addClass('active');
      $("#filter_fields .fields_list .list-group-item").removeClass('active');
      $(this).addClass('active');
    }
  });

  $("#update .fields_list .list-group-item").live('click', function(){
    if(!$(this).hasClass('active')){
      var tab = $(this).attr('data-tab');
      $('#update .block_all_fields .field_list_base').removeClass('active');
      $('#update .block_all_fields .field_list_'+tab).addClass('active');
      $("#update .fields_list .list-group-item").removeClass('active');
      $(this).addClass('active');
    }
  });

  replaceUrlFile();
  $(document).on('keyup', 'input[name=name_file]', function(){
    replaceUrlFile();
  });
  $(document).on('change', 'input[name=format_file]', function(){
    replaceUrlFile();
  });

  $(document).on('change', 'input[name=name_export_file]', function(){
    if($('input[name=name_export_file]:checked').val() == 1){
      $('.form_group_name_file').addClass('active_block');
      $('.auto_description_ex').addClass('active_block');
    }
    else{
      $('.form_group_name_file').removeClass('active_block');
      $('.auto_description_ex').removeClass('active_block');
    }
  });


  $(document).on('change', 'input[name=selection_type_price]', function(){
    $('.price .label_selection_type').removeClass('active');
    $('.price .label_selection_type').css('border-color', '#cccccc');
    $(this).prev().addClass('active');
  });

  $(document).on('change', 'input[name=active_products]', function(){
    if($('#active_products_on:checked').val()){
      $('#inactive_products_off').prop('checked', true);
      $('#inactive_products_on').prop('checked', false);
    }
  });

  $(document).on('change', 'input[name=inactive_products]', function(){
    if($('#inactive_products_on:checked').val()){
      $('#active_products_off').prop('checked', true);
      $('#active_products_on').prop('checked', false);
    }
  });

  $(document).on('change', 'input[name=selection_type_quantity]', function(){
    $('.quantity .label_selection_type').removeClass('active');
    $('.quantity .label_selection_type').css('border-color', '#cccccc');
    $(this).prev().addClass('active');
  });

  $(document).on('change', '.type_visibility_checkbox', function(){
    if($(this).prev().hasClass('active')){
      $(this).prev().removeClass('active');
    }
    else{
      $(this).prev().addClass('active');
    }
  });

  $(document).on('change', '.type_condition_checkbox', function(){
    if($(this).prev().hasClass('active')){
      $(this).prev().removeClass('active');
    }
    else{
      $(this).prev().addClass('active');
    }
  });

  $(document).on('change', '#filter_fields input[name=format_file]', function(){
    if($('#filter_fields input[name=format_file]:checked').val() !== 'xlsx'){
      $('li[data-value=image_cover]').hide();
    }
    else{
      $('li[data-value=image_cover]').show();
    }
  });

  $("body").on("mouseenter", "#update .content_fields li", function(){
      if($(this).hasClass("isset_hint")){
        $("body").append("<div class='hint_content'>"+$(this).attr('data-hint')+"</div>");
        var top = $(this).offset().top;
        var left = $(this).offset().left;
        $('.hint_content').css({'top':top-15, 'left':left-190});
        $('.hint_content').fadeIn();
      }
    }
  ).on("mouseleave", ".content_fields li",
    function(){
      if($(this).hasClass("isset_hint")){
        $('.hint_content').remove()

      }
    }
  );

  $(document).on('click', '#update .block_base_fields li', function(e){
    if(e.ctrlKey) {
      $(this).addClass('checked');
    }
    else{
      $('#update .block_base_fields li').removeClass('checked');
      $(this).addClass('checked');
    }
  });



  $(document).on('click', '#update .selected_fields li', function(e){
    if(e.ctrlKey) {
      $(this).addClass('checked');
    }
    else{
      $('#update .selected_fields li').removeClass('checked');
      $(this).addClass('checked');
    }
  });

  $("body").on("mouseenter", "#filter_fields .content_fields li", function(){
      if($(this).hasClass("isset_hint")){
        $("body").append("<div class='hint_content'>"+$(this).attr('data-hint')+"</div>");
        var top = $(this).offset().top;
        var left = $(this).offset().left;
        $('.hint_content').css({'top':top-15, 'left':left-190});
        $('.hint_content').fadeIn();
      }
    }
  ).on("mouseleave", ".content_fields li",
    function(){
      if($(this).hasClass("isset_hint")){
        $('.hint_content').remove()
      }
    }
  );

  $('.selected_fields').sortable({
    revert:false,
    axis: "y"
  });

  $(document).on('click', '#filter_fields .block_base_fields li', function(e){
    if(e.ctrlKey) {
      $(this).addClass('checked');
    }
    else{
      $('#filter_fields .block_base_fields li').removeClass('checked');
      $(this).addClass('checked');
    }
  });

  $(document).on('click', '#filter_fields .selected_fields li', function(e){
    if(!$(this).hasClass('disable_fields')) {
      if(e.ctrlKey) {
        $(this).addClass('checked');
      }
      else {
        $('#filter_fields .selected_fields li').removeClass('checked');
        $(this).addClass('checked');
      }
    }
  });

  $(document).on('change', '.select_products', function(){
    var id_shop = $("input[name=id_shop]").val();
    var shopGroupId = $("input[name=shopGroupId]").val();
    $.ajax({
      url: '../modules/updateproducts/send.php',
      type: 'post',
      data: 'ajax=true&add_product=true&id_product='+$(this).val() + '&id_shop=' + id_shop + '&shopGroupId=' + shopGroupId,
      dataType: 'json'
    });
  });

  $(document).on('click', '.updateproducts .nav-tabs li a', function(e){
    var tab = $(this).attr('href');
    tabActive(tab);
  });

  $(document).on('change', '.select_manufacturers', function(){
    var id_shop = $("input[name=id_shop]").val();
    var shopGroupId = $("input[name=shopGroupId]").val();
    $.ajax({
      url: '../modules/updateproducts/send.php',
      type: 'post',
      data: 'ajax=true&add_manufacturer=true&id_manufacturer='+$(this).val() + '&id_shop=' + id_shop + '&shopGroupId=' + shopGroupId,
      dataType: 'json'
    });
  });

  $(document).on('change', '.select_suppliers', function(){
    var id_shop = $("input[name=id_shop]").val();
    var shopGroupId = $("input[name=shopGroupId]").val();
    $.ajax({
      url: '../modules/updateproducts/send.php',
      type: 'post',
      data: 'ajax=true&add_supplier=true&id_supplier='+$(this).val() + '&id_shop=' + id_shop + '&shopGroupId=' + shopGroupId,
      dataType: 'json'
    });
  });

  $(document).on('change', '.selection_all', function(){
    $('#filter_fields .export_fields input').prop('checked', this.checked);
    $('#filter_fields .export_fields input[value=id_product]').prop('checked', true);
    $('#filter_fields .export_fields input[value=id_product_attribute]').prop('checked', true);
  });

  $(document).on('change', '.selection_all_update', function(){
    $('#update .export_fields input').prop('checked', this.checked);
  });

  $(document).on('keyup', '#update #search_field', function(){
    var self = $(this);
    $('#update .export_fields label').each(function(){
      $(this).parent().css('opacity', '0.5');
      if( $(this).text().indexOf(self.val()) >= 0 ){
        $(this).parent().css('opacity', '1');
      }
    });
  });



  $(document).on('click', '.product_list #show_checked', function(e){
    e.preventDefault();
    $(".product_list .col-lg-6 .search_checkbox_table").val("");
    var id_lang = $("select[name=id_lang]").val();
    var id_shop = $("input[name=id_shop]").val();
    var shopGroupId = $("input[name=shopGroupId]").val();
    $.ajax({
      url: '../modules/updateproducts/send.php',
      type: 'post',
      data: 'ajax=true&show_checked_products=true' +'&id_shop='+id_shop+'&id_lang='+id_lang + '&shopGroupId=' + shopGroupId,
      dataType: 'json',
      success: function(json) {
        $('.alert, .alert-danger, .alert-success').remove();
        $(".product_list .col-lg-6 tbody").replaceWith(json['products']);
      }
    });
  });

  $(document).on('click', '.manufacturer_list #show_checked', function(e){
    e.preventDefault();
    var id_shop = $("input[name=id_shop]").val();
    var shopGroupId = $("input[name=shopGroupId]").val();
    $(".manufacturer_list .col-lg-6 .search_checkbox_table").val("");
    $.ajax({
      url: '../modules/updateproducts/send.php',
      type: 'post',
      data: 'ajax=true&show_checked_manufacturers=true&id_shop=' + id_shop + '&shopGroupId=' + shopGroupId,
      dataType: 'json', 
      success: function(json) {
        $('.alert, .alert-danger, .alert-success').remove();
        $(".manufacturer_list .col-lg-6 tbody").replaceWith(json['manufacturers']);
      }
    });
  });

  $(document).on('click', '.supplier_list #show_checked', function(e){
    e.preventDefault();
    var id_shop = $("input[name=id_shop]").val();
    var shopGroupId = $("input[name=shopGroupId]").val();
    $(".supplier_list .col-lg-6 .search_checkbox_table").val("");
    $.ajax({
      url: '../modules/updateproducts/send.php',
      type: 'post',
      data: 'ajax=true&show_checked_suppliers=true&id_shop=' + id_shop + '&shopGroupId=' + shopGroupId,
      dataType: 'json',
      success: function(json) {
        $('.alert, .alert-danger, .alert-success').remove();
        $(".supplier_list .col-lg-6 tbody").replaceWith(json['suppliers']);
      }
    });
  });

  $(document).on('click', '.product_list #show_all', function(e){
    e.preventDefault();
    var id_shop = $("input[name=id_shop]").val();
    var shopGroupId = $("input[name=shopGroupId]").val();
    $(".product_list .col-lg-6 .search_checkbox_table").val("");
    var id_lang = $("select[name=id_lang]").val();
    var id_shop = $("input[name=id_shop]").val();
    $.ajax({
      url: '../modules/updateproducts/send.php',
      type: 'post',
      data: 'ajax=true&show_all_products=true' + '&id_shop='+id_shop+'&id_lang='+id_lang + '&shopGroupId=' + shopGroupId,
      dataType: 'json',
      success: function(json) {
        $('.alert, .alert-danger, .alert-success').remove();
        $(".product_list .col-lg-6 tbody").replaceWith(json['products']);
      }
    });
  });

  $(document).on('click', '.manufacturer_list #show_all', function(e){
    e.preventDefault();
    var id_shop = $("input[name=id_shop]").val();
    var shopGroupId = $("input[name=shopGroupId]").val();
    $(".manufacturer_list .col-lg-6 .search_checkbox_table").val("");
    $.ajax({
      url: '../modules/updateproducts/send.php',
      type: 'post',
      data: 'ajax=true&show_all_manufacturers=true&id_shop=' + id_shop + '&shopGroupId=' + shopGroupId,
      dataType: 'json',
      success: function(json) {
        $('.alert, .alert-danger, .alert-success').remove();
        $(".manufacturer_list .col-lg-6 tbody").replaceWith(json['manufacturers']);
      }
    });
  });

  $(document).on('click', '.supplier_list #show_all', function(e){
    e.preventDefault();
    var id_shop = $("input[name=id_shop]").val();
    var shopGroupId = $("input[name=shopGroupId]").val();
    $(".supplier_list .col-lg-6 .search_checkbox_table").val("");
    $.ajax({
      url: '../modules/updateproducts/send.php',
      type: 'post',
      data: 'ajax=true&show_all_suppliers=true&id_shop=' + id_shop + '&shopGroupId=' + shopGroupId,
      dataType: 'json',
      success: function(json) {
        $('.alert, .alert-danger, .alert-success').remove();
        $(".supplier_list .col-lg-6 tbody").replaceWith(json['suppliers']);
      }
    });
  });

  $(document).on('keyup', '.product_list .search_checkbox_table', function(e){
    var id_lang = $("select[name=id_lang]").val();
    var id_shop = $("input[name=id_shop]").val();
    var shopGroupId = $("input[name=shopGroupId]").val();
    var self = $(this);
    $.ajax({
      url: '../modules/updateproducts/send.php',
      type: 'post',
      data: 'ajax=true&search_product=' + $(this).val() +'&id_shop='+id_shop+'&id_lang='+id_lang + '&shopGroupId=' + shopGroupId,
      dataType: 'json',
      success: function(json) {
        $('.alert, .alert-danger, .alert-success').remove();
        if (json['products']) {
          self.parents('table').find('tbody').replaceWith(json['products']);
        }
      }
    });
  })

  $(document).on('keyup', '.manufacturer_list .search_checkbox_table', function(e){
    var self = $(this);
    var id_shop = $("input[name=id_shop]").val();
    var shopGroupId = $("input[name=shopGroupId]").val();
    $.ajax({
      url: '../modules/updateproducts/send.php',
      type: 'post',
      data: 'ajax=true&search_manufacturer=' + $(this).val() + '&id_shop=' + id_shop + '&shopGroupId=' + shopGroupId,
      dataType: 'json',
      success: function(json) {
        $('.alert, .alert-danger, .alert-success').remove();
        if (json['manufacturers']) {
          self.parents('table').find('tbody').replaceWith(json['manufacturers']);
        }
      }
    });
  })

  $(document).on('keyup', '.supplier_list .search_checkbox_table', function(e){
    var self = $(this);
    var id_shop = $("input[name=id_shop]").val();
    var shopGroupId = $("input[name=shopGroupId]").val();
    $.ajax({
      url: '../modules/updateproducts/send.php',
      type: 'post',
      data: 'ajax=true&search_supplier=' + $(this).val() + '&id_shop=' + id_shop + '&shopGroupId=' + shopGroupId,
      dataType: 'json',
      success: function(json) {
        $('.alert, .alert-danger, .alert-success').remove();
        if (json['suppliers']) {
          self.parents('table').find('tbody').replaceWith(json['suppliers']);
        }
      }
    });
  })

  $(document).on('click', '.delete_setting', function(e){
    var id = $(this).attr('id-setting');
    var id_shop = $("input[name=id_shop]").val();
    var shopGroupId = $("input[name=shopGroupId]").val();
    $.ajax({
      url: '../modules/updateproducts/send.php',
      type: 'post',
      data: 'ajax=true&removeSetting=true&id=' + id + '&id_shop=' + id_shop + '&shopGroupId=' + shopGroupId,
      dataType: 'json',
      success: function(json) {
        $('.alert, .alert-danger, .alert-success').remove();
        if (json['success']) {
          location.href = $("input[name=base_url]").val();
        }
      }
    });
  });

  $(document).on('click', '.delete_setting_update', function(e){
    var id = $(this).attr('id-setting-update');
    var id_shop = $("input[name=id_shop]").val();
    var shopGroupId = $("input[name=shopGroupId]").val();
    $.ajax({
      url: '../modules/updateproducts/send.php',
      type: 'post',
      data: 'ajax=true&removeSettingUpdate=true&id=' + id + '&id_shop=' + id_shop + '&shopGroupId=' + shopGroupId,
      dataType: 'json',
      success: function(json) {
        $('.alert, .alert-danger, .alert-success').remove();
        if (json['success']) {
          location.href = $("input[name=base_url]").val();
        }
      }
    });
  });

  $(document).on('click', 'button.export', function(e){
    exportProducts(0);
  });

  $(document).on('click', '.saveSettingsExport', function(e){
    var data = '';
    if($('input[name=format_file]:checked').val() !== 'xlsx'){
      $.each($('#filter_fields .selected_fields li'), function(i){
        if($(this).attr('data-value') !== 'image_cover'){
          data += '&field['+$(this).attr('data-value')+']='+ $(this).attr('data-name');
        }
      });
    }
    else{
      $.each($('#filter_fields .selected_fields li'), function(i){
        data += '&field['+$(this).attr('data-value')+']='+ $(this).attr('data-name');
      });
    }

    $.ajax({
      url: '../modules/updateproducts/send.php',
      type: 'post',
      data: 'ajax=true&saveSettings=true&' + $('form.updateproducts').serialize()+data,
      dataType: 'json',
      beforeSend: function(){
        if( $('.progres_bar_ex').length < 1 ){
          $("body").append('<div class="progres_bar_ex"><div class="loading_block"><div class="loading"></div><div class="exporting_notification"></div></div></div>');
        }
      },
      complete: function(){
        $(".progres_bar_ex").hide();
      },
      success: function(json) {
        $('.alert, .alert-danger, .alert-success').remove();
        if( !json ){
          $('.alert-danger, .alert-success').remove();
          $(".progres_bar_ex").remove();
          $(document).scrollTop(0);
          $('#bootstrap_products').before('<div class="alert alert-danger">Some error occurred please contact us!</div>');
        }
        if (json['error']) {
          showErrorMessage(json['error']);
        }
        if( json['error_list'] ){

          $(".progres_bar_ex").remove();
          $('.alert-danger, .alert-success').remove();
          $(document).scrollTop(0);

          var error_list = json['error_list'];
          var msg = '';
          $.each( error_list, function( key, value ) {
            if(key == 0){
              activeErrorTab(value.tab, value.field);
            }
            msg = msg+'<li><a class="error_tab" data-tab="'+value.tab+'" data-field="'+value.field+'">'+value.msg+'</a></li>';
          });
          $('#bootstrap_products').before('<div class="alert alert-danger"><ul class="alert-danger-export">' + msg + '</ul></div>');
        }
        if(json['id']){
          location.href = $("input[name=base_url]").val() + '&settings='+json['id']
        }

      },
      error: function(){
        $('.alert-danger, .alert-success').remove();
        $(".progres_bar_ex").remove();
        $(document).scrollTop(0);
        $('#bootstrap_products').before('<div class="alert alert-danger">Some error occurred please contact us!</div>');
      }
    });
  });

  $(document).on('click', '.saveSettingsUpdate', function(e){
    var data = '';
    $.each($('#update .selected_fields li'), function(i){
      data += '&field_update['+$(this).attr('data-value')+']='+ $(this).attr('data-name');
    });

    $.ajax({
      url: '../modules/updateproducts/send.php',
      type: 'post',
      data: 'ajax=true&saveSettingsUpdate=true&' + $('form.updateproducts').serialize()+data,
      dataType: 'json',
      beforeSend: function(){
        if( $('.progres_bar_ex').length < 1 ){
          $("body").append('<div class="progres_bar_ex"><div class="loading_block"><div class="loading"></div><div class="exporting_notification"></div></div></div>');
        }
      },
      complete: function(){
        $(".progres_bar_ex").hide();
      },
      success: function(json) {
        $('.alert, .alert-danger, .alert-success').remove();
        if( !json ){
          $('.alert-danger, .alert-success').remove();
          $(".progres_bar_ex").remove();
          $(document).scrollTop(0);
          $('#bootstrap_products').before('<div class="alert alert-danger">Some error occurred please contact us!</div>');
        }

        if( json['error_list'] ){

          $(".progres_bar_ex").remove();
          $('.alert-danger, .alert-success').remove();
          $(document).scrollTop(0);

          var error_list = json['error_list'];
          var msg = '';
          $.each( error_list, function( key, value ) {
            if(key == 0){
              activeErrorTab(value.tab, value.field);
            }
            msg = msg+'<li><a class="error_tab" data-tab="'+value.tab+'" data-field="'+value.field+'">'+value.msg+'</a></li>';
          });
          $('#bootstrap_products').before('<div class="alert alert-danger"><ul class="alert-danger-export">' + msg + '</ul></div>');
        }

        if(json['id']){
          location.href = $("input[name=base_url]").val() + '&settingsUpdate='+json['id'];
        }

      },
      error: function(){
        $('.alert-danger, .alert-success').remove();
        $(".progres_bar_ex").remove();
        $(document).scrollTop(0);
        $('#bootstrap_products').before('<div class="alert alert-danger">Some error occurred please contact us!</div>');
      }
    });
  });

  $(document).on('click', 'button.update', function(e){
    updateProducts(0);



  })
});

function updateProducts(pageLimit) {

  if( pageLimit == 0 ){
    refreshIntervalId = setInterval(function(){ returnUpdatedProducts($("input[name=id_shop]").val()); }, 1000);
  }

  var xlsxData = new FormData();
  xlsxData.append('file', $('input[name=file]')[0].files[0]);
  xlsxData.append('id_shop', $("input[name=id_shop]").val());
  xlsxData.append('separate_update', $("input[name=separate_update]:checked").val());
  xlsxData.append('disable_hooks', $("input[name=disable_hooks]:checked").val());
  xlsxData.append('remove_images', $("input[name=remove_images]:checked").val());
  xlsxData.append('id_lang_update', $("select[name=id_lang_update]").val());
  xlsxData.append('id_lang', $("input[name=current_lang_id]").val());
  xlsxData.append('format_file', $("input[name=format_file_update]:checked").val());
  xlsxData.append('update', true);
  xlsxData.append('ajax', true);
  xlsxData.append('page_limit', pageLimit);

  $.each( $('#update .selected_fields li'), function() {
    xlsxData.append('field_update['+$(this).attr('data-value')+']',$(this).attr('data-name'));
  });

  $.ajax({
    url: '../modules/updateproducts/send.php',
    type: 'post',
    data: xlsxData,
    dataType: 'json',
    processData: false,
    contentType: false,
    beforeSend: function(){
      if( $('.progres_bar_ex').length < 1 ){
        $("body").append('<div class="progres_bar_ex"><div class="loading_block"><div class="loading"></div><div class="exporting_notification"></div></div></div>');
      }
    },
    success: function(json) {
      $('.alert, .alert-danger, .alert-success').remove();
      if( !json ){
        $('.alert-danger, .alert-success').remove();
        $(".progres_bar_ex").remove();
        $(document).scrollTop(0);
        $('#bootstrap_products').before('<div class="alert alert-danger">Some error occurred please check <a href="../modules/updateproducts/error.log" target="_blank">error.log</a> file or contact us!</div>');
      }
      if (json['error']) {
        clearInterval(refreshIntervalId);
        $(".progres_bar_ex").remove();
        $('.alert-danger, .alert-success').remove();
        $(document).scrollTop(0);
        $('#bootstrap_products').before('<div class="alert alert-danger">' + json['error'] + '</div>');
      }
      else {
        if( json.success ){
          clearInterval(refreshIntervalId);
          $(".progres_bar_ex").remove();
          $('.alert-danger, .alert-success').remove();
          $(document).scrollTop(0);
          
          var success = json['success'];
          var error_logs = success.error_logs;
          var url = error_logs;
          
          if(error_logs){
            url = '<a class="error_logs_import" href="'+url+'">error_logs.csv<a>';
          }
          else{
            url = '';
          }

          $('#bootstrap_products').before('<div class="alert alert-success">' + success.message + url + '</div>');
        }
        if( json['page_limit'] ){
          updateProducts(json['page_limit']);
        }
      }
    },
    error: function(){
      clearInterval(refreshIntervalId);
      $('.alert-danger, .alert-success').remove();
      $(".progres_bar_ex").remove();
      $(document).scrollTop(0);
      $('#bootstrap_products').before('<div class="alert alert-danger">Some error occurred please check <a href="../modules/updateproducts/error.log" target="_blank">error.log</a> file or contact us!</div>');
    }
  });
}

refreshIntervalId = false;
function exportProducts( pageLimit ) {
  if( pageLimit == 0 ){
    refreshIntervalId = setInterval(function(){ returnExportedProducts($("input[name=id_shop]").val()); }, 1000);
  }

  var data = '';
  if($('input[name=format_file]:checked').val() !== 'xlsx'){
    $.each($('#filter_fields .selected_fields li'), function(i){
      if($(this).attr('data-value') !== 'image_cover'){
        data += '&field['+$(this).attr('data-value')+']='+ $(this).attr('data-name');
      }
    });
  }
  else{
    $.each($('#filter_fields .selected_fields li'), function(i){
      data += '&field['+$(this).attr('data-value')+']='+ $(this).attr('data-name');
    });
  }

  data += '&page_limit='+pageLimit;

  $.ajax({
    url: '../modules/updateproducts/send.php',
    type: 'post',
    data: 'ajax=true&export=true&' + $('form.updateproducts').serialize()+data,
    dataType: 'json',
    beforeSend: function(){
      if( $('.progres_bar_ex').length < 1 ){
        $("body").append('<div class="progres_bar_ex"><div class="loading_block"><div class="loading"></div><div class="exporting_notification"></div></div></div>');
      }
    },
    success: function(json) {
      if( !json ){
        clearInterval(refreshIntervalId);
        $('.alert-danger, .alert-success').remove();
        $(".progres_bar_ex").remove();
        $(document).scrollTop(0);
        $('#bootstrap_products').before('<div class="alert alert-danger">Some error occurred please check <a href="../modules/updateproducts/error.log" target="_blank">error.log</a> file or contact us!</div>');
      }
      if (json['error']) {
        clearInterval(refreshIntervalId);
        $(".progres_bar_ex").remove();
        $('.alert-danger, .alert-success').remove();
        $(document).scrollTop(0);
        $('#bootstrap_products').before('<div class="alert alert-danger">' + json['error'] + '</div>');
      }
      else {
        if( json.file ){
          clearInterval(refreshIntervalId);
          $(".progres_bar_ex").remove();
          $('.alert-danger, .alert-success').remove();
          location.href = json.file;
        }

        if( json['error_list'] ){

          $(".progres_bar_ex").remove();
          $('.alert-danger, .alert-success').remove();
          clearInterval(refreshIntervalId);
          $(document).scrollTop(0);

          var error_list = json['error_list'];
          var msg = '';
          $.each( error_list, function( key, value ) {
            if(key == 0){
              activeErrorTab(value.tab, value.field);
            }
            msg = msg+'<li><a class="error_tab" data-tab="'+value.tab+'" data-field="'+value.field+'">'+value.msg+'</a></li>';
          });
          $('#bootstrap_products').before('<div class="alert alert-danger"><ul class="alert-danger-export">' + msg + '</ul></div>');
        }


        if( json['page_limit'] ){
          exportProducts(json['page_limit']);
        }
      }
    },
    error: function(){
      clearInterval(refreshIntervalId);
      $('.alert-danger, .alert-success').remove();
      $(".progres_bar_ex").remove();
      $(document).scrollTop(0);
      $('#bootstrap_products').before('<div class="alert alert-danger">Some error occurred please check <a href="../modules/updateproducts/error.log" target="_blank">error.log</a> file or contact us!</div>');
    }
  });
}

function returnExportedProducts(id_shop){
  $.ajax({
    url: '../modules/updateproducts/send.php',
    type: 'post',
    data: 'ajax=true&returnExportCount=true&id_shop='+id_shop,
    dataType: 'json',
    success: function(json) {
      if (json['export_notification']) {
        $('.exporting_notification').html(json['export_notification'])
      }
    }
  });
}

function returnUpdatedProducts(id_shop){
  $.ajax({
    url: '../modules/updateproducts/send.php',
    type: 'post',
    data: 'ajax=true&returnUpdateCount=true&id_shop='+id_shop,
    dataType: 'json',
    success: function(json) {
      if (json['update_notification']) {
        $('.exporting_notification').html(json['update_notification'])
      }
    }
  });
}

function tabActive(tab){
  if(tab == '#update' || tab == '#update_settings'){
    $('#fieldset_2_2').show()
    $('#fieldset_1_1').hide()
  }
  else{
    $('#fieldset_2_2').hide()
    $('#fieldset_1_1').show()
  }
}

function showSuccessMessage(msg) {
  $.growl.notice({ title: "", message:msg});
}

function showErrorMessage(msg) {
  $.growl.error({ title: "", message:msg});
}
function replaceUrlFile(){
  var url = $('.href_export_file').attr('data-file-url');
  var name_file = $('input[name=name_file]').val();
  var type = $('input[name=format_file]:checked').val()
  var file_url = url+name_file+'.'+type;
  if(name_file){
    $('.href_export_file').attr('href', file_url);
    $('.href_export_file').html(file_url);
    $('.available_url').show();
  }
  else{
    $('.href_export_file').attr('href', '');
    $('.href_export_file').html('');
    $('.available_url').hide();
  }

}

function activeErrorTab( tab, field ){
  if(tab){
    $('.updateproducts #export').removeClass('active');
    $('.updateproducts #filter_products').removeClass('active');
    $('.updateproducts #filter_fields').removeClass('active');
    $('.updateproducts #update').removeClass('active');
    $('.updateproducts #new_settings').removeClass('active');
    $('.updateproducts #update_settings').removeClass('active');
    $('.updateproducts #'+tab).addClass('active');
    $('.updateproducts .nav-tabs li').removeClass('active');
    $('.updateproducts .nav-tabs li a[href=#'+tab+']').parent().addClass('active');
  }
  if(field){
    if( field == 'selection_type_price' ) {
      $('#'+tab+' input[name=price_value]').focus();
      $('#'+tab+' input[name=price_value]').blur();
      $('#'+tab+' .block_selection_type.price .label_selection_type ').css('border-color', 'red');
    }
    else if( field == 'selection_type_quantity' ) {
      $('#'+tab+' input[name=quantity_value]').focus();
      $('#'+tab+' input[name=quantity_value]').blur();
      $('#'+tab+' .block_selection_type.quantity .label_selection_type ').css('border-color', 'red');
    }
    else{
      $('input[name='+field+']').focus();
    }

  }

}
