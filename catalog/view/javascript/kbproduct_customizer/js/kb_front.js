
function kb_tab_change(elm) {
    var link_Id = elm.attr('href');
    elm.parent('li').addClass('active').siblings().removeClass('active');
    $('.tab-content').find('.tab-pane').removeClass('active');
    $('.image-group-type').hide();
    $('.tab-images-block').hide();
    if (link_Id == '#kbimages' || link_Id == '#kbupload' || link_Id == '#kbqrcode') {
        $('.tab-content').find('#home').addClass('active');
        $('.tab-home-block').find('a[href="' + link_Id + '"]').closest('li').addClass('active');
        if (link_Id == '#kbimages') {
            $('.image-group-type').show();
        }
    }
    if (link_Id == '#menu1') {
        $('.upload-custom-text').show();
    }
    if (link_Id == '#home') {
        $('.tab-home-block').find('a[href="#kbimages"]').closest('li').addClass('active');
        $('.tab-content').find('#kbimages').addClass('active');
        $('.image-group-type').show();
    }
    $('.tab-content').find(link_Id).addClass('active');
    $('.image-custom-editing').hide();
    $('.custom-text-editing').hide();
}

/*
 * function to display images
 * when clicking on the Image Category
 * 
 * @param {type} elm
 */
function displayImageGroup(elm)
{
    $('.image-group-type').hide();
    $('.tab-images-block').show();
    $('.kbpc-images-clips #kb-img-grp-value').val(elm.data('value'));
    $('.kbpc-images-clips .mad-select-drop').find("li[data-value='" + elm.data('value') + "']").addClass("selected").siblings("li").removeClass("selected");
    $('.kbpc-images-clips .mad-select').find("li[data-value='" + elm.data('value') + "']").addClass("selected").siblings("li").removeClass("selected");
    $('.tab-group-img-display ul li').removeClass('kbshow');
    $('.tab-group-img-display ul').find('li[data-groupimg="' + elm.data('value') + '"]').addClass('kbshow');
    $('.tab-group-img-display').show();
}

$(document).ready(function ()
{
    if ($('#button-customization-success').length) {
        $("#button-customization-success").click(function () {
            setTimeout(
                    function () {
                        $('#kbcm-container-block').addClass('transform');
                        $($('[id^=kb-canvas-pc-block]').get().reverse()).each(function () {
                            var layer_div_class = $(this).attr('class').split('-');
                            var img = $(this).find('#customizeProductFacing').attr('src');
                            addCanvasToBackground(img, layer_div_class[3], 0);  //add the image into canvas
                        });
                        $('body').css('overflow', 'hidden');
                    }, 1000);
            $('#kbpc-container-popover').css('left', '0%');
            $('#kbpc-container-popover').show(); //display the customization popup
        });
    }
    
    /*
     * close the customization popup if click on close button
     */
    $('.kbpc-close-btn').on('click', function () {
        $('#kbcm-container-block').removeClass('transform');
        setTimeout(function () {
            $('#kbpc-container-popover').css('left', '-100%');
            $('#kbpc-container-popover').hide();    //hide the popup
            $('body').css('overflow', 'scroll');    //scroll to left
        }, 500);
    });

    /*
     * display the image category 
     * if click on the back button
     */
    $('.kbpc-back-to-imggroup').click(function () {
        $('.tab-images-block').hide();
        $('.image-group-type').show();
    });

    var j = 0;
    var m = 0;
    /*
     * Upload the image and apply on the canvas
     */
    if (window.File && window.FileList && window.FileReader) {
        $("#kb_pc_files").on("change", function (e) {
            $('.error-message').remove();
            /*
             * validate the image
             */
            var check_image = velovalidation.checkImage($(this), kb_file_upload_size * 1024, 'KB');
            if (check_image != true) {
                $('#kb_pc_files').val('');
                $('#kbupload').append('<p class="error-message">' + check_image + '</p>');
                return;
            }
            var files = e.target.files,
                    filesLength = files.length;
            for (var i = 0; i < filesLength; i++) {
                var f = files[i]
                var fileReader = new FileReader();
                /*
                 * use the onload function to get the image data
                 */
                fileReader.onload = (function (e) {
                    var file = e.target;
                    var offset = 50;
                    var left = fabric.util.getRandomInt(0 + offset, 200 - offset);
                    var top = fabric.util.getRandomInt(0 + offset, 400 - offset);
                    kb_cust_cost = kb_cust_cost + parseFloat(kb_file_upload_price);
                    kbupdateCustPrice();
                    /*
                     * convert the image into fabric image using uploaded image
                     */
                    fabric.Image.fromURL(e.target.result, function (image) {
                        image.set({
                            left: left,
                            top: top,
                            angle: 0,
                            padding: 10,
                            cornersize: 10,
                            width: 200,
                            height: 200,
                            hasRotatingPoint: false,
                            cost: kb_file_upload_price, //set the price of uploaded image
                            img_type: 'uploaded',    //set the type of image
                        });
                        //add the image on the canvas
                        canvas.add(image);
                    });

                });
                fileReader.readAsDataURL(f);
                j++;
            }
            $('#kb_pc_files').val('');
        });

    } else {
//        alert(kb_file_support_api);
    }
});

/*
 * function to display the image category
 *  dropdown and display the images accordingly
 */
jQuery(function ($) {
    var madSelectHover = 0;
    $(".mad-select").each(function () {
        var $input = $(this).find("input"),
                $ul = $(this).find("> ul"),
                $ulDrop = $ul.clone().addClass("mad-select-drop");
        $(this)
                .append('<i class="icon-angle-down"></i>', $ulDrop)
                .on({
                    hover: function () {
                        madSelectHover ^= 1;
                    },
                    click: function () {
                        $ulDrop.toggleClass("show");
                    }
                });
        $ul.add($ulDrop).find("li[data-value='" + $input.val() + "']").addClass("selected");
        $ulDrop.on("click", "li", function (evt) {
            evt.stopPropagation();
            $input.val($(this).data("value")); // Update hidden input value
            $ul.find("li").eq($(this).index()).add(this).addClass("selected")
                    .siblings("li").removeClass("selected");
            $('.tab-group-img-display ul li').removeClass('kbshow');
            //display the image of selected categroy
            $('.tab-group-img-display ul').find('li[data-groupimg="' + $(this).data("value") + '"]').addClass('kbshow');
        });
        $ul.on("click", function () {
            var liTop = $ulDrop.find("li.selected").position().top;
            $ulDrop.scrollTop(liTop + $ulDrop[0].scrollTop);
        });
    });

    $(document).on("mouseup", function () {
        $(".mad-select-drop").removeClass("show");
    });
});


function formatCurrency(price, currencyFormat, currencySign, currencyBlank)
{
	var blank = '';
	price = parseFloat(price).toFixed(2);
        var i = String(parseInt(price = Math.abs(Number(price) || 0)));
        var j = (j = i.length) > 3 ? j % 3 : 0;
	if (currencyBlank > 0)
		blank = ' ';
	if (currencyFormat == 1){
//		return currencySign + blank + formatNumber(price, priceDisplayPrecision, ',', '.');
            if(currencySign_left != ''){
		return currencySign_left +
                        (j ? i.substr(0, j) + ',' : "") +
                        i.substr(j).replace(/(\.{3})(?=\.)/g, "$1" + ',') +
                        (2 ? '.' + Math.abs(price - i).toFixed(2).slice(2) : "");
            }else{
                return (j ? i.substr(0, j) + ',' : "") +
                        i.substr(j).replace(/(\.{3})(?=\.)/g, "$1" + ',') +
                        (2 ? '.' + Math.abs(price - i).toFixed(2).slice(2) : "") + currencySign_right;
            }
        }
	return price;
}
/*
 * function to calculate the customization price
 *  and total price
 */
var l = 0;
function kbupdateCustPrice()
{
    var c_cost = parseFloat(kb_cust_cost);
    product_cost = 0;
    if (typeof priceWithDiscountsDisplay != 'undefined') {
        product_cost = priceWithDiscountsDisplay;
    }
    var total_cost = c_cost + product_cost;
    $('.cproduct_price').text(formatCurrency(product_cost, currencyFormat, currencySign, currencyBlank));
    $('.custom_price').text(formatCurrency(c_cost, currencyFormat, currencySign, currencyBlank));
    $('.total_custom_price').text(formatCurrency(total_cost, currencyFormat, currencySign, currencyBlank));
}
