<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-ciaccessory-setting" data-toggle="tooltip" title="" class="btn btn-success"><i class="fa fa-check"></i> <?php echo $button_save; ?></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>

    <?php if ($success) { ?>
    <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>

    <?php if($action_enable_events) { ?>
    <div class="alert alert-warning inspect-warning"><i class="fa fa-exclamation-circle"></i> <?php echo $info_disabled_events; ?> <button type="button" class="btn btn-primary btn-sm button-enable-event" onclick="enableEvents();"><i class="fa fa-cog"></i> <?php echo $button_enable_event; ?></button></div>
    <?php } ?>
    <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-ciaccessory-setting" class="form-horizontal">
      <div class="row">
        <div class="col-sm-6">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-cog"></i> <?php echo $text_edit; ?></h3>
            <div class="pull-right">
              <label><?php echo $entry_store; ?></label>
              <select name="store_id" onchange="window.location = 'index.php?route=extension/module/ciaccessory_setting&<?php echo $module_token; ?>=<?php echo $ci_token; ?>&store_id='+ this.value;">
                <option value="0"><?php echo $text_default; ?></option>
                <?php foreach($stores as $store) { ?>
                <option value="<?php echo $store['store_id']; ?>" <?php echo ($store['store_id'] == $store_id) ? 'selected="selected"' : ''; ?>><?php echo $store['name']; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>
          <div class="panel-body">
            <div class="form-group">
              <label class="col-sm-12 control-label" for="input-status"><?php echo $entry_status; ?></label>
              <div class="col-sm-12">
                <div class="btn-group" data-toggle="buttons">
                  <label class="btn btn-default <?php echo $module_ciaccessory_setting_status ? 'active' : ''; ?>">
                    <input name="module_ciaccessory_setting_status" <?php echo $module_ciaccessory_setting_status ? 'checked="checked"' : ''; ?> autocomplete="off" value="1" type="radio"><?php echo $text_enabled; ?>
                  </label>
                  <label class="btn btn-default <?php echo !$module_ciaccessory_setting_status ? 'active' : ''; ?>">
                    <input name="module_ciaccessory_setting_status" <?php echo !$module_ciaccessory_setting_status ? 'checked="checked"' : ''; ?> autocomplete="off" value="0" type="radio"> <?php echo $text_disabled; ?>
                  </label>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-12 control-label"><?php echo $entry_display_widget; ?></label>
              <div class="col-sm-12">
                <div class="btn-group" data-toggle="buttons">
                  <label class="btn btn-default setting_widget <?php echo $module_ciaccessory_setting_widget == 'tab_inside' ? 'active' : ''; ?>">
                    <input name="module_ciaccessory_setting_widget" <?php echo $module_ciaccessory_setting_widget == 'tab_inside' ? 'checked="checked"' : ''; ?> autocomplete="off" value="tab_inside" type="radio"><?php echo $text_tab_inside; ?>
                  </label>
                  <label class="btn btn-default setting_widget <?php echo $module_ciaccessory_setting_widget == 'tab_outside' ? 'active' : ''; ?>">
                    <input name="module_ciaccessory_setting_widget" <?php echo $module_ciaccessory_setting_widget == 'tab_outside' ? 'checked="checked"' : ''; ?> autocomplete="off" value="tab_outside" type="radio"><?php echo $text_tab_outside; ?>
                  </label>
                </div>
              </div>
            </div>
            <!-- Journal3 Compatibility start -->
            <?php if($config_theme == 'journal3') { ?>
            <div class="form-group journal-module-id" style="display: <?php echo ($module_ciaccessory_setting_widget == 'tab_inside') ? 'block"' : 'none'; ?>">
              <label class="col-sm-12 control-label"><?php echo $entry_module_id; ?></label>
              <div class="col-sm-12">
                <input type="text" name="module_ciaccessory_setting_module_id" value="<?php echo $module_ciaccessory_setting_module_id; ?>" placeholder="<?php echo $entry_module_id; ?>" class="form-control">
              </div>
            </div>
            <?php } ?>
            <!-- Journal3 Compatibility end -->

            <div class="form-group find-before" style="display: <?php echo ($module_ciaccessory_setting_widget == 'tab_outside') ? 'block"' : 'none'; ?>">
              <label class="col-sm-12 control-label"><?php echo $entry_find_before; ?></label>
              <div class="col-sm-12">
                <textarea name="module_ciaccessory_setting_find_before" placeholder="<?php echo $entry_find_before; ?>" class="form-control"><?php echo $module_ciaccessory_setting_find_before; ?></textarea>
                <div class="alert alert-info"><i class="fa fa-info-circle"></i> <?php echo $help_find_before; ?></div>
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-12 control-label"><?php echo $entry_display_layout; ?></label>
              <div class="col-sm-12">
                <div class="btn-group" data-toggle="buttons">
                  <label class="btn btn-default display_layout <?php echo $module_ciaccessory_setting_display_layout == 'grid' ? 'active' : ''; ?>">
                    <input name="module_ciaccessory_setting_display_layout" <?php echo $module_ciaccessory_setting_display_layout == 'grid' ? 'checked="checked"' : ''; ?> autocomplete="off" value="grid" type="radio"><?php echo $text_grid; ?>
                  </label>
                  <label class="btn btn-default display_layout <?php echo $module_ciaccessory_setting_display_layout == 'list' ? 'active' : ''; ?>">
                    <input name="module_ciaccessory_setting_display_layout" <?php echo $module_ciaccessory_setting_display_layout == 'list' ? 'checked="checked"' : ''; ?> autocomplete="off" value="list" type="radio"><?php echo $text_list; ?>
                  </label>
                </div>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-12 control-label"><?php echo $entry_display_title; ?></label>
              <div class="col-sm-12">
                <div class="btn-group" data-toggle="buttons">
                  <label class="btn btn-default display_title <?php echo $module_ciaccessory_setting_display_title ? 'active' : ''; ?>">
                    <input name="module_ciaccessory_setting_display_title" <?php echo $module_ciaccessory_setting_display_title ? 'checked="checked"' : ''; ?> autocomplete="off" value="1" type="radio"><?php echo $text_yes; ?>
                  </label>
                  <label class="btn btn-default display_title <?php echo !$module_ciaccessory_setting_display_title ? 'active' : ''; ?>">
                    <input name="module_ciaccessory_setting_display_title" <?php echo !$module_ciaccessory_setting_display_title ? 'checked="checked"' : ''; ?> autocomplete="off" value="0" type="radio"> <?php echo $text_no; ?>
                  </label>
                </div>
              </div>
            </div>
            <div class="title-group form-group required" style="display: <?php echo ($module_ciaccessory_setting_display_title) ? 'block"' : 'none'; ?>">
                <label class="col-sm-12 control-label"><?php echo $entry_title; ?></label>
                <div class="col-sm-12">
                  <?php foreach ($languages as $language) { ?>
                  <div class="input-group">
                    <span class="input-group-addon">
                      <?php if(VERSION >= '2.2.0.0') { ?>
                      <img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" title="<?php echo $language['name']; ?>" />
                      <?php } else{ ?>
                      <img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" />
                      <?php } ?>
                    </span>
                    <input type="text" name="module_ciaccessory_setting_title[<?php echo $language['language_id']; ?>][title]" value="<?php echo isset($module_ciaccessory_setting_title[$language['language_id']]['title']) ? $module_ciaccessory_setting_title[$language['language_id']]['title'] : ''; ?>" placeholder="<?php echo $entry_title; ?>" class="form-control" />
                  </div>
                  <?php if (isset($error_title[$language['language_id']])) { ?>
                  <div class="text-danger"><?php echo $error_title[$language['language_id']]; ?></div>
                  <?php } ?>
                  <?php } ?>
                </div>
              </div>
            <div class="tab-inside-group form-group" style="display: <?php echo ($module_ciaccessory_setting_widget == 'tab_inside') ? 'block"' : 'none'; ?>">
                <label class="col-sm-12 control-label"><?php echo $entry_tab_title; ?></label>
                <div class="col-sm-12">
                  <?php foreach ($languages as $language) { ?>
                  <div class="input-group">
                    <span class="input-group-addon">
                      <?php if(VERSION >= '2.2.0.0') { ?>
                      <img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" title="<?php echo $language['name']; ?>" />
                      <?php } else{ ?>
                      <img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" />
                      <?php } ?>
                    </span>
                    <input type="text" name="module_ciaccessory_setting_title[<?php echo $language['language_id']; ?>][tab_title]" value="<?php echo isset($module_ciaccessory_setting_title[$language['language_id']]['tab_title']) ? $module_ciaccessory_setting_title[$language['language_id']]['tab_title'] : ''; ?>" placeholder="<?php echo $entry_tab_title; ?>" class="form-control" />
                  </div>
                  <?php } ?>

                  <?php if (isset($error_tab_title[$language['language_id']])) { ?>
                  <div class="text-danger"><?php echo $error_tab_title[$language['language_id']]; ?></div>
                  <?php } ?>
                </div>
              </div>
            </div>
          </div>
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title"><i class="fa fa-cog"></i> <?php echo $text_edit_product; ?></h3>
            </div>
            <div class="panel-body">
              <div class="form-group">
                <label class="col-sm-12 control-label"><?php echo $entry_display_image; ?></label>
                <div class="col-sm-12">
                  <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-default display_image <?php echo $module_ciaccessory_setting_display_image ? 'active' : ''; ?>">
                      <input name="module_ciaccessory_setting_display_image" <?php echo $module_ciaccessory_setting_display_image ? 'checked="checked"' : ''; ?> autocomplete="off" value="1" type="radio"><?php echo $text_yes; ?>
                    </label>
                    <label class="btn btn-default display_image <?php echo !$module_ciaccessory_setting_display_image ? 'active' : ''; ?>">
                      <input name="module_ciaccessory_setting_display_image" <?php echo !$module_ciaccessory_setting_display_image ? 'checked="checked"' : ''; ?> autocomplete="off" value="0" type="radio"> <?php echo $text_no; ?>
                    </label>
                  </div>
                </div>
              </div>
              <div class="image-group form-group" style="display: <?php echo ($module_ciaccessory_setting_display_image) ? 'block"' : 'none'; ?>">
                <label class="col-sm-12 control-label"><?php echo $entry_image_type; ?></label>
                <div class="col-sm-12">
                  <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-default <?php echo $module_ciaccessory_setting_image_type == 'square' ? 'active' : ''; ?>">
                      <input name="module_ciaccessory_setting_image_type" <?php echo $module_ciaccessory_setting_image_type == 'square' ? 'checked="checked"' : ''; ?> autocomplete="off" value="square" type="radio"><?php echo $text_square; ?>
                    </label>
                    <label class="btn btn-default <?php echo $module_ciaccessory_setting_image_type == 'circle' ? 'active' : ''; ?>">
                      <input name="module_ciaccessory_setting_image_type" <?php echo $module_ciaccessory_setting_image_type == 'circle' ? 'checked="checked"' : ''; ?> autocomplete="off" value="circle" type="radio"><?php echo $text_circle; ?>
                    </label>
                  </div>
                </div>
              </div>
              <div class="image-group form-group" style="display: <?php echo ($module_ciaccessory_setting_display_image) ? 'block"' : 'none'; ?>">
                <label class="col-sm-12 control-label" for="input-image-width"><?php echo $entry_image_size; ?></label>
                <div class="col-sm-12">
                  <div class="row">
                    <div class="col-sm-6">
                      <div class="input-group">
                      <input type="text" name="module_ciaccessory_setting_image_width" value="<?php echo $module_ciaccessory_setting_image_width; ?>" placeholder="<?php echo $entry_width; ?>" id="input-image-width" class="form-control" />
                      <span class="input-group-btn">
                        <button type="button" class="btn btn-default"><i class="fa fa-arrows-h"></i></button>
                      </span>
                      </div>
                    </div>
                    <div class="col-sm-6">
                      <div class="input-group">
                        <input type="text" name="module_ciaccessory_setting_image_height" value="<?php echo $module_ciaccessory_setting_image_height; ?>" placeholder="<?php echo $entry_height; ?>" class="form-control" />
                        <span class="input-group-btn">
                          <button type="button" class="btn btn-default"><i class="fa fa-arrows-v"></i></button>
                        </span>
                      </div>
                    </div>
                  </div>
                  <?php if ($error_image_size) { ?>
                  <div class="text-danger"><?php echo $error_image_size; ?></div>
                  <?php } ?>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-12 control-label"><?php echo $entry_display_name; ?></label>
                <div class="col-sm-12">
                  <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-default <?php echo $module_ciaccessory_setting_display_name ? 'active' : ''; ?>">
                      <input name="module_ciaccessory_setting_display_name" <?php echo $module_ciaccessory_setting_display_name ? 'checked="checked"' : ''; ?> autocomplete="off" value="1" type="radio"><?php echo $text_yes; ?>
                    </label>
                    <label class="btn btn-default <?php echo !$module_ciaccessory_setting_display_name ? 'active' : ''; ?>">
                      <input name="module_ciaccessory_setting_display_name" <?php echo !$module_ciaccessory_setting_display_name ? 'checked="checked"' : ''; ?> autocomplete="off" value="0" type="radio"> <?php echo $text_no; ?>
                    </label>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-12 control-label"><?php echo $entry_display_price; ?></label>
                <div class="col-sm-12">
                  <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-default <?php echo $module_ciaccessory_setting_display_price ? 'active' : ''; ?>">
                      <input name="module_ciaccessory_setting_display_price" <?php echo $module_ciaccessory_setting_display_price ? 'checked="checked"' : ''; ?> autocomplete="off" value="1" type="radio"><?php echo $text_yes; ?>
                    </label>
                    <label class="btn btn-default <?php echo !$module_ciaccessory_setting_display_price ? 'active' : ''; ?>">
                      <input name="module_ciaccessory_setting_display_price" <?php echo !$module_ciaccessory_setting_display_price ? 'checked="checked"' : ''; ?> autocomplete="off" value="0" type="radio"> <?php echo $text_no; ?>
                    </label>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-12 control-label"><?php echo $entry_text_alignment; ?></label>
                <div class="col-sm-12">
                  <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-default <?php echo $module_ciaccessory_setting_text_alignment == 'text-left' ? 'active' : ''; ?>">
                      <input name="module_ciaccessory_setting_text_alignment" <?php echo $module_ciaccessory_setting_text_alignment == 'text-left' ? 'checked="checked"' : ''; ?> autocomplete="off" value="text-left" type="radio"><?php echo $text_left; ?>
                    </label>
                    <label class="btn btn-default <?php echo $module_ciaccessory_setting_text_alignment == 'text-center' ? 'active' : ''; ?>">
                      <input name="module_ciaccessory_setting_text_alignment" <?php echo $module_ciaccessory_setting_text_alignment == 'text-center' ? 'checked="checked"' : ''; ?> autocomplete="off" value="text-center" type="radio"><?php echo $text_center; ?>
                    </label>
                    <label class="btn btn-default <?php echo $module_ciaccessory_setting_text_alignment == 'text-right' ? 'active' : ''; ?>">
                      <input name="module_ciaccessory_setting_text_alignment" <?php echo $module_ciaccessory_setting_text_alignment == 'text-right' ? 'checked="checked"' : ''; ?> autocomplete="off" value="text-right" type="radio"><?php echo $text_right; ?>
                    </label>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-12 control-label"><?php echo $entry_display_qty; ?></label>
                <div class="col-sm-12">
                  <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-default <?php echo $module_ciaccessory_setting_display_qty ? 'active' : ''; ?>">
                      <input name="module_ciaccessory_setting_display_qty" <?php echo $module_ciaccessory_setting_display_qty ? 'checked="checked"' : ''; ?> autocomplete="off" value="1" type="radio"><?php echo $text_yes; ?>
                    </label>
                    <label class="btn btn-default <?php echo !$module_ciaccessory_setting_display_qty ? 'active' : ''; ?>">
                      <input name="module_ciaccessory_setting_display_qty" <?php echo !$module_ciaccessory_setting_display_qty ? 'checked="checked"' : ''; ?> autocomplete="off" value="0" type="radio"> <?php echo $text_no; ?>
                    </label>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-12 control-label"><?php echo $entry_display_button; ?></label>
                <div class="col-sm-12">
                  <div class="btn-group" data-toggle="buttons">
                    <label class="btn btn-default <?php echo $module_ciaccessory_setting_display_button ? 'active' : ''; ?>">
                      <input name="module_ciaccessory_setting_display_button" <?php echo $module_ciaccessory_setting_display_button ? 'checked="checked"' : ''; ?> autocomplete="off" value="1" type="radio"><?php echo $text_yes; ?>
                    </label>
                    <label class="btn btn-default <?php echo !$module_ciaccessory_setting_display_button ? 'active' : ''; ?>">
                      <input name="module_ciaccessory_setting_display_button" <?php echo !$module_ciaccessory_setting_display_button ? 'checked="checked"' : ''; ?> autocomplete="off" value="0" type="radio"> <?php echo $text_no; ?>
                    </label>
                  </div>
                </div>
              </div>
          </div>
        </div>
        </div>
        <div class="col-sm-6">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-cog"></i> <?php echo $text_color; ?></h3>
          </div>
          <div class="panel-body">
            <div class="form-group color-group" style="display: <?php echo ($module_ciaccessory_setting_display_layout == 'grid') ? 'block"' : 'none'; ?>">
              <label class="col-sm-12 control-label"><?php echo $entry_backgroundcolor; ?></label>
              <div class="col-sm-12">
                <input type="text" name="module_ciaccessory_setting_backgroundcolor" value="<?php echo $module_ciaccessory_setting_backgroundcolor; ?>" class="form-control color-picker">
              </div>
            </div>
            <div class="form-group color-group" style="display: <?php echo ($module_ciaccessory_setting_display_layout == 'grid') ? 'block"' : 'none'; ?>">
              <label class="col-sm-12 control-label"><?php echo $entry_bordercolor; ?></label>
              <div class="col-sm-12">
                <input type="text" name="module_ciaccessory_setting_bordercolor" value="<?php echo $module_ciaccessory_setting_bordercolor; ?>" class="form-control color-picker">
              </div>
            </div>
            <div class="form-group color-group" style="display: <?php echo ($module_ciaccessory_setting_display_layout == 'grid') ? 'block"' : 'none'; ?>">
              <label class="col-sm-12 control-label"><?php echo $entry_textcolor; ?></label>
              <div class="col-sm-12">
                <input type="text" name="module_ciaccessory_setting_textcolor" value="<?php echo $module_ciaccessory_setting_textcolor; ?>" class="form-control color-picker">
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-12 control-label"><?php echo $entry_button_backgroundcolor; ?></label>
              <div class="col-sm-12">
                <input type="text" name="module_ciaccessory_setting_button_backgroundcolor" value="<?php echo $module_ciaccessory_setting_button_backgroundcolor; ?>" class="form-control color-picker">
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-12 control-label"><?php echo $entry_button_textcolor; ?></label>
              <div class="col-sm-12">
                <input type="text" name="module_ciaccessory_setting_button_textcolor" value="<?php echo $module_ciaccessory_setting_button_textcolor; ?>" class="form-control color-picker">
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-12 control-label"><?php echo $entry_button_hover_backgroundcolor; ?></label>
              <div class="col-sm-12">
                <input type="text" name="module_ciaccessory_setting_button_hover_backgroundcolor" value="<?php echo $module_ciaccessory_setting_button_hover_backgroundcolor; ?>" class="form-control color-picker">
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-12 control-label"><?php echo $entry_button_hover_textcolor; ?></label>
              <div class="col-sm-12">
                <input type="text" name="module_ciaccessory_setting_button_hover_textcolor" value="<?php echo $module_ciaccessory_setting_button_hover_textcolor; ?>" class="form-control color-picker">
              </div>
            </div>
          </div>
        </div>
        </div>
        <div class="col-sm-6">
          <div class="panel panel-default">
            <div class="panel-heading">
              <h3 class="panel-title"><i class="fa fa-cog"></i> <?php echo $entry_custom_css; ?></h3>
            </div>
            <div class="panel-body">
              <textarea name="module_ciaccessory_setting_custom_css" placeholder="<?php echo $entry_custom_css; ?>" class="form-control" rows="10"><?php echo $module_ciaccessory_setting_custom_css; ?></textarea>
            </div>
          </div>
        </div>
        <div class="col-sm-6">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h3 class="panel-title"><i class="fa fa-cog"></i> <?php echo $text_edit_option; ?></h3>
          </div>
          <div class="panel-body">
          <div class="form-group">
              <label class="col-sm-12 control-label" for="input-image-width"><?php echo $entry_image_size; ?></label>
              <div class="col-sm-12">
                <div class="row">
                  <div class="col-sm-6">
                    <div class="input-group">
                    <input type="text" name="module_ciaccessory_setting_image_thumb_width" value="<?php echo $module_ciaccessory_setting_image_thumb_width; ?>" placeholder="<?php echo $entry_width; ?>" id="input-image-width" class="form-control" />
                    <span class="input-group-btn">
                      <button type="button" class="btn btn-default"><i class="fa fa-arrows-h"></i></button>
                    </span>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="input-group">
                      <input type="text" name="module_ciaccessory_setting_image_thumb_height" value="<?php echo $module_ciaccessory_setting_image_thumb_height; ?>" placeholder="<?php echo $entry_height; ?>" class="form-control" />
                      <span class="input-group-btn">
                        <button type="button" class="btn btn-default"><i class="fa fa-arrows-v"></i></button>
                      </span>
                    </div>
                  </div>
                </div>
                <?php if ($error_image_thumb_size) { ?>
                <div class="text-danger"><?php echo $error_image_thumb_size; ?></div>
                <?php } ?>
              </div>
            </div>
          </div>
        </div>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-12">
          <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title"><i class="fa fa-info-circle"></i> <?php echo $text_edit_description; ?></h3></div>
            <div class="panel-body">
              <ul class="nav nav-tabs" id="language">
                <?php foreach ($languages as $language) { ?>
                <li><a href="#language<?php echo $language['language_id']; ?>" data-toggle="tab">
                  <?php if(VERSION >= '2.2.0.0') { ?>
                    <img src="language/<?php echo $language['code']; ?>/<?php echo $language['code']; ?>.png" title="<?php echo $language['name']; ?>" />
                    <?php } else{ ?>
                    <img src="view/image/flags/<?php echo $language['image']; ?>" title="<?php echo $language['name']; ?>" />
                    <?php } ?> <?php echo $language['name']; ?></a></li>
                <?php } ?>
              </ul>
              <div class="tab-content">
                <?php foreach ($languages as $language) { ?>
                <div class="tab-pane" id="language<?php echo $language['language_id']; ?>">
                  <div class="form-group">
                    <label class="col-sm-2 control-label" for="input-top-description<?php echo $language['language_id']; ?>"><?php echo $entry_top_description; ?></label>
                    <div class="col-sm-10">
                      <textarea name="module_ciaccessory_setting_title[<?php echo $language['language_id']; ?>][top_description]" placeholder="<?php echo $entry_top_description; ?>" id="input-top-description<?php echo $language['language_id']; ?>" class="form-control summernote" data-toggle="summernote" data-lang=""><?php echo isset($module_ciaccessory_setting_title[$language['language_id']]) ? $module_ciaccessory_setting_title[$language['language_id']]['top_description'] : ''; ?></textarea>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="col-sm-2 control-label" for="input-bottom-description<?php echo $language['language_id']; ?>"><?php echo $entry_bottom_description; ?></label>
                    <div class="col-sm-10">
                      <textarea name="module_ciaccessory_setting_title[<?php echo $language['language_id']; ?>][bottom_description]" placeholder="<?php echo $entry_bottom_description; ?>" id="input-bottom-description<?php echo $language['language_id']; ?>" class="form-control summernote" data-toggle="summernote" data-lang=""><?php echo isset($module_ciaccessory_setting_title[$language['language_id']]) ? $module_ciaccessory_setting_title[$language['language_id']]['bottom_description'] : ''; ?></textarea>
                    </div>
                  </div>
                </div>
                <?php } ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
<?php if(VERSION <= '2.2.0.0') { ?>
<script type="text/javascript"><!--
<?php foreach ($languages as $language) { ?>
$('#input-top-description<?php echo $language['language_id']; ?>').summernote({ height: 300 });
$('#input-bottom-description<?php echo $language['language_id']; ?>').summernote({ height: 300 });
<?php } ?>
//--></script>
<?php } else if(VERSION <= '2.3.0.2') { ?>
<script type="text/javascript" src="view/javascript/summernote/summernote.js"></script>
<link href="view/javascript/summernote/summernote.css" rel="stylesheet" />
<script type="text/javascript" src="view/javascript/summernote/opencart.js"></script>
<?php } else if(VERSION >= '3.0.0.0') { ?>
<link href="view/javascript/codemirror/lib/codemirror.css" rel="stylesheet" />
<link href="view/javascript/codemirror/theme/monokai.css" rel="stylesheet" />
<script type="text/javascript" src="view/javascript/codemirror/lib/codemirror.js"></script>
<script type="text/javascript" src="view/javascript/codemirror/lib/xml.js"></script>
<script type="text/javascript" src="view/javascript/codemirror/lib/formatting.js"></script>
<script type="text/javascript" src="view/javascript/summernote/summernote.js"></script>
<link href="view/javascript/summernote/summernote.css" rel="stylesheet" />
<script type="text/javascript" src="view/javascript/summernote/summernote-image-attributes.js"></script>
<script type="text/javascript" src="view/javascript/summernote/opencart.js"></script>
<?php } ?>
<script type="text/javascript"><!--
$('#language a:first').tab('show');
//--></script>
  <script type="text/javascript"><!--
  $('.display_layout').click(function() {
    if($(this).find('input').val() == 'grid') {
      $('.color-group').show();
    } else {
      $('.color-group').hide();
    }
  });

  $('.display_title').click(function() {
    if($(this).find('input').val() == 1) {
      $('.title-group').show();
    }else{
      $('.title-group').hide();
    }
  });

  $('.setting_widget').click(function() {
    if($(this).find('input').val() == 'tab_inside') {
      $('.tab-inside-group').show();
      $('.journal-module-id').show();
      $('.find-before').hide();
    }else{
      $('.tab-inside-group').hide();
      $('.journal-module-id').hide();
      $('.find-before').show();
    }
  });

  $('.display_image').click(function() {
    if($(this).find('input').val() == 1) {
      $('.image-group').show();
    }else{
      $('.image-group').hide();
    }
  });
  //--></script>
  <script type="text/javascript">
  var element = null;
  $('.color-picker').ColorPicker({
    curr : '',
    onShow: function (colpkr) {
      $(colpkr).fadeIn(500);
      return false;
    },
    onHide: function (colpkr) {
      $(colpkr).fadeOut(500);
    return false;
    },
    onSubmit: function(hsb, hex, rgb, el) {
      $(el).val('#'+hex);
      $(el).ColorPickerHide();
    },
    onBeforeShow: function () {
      $(this).ColorPickerSetColor(this.value);
    },
    onChange: function (hsb, hex, rgb) {
      element.curr.parent().next().find('.preview').css('background', '#' + hex);
      element.curr.val('#'+hex);
    }
  }).bind('keyup', function(){
    $(this).ColorPickerSetColor(this.value);
  }).click(function(){
    element = this;
    element.curr = $(this);
  });

  $.each($('.color-picker'),function(key,value) {
    $(this).parent().next().find('.preview').css({'background': $(this).val()});
  });
</script>

<?php if($action_enable_events) { ?>
<script type="text/javascript">
function enableEvents() {
  $.ajax({
    url: '<?php echo $action_enable_events; ?>',
    dataType: 'json',
    beforeSend: function() {
      $('.button-enable-event').attr('disabled', true);
    },
    complete: function() {
      $('.button-enable-event').attr('disabled', false);
    },
    success: function(json) {
      $('.inspect-warning, .inspect-danger, .inspect-success').remove();

      if(json['warning']) {
        $('.container-fluid > .panel').before('<div class="alert alert-danger inspect-alert"><i class="fa fa-exclamation-circle"></i> ' + json['warning'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');
      }

      if(json['success']) {
        location = json['success'];
      }
    }
  });
}
</script>
<?php } ?>

<style type="text/css">
  .form-horizontal .control-label{
    text-align: left;
  }
  .form-group label { margin-bottom: 5px !important; }
  .btn-group .active{ background : #1872A2; color: #fff; border-color: #1872A2; }
  .btn-group .btn:hover{ background : #1872A2; color: #fff; border-color: #1872A2; }
</style>
</div>
<?php echo $footer; ?>