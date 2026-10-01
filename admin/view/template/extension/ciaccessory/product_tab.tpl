<div class="tab-pane" id="tab-ciproduct-accessories">
  <div class="form-group">
    <label class="col-sm-2 control-label" for="input-accessory"><span data-toggle="tooltip" title="<?php echo $help_accessory; ?>"><?php echo $entry_accessory; ?></span></label>
    <div class="col-sm-10">
      <input type="text" name="accessory" value="" placeholder="<?php echo $placeholder_accessory; ?>" id="input-accessory" class="form-control" />
    </div>
  </div>
  <div class="panel panel-default">
    <div class="panel-body" id="product-accessory">
      <div class="row">
        <?php $accessory_row = 0; ?>
        <?php if ($ci_product_accessories) { ?>
          <?php foreach ($ci_product_accessories as $product_accessory) { ?>
          <div id="accessory-row<?php echo $product_accessory['accessory_id']; ?>" class="col-sm-4 accessory-row" style="margin-bottom: 30px; cursor: move;">
            <div class="image text-center">
              <?php if (!empty($product_accessory['image'])) { ?>
                <img src="<?php echo $product_accessory['image']; ?>" alt="<?php echo $product_accessory['name']; ?>" class="img-thumbnail img-circle" />
                <?php } else { ?>
                <span class="img-thumbnail list"><i class="fa fa-camera fa-2x"></i></span>
                <?php } ?>
            </div>
            <div class="name">
              <input type="hidden" name="product_accessory[<?php echo $accessory_row; ?>][accessory_id]" value="<?php echo $product_accessory['accessory_id']; ?>" />
              <input type="hidden" name="product_accessory[<?php echo $accessory_row; ?>][sort_order]" class="form-control asc-sort" value="<?php echo $product_accessory['sort_order']; ?>" />
              <br>
              <h4 class="text-center" style="line-height: 27px;"><?php echo $product_accessory['name']; ?></h4>
            </div>
            <div class="status">
              <div class="input-group">
                <select name="product_accessory[<?php echo $accessory_row; ?>][status]" class="form-control">
                  <option value="1" <?php echo $product_accessory['status'] ? 'selected="selected"' : ''; ?>><?php echo $text_enabled; ?></option>
                  <option value="0" <?php echo !$product_accessory['status'] ? 'selected="selected"' : ''; ?>><?php echo $text_disabled; ?></option>
                </select>
                <div class="input-group-btn">
                  <button type="button" onclick="$('#accessory-row<?php echo $product_accessory['accessory_id']; ?>').remove();" title="<?php echo $button_remove; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button>
                </div>
              </div>
            </div>
          </div>
          <?php $accessory_row++; ?>
        <?php } ?>
        <?php } else { ?>
          <div class="col-sm-12 no-result text-center"><h4><?php echo $text_no_results; ?></h4></div>
        <?php } ?>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript"><!--
var accessory_row = '<?php echo $accessory_row; ?>';
$('input[name=\'accessory\']').autocomplete({
  'source': function(request, response) {
    $.ajax({
      url: 'index.php?route=extension/module/ciaccessory_setting/productAutocomplete&<?php echo $module_token; ?>=<?php echo $ci_token; ?>&filter_name=' +  encodeURIComponent(request),
      dataType: 'json',
      success: function(json) {
        response($.map(json, function(item) {
          return {
            label: item['name'],
            value: item['product_id'],
            image: item['image'],
            model: item['model'],
            href: item['href']
          }
        }));
      }
    });
  },
  'select': function(item) {
    $('#tab-product-accessories input[name=\'accessory\']').val('');

    $('#product-accessory #accessory-row' + item['value']).remove();

    $('#product-accessory .no-result').remove();

    var row_lenght = $('#product-accessory .accessory-row').length;

    var html = '';
    html += '<div id="accessory-row'+ item['value'] +'" class="col-sm-4 accessory-row" style="margin-bottom: 30px; cursor: move;">';
      html += '<div class="image text-center">';
          html += '<img src="'+ item['image'] +'" alt="'+ item['label'] +'" class="img-thumbnail img-circle" />';
      html += '</div>';
      html += '<div class="name">';
        html += '<input type="hidden" name="product_accessory['+ accessory_row +'][accessory_id]" value="'+ item['value'] +'" />';
        html += '<input type="hidden" name="product_accessory['+ accessory_row +'][sort_order]" class="form-control asc-sort" value="'+ (row_lenght + 1) +'" />';
        html += '<br>';
        html += '<h4 class="text-center" style="line-height: 27px;">'+ item['label'] +'</h4>';
      html += '</div>';
      html += '<div class="status">';
        html += '<div class="input-group">';
          html += '<select name="product_accessory['+ accessory_row +'][status]" class="form-control">';
            html += '<option value="1"><?php echo $text_enabled; ?></option>';
            html += '<option value="0"><?php echo $text_disabled; ?></option>';
          html += '</select>';
          html += '<div class="input-group-btn">';
            html += '<button type="button" onclick="$(\'#accessory-row' + item['value'] + '\').remove()" title="<?php echo $button_remove; ?>" class="btn btn-danger"><i class="fa fa-minus-circle"></i></button>';
          html += '</div>';
        html += '</div>';
      html += '</div>';
    html += '</div>';

    $('#product-accessory .row').append(html);

    accessory_row++;
  }
});

$(document).ready(function() {
  $("#product-accessory .row").sortable({
    cursor: "move",
    stop: function() {
      $('#product-accessory .row .col-sm-4').each(function() {
        $(this).find('.asc-sort').val($(this).index());
      });
    }
  });
});
//--></script>