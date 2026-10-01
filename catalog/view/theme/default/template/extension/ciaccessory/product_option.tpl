
  <?php if ($options) {
    $col_class = 'col-sm-12';
  } else{
    $col_class = 'col-sm-12';
  }
  ?>
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title"><?php echo $text_product_accessories; ?></h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="<?php echo $col_class; ?>">
            <div class="image sm-100 xl-50">
              <a title="<?php echo $heading_title; ?>"><img src="<?php echo $thumb; ?>" title="<?php echo $heading_title; ?>" alt="<?php echo $heading_title; ?>" /></a>
            </div>
            <div class="sm-100 xl-50 product-right-info">
              <h3><?php echo $heading_title; ?></h3>
              <?php if ($price) { ?>
              <ul class="list-unstyled">
                <?php if (!$special) { ?>
                <li>
                  <h2><?php echo $price; ?></h2>
                </li>
                <?php } else { ?>
                <li><span class="old-price" style="text-decoration: line-through;"><?php echo $price; ?></span></li>
                <li>
                  <h2 class="new-price"><?php echo $special; ?></h2>
                </li>
                <?php } ?>
                <?php if ($tax) { ?>
                <li><?php echo $text_tax; ?> <?php echo $tax; ?></li>
                <?php } ?>
                <?php if ($points) { ?>
                <li><?php echo $text_points; ?> <?php echo $points; ?></li>
                <?php } ?>
              </ul>
              <?php } ?>

            <?php if ($recurrings) { ?>
            <h5><?php echo $text_payment_recurring ?></h5>
            <div class="form-group required">
              <select name="recurring_id" class="form-control">
                <option value=""><?php echo $text_select; ?></option>
                <?php foreach ($recurrings as $recurring) { ?>
                <option value="<?php echo $recurring['recurring_id'] ?>"><?php echo $recurring['name'] ?></option>
                <?php } ?>
              </select>
              <div class="help-block" id="recurring-description"></div>
            </div>
            <?php } ?>

            <?php if ($options) { ?>
              <h3><?php echo $text_option; ?></h3>
              <?php foreach ($options as $option) { ?>
              <?php if ($option['type'] == 'select') { ?>
              <div class="form-group<?php echo ($option['required'] ? ' required' : ''); ?>">
                <label class="control-label" for="input-option-acc<?php echo $option['product_option_id']; ?>"><?php echo $option['name']; ?></label>
                <select name="option[<?php echo $option['product_option_id']; ?>]" id="input-option-acc<?php echo $option['product_option_id']; ?>" class="form-control">
                  <option value=""><?php echo $text_select; ?></option>
                  <?php foreach ($option['product_option_value'] as $option_value) { ?>
                  <option value="<?php echo $option_value['product_option_value_id']; ?>"><?php echo $option_value['name']; ?>
                  <?php if ($option_value['price']) { ?>
                  (<?php echo $option_value['price_prefix']; ?><?php echo $option_value['price']; ?>)
                  <?php } ?>
                  </option>
                  <?php } ?>
                </select>
              </div>
              <?php } ?>
              <?php if ($option['type'] == 'radio') { ?>
              <div class="form-group<?php echo ($option['required'] ? ' required' : ''); ?>">
                <label class="control-label"><?php echo $option['name']; ?></label>
                <div id="input-option-acc<?php echo $option['product_option_id']; ?>">
                  <?php foreach ($option['product_option_value'] as $option_value) { ?>
                  <div class="radio">
                    <label>
                      <input type="radio" name="option[<?php echo $option['product_option_id']; ?>]" value="<?php echo $option_value['product_option_value_id']; ?>" />
                      <?php echo $option_value['name']; ?>
                      <?php if ($option_value['price']) { ?>
                      (<?php echo $option_value['price_prefix']; ?><?php echo $option_value['price']; ?>)
                      <?php } ?>
                    </label>
                  </div>
                  <?php } ?>
                </div>
              </div>
              <?php } ?>
              <?php if ($option['type'] == 'checkbox') { ?>
              <div class="form-group<?php echo ($option['required'] ? ' required' : ''); ?>">
                <label class="control-label"><?php echo $option['name']; ?></label>
                <div id="input-option-acc<?php echo $option['product_option_id']; ?>">
                  <?php foreach ($option['product_option_value'] as $option_value) { ?>
                  <div class="checkbox">
                    <label>
                      <input type="checkbox" name="option[<?php echo $option['product_option_id']; ?>][]" value="<?php echo $option_value['product_option_value_id']; ?>" />
                      <?php echo $option_value['name']; ?>
                      <?php if ($option_value['price']) { ?>
                      (<?php echo $option_value['price_prefix']; ?><?php echo $option_value['price']; ?>)
                      <?php } ?>
                    </label>
                  </div>
                  <?php } ?>
                </div>
              </div>
              <?php } ?>
              <?php if ($option['type'] == 'image') { ?>
              <div class="form-group<?php echo ($option['required'] ? ' required' : ''); ?>">
                <label class="control-label"><?php echo $option['name']; ?></label>
                <div id="input-option-acc<?php echo $option['product_option_id']; ?>">
                  <?php foreach ($option['product_option_value'] as $option_value) { ?>
                  <div class="radio">
                    <label>
                      <input type="radio" name="option[<?php echo $option['product_option_id']; ?>]" value="<?php echo $option_value['product_option_value_id']; ?>" />
                      <img src="<?php echo $option_value['image']; ?>" alt="<?php echo $option_value['name'] . ($option_value['price'] ? ' ' . $option_value['price_prefix'] . $option_value['price'] : ''); ?>" class="img-thumbnail" /> <?php echo $option_value['name']; ?>
                      <?php if ($option_value['price']) { ?>
                      (<?php echo $option_value['price_prefix']; ?><?php echo $option_value['price']; ?>)
                      <?php } ?>
                    </label>
                  </div>
                  <?php } ?>
                </div>
              </div>
              <?php } ?>
              <?php if ($option['type'] == 'text') { ?>
              <div class="form-group<?php echo ($option['required'] ? ' required' : ''); ?>">
                <label class="control-label" for="input-option-acc<?php echo $option['product_option_id']; ?>"><?php echo $option['name']; ?></label>
                <input type="text" name="option[<?php echo $option['product_option_id']; ?>]" value="<?php echo $option['value']; ?>" placeholder="<?php echo $option['name']; ?>" id="input-option-acc<?php echo $option['product_option_id']; ?>" class="form-control" />
              </div>
              <?php } ?>
              <?php if ($option['type'] == 'textarea') { ?>
              <div class="form-group<?php echo ($option['required'] ? ' required' : ''); ?>">
                <label class="control-label" for="input-option-acc<?php echo $option['product_option_id']; ?>"><?php echo $option['name']; ?></label>
                <textarea name="option[<?php echo $option['product_option_id']; ?>]" rows="5" placeholder="<?php echo $option['name']; ?>" id="input-option-acc<?php echo $option['product_option_id']; ?>" class="form-control"><?php echo $option['value']; ?></textarea>
              </div>
              <?php } ?>
              <?php if ($option['type'] == 'file') { ?>
              <div class="form-group<?php echo ($option['required'] ? ' required' : ''); ?>">
                <label class="control-label"><?php echo $option['name']; ?></label>
                <button type="button" id="button-upload-acc<?php echo $option['product_option_id']; ?>" data-loading-text="<?php echo $text_loading; ?>" class="btn btn-default btn-block"><i class="fa fa-upload"></i> <?php echo $button_upload; ?></button>
                <input type="hidden" name="option[<?php echo $option['product_option_id']; ?>]" value="" id="input-option-acc<?php echo $option['product_option_id']; ?>" />
              </div>
              <?php } ?>
              <?php if ($option['type'] == 'date') { ?>
              <div class="form-group<?php echo ($option['required'] ? ' required' : ''); ?>">
                <label class="control-label" for="input-option-acc<?php echo $option['product_option_id']; ?>"><?php echo $option['name']; ?></label>
                <div class="input-group date">
                  <input type="text" name="option[<?php echo $option['product_option_id']; ?>]" value="<?php echo $option['value']; ?>" data-date-format="YYYY-MM-DD" id="input-option-acc<?php echo $option['product_option_id']; ?>" class="form-control" />
                  <span class="input-group-btn">
                  <button class="btn btn-default" type="button"><i class="fa fa-calendar"></i></button>
                  </span></div>
              </div>
              <?php } ?>
              <?php if ($option['type'] == 'datetime') { ?>
              <div class="form-group<?php echo ($option['required'] ? ' required' : ''); ?>">
                <label class="control-label" for="input-option-acc<?php echo $option['product_option_id']; ?>"><?php echo $option['name']; ?></label>
                <div class="input-group datetime">
                  <input type="text" name="option[<?php echo $option['product_option_id']; ?>]" value="<?php echo $option['value']; ?>" data-date-format="YYYY-MM-DD HH:mm" id="input-option-acc<?php echo $option['product_option_id']; ?>" class="form-control" />
                  <span class="input-group-btn">
                  <button type="button" class="btn btn-default"><i class="fa fa-calendar"></i></button>
                  </span></div>
              </div>
              <?php } ?>
              <?php if ($option['type'] == 'time') { ?>
              <div class="form-group<?php echo ($option['required'] ? ' required' : ''); ?>">
                <label class="control-label" for="input-option-acc<?php echo $option['product_option_id']; ?>"><?php echo $option['name']; ?></label>
                <div class="input-group time">
                  <input type="text" name="option[<?php echo $option['product_option_id']; ?>]" value="<?php echo $option['value']; ?>" data-date-format="HH:mm" id="input-option-acc<?php echo $option['product_option_id']; ?>" class="form-control" />
                  <span class="input-group-btn">
                  <button type="button" class="btn btn-default"><i class="fa fa-calendar"></i></button>
                  </span></div>
              </div>
              <?php } ?>
              <?php } ?>
            <?php } ?>

            <div class="clearfix">
              <div class="input-group ciacessory-spinner-option">
                <span class="input-group-btn">
                  <button class="btn btn-default" data-dir="down"><i class="fa fa-minus"></i></button>
                </span>
                <input type="text" name="quantity" value="<?php echo $post_quantity; ?>" size="2" class="form-control" />
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>" />
                <span class="input-group-btn">
                  <button class="btn btn-default" data-dir="up"><i class="fa fa-plus"></i></button>
                </span>
              </div>
            </div>
            <div class="buttons">
              <button type="button" data-loading-text="<?php echo $text_loading; ?>" class=" button btn btn-primary btn-block ciaccess-button-option" id="ciaccess-button-option<?php echo $product_id; ?>" data-product-id="<?php echo $product_id; ?>" data-options="<?php echo count($options); ?>"><i class="fa fa-shopping-cart"></i>  <?php echo $button_cart; ?></button>
            </div>
          </div>
          </div>
        </div>
      </div>
    </div>
  </div>
<script type="text/javascript"><!--
  $('.date').datetimepicker({
    pickTime: false
  });

   $('.datetime').datetimepicker({
    pickDate: true,
    pickTime: true
  });

  $('.time').datetimepicker({
    pickDate: false
  });

//--></script>
<script type="text/javascript"><!--
$('.ciaccess-button-option').on('click', function() {
  $.ajax({
    url: 'index.php?route=checkout/cart/add',
    type: 'post',
    data: $('#cioptionModal input[type=\'text\'], #cioptionModal input[type=\'hidden\'], #cioptionModal input[type=\'radio\']:checked, #cioptionModal input[type=\'checkbox\']:checked, #cioptionModal select, #cioptionModal textarea'),
    dataType: 'json',
    beforeSend: function() {
      $('.ciaccess-button-option').button('loading');
    },
    complete: function() {
      $('.ciaccess-button-option').button('reset');
    },
    success: function(json) {
      $('#cioptionModal .alert, #cioptionModal .text-danger').remove();
      $('#cioptionModal .form-group').removeClass('has-error');

      if (json['error']) {
        if (json['error']['option']) {
          for (i in json['error']['option']) {
            var element = $('#input-option-acc' + i.replace('_', '-'));

            if (element.parent().hasClass('input-group')) {
              element.parent().after('<div class="text-danger">' + json['error']['option'][i] + '</div>');
            } else {
              element.after('<div class="text-danger">' + json['error']['option'][i] + '</div>');
            }
          }
        }

        if (json['error']['recurring']) {
          $('#cioptionModal select[name=\'recurring_id\']').after('<div class="text-danger">' + json['error']['recurring'] + '</div>');
        }

        // Highlight any found errors
        $('#cioptionModal .text-danger').parent().addClass('has-error');
      }

      if (json['success']) {
        $('#cioptionModal .modal-header').before('<div class="alert alert-success success">' + json['success'] + '<button type="button" class="close" data-dismiss="alert">&times;</button></div>');

        $('#cart-total').html(json['total']);

        $('#cart > ul').load('index.php?route=common/cart/info ul li');

         setTimeout(function() {
          $('#cioptionModal').modal('hide');
         }, 5000);

         setTimeout(function() {
          $('#cioptionModal').html('');
         }, 7000);
      }
    }
  });
});
//--></script>
<script type="text/javascript"><!--
  $('.ciacessory-spinner-option button').on('click', function () {
    var button = $(this),
      oldValue = button.closest('.ciacessory-spinner-option').find('input[name=\'quantity\']').val().trim(),
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
    button.closest('.ciacessory-spinner-option').find('input[name=\'quantity\']').val(newvalue);
  });
  //--></script>