<?php if(!empty($product_accessories)) { ?>
<div class="ci_product_accessories">
  <?php if($module_ciaccessory_setting_display_title) { ?>
  <h3 class="heading-title access-title title module-title"><?php echo $text_product_accessories; ?></h3>
  <?php } ?>

  <?php echo $top_description; ?>

  <?php if($module_ciaccessory_setting_display_layout == 'list') { ?>
  <div class="table-responsive cart-info">
  <table class="table table-bordered table-hover table-accessories">
    <thead class="thead-default">
      <tr>
        <?php if($module_ciaccessory_setting_display_image) { ?>
        <th class="<?php echo $module_ciaccessory_setting_text_alignment; ?>"><?php echo $column_image; ?></th>
        <?php } ?>

        <?php if($module_ciaccessory_setting_display_name) { ?>
        <th class="<?php echo $module_ciaccessory_setting_text_alignment; ?>"><?php echo $column_product; ?></th>
        <?php } ?>

        <?php if($module_ciaccessory_setting_display_price) { ?>
        <th class="<?php echo $module_ciaccessory_setting_text_alignment; ?>"><?php echo $column_price; ?></th>
        <?php } ?>

        <?php if($module_ciaccessory_setting_display_qty) { ?>
        <th class="<?php echo $module_ciaccessory_setting_text_alignment; ?>"><?php echo $column_quantity; ?></th>
        <?php } ?>

        <?php if($module_ciaccessory_setting_display_button) { ?>
        <th class="<?php echo $module_ciaccessory_setting_text_alignment; ?>"><?php echo $column_action; ?></th>
        <?php } ?>
      </tr>
    </thead>
    <tbody>
    <?php foreach($product_accessories as $product_accessory) { ?>
      <tr id="product-accessory<?php echo $product_accessory['product_id']; ?>">
        <?php if($module_ciaccessory_setting_display_image) { ?>
        <?php $image_type_class = ($module_ciaccessory_setting_image_type == 'circle') ? 'img-circle' : ''; ?>
        <td class="<?php echo $module_ciaccessory_setting_text_alignment; ?>"><a href="<?php echo $product_accessory['href']; ?>" title="<?php echo $product_accessory['name']; ?>"><img src="<?php echo $product_accessory['thumb']; ?>" title="<?php echo $product_accessory['name']; ?>" alt="<?php echo $product_accessory['name']; ?>" class="<?php echo $image_type_class; ?>" /></a></td>
        <?php } ?>

        <?php if($module_ciaccessory_setting_display_name) { ?>
        <td class="<?php echo $module_ciaccessory_setting_text_alignment; ?>"><a href="<?php echo $product_accessory['href']; ?>" title="<?php echo $product_accessory['name']; ?>"><?php echo $product_accessory['name'] ?></a></td>
        <?php } ?>

        <?php if($module_ciaccessory_setting_display_price) { ?>
        <td class="<?php echo $module_ciaccessory_setting_text_alignment; ?>"><?php if ($product_accessory['price']) { ?>
        <?php if (!$product_accessory['special']) { ?>
          <div class="price"><?php echo $product_accessory['price']; ?></div>
        <?php } else { ?>
          <div class="special-price">
            <span class="old" style="text-decoration: line-through;"><?php echo $product_accessory['price']; ?></span>
            <span class="special"><?php echo $product_accessory['special']; ?></span>
          </div>
        <?php } ?>
        <?php } ?></td>
        <?php } ?>

        <?php if($module_ciaccessory_setting_display_qty) { ?>
        <td class="<?php echo $module_ciaccessory_setting_text_alignment; ?>">
          <div class="input-group ciacessory-spinner">
            <span class="input-group-btn">
              <button class="btn btn-default" data-dir="down"><i class="fa fa-minus"></i></button>
            </span>
            <input type="text" name="accessory_quantity" value="<?php echo $product_accessory['minimum']; ?>" size="2" class="form-control" />
            <span class="input-group-btn">
              <button class="btn btn-default" data-dir="up"><i class="fa fa-plus"></i></button>
            </span>
          </div>
        </td>
        <?php } ?>

        <?php if($module_ciaccessory_setting_display_button) { ?>
        <td class="<?php echo $module_ciaccessory_setting_text_alignment; ?>"><button type="button" data-loading-text="<?php echo $text_loading; ?>" class="button btn btn-primary ciaccess-button" id="ciaccess-button<?php echo $product_accessory['product_id']; ?>" data-product-id="<?php echo $product_accessory['product_id']; ?>" data-options="<?php echo $product_accessory['total_options']; ?>"> <i class="fa fa-shopping-cart"></i> <?php echo $button_cart; ?></button></td>
        <?php } ?>
      </tr>
      <?php } ?>
    </tbody>
  </table>
  </div>
  <?php } ?>

  <?php if($module_ciaccessory_setting_display_layout == 'grid') { ?>
  <ul class="list-inline product-accessories">
    <?php foreach($product_accessories as $product_accessory) { ?>
    <li id="product-accessory<?php echo $product_accessory['product_id']; ?>">
      <div class="inner-access clearfix <?php echo $module_ciaccessory_setting_text_alignment; ?>">
        <?php if($module_ciaccessory_setting_display_image) { ?>
        <?php $image_type_class = ($module_ciaccessory_setting_image_type == 'circle') ? 'img-circle' : ''; ?>
        <div class="image">
          <a href="<?php echo $product_accessory['href']; ?>" title="<?php echo $product_accessory['name']; ?>"><img src="<?php echo $product_accessory['thumb']; ?>" title="<?php echo $product_accessory['name']; ?>" alt="<?php echo $product_accessory['name']; ?>" class="<?php echo $image_type_class; ?>" /></a>
        </div>
        <?php } ?>

        <?php if($module_ciaccessory_setting_display_name) { ?>
        <div class="name"><a href="<?php echo $product_accessory['href']; ?>" title="<?php echo $product_accessory['name']; ?>"><?php echo $product_accessory['name'] ?></a></div>
        <?php } ?>

        <?php if($module_ciaccessory_setting_display_model) { ?>
        <div class="model"><?php echo $product_accessory['model'] ?></div>
        <?php } ?>


        <?php if ($module_ciaccessory_setting_display_price && $product_accessory['price']) { ?>
        <?php if (!$product_accessory['special']) { ?>
          <div class="price"><?php echo $product_accessory['price']; ?></div>
        <?php } else { ?>
          <div class="special-price">
            <span class="old" style="text-decoration: line-through;"><?php echo $product_accessory['price']; ?></span>
            <span class="special"><?php echo $product_accessory['special']; ?></span>
          </div>
        <?php } ?>
        <?php } ?>

        <?php if($module_ciaccessory_setting_display_qty || $module_ciaccessory_setting_display_button) { ?>
          <?php if($module_ciaccessory_setting_display_qty) { ?>
          <div class="input-group ciacessory-spinner">
            <span class="input-group-btn">
              <button class="btn btn-default" data-dir="down"><i class="fa fa-minus"></i></button>
            </span>
            <input type="text" name="accessory_quantity" value="<?php echo $product_accessory['minimum']; ?>" size="2" class="form-control" />
            <span class="input-group-btn">
              <button class="btn btn-default" data-dir="up"><i class="fa fa-plus"></i></button>
            </span>
          </div>
          <?php } ?>
          <?php if($module_ciaccessory_setting_display_button) { ?>
          <span class="input-group text-right cart-btn">
            <button type="button" data-loading-text="<?php echo $text_loading; ?>" class=" button btn btn-primary ciaccess-button" id="ciaccess-button<?php echo $product_accessory['product_id']; ?>" data-product-id="<?php echo $product_accessory['product_id']; ?>" data-options="<?php echo $product_accessory['total_options']; ?>"><i class="fa fa-shopping-cart"></i> <span class="hidden-lg hidden-md"><?php echo $button_cart; ?></span></button>
          </span>
          <?php } ?>
        <?php } ?>
      </div>
    </li>
    <?php } ?>
  </ul>
  <?php } ?>

  <br/>
  <?php echo $bottom_description; ?>

  <div id="cioptionModal" class="modal cioptionModal">
  </div>
  <script type="text/javascript"><!--
  $('.ciaccess-button').on('click', function() {
    var data_options = $(this).attr('data-options');
    var product_id = $(this).attr('data-product-id');
    var quantity = $('#product-accessory'+ product_id +' input[name=\'accessory_quantity\']').val();

    if(data_options == '0' || data_options == '') {
       $.ajax({
        url: 'index.php?route=checkout/cart/add',
        type: 'post',
        data: 'product_id=' + product_id + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
        dataType: 'json',
        beforeSend: function() {
          $('#ciaccess-button'+ product_id).attr('disabled', true);
        },
        complete: function() {
          $('#ciaccess-button'+ product_id).attr('disabled', false);
        },
        success: function(json) {
          $('.alert').remove();

          if (json['success']) {
            if (json['notification']) {
                parent.show_notification(json['notification']);
              } else {
                $('.breadcrumb').after('<div class="alert alert-success">' + json['success'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');
              }

            $('#cart-total').html(json['total']);

            $('html, body').animate({ scrollTop: 0 }, 'slow');

            $('#cart > ul').load('index.php?route=common/cart/info ul li');
          }
        }
      });
    } else{
     $.ajax({
        url: 'index.php?route=extension/ciproduct_accessory/options',
        type: 'post',
        data: 'product_id=' + product_id + '&quantity=' + (typeof(quantity) != 'undefined' ? quantity : 1),
        dataType: 'html',
        beforeSend: function() {
          $('#ciaccess-button'+ product_id).attr('disabled', true);
        },
        complete: function() {
          $('#ciaccess-button'+ product_id).attr('disabled', false);
        },
        success: function(html) {
          $('#cioptionModal').html(html);

          $('#cioptionModal').modal('show');
           /*setTimeout(function() {
            $('#cioptionModal').appendTo("body").modal('show');
           }, 500);  */
        }
      });
    }
  });
  //--></script>
  <script type="text/javascript"><!--
  $(document).on('click', '.ciacessory-spinner button', function () {
    var button = $(this),
      oldValue = button.closest('.ciacessory-spinner').find('input').val().trim(),
      newvalue = 1;

    if (button.attr('data-dir') == 'up') {
      newvalue = parseInt(oldValue) + 1;
    } else if (button.attr('data-dir') == 'down') {
      if (oldValue > 1) {
        newvalue = parseInt(oldValue) - 1;
      } else {
        newvalue = 1;
      }
    }
    button.closest('.ciacessory-spinner').find('input').val(newvalue);
  });
  //--></script>
<script type="text/javascript"><!--
  $(document).delegate('button[id^=\'button-upload-acc\']', 'click', function() {
    var node = this;

    $('#form-upload-acc').remove();

    $('body').prepend('<form enctype="multipart/form-data" id="form-upload-acc" style="display: none;"><input type="file" name="file" /></form>');

    $('#form-upload-acc input[name=\'file\']').trigger('click');

    timer = setInterval(function() {
      if ($('#form-upload-acc input[name=\'file\']').val() != '') {
        clearInterval(timer);

        $.ajax({
          url: 'index.php?route=tool/upload',
          type: 'post',
          dataType: 'json',
          data: new FormData($('#form-upload-acc')[0]),
          cache: false,
          contentType: false,
          processData: false,
          beforeSend: function() {
            $(node).button('loading');
          },
          complete: function() {
            $(node).button('reset');
          },
          success: function(json) {
            $('.text-danger').remove();

            if (json['error']) {
              $(node).parent().find('input').after('<div class="text-danger">' + json['error'] + '</div>');
            }

            if (json['success']) {
              alert(json['success']);

              $(node).parent().find('input').attr('value', json['code']);
            }
          },
          error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
          }
        });
      }
    }, 500);
  });
  //--></script>
  <style type="text/css">
  .product-accessories .inner-access{
    background: <?php echo $module_ciaccessory_setting_backgroundcolor; ?>;
  }
  .product-accessories .name a, .product-accessories .special-price .old, .product-accessories .special, .product-accessories .price{
    color: <?php echo $module_ciaccessory_setting_textcolor; ?>;
  }
  .product-accessories li .inner-access{
   border-color: <?php echo $module_ciaccessory_setting_bordercolor; ?>;
  }

  .ciaccess-button{
   <?php if($module_ciaccessory_setting_button_backgroundcolor){ ?>
   border: none;
   background-image: none;
   background: <?php echo $module_ciaccessory_setting_button_backgroundcolor; ?>;
   <?php } ?>
   color: <?php echo $module_ciaccessory_setting_button_textcolor; ?>;
  }

  .ciaccess-button:hover{
    border: none;
    background-image: none;
    background: <?php echo $module_ciaccessory_setting_button_hover_backgroundcolor; ?>;
    color: <?php echo $module_ciaccessory_setting_button_hover_textcolor; ?>;
  }

  <?php echo $custom_css; ?>
  </style>
</div>
<?php } ?>