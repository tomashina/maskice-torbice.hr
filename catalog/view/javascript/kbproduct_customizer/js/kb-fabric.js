
var canvas;
var kb_canvas = [];
var a;
var b;
var canvas_push = [];
var canvasSideObject = [];
var kb_canvas_side = 1;
var kb_cust_cost = 0;
var kb_layer_count = 1;
var kb_additional_cost = 0;
var kb_color_add = 0;
var kbCanvasState = [];
var kbCurrentStateIndex = -1;
var kbUndoStatus = false;
var kbRedoStatus = false;
var kbUndoFinishedStatus = 1;
var kbRedoFinishedStatus = 1;

//BOC changed by Shivam Bansal for adding the image transparency status
var transparency = parseInt($("#transparency").val());
//EOC changed by Shivam Bansal for adding the image transparency status

/*
 * function to save product customization
 *
 */
function savePCProductCustomization()
{
    var canvasSideObject = [];
    var canvas_layer = [];
    var kb_layer_obj_empty = [];
    var kb_layer_obj_add = 0;
    /*
     * Canvas kb_canvas having data of all
     * the canvas images used in the product
     */
    if (kb_canvas.length) {
        /*
         * loop kb_canvas to separate the object and product image into the array 
         */
        for (var u = 1; u <= kb_canvas.length - 1; u++) {
            if (kb_canvas[u] != '') {
                var kb_cart_canvas = kb_canvas[u];
                //BOC changed by Shivam Bansal for adding the image transparency status
                if(transparency) {
                    var canvas_var = {
                        object: kb_cart_canvas.backgroundImage, //get image data
                        name: kb_all_sides_name[u], //get image data
                        src: kb_cart_canvas.toDataURL({format: 'png'}), //get base64 image data of the image
                        backgroundColor: kb_cart_canvas.backgroundColor //get background color applied on the product
                    };
                } else {
                    var canvas_var = {
                        object: kb_cart_canvas.overlayImage, //get image data
                        name: kb_all_sides_name[u], //get image data
                        src: kb_cart_canvas.toDataURL({format: 'png'}), //get base64 image data of the image
                        backgroundColor: kb_cart_canvas.backgroundColor //get background color applied on the product
                    };
                }
                //EOC changed by Shivam Bansal for adding the image transparency status
                canvas_layer.push(canvas_var);
                var sideObject = [];
                /*
                 * loop kb_cart_canvas.getObjects() to get objects applied on the product
                 */
                $.each(kb_cart_canvas.getObjects(), function (index, value) {
                    var kbcost = 0;
                    kbcost = parseFloat(value.cost);
                    sideObject.push({
                        "id": kb_side_object + " " + (index + 1), //push object ID to make it unique
                        "src": value.toDataURL(), //push base64 object data to convert it into image
                        "object": value, //push object data
                        "cost": kbcost //push object cost applied while doing customization
                    });
                });
                if (sideObject.length <= 0) {
                    kb_layer_obj_empty.push(true);
                } else {
                    kb_layer_obj_add = 1;
                }
                canvasSideObject.push(sideObject);
            }
        }
    }

    var kb_cust_confirm = 0;
    if (kb_layer_obj_empty.length) {
        kb_cust_confirm = 1;
    }

    /*
     * code block to apply fixed design price
     * in the customization cost
     */
    var additional_cost_insert = 0;
    if (kb_layer_obj_add) {
        kb_cust_cost = kb_cust_cost + parseFloat(kb_design_fixed_price);
        kbupdateCustPrice();
        additional_cost_insert = 1;
    } else {
        if (kb_color_add) {
            kb_cust_cost = kb_cust_cost + parseFloat(kb_design_fixed_price);
            kbupdateCustPrice();
            additional_cost_insert = 1;
        }
    }

    var cus_cost = parseFloat(kb_cust_cost);
    var kbproduct_cost = 0;
    if (typeof priceWithDiscountsDisplay != 'undefined') {
        kbproduct_cost = priceWithDiscountsDisplay;
    }
    var kbtotal_cost = cus_cost + kbproduct_cost;

    //Convert price in the default Currency format
    var custm_price = formatCurrency(cus_cost, currencyFormat, currencySign, currencyBlank);
    var pro_price = formatCurrency(kbtotal_cost, currencyFormat, currencySign, currencyBlank);
    var str = kb_cusm_msg + ' ' + custm_price + ' ' + kb_cusm_msg1 + ' ' + pro_price + '.';
    if (typeof kb_warning_msg != 'undefined') {
        var msg = '';
        msg += '<p>' + str + '</p>';
        if (additional_cost_insert && (typeof kb_design_fixed_price != 'undefined')) {
            var additional_des_cost = parseFloat(kb_design_fixed_price);
            if (additional_des_cost > 0) {
                var additional_design_cost_display = formatCurrency(parseFloat(kb_design_fixed_price), currencyFormat, currencySign, currencyBlank);
                msg += '<p><span style="font-style: italic;font-size: 12px;">' + kb_cp_add_customization_display_msg + ' ' + additional_design_cost_display + ' ' + '(' + kb_include + ')</span></p>';
            }
        }
        msg += '<br/>';
        if (kb_cust_confirm) {
            msg += '<p>' + kb_cp_empty_cust_conf + '</p>';
        }
        /*
         * display confirm dialog box before 
         * adding the product into the cart
         */
        dialog.confirm({
            message: msg, //display the message
            cancel: kb_cancel_btn, //display the cancel button text
            button: kb_ok_btn, //display the ok button text
            callback: function (response) {
                if (response) {
                    var kb_current = parseInt($.now() / 1000);
                    $('.kbcustomizer-loder.loading-mask').show();
                    $.ajax({
                        type: 'POST',
                        url: 'index.php?route=kbproduct_customizer/kbproduct_customizer/saveCustomizedProduct',
                        dataType: 'html',
                        data: {
                            token: kb_token,
                            canvasObjectSide: JSON.stringify(canvasSideObject), //convert the object data into JSON
                            step: 'saveCustomize',
                            ajax: true,
                            kb_current: kb_current,
                            canvasLayer: JSON.stringify(canvas_layer), //convert the canvas image data into JSON
                            id_product: $('input[name="id_product"]').val(),
                            ipa: $('input[name="id_product_attribute"]').val(),
                            customization_cost: kb_cust_cost, //final customization cost
                        },
                        beforeSend: function () {
                            $('body').addClass("kb_loading");  //display loader

                        },
                        success: function (data) {
//                            var id_pro = data['id_product'];
                            var id_cus = data;
//                            var ipa = data['id_product_attribute'];
                            $('#kbproduct_customization_add').val(id_cus);
                            $('.kbcustomizer-loder.loading-mask').hide();
//                            alert(id_cus);
                            //add product into the cart
                            kbaddtocart(id_cus);
                        }
                    });

                } else {
                    if (additional_cost_insert) {
                        /*
                         * if there cancel the request then
                         * remove the fixed price from the final cost
                         */
                        kb_cust_cost = kb_cust_cost - parseFloat(kb_design_fixed_price);
                        kbupdateCustPrice();
                        additional_cost_insert = 0;

                    }
                }
            }
        });
    }
    event.preventDefault();
}

/*
 * function to add product into the cart
 * 
 * @param {int} id_cus
 */
function kbaddtocart(id_cus)
{
//    setTimeout(
    $.ajax({
        url: 'index.php?route=kbproduct_customizer/kbproduct_customizer/checkProductAdd',
        type: 'post',
        data: $('#product input[type=\'text\'], #product input[type=\'hidden\'], #product input[type=\'radio\']:checked, #product input[type=\'checkbox\']:checked, #product select, #product textarea,#kbproduct_customization_add'),
        dataType: 'json',
        beforeSend: function () {
            $('#button-cart').button('loading');
        },
        complete: function () {
            $('#button-cart').button('reset');
        },
        success: function (json) {
            $('.alert, .text-danger').remove();
            $('.form-group').removeClass('has-error');

            if (json['error']) {
                $('#kbcm-container-block').removeClass('transform');
                setTimeout(function () {
                    $('#kbpc-container-popover').css('left', '-100%');
                    $('#kbpc-container-popover').hide();    //hide the popup
                    $('body').css('overflow', 'scroll');    //scroll to left
                }, 500);

                if (json['error']['option']) {
                    for (i in json['error']['option']) {
                        var element = $('#input-option' + i.replace('_', '-'));

                        if (element.parent().hasClass('input-group')) {
                            element.parent().after('<div class="text-danger">' + json['error']['option'][i] + '</div>');
                        } else {
                            element.after('<div class="text-danger">' + json['error']['option'][i] + '</div>');
                        }
                    }
                }

                if (json['error']['recurring']) {
                    $('select[name=\'recurring_id\']').after('<div class="text-danger">' + json['error']['recurring'] + '</div>');
                }

                // Highlight any found errors
                $('.text-danger').parent().addClass('has-error');
            }

            if (json['success']) {
                window.location = json['redirect'];
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
    });
}

/*
 * function to add text on the canvas
 * 
 * @returns {Boolean}
 */
function processAddPCText() {
    $('.error-message').remove();
    $("#uploaded-text").removeClass('error-field');
    $('#font-size').val('32');  //add the default font size
    /*
     * first validate the text field 
     */
    var check_text = velovalidation.checkMandatory($("#uploaded-text"), kb_max_text_length, kb_min_text_length);
    if (check_text != true) {
        $("#uploaded-text").addClass('error-field');
        $("#uploaded-text").after('<p class="error-message">' + check_text + '</p>');
        return false;
    }
    var text = $("#uploaded-text").val();  //get field value
    if (text != '') {
        var font_cost = getFontCost($("#uploaded-text")); //calculate the cost of text added
        $('#uploaded-text').val('');
        /*
         * convert the text into fabric.Text which will
         * be applied on the canvas image
         */
        var fabricText = new fabric.Text(text, {
            centerTransform: true,
            fill: "#000000",
            originX: "left",
            padding: 10,
            rotatingPointOffset: 10,
            scaleX: 1,
            scaleY: 1,
            textAlign: "left",
            transparentCorners: true,
            left: fabric.util.getRandomInt(0, 200),
            top: fabric.util.getRandomInt(0, 400),
            fontFamily: $('#input-font').val(), //set the selected font
            fontSize: $('#font-size').val(), //set the selected font size
            cost: font_cost,
        });

        //Add the fabircText on the canvas
        canvas.add(fabricText);
        canvas.item(canvas.item.length - 1).hasRotatingPoint = true;
        //update the customization cost
        kb_cust_cost = kb_cust_cost + parseFloat(font_cost);
        kbupdateCustPrice();
        $("#kb-editor-tool").show();
        $('#text-opacity-slider').val('1');
        var canvasSideObject = [];
        canvasSideObject = getcanvasSidesObject();
    }
}

/*
 * function to update the text on the canvas image
 * 
 * @returns {Boolean}
 */
function processUpdatePCText(elm)
{
    var activeObject = canvas.getActiveObject();  //get the selected object
    if (activeObject && activeObject.type === 'text') {  //check if object is text
        $('.error-message').remove();
        $("#upload-text").removeClass('error-field');
        /*
         * validate the text field
         */
        var check_text = velovalidation.checkMandatory($("#upload-text"), kb_max_text_length, kb_min_text_length);
        if (check_text != true) {
            $("#upload-text").addClass('error-field');
            $("#upload-text").after('<p class="error-message">' + check_text + '</p>');
            return false;
        }
        //remove the previous cost of the text added to the canvas
        kb_cust_cost = kb_cust_cost - parseFloat(activeObject.cost);
        //Update the text
        activeObject.text = elm.value;
        //update the cost
        kb_cost = getFontCost($(elm));
        activeObject.cost = kb_cost;
        kb_cust_cost = kb_cust_cost + parseFloat(kb_cost);
        canvas.renderAll();
        kbupdateCustPrice();
        var canvasSideObject = [];
        canvasSideObject = getcanvasSidesObject();
    }
}

/*
 * function to download the Customized PNG Image
 */
function processDownloadCustomize()
{
    //BOC changed by Shivam Bansal for adding the image transparency status
    if(transparency) {
        variable = canvas.backgroundImage;
    } else {
        variable = canvas.overlayImage;
    }
    if (variable) {
    //EOC changed by Shivam Bansal for adding the image transparency status
        canvas.deactivateAll().renderAll(); //deactivate all the selected object
        var data = canvas.toDataURL({//get the base64 PNG= image data
            format: 'png',
            multiplier: 1
        });
        var img = document.createElement('img'); //create <img> tag
        img.src = data;
        var link = document.createElement('a');
        link.setAttribute("download", "product_customize.png"); //set the name of download attribute
        link.setAttribute("href", data); //add href URL of base64 image
        link.appendChild(img);
        link.click(); //forced click to download the image
        var canvasSideObject = [];
        canvasSideObject = getcanvasSidesObject();
    }
}

/*
 * function to display the custmization 
 * into image in new window  
 */
function processPrintCustomize()
{
    //BOC changed by Shivam Bansal for adding the image transparency status
    if(transparency) {
        variable = canvas.backgroundImage;
    } else {
        variable = canvas.overlayImage;
    }
    if (variable) {
    //EOC changed by Shivam Bansal for adding the image transparency status
        canvas.deactivateAll().renderAll();
        var dataUrl = canvas.toDataURL(); //get canvas data URL
        var printData = '<!DOCTYPE html>';
        printData += '<html>'
        printData += '<head><title>Customization</title></head>';
        printData += '<body>'
        printData += '<img src="' + dataUrl + '">';
        printData += '</body>';
        printData += '</html>';
        var cont = window.open('', '', 'width=550,height=550');
        cont.document.open();
        cont.document.write(printData);
        cont.document.close();
        cont.focus();
        cont.print();
        cont.close();
        var canvasSideObject = [];
        canvasSideObject = getcanvasSidesObject();
    }
}

/*
 * function to undo the customization 
 */
function processUndoCustomize()
{
    //BOC changed by Shivam Bansal for adding the image transparency status
    if(transparency) {
        variable = canvas.backgroundImage;
    } else {
        variable = canvas.overlayImage;
    }
    if (variable) {
    //EOC changed by Shivam Bansal for adding the image transparency status
        if (canvas._objects.length > 0) {  //check if there is any object applied on the canvas
            kb_cust_cost = parseFloat(kb_cust_cost) - parseFloat(canvas._objects[canvas._objects.length - 1].cost);
            //remove the object from the canvas using pop(); function.
            canvas_push.push(canvas._objects.pop());
            canvas.renderAll();
            //update the price of customization
            kbupdateCustPrice();
        }
        var canvasSideObject = [];
        canvasSideObject = getcanvasSidesObject();
    }
}

/*
 * function to redo the customization
 */
function processRedoCustomize()
{
    //BOC changed by Shivam Bansal for adding the image transparency status
    if(transparency) {
        variable = canvas.backgroundImage;
    } else {
        variable = canvas.overlayImage;
    }
    if (variable) {
    //EOC changed by Shivam Bansal for adding the image transparency status
        if (canvas_push.length > 0) { //check if there is any object present on the array variable
            //update the price
            kb_cust_cost = parseFloat(kb_cust_cost) + parseFloat(canvas_push[canvas_push.length - 1].cost);
            //add the object using add(); function to the canvas
            canvas.add(canvas_push.pop());
            kbupdateCustPrice();
        }
    }
}

/*
 * function to empty the canvas image
 */
function processEmptyCanvas()
{
    /*
     * display the confirm dialog box to the 
     * user before making the canvas empty
     */
    dialog.confirm({
        message: kb_cp_clear_mesg, //confirmation message
        cancel: kb_cancel_btn, //cancel button text
        button: kb_ok_btn, //confirm button text
        callback: function (response) {
            if (response) {
                /*
                 * loop through all the applied object on the
                 * canvas to update the costing of the customization
                 */
                $.each(canvas.getObjects(), function (index, value) {
                    kb_cust_cost = kb_cust_cost - parseFloat(value.cost);
                });
                /*
                 * using clear() function to empty the canvas
                 */
                canvas.clear();
                //update the price
                kbupdateCustPrice();
                var canvasSideObject = [];
                canvasSideObject = getcanvasSidesObject();
            }
        }
    });
}

/*
 * function to add QR code on the Canvas
 */
function processAddQRCode()
{
    var qr_text = $('input[name="kb-qrcode-input"]').val().trim(); //get text for QR code
    $('.error-message').remove();
    $('input[name="kb-qrcode-input"]').removeClass('error-field');
    //Validate the QR code text field
    var check_qr = velovalidation.checkMandatory($('input[name="kb-qrcode-input"]'));
    if (check_qr != true) {
        $('input[name="kb-qrcode-input"]').addClass('error-field');
        $('input[name="kb-qrcode-input"]').after('<p class="error-message">' + check_qr + '</p>');
        return false;
    }
    if (typeof qr_text != 'undefined' && qr_text != '') {
        /*
         * Convert text into QR Code
         *  using the qrcode() function
         */
        $('#kbaddqrcode').qrcode(qr_text);
        var qr_canvas = document.querySelector("#kbaddqrcode canvas");
        var img_file = qr_canvas.toDataURL("image/png"); //get the base64 image of QR code

        setTimeout(function () {
            var qrcode_data = $('#kbaddqrcode').html();
            if (qrcode_data != '') {
                $('#kbaddqrcode').html('');
                var kb_cost = kb_qrcode_price;
                /*
                 * function to convert the base64 image
                 * into the Fabric Image to apply on the canvas
                 */
                fabric.Image.fromURL(img_file, function (image) {
                    var offset = 50;
                    var left = fabric.util.getRandomInt(0 + offset, 200 - offset);
                    var top = fabric.util.getRandomInt(0 + offset, 400 - offset);
                    image.set({
                        left: left,
                        top: top,
                        angle: 0,
                        padding: 10,
                        cornersize: 10,
                        width: 100,
                        height: 100,
                        img_type: 'qrcode', //add the image type to identify the QR code
                        actual_cost: kb_cost,
                        backgroundColor: 'transparent', //set the transparent background of the image
                        cost: kb_cost,
                    });
                    //add the QR code image into the canvas
                    canvas.add(image);
                });
                kb_cust_cost = kb_cust_cost + parseFloat(kb_cost);
                //update the price
                kbupdateCustPrice();
            }
        }, 500);
        var canvasSideObject = [];
        canvasSideObject = getcanvasSidesObject();
    }
}

/*
 * function to add gallery image on the canvas
 */
function processAddGalleryImage(elm, kb_this)
{
    var el = elm.target; //get the selected element
    var offset = 50;
    var left = fabric.util.getRandomInt(0 + offset, 200 - offset);
    var top = fabric.util.getRandomInt(0 + offset, 400 - offset);
    var angle = fabric.util.getRandomInt(-20, 40);
    var width = fabric.util.getRandomInt(30, 50);
    var opacity = (function (min, max) {
        return Math.random() * (max - min) + min;
    })(0.5, 1);
    var obj_cost = $(kb_this).data('price'); //get the cost of the image
    kb_cost = obj_cost;
    /*
     * add the image using fabric.Image on the canvas 
     */
    fabric.Image.fromURL(el.src, function (image) {
        image.set({
            left: left,
            top: top,
            angle: 0,
            padding: 10,
            cornersize: 10,
            height: 100,
            width: 100,
            hasRotatingPoint: true,
            cost: kb_cost, //cost of image
            img_type: 'fixed', //set the type to identify the image
            actual_cost: obj_cost, //set the actual cost of the image
        });
        canvas.add(image); //add the image on the canvas
    });
    //update the price of customization
    kb_cust_cost = kb_cust_cost + parseFloat(kb_cost);
    var canvasSideObject = [];
    canvasSideObject = getcanvasSidesObject();
    kbupdateCustPrice();
}

/*
 * function to remove the
 * selected object from the canvas
 */
function processPCRemoveSelected()
{
    var is_select = 0;
    var activeObject = canvas.getActiveObject(); //get the selected object
    var activeGroup = canvas.getActiveGroup();  //get the selected object
    if (activeObject) {
        is_select = 1;
    } else if (activeGroup) {
        is_select = 1;
    }
    if (is_select) {
        //display the confirmation dialog box
        // to the user before removing the object
        dialog.confirm({
            message: kb_cp_remove_obj_msg,
            cancel: kb_cancel_btn,
            button: kb_ok_btn,
            callback: function (response) {
                if (response) {
                    var activeObject = canvas.getActiveObject(),
                            activeGroup = canvas.getActiveGroup();
                    if (activeObject) {
                        kb_cust_cost = kb_cust_cost - parseFloat(activeObject.cost);
                        /*
                         * Remove the object from the canvas
                         */
                        canvas.remove(activeObject);
                        $("#uploaded-text").val("");
                        $("#upload-text").val("");
                        $("#upload-qr-text").val("");
                        //update the price
                        kbupdateCustPrice();
                        var canvasSideObject = [];
                        canvasSideObject = getcanvasSidesObject();
                    } else if (activeGroup) {
                        var objectsInGroup = activeGroup.getObjects();
                        /*
                         * Remove the object group from the canvas
                         */
                        canvas.discardActiveGroup();
                        objectsInGroup.forEach(function (object) {
                            kb_cust_cost = kb_cust_cost - parseFloat(object.cost);
                            canvas.remove(object);
                        });
                        //update the price
                        kbupdateCustPrice();
                        var canvasSideObject = [];
                        canvasSideObject = getcanvasSidesObject();
                    }
                }
            }
        });
    }
}

/*
 * function to move the object
 * in front on the canvas
 */
function processBrintToFront()
{
    var activeObject = canvas.getActiveObject(),
            activeGroup = canvas.getActiveGroup(); //get the selected object
    if (activeObject) {
        //move the object to front position
        activeObject.bringToFront();
    } else if (activeGroup) {
        var objectsInGroup = activeGroup.getObjects();
        canvas.discardActiveGroup();
        //move the object group to front position
        objectsInGroup.forEach(function (object) {
            object.bringToFront();
        });
    }
    var canvasSideObject = [];
    canvasSideObject = getcanvasSidesObject();
}

/*
 * function to move the object to the back
 */
function processSendToBack()
{
    var activeObject = canvas.getActiveObject(), //get the selected object on the canvas
            activeGroup = canvas.getActiveGroup();
    if (activeObject) {
        //move the object to back using sendToback() function
        activeObject.sendToBack();
    } else if (activeGroup) {
        var objectsInGroup = activeGroup.getObjects();
        canvas.discardActiveGroup();
        objectsInGroup.forEach(function (object) {
            //move the object group to back using sendToback() function
            object.sendToBack();
        });
    }
    var canvasSideObject = [];
    canvasSideObject = getcanvasSidesObject();
}

/*
 * function to make the text bold on the canvas
 */
function processPCTextBold(elm)
{
    var activeObject = canvas.getActiveObject();  //get the selected object on the canvas
    if (activeObject) {
        if (activeObject.type === 'text') { //check if object is text
            //update the fontweight of the text
            activeObject.fontWeight = (activeObject.fontWeight == 'bold' ? '' : 'bold');
        } else if (activeObject.type === 'curvedText') { //check if the object is curvedtext
            //update the fontweight of the text
            activeObject.set('fontWeight', (activeObject.fontWeight == 'bold' ? '' : 'bold'));
        }
    }
    canvas.renderAll(); //update the canvas
    $(elm).toggleClass('btn-active');
    var canvasSideObject = [];
    canvasSideObject = getcanvasSidesObject();
}

/*
 * function to make the text italic on the canvas
 */
function processPCTextItalic(elm)
{
    var activeObject = canvas.getActiveObject();  //get the selected object on the canvas
    if (activeObject) {
        if (activeObject.type === 'text') {//check if object is text
            //update the fontweight of the text
            activeObject.fontStyle = (activeObject.fontStyle == 'italic' ? '' : 'italic');
        } else if (activeObject.type === 'curvedText') { //check if the object is curvedtext
            //update the fontweight of the text
            activeObject.set('fontStyle', (activeObject.fontStyle == 'italic' ? '' : 'italic'));
        }
    }
    canvas.renderAll(); //update the canvas
    $(elm).toggleClass('btn-active');
    var canvasSideObject = [];
    canvasSideObject = getcanvasSidesObject();
}

/*
 * function to make the text strike on the canvas
 */
function processPCTextStrike(elm)
{
    var activeObject = canvas.getActiveObject();  //get the selected object on the canvas
    if (activeObject) {
        if (activeObject.type === 'text') {//check if object is text
            //update the textdecoration of the text
            activeObject.textDecoration = (activeObject.textDecoration == 'line-through' ? '' : 'line-through');
        } else if (activeObject.type === 'curvedText') { //check if the object is curvedtext
            //update the textdecoration of the text
            activeObject.set('textDecoration', (activeObject.textDecoration == 'line-through' ? '' : 'line-through'));
        }
    }
    canvas.renderAll(); //update the canvas
    $(elm).toggleClass('btn-active');
    var canvasSideObject = [];
    canvasSideObject = getcanvasSidesObject();
}

/*
 * function to make the text underline on the canvas
 */
function processPCTextUnderLine(elm)
{
    var activeObject = canvas.getActiveObject();  //get the selected object on the canvas
    if (activeObject) {
        if (activeObject.type === 'text') {//check if object is text
            //update the textdecoration of the text
            activeObject.textDecoration = (activeObject.textDecoration == 'underline' ? '' : 'underline');
        } else if (activeObject.type === 'curvedText') { //check if the object is curvedtext
            //update the textdecoration of the text
            activeObject.set('textDecoration', (activeObject.textDecoration == 'underline' ? '' : 'underline'));
        }
    }
    canvas.renderAll(); //update the canvas
    $(elm).toggleClass('btn-active');
    var canvasSideObject = [];
    canvasSideObject = getcanvasSidesObject();
}

/*
 * function to update the font family of the text on the canvas
 */
function processPCTextFont(elm)
{
    var activeObject = canvas.getActiveObject();   //get the selected object on the canvas
    if (activeObject) {
        if (activeObject.type === 'text') {//check if object is text
            //update the font-family of the text
            activeObject.fontFamily = elm.value;
        } else if (activeObject.type === 'curvedText') { //check if the object is curvedtext
            //update the font-family of the text
            activeObject.set('fontFamily', elm.value);
        }

    }
    canvas.renderAll(); //update the canvas
    var canvasSideObject = [];
    canvasSideObject = getcanvasSidesObject();
}

/*
 * function to update the font size
 *  of the text on the canvas
 */
function processPCTextSize(elm)
{
    var activeObject = canvas.getActiveObject();  //get the selected object on the canvas
    if (activeObject) {
        if (activeObject.type === 'text') {//check if object is text
            //update the font size of the text
            activeObject.fontSize = parseInt(elm.value, 10);
        } else if (activeObject.type === 'curvedText') { //check if the object is curvedtext
            //update the font size of the text
            activeObject.set('fontSize', parseInt(elm.value, 10));
        }
        canvas.renderAll();  //update the canvas
    }
    var canvasSideObject = [];
    canvasSideObject = getcanvasSidesObject();
}

/*
 * function to update the line height of the text on the canvas
 */
function processPCTextLineHeight(elm)
{
    var activeObject = canvas.getActiveObject();  //get the selected object on the canvas
    if (activeObject) {
        if (activeObject.type === 'text') {//check if object is text
            //update the line height of the text
            activeObject.lineHeight = parseInt(elm.value, 10);
        } else if (activeObject.type === 'curvedText') {  //check if the object is curvedtext
            //update the line height of the text
            activeObject.set('lineHeight', parseInt(elm.value, 10));
        }
    }
    canvas.renderAll();  //update the canvas
    var canvasSideObject = [];
    canvasSideObject = getcanvasSidesObject();
}

/*
 * function to align the text on left on the canvas
 */
function processPCAlignLeft(elm)
{
    var activeObject = canvas.getActiveObject();   //get the selected object on the canvas
    if (activeObject) {
        if (activeObject.type === 'text') { //check if object is text
            //align the text to the left
            activeObject.textAlign = 'left';
        } else if (activeObject.type === 'curvedText') {  //check if the object is curvedtext
            //align the text to the left
            activeObject.set('textAlign', 'left');
        }
    }
    canvas.renderAll();   //update the canvas
    $(elm).addClass('btn-active');
    $(elm).siblings('.btn-align').removeClass('btn-active');
    var canvasSideObject = [];
    canvasSideObject = getcanvasSidesObject();
}

/*
 * function to align the text on right on the canvas
 */
function processPCAlighRight(elm)
{
    var activeObject = canvas.getActiveObject();   //get the selected object on the canvas
    if (activeObject) {
        if (activeObject.type === 'text') { //check if object is text
            //align the text to the right
            activeObject.textAlign = 'right';
        } else if (activeObject.type === 'curvedText') { //check if the object is curvedtext
            //align the text to the right
            activeObject.set('textAlign', 'right');
        }
    }
    canvas.renderAll(); //update the canvas
    $(elm).addClass('btn-active');
    $(elm).siblings('.btn-align').removeClass('btn-active');
    var canvasSideObject = [];
    canvasSideObject = getcanvasSidesObject();
}

/*
 * function to align the text on center on the canvas
 */
function processPCAlignCenter(elm)
{
    var activeObject = canvas.getActiveObject();   //get the selected object on the canvas
    if (activeObject) {
        if (activeObject.type === 'text') {   //check if object is text
            //align the text to the center
            activeObject.textAlign = 'center';
        } else if (activeObject.type === 'curvedText') {   //check if the object is curvedtext
            //align the text to the center
            activeObject.set('textAlign', 'center');
        }
    }
    canvas.renderAll(); //update the canvas
    $(elm).addClass('btn-active');
    $(elm).siblings('.btn-align').removeClass('btn-active');
    var canvasSideObject = [];
    canvasSideObject = getcanvasSidesObject();
}

/*
 * function to align the text justify on the canvas
 */
function processPCAlignJustify(elm)
{
    var activeObject = canvas.getActiveObject();   //get the selected object on the canvas
    if (activeObject) {
        if (activeObject.type === 'text') {   //check if object is text
            //align the text to the justify
            activeObject.textAlign = 'justify';
        } else if (activeObject.type === 'curvedText') {   //check if the object is curvedtext
            //align the text to the justify
            activeObject.set('textAlign', 'justify');
        }
    }
    canvas.renderAll();    //update the canvas
    $(elm).addClass('btn-active');
    $(elm).siblings('.btn-align').removeClass('btn-active');
    var canvasSideObject = [];
    canvasSideObject = getcanvasSidesObject();
}

/*
 * function to update the color
 *  of the text on the canvas
 */
function processPCtextColor(elm)
{
    var activeObject = canvas.getActiveObject();    //get the selected object on the canvas
    if (activeObject) {
        if (activeObject.type === 'text') {   //check if object is text
            //update the color of the text
            activeObject.fill = '#' + $(elm).val();
        } else if (activeObject.type === 'curvedText') {  //check if the object is curvedtext
            //update the color of the text
            activeObject.set('fill', '#' + $(elm).val());
        }
    }
    canvas.renderAll();  //update the canvas
    var canvasSideObject = [];
    canvasSideObject = getcanvasSidesObject();
}

/*
 * function to update the opacity
 *  of the text on the canvas
 */
function processPCTextOpacity(elm)
{
    var activeObject = canvas.getActiveObject();
    if (activeObject && activeObject.type === 'text') {  //check if object is text
        //update the opacity of the text
        activeObject.opacity = $(elm).val();
        canvas.renderAll();  //update the canvas
    }
    var canvasSideObject = [];
    canvasSideObject = getcanvasSidesObject();
}

/*
 * function to update the opacity
 *  of the image on the canvas
 */
function processImageOpacity(elm)
{
    var activeObject = canvas.getActiveObject();     //get the selected object on the canvas
    if (activeObject && activeObject.type === 'image') {   //check if object is image
        //update the opacity of the image
        activeObject.opacity = $(elm).val();
        canvas.renderAll();  //update the canvas
    }
    var canvasSideObject = [];
    canvasSideObject = getcanvasSidesObject();
}

/*
 * function to apply filter on the image
 * 
 * @param {type} index
 * @param {type} filter
 */
function applyFilter(index, filter) {
    var obj = canvas.getActiveObject();
    obj.filters[index] = filter;
    obj.applyFilters(canvas.renderAll.bind(canvas));
}

$(document).ready(function () {

    $(document).on('click', '.kb-save-pc-customize-btn', function (event) {
        savePCProductCustomization();
    });

    /*
     * Convert the product images to fabric canvas
     */
    $('[id^=kb-canvas-pc-block]').each(function () {
        var layer_div_class = 'kb-canvas-layer-' + kb_layer_count;
        if ($('.' + layer_div_class).find($('.kbtcanvas')).length) {
            //Convert the image to canvas
            canvas = new fabric.Canvas('tcanvas_' + kb_layer_count, {
                hoverCursor: 'pointer',
                selection: true,
                selectionBorderColor: 'blue'
            });
            //setup front side canvas and callback functions
            canvas.on({
                'object:moving': function (e) {
                },
                'object:added': function (e) {
                },
                'object:modified': function (e) {
                },
                'object:scaling': onSelectedScaling,
                'object:selected': onObjectSelected,
                'selection:cleared': onSelectedCleared,
            });
            // piggyback on `canvas.findTarget`, to fire "object:over" and "object:out" events
            canvas.findTarget = (function (originalFn) {
                return function () {
                    var target = originalFn.apply(this, arguments);
                    if (target) {
                        if (this._hoveredTarget !== target) {
                            canvas[kb_layer_count].fire('object:over', {target: target});
                            if (this._hoveredTarget) {
                                canvas[kb_layer_count].fire('object:out', {target: this._hoveredTarget});
                            }
                            this._hoveredTarget = target;
                        }
                    } else if (this._hoveredTarget) {
                        canvas[kb_layer_count].fire('object:out', {target: this._hoveredTarget});
                        this._hoveredTarget = null;
                    }
                    return target;
                };
            })(canvas.findTarget);

            canvas.on('object:over', function (e) {
            });

            canvas.on('object:out', function (e) {
            });
            kb_canvas[kb_layer_count] = canvas;
        }
        kb_layer_count++;
    });

    //add text on canvas
    $('.text-upload-btn').click(function () {
        processAddPCText();

    });

    $(document).on('click', '.close-edit-circle', function () {
        $('.upload-custom-text').show();
        $('.upload-custom-image').addClass('active');
        $('.custom-text-editing').hide();
        var canvasSideObject = [];
        canvasSideObject = getcanvasSidesObject();
    });

    //download customized image
    $('#downloadKbPCObj').click(function () {
        processDownloadCustomize();

    });

    //print customize image
    $('#printKbPCObj').click(function () {
        processPrintCustomize();

    });

    //undo the object changes
    $('#undoKbCanvas').click(function () {
        processUndoCustomize();
    });

    //redo the object changes
    $('#redoKbCanvas').click(function () {
        processRedoCustomize();
    });

    //empty the canvas
    $('#emptyKbCanvas').click(function () {
        processEmptyCanvas();
    });

    //update the text
    $("#upload-text").keyup(function () {
        processUpdatePCText(this);
    });

    //add QR code on canvas
    $('.kb-add-qrcode-product').click(function (e) {
        processAddQRCode();
    });

    //add gallery image on canvas
    $(".kb-img-pc-addproduct").click(function (e) {
        processAddGalleryImage(e, this);
    });

    //remove the selected object
    $('#remove-selected').click(function () {
        processPCRemoveSelected();
    });

    //bring the object to front
    $('#bring-to-front').click(function () {
        processBrintToFront();
    });

    //send the object to the back
    $('#send-to-back').click(function () {
        processSendToBack();
    });

    //make the text bold
    $("#textBold").click(function () {
        processPCTextBold(this);
    });

    //make the text italic
    $("#textItalic").click(function () {
        processPCTextItalic(this);
    });

    //make the text strike
    $("#textStrike").click(function () {
        processPCTextStrike(this);
    });

    //apply the underline on the text
    $("#textUnderline").click(function () {
        processPCTextUnderLine(this);
    });

    //update the font family
    $("#input-font").change(function () {
        processPCTextFont(this);
    });

    //update the font size
    $("#font-size").change(function () {
        processPCTextSize(this);
    });

    //update the line height
    $("#line-height").change(function () {
        processPCTextLineHeight(this);
    });

    //align the text on left
    $('#alignleft').click(function () {
        processPCAlignLeft(this);
    });

    //align the text on right
    $('#alignright').click(function () {
        processPCAlighRight(this);
    });

    //align the text on center
    $('#aligncenter').click(function () {
        processPCAlignCenter(this);

    });

    //align the text to justify
    $('#alignjustify').click(function () {
        processPCAlignJustify(this);
    });

    //update the text color
    $('#text_color').change(function () {
        processPCtextColor(this);
    });

    //update the opacity of the text
    $('#text-opacity-slider').on("change mousemove", function () {
        processPCTextOpacity(this);
    });

    //update the opacity of the image
    $('#opacity-slider').on("change mousemove", function () {
        processImageOpacity(this);
    });

    //convert the text into curved text
    $('.kb-curved-switch input').click(function ()
    {
        $(this).parents('.checker').toggleClass('inputChecked');
        if ($(this).is(':checked')) {
            $('.kb-curved-text-tool').show();
        } else {
            $('.kb-curved-text-tool').hide();
        }
        renderCurveText($(this));
    });

    //apply grayscale filter on the image
    $('#grayscale').change(function () {
        applyFilter(0, $(this).is(':checked') && new fabric.Image.filters.Grayscale());
        var canvasSideObject = [];
        canvasSideObject = getcanvasSidesObject();
    });

    //apply invert filter on the image
    $('#invert').change(function () {
        applyFilter(1, $(this).is(':checked') && new fabric.Image.filters.Invert());
        var canvasSideObject = [];
        canvasSideObject = getcanvasSidesObject();
    });

    //apply sepia filter on the image
    $('#sepia').change(function () {
        applyFilter(2, $(this).is(':checked') && new fabric.Image.filters.Sepia());
        var canvasSideObject = [];
        canvasSideObject = getcanvasSidesObject();
    });

    //apply emboss filter on the image
    $('#emboss').change(function () {
        applyFilter(3, $(this).is(':checked') && new fabric.Image.filters.Convolute({
            matrix: [1, 1, 1,
                1, 0.7, -1,
                -1, -1, -1]
        }));
        var canvasSideObject = [];
        canvasSideObject = getcanvasSidesObject();
    });

    //apply sharpen filter on the image
    $('#sharpen').change(function () {
        applyFilter(4, $(this).is(':checked') && new fabric.Image.filters.Convolute({
            matrix: [0, -1, 0,
                -1, 5, -1,
                0, -1, 0]
        }));
        var canvasSideObject = [];
        canvasSideObject = getcanvasSidesObject();
    });
    //apply blur filter on the image
    $('#blur').change(function () {
        applyFilter(5, $(this).is(':checked') && new fabric.Image.filters.Convolute({
            matrix: [1 / 9, 1 / 9, 1 / 9,
                1 / 9, 1 / 9, 1 / 9,
                1 / 9, 1 / 9, 1 / 9]
        }));
        var canvasSideObject = [];
        canvasSideObject = getcanvasSidesObject();
    });


    //close the customization block
    $('.close-circle span').click(function ()
    {
        $('.image-custom-editing').hide();
        $('#kbupload').addClass('active');
        $('#kbimages').removeClass('active');
        $('#kbqrcode').removeClass('active');
        $('#menu1').removeClass('active');
        var canvasSideObject = [];
        canvasSideObject = getcanvasSidesObject();
    });

    //update the radius of the curved text
    $('#curved-text-radius').on("change mousemove", function () {
        var activeObject = canvas.getActiveObject();
        if (activeObject && activeObject.type === 'curvedText') {  //check if the object is curvedtext
            activeObject.set('radius', this.value); //set the radius of the text
            canvas.renderAll();  //update the canvas
        }
        var canvasSideObject = [];
        canvasSideObject = getcanvasSidesObject();
    });

    //update the text spacing on the curved text
    $('#curved-text-spacing').on("change mousemove", function () {
        var activeObject = canvas.getActiveObject();
        if (activeObject && activeObject.type === 'curvedText') { //check if the object is curvedtext
            activeObject.set('spacing', this.value);    //set the spacing on the text
            canvas.renderAll();
        }
        var canvasSideObject = [];
        canvasSideObject = getcanvasSidesObject();
    });

    //update the background color on the text
    $('#text_bgcolor').change(function () {
        var activeObject = canvas.getActiveObject();    //get the selected object on the canvas
        if (activeObject) {
            if (activeObject.type === 'text') {  //check if object is text
                activeObject.textBackgroundColor = '#' + this.value;  //set background color of text
            } else if (activeObject.type === 'curvedText') {     //check if the object is curvedtext
                activeObject.set('textBackgroundColor', '#' + this.value);  //set background color of text
            }
        }
        canvas.renderAll();  //update the canvas
        var canvasSideObject = [];
        canvasSideObject = getcanvasSidesObject();
    });

    //remove the background color on text
    $('#remove_font_bk').on('click', function () {
        var activeObject = canvas.getActiveObject();    //get the selected object on the canvas
        if (activeObject && activeObject.type === 'text') {  //check if object is text
            activeObject.textBackgroundColor = 'transparent'; //set the background color to Transparent
            canvas.renderAll();
        }
        var canvasSideObject = [];
        canvasSideObject = getcanvasSidesObject();
    });

    $("#drawingArea").hover(
            function () {
                canvas.renderAll();
            }
    );

    //Add the background color on the canvas
    $('.kb-color-item').click(function () {
        var color = $(this).data('value');
        canvas.setBackgroundColor(color, canvas.renderAll.bind(canvas)); //set the background color on the canvas
        canvas.renderAll();
        if (color != '#ffffff') {
            kb_color_add = 1;
        } else {
            kb_color_add = 0;
        }
        var canvasSideObject = [];
        canvasSideObject = getcanvasSidesObject();
    });
});


function getRandomNum(min, max) {
    return Math.random() * (max - min) + min;
}

/*
 * Callback function on object scaling
 */
function onSelectedScaling(e)
{
    var selectedObject = e.target;
    if (selectedObject.type === 'image') {
        kb_cost = selectedObject.cost;
        kb_cust_cost = kb_cust_cost - parseFloat(selectedObject.cost);
        kb_cust_cost = kb_cust_cost + parseFloat(kb_cost);
        selectedObject.cost = kb_cost;
    }
    kbupdateCustPrice();
}

/*
 * Callback function on object selected
 */
function onObjectSelected(e) {
    var selectedObject = e.target;
    $("#upload-text").val("");
    $("#upload-qr-text").val("");
    $("#uploaded-text").val("");
    var has_rotate = true;
    if (typeof kb_cp_rotate != 'undefined' && !kb_cp_rotate) {
        has_rotate = false;
    }

    /*
     * lock the scaling if resize is disabled 
     */
    if (typeof kb_cp_resize != 'undefined' && !kb_cp_resize) {
        selectedObject.lockScalingX = true;
        selectedObject.lockScalingY = true;
    }

    /*
     * update the rotating point on the object if it is disabled
     */
    selectedObject.hasRotatingPoint = has_rotate;
    //persistent the form data
    if (selectedObject &&
            (selectedObject.type === 'text' ||
                    selectedObject.type === 'curvedText')) {
        $('.upload-custom-text').hide();
        if ($('#product-feature ul.nav').find('li a.kb_change_text').length) {
            $('#product-feature ul.nav').find('li').removeClass('active');
            $('#product-feature ul.nav').find('li a.kb_change_text').parent().addClass('active');
        }
        if ($('#product-feature .tab-content #menu1').length) {
            $('#product-feature div.tab-content').find('.tab-pane').removeClass('active');
            $('#product-feature .tab-content #menu1').addClass('active');
        }
        $(".custom-text-editing").show();
        $("#upload-text").val(selectedObject.getText()); //add the text in the text field
        $('#text_color').val(selectedObject.fill); //set the color
        $('#text-opacity-slider').val(selectedObject.opacity); //set the opacity
        $('#input-font').val(selectedObject.fontFamily); //set the font-family
        $('#font-size').val(selectedObject.fontSize); //set the font size
        $('#line-height').val(selectedObject.lineHeight); //set the line height
        //active/inactive the align toggle button
        if (selectedObject.textAlign != 'undefined') {
            $('#align' + selectedObject.textAlign).addClass('btn-active');
            $('#align' + selectedObject.textAlign).siblings('.btn-align').removeClass('btn-active');
        }
        //remove the active class from the font style
        $('#textBold').removeClass('btn-active');
        $('#textItalic').removeClass('btn-active');
        $('#textStrike').removeClass('btn-active');
        $('#textUnderline').removeClass('btn-active');

        //set the font style
        if (selectedObject.fontWeight == 'bold') {
            $('#textBold').addClass('btn-active');
        }
        if (selectedObject.fontStyle == 'italic') {
            $('#textItalic').toggleClass('btn-active');
        }
        if (selectedObject.textDecoration == 'line-through') {
            $('#textStrike').toggleClass('btn-active');
        }
        if (selectedObject.textDecoration == 'underline') {
            $('#textUnderline').toggleClass('btn-active');
        }
        //display the tool editor
        $("#kb-editor-tool").show();
        //display the opacity slider
        $('#text-opacity-slider').closest('.form-group').show();
        if (selectedObject.type === 'curvedText') {
            if (typeof selectedObject.radius != 'undefined') {
                $('#curved-text-radius').val(selectedObject.radius); //set the radius
            }
            if (typeof selectedObject.spacing != 'undefined') {
                $('#curved-text-spacing').val(selectedObject.spacing); //set the spacing
            }
            $('#text-opacity-slider').closest('.form-group').hide();
        }
    } else if (selectedObject && selectedObject.type === 'image') {
        $("#kb-editor-tool").show();
        $('.image-custom-editing').show();
        $('#opacity-slider').val(selectedObject.opacity);
        $('#kbimages').removeClass('active');
        $('#kbupload').removeClass('active');
        $('#kbqrcode').removeClass('active');
        if ($('#product-feature ul.nav').find('li a.kb_change_home').length) {
            $('#product-feature ul.nav').find('li').removeClass('active');
            $('#product-feature ul.nav').find('li a.kb_change_home').parent().addClass('active');
        }
        if ($('#product-feature .tab-content #home').length) {
            $('#product-feature div.tab-content').find('.tab-pane').removeClass('active');
            $('#product-feature .tab-content #home').addClass('active');
        }

        //checked the filters
        $('#menu1').removeClass('active');
        var filters = ['grayscale', 'invert', 'sepia',
            'emboss', 'sharpen', 'blur'];
        if ($('.color-filters input[type="checkbox"]').length) {
            $('.color-filters input[type="checkbox"]').prop('checked', false);
            $('.color-filters input[type="checkbox"]').closest('span').removeClass('checked');
            $('.color-filters input[type="checkbox"]').closest('checker').removeClass('focus');
            for (var i = 0; i < filters.length; i++) {
                if ($('.color-filters #' + filters[i]).length &&
                        (typeof canvas.getActiveObject().filters[i] != 'undefined' && canvas.getActiveObject().filters[i])) {
                    $('.color-filters #' + filters[i]).prop('checked', true);
                    $('.color-filters #' + filters[i]).closest('span').addClass('checked');  //if filter is used by object then mark as checked
                }
            }
        }
    }
    var canvasSideObject = [];
    canvasSideObject = getcanvasSidesObject();
}

/*
 * Callback function used when object is deselected
 */
function onSelectedCleared(e) {
    var selectedObject = e.target;
    $("#upload-text").val("");
    $("#upload-qr-text").val("");
    $("#uploaded-text").val("");
    $("#kb-editor-tool").hide();
    $('#kbcm-container-block #product-feature ul.nav').find('li:first').addClass('active').siblings().removeClass('active');
    $('#kbcm-container-block #product-feature div.tab-content').find('.tab-pane:first').addClass('active').siblings().removeClass('active');
    //remove the checked class from the checkbox
    if ($('.color-filters input[type="checkbox"]').length) {
        $('.color-filters input[type="checkbox"]').prop('checked', false);
        $('.color-filters input[type="checkbox"]').closest('span').removeClass('checked');
        $('.color-filters input[type="checkbox"]').closest('checker').removeClass('focus');
    }

    var canvasSideObject = [];
    canvasSideObject = getcanvasSidesObject();
}

function removeWhite() {
    var activeObject = canvas.getActiveObject();
    if (activeObject && activeObject.type === 'image') {
        activeObject.filters[2] = new fabric.Image.filters.RemoveWhite({hreshold: 100, distance: 10});//0-255, 0-255
        activeObject.applyFilters(canvas.renderAll.bind(canvas));
    }
}

/*
 * function to toggle text into Curve text
 */
function renderCurveText(elm)
{
    var props = {};
    var obj = canvas.getActiveObject();    //get the selected object on the canvas
    var data = '';
    if (obj) {
        if (/curvedText/.test(obj.type)) { //check if object is curved text
            var default_text = obj.getText();
            props = obj.toObject();
            delete props['type'];
            var newProp = kbmerge_prop(props, {});
            var data = new fabric.Text(default_text, newProp);  //convert the curved text into plain text
        } else if (/text/.test(obj.type)) {   //check if object is plain text
            var default_text = obj.getText();
            props = obj.toObject();
            delete props['type'];
            var newProp = kbmerge_prop(props, {});
            var data = new fabric.CurvedText(default_text, newProp);  //convert the plain text into curved text
        }
        //checkbox is unchecked then 
        //convert curved text into plain text
        if (!elm.is(':checked')) {
            var default_text = obj.getText();
            props = obj.toObject();
            delete props['type'];
            var newProp = kbmerge_prop(props, {});
            var data = new fabric.Text(default_text, newProp); //convert the curved text into plain text
        }
        canvas.remove(obj); //remove the previous object
        canvas.add(data).renderAll(); //add the converted object to the canvas
        canvas.setActiveObject(canvas.item(canvas.getObjects().length - 1)); //set the converted object as selected 
    }
    var canvasSideObject = [];
    canvasSideObject = getcanvasSidesObject();
}

//merge the object
function kbmerge_prop(obj1, obj2)
{
    for (var p in obj2) {
        try {
            if (obj2[p].constructor == Object) {
                obj1[p] = MergeRecursive(obj1[p], obj2[p]);
            } else {
                obj1[p] = obj2[p];
            }
        } catch (e) {
            obj1[p] = obj2[p];
        }
    }
    return obj1;
}

/*
 * function to add the product images on the fabric canvas
 * 
 * @param {type} src
 * @param {type} kb_canvas_side
 * @param {type} kbclick
 */
function addCanvasToBackground(src, kb_canvas_side, kbclick)
{
    if ($(document).width() > 600) {
        canvas.width = '530';
        canvas.height = '550';
        $(".canvas-container").css("height", "550px");

    } else {
        canvas.width = $(document).width() - 25;
        canvas.height = ($(document).width() - 25) * (530 / 550);
        $('.canvas-container-outer').css('width', "100%");
        $("#customizeProductFacing").css("height", canvas.height + "px");
        $("#customizeProductFacing").css("width", canvas.width + "px");
        $(".canvas-container").css("height", canvas.height + "px");
    }

    canvas = kb_canvas[kb_canvas_side];
    if (typeof counter == 'undefined') {
        counter = 1;
    }

    var ctx = canvas.getContext("2d"); //
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    /*
     * Add the product image on the canvas overlay
     */
    fabric.Image.fromURL(src, function (img) {
        var canvasheight = (canvas.width / img.width) * img.height;
        var canvaswidth = canvas.width;
        if (canvasheight > 550) {
            canvasheight = 550;
            canvaswidth = (canvasheight / img.height) * img.width;
        } else {
            canvaswidth = canvas.width;
            canvasheight = canvas.height;
        }

        canvas.setHeight(canvasheight);
        canvas.setWidth(canvaswidth);

        var center = canvas.getCenter(); //get the center of the canvas
        /*
         * Add the product image on the canvas overlay
         */
        img.set({width: canvaswidth, height: canvasheight, originX: 'left', originY: 'top'});
        //BOC changed by Shivam Bansal for adding the image transparency status
        if(transparency) {
            canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas), {
                scaleX: 1,
                scaleY: 1,
                top: center.top,
                left: center.left,
                originX: 'center',
                originY: 'center',
                width: img.width, //set width of the canvas product
                height: img.height, //set height of the canvas product
                backgroundImageStretch: false,
            });
        } else {
            canvas.setOverlayImage(img, canvas.renderAll.bind(canvas), {
                scaleX: 1,
                scaleY: 1,
                top: center.top,
                left: center.left,
                originX: 'center',
                originY: 'center',
                width: img.width, //set width of the canvas product
                height: img.height, //set height of the canvas product
                backgroundImageStretch: false,
            });
        }
        //EOC changed by Shivam Bansal for adding the image transparency status
        if (kbclick != 1) {
            canvas.backgroundColor = "#FFFFFF"; //set background color of the canvas product
        }

        $('.canvas-container-outer').css('width', canvaswidth);
        canvas.renderAll(); //render the canvas
        kbCanvasState = [];
        kbCurrentStateIndex = -1;
        kbUndoStatus = false;
        kbRedoStatus = false;
        kbUndoFinishedStatus = 1;
        kbRedoFinishedStatus = 1;

        $('[id^=kb-canvas-pc-block]').each(function () {
            $(this).hide();
        });
        $('.kb-canvas-layer-' + kb_canvas_side).show();
    });
}

var layerKey = null;
var designedLayers = {};
var activeLayer = 0;

/*
 * function to add the product image
 * on the canvas on clicking the preview image
 * 
 * @param {type} elm
 * @param {type} counter
 */
function loadKbProductLayer(elm, counter)
{
    image = elm.attr('src');
    layerKey = (typeof layerKey == "undefined") ? null : layerKey;
    kb_canvas_side = counter;
    if (layerKey != null) {
        designedLayers[activeLayer] = getAllCanvasJSON();
        if (designedLayers[layerKey] != null) {
            kb_canvas[kb_canvas_side].loadFromJSON(designedLayers[layerKey], function () {});  //get the canvas data in the JSON format
            canvasSideObject = getcanvasSidesObject(); //get all the object applied on the canvas
        } else {
            /*
             * add image on the canvas if click on the another preview image
             */
            addCanvasToBackground(image, kb_canvas_side, 1);
            canvasSideObject = getcanvasSidesObject();
        }
        activeLayer = layerKey;
    }

    if (layerKey == null) {
        setTimeout(function () {
            /*
             * add image on the canvas if click on the another preview image
             */
            addCanvasToBackground(image, kb_canvas_side, 1);
        }, 500);
    }
}

/*
 * function to get the canvas
 *  data in the JSON format
 * 
 * @returns JSON data
 */
function getAllCanvasJSON()
{
    kb_canvas[kb_canvas_side].deactivateAll().renderAll();
    return kb_canvas[kb_canvas_side].toJSON();
}

/*
 * function to return the array having
 * all the object applied on the current canvas
 * 
 * @returns {Array}
 */
function getcanvasSidesObject()
{
    var sideObject = [];
    $.each(kb_canvas[kb_canvas_side].getObjects(), function (index, value) {
        sideObject.push({"id": kb_side_object + " " + (index + 1), "src": value.toDataURL(), "object": value});
    });
    return sideObject.reverse();
}

/*
 * function to calculate the cost 
 * of the text added on the canvas
 * 
 * @param {type} font
 * @returns {Number|kb_text_fixed_price}
 */
function getFontCost(font)
{
    var text_price = 0;
    text_price = kb_text_fixed_price;
    if (kb_enable_cost_character) {
        var font_length = font.val().replace(/ /g, '').length;
        text_price = text_price * font_length;
    }
    return text_price;
}