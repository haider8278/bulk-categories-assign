function inArray(needle, haystack) {
    var length = haystack.length;
    for (var i = 0; i < length; i++) {
        var cs = jQuery.trim(haystack[i]);
        //console.log(cs + "=>" + needle);
        if (cs == needle) {
            console.log(cs + "=>" + needle);
            return true;
        }
    }
    return false;
}
jQuery(document).ready(function($) {
    var admin_url = $("#admin_url").val();
    var ajaxurl = admin_url + '/admin-ajax.php';
    $('#upload-btn').click(function(e) {
        e.preventDefault();
        var image = wp.media({
                title: 'Upload Image',
                // mutiple: true if you want to upload multiple files at once
                multiple: false
            }).open()
            .on('select', function(e) {
                // This will return the selected image from the Media Uploader, the result is an object
                var uploaded_image = image.state().get('selection').first();
                // We convert uploaded_image to a JSON object to make accessing it easier
                // Output to the console uploaded_image
                console.log(uploaded_image);
                var image_url = uploaded_image.toJSON().url;
                // Let's assign the url value to the input field
                //$('#image_url').val(image_url);
                $("#selectable").append('<li class="ui-state-default ui-selectee"><img data-src="' + image_url + '" src="<?php echo BCA_URL;?>/timthumb.php?src=' + image_url + '&w=150&h=150" class="ui-selectee"><span style="display: none;" class="ui-selectee"></span></li>');
            })
    });

    $(".cat-checkbox").on('click', function() {
        //alert('here');
        var cates = $(this).attr("data-parents");
        var cat_arrs = Array();
        cat_arrs = cates.split(",");
        //console.log(cat_arrs);
        if ($(this).is(":checked")) {
            $.each(cat_arrs, function(index, element) {
                //alert("#in-category-"+element);
                //$("#in-category-"+element).click();
                //$("#in-category-"+element).attr("checked","checked");  /////////// uncomment to select parent categories
            });
        } else {
            $.each(cat_arrs, function(index, element) {
                //alert("#in-category-"+element);
                //document.getElementById("in-category-"+element).click();
                //$("#in-category-"+element).click();
                //$("#in-category-"+element).attr("checked",false);
            });
        }
    });


    $("#selectable").selectable({
        selected: function(event, ui) {
            var count = $(".ui-selected").length;
            //alert(count);
            if (count == 1) {
                //$(".content-right").html("");
                $(".single").show();
                $(".multiple").hide();
                var img_src = $("li.ui-selected").children("img").attr("data-src");
                var title = $("li.ui-selected").children("img").attr("title");
                var caption = $("li.ui-selected").children("img").attr("data-caption");
                //var desc = $("li.ui-selected").children("img").attr("data-desc");
                var date = $("li.ui-selected").children("img").attr("data-date");
                //var alt = $("li.ui-selected").children("img").attr("alt");
                var name = $("li.ui-selected").children("img").attr("data-title");
                var imgid = $("li.ui-selected").children("img").attr("data-imgid");
                var size = $("li.ui-selected").children("img").attr("data-size");
                var dim = $("li.ui-selected").children("img").attr("data-dim");
                var edit_url = admin_url + '/post.php?post=' + imgid + '&action=edit&image-editor';
                //console.log($("li.ui-selected").children("img").attr("src"));
                $("#thumb").attr("src", img_src);
                $("#imgurl").val(img_src);
                //$("#post_title").val(title);
                $(".filename").text(title);
                $(".uploaded").text(date);
                //$("#alttext").val(alt);
                $("#post_excerpt").val(caption);
                //$("#post_content").val(desc);
                $(".edit-attachment").attr('href', edit_url);
                $(".delete-attachment").attr('data-id', imgid);
                $(".file-size").text(size);
                $(".dimensions").text(dim);
                $("#post_ID").val(imgid);
                var catagories;
                catagories = $("li.ui-selected").children("span").text();
                var cat_arr = Array();
                cat_arr = catagories.split(",");
                //var arr = $.map(catagories, function(el) { return el; });
                //alert(arr);
                console.log(cat_arr);
                var catelements = $("input[name^=post_category]");
                //$('input:checkbox').removeAttr('checked');
                //$("#categorychecklist input:checkbox").removeAttr("checked");
                $.each(catelements, function(index, element) {
                    //this.removeAttribute("checked");
                    if (inArray(this.value, cat_arr)) {
                        this.setAttribute("checked", true);
                        //this.parentNode.style.color='green';
                        //alert(this.value);
                    } else {
                        this.removeAttribute("checked");
                        //this.parentNode.style.color='black';
                        //this.setAttribute("checked", false);
                    }
                });
            } else {
                var $catelements = $("input[name^=post_category]");
                $catelements.each(function(index, element) {
                    this.removeAttribute("checked");
                });
                var $catelements2 = $("input[name^=category_multiple]");
                $catelements2.each(function(index, element) {
                    this.removeAttribute("checked");
                });
                $(".single").hide();
                $(".multiple").show();

            }
        }
    });

    ///////////////////DELETE ATTACHMENT IMAGES /////////////////////
    $(".delete-attachment").on('click', function() {
        var id = $(this).attr('data-id');
        var post_data = {
            'action': 'delete_bca_image',
            'imgId': id
        };
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: post_data,
            success: function(data) {
                //if(data=='success'){
                $(".single").hide();
                $("#attc-" + id).remove();

                //}
            }
        });
    });

    $(".inputs").on('change', function() {
        var id = $("#post_ID").val();
        var key = $(this).attr("id");
        var value = $(this).val();
        var post_data = {
            'action': 'update_bca_image',
            'imgId': id,
            'key': key,
            'value': value
        };

        $(".spinner").show();
        //alert(key);
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: post_data,
            success: function(data) {
                //if(data=='success'){
                $(".spinner").hide();
                switch (key) {
                    case 'post_title':
                        $("#attc-" + id + " img").attr("title", value);
                        break;
                    case 'post_excerpt':
                        $("#attc-" + id + " img").attr("data-caption", value);
                        break;
                    case 'post_content':
                        $("#attc-" + id + " img").attr("data-desc", value);
                        break;
                }

                //}
            }
        });
    });
    ///////////////////// Save catagories for single image
    $("#save_cat").on('click', function() {
        var $form = $("#cat_form");
        if ($("input[name^=post_category]:checked").length < 1) {
            //alert("Please select atleast one category");
            //return false;
        }
        var form_data = $form.serializeArray();
        var id = $("#post_ID").val();
        $("#postImage").val(id);
        var pageno = $("#pageno").val();
        if (pageno == '') {
            pageno = 1;
        }
        var action = admin_url + '/admin.php?page=bca_actions&action=savecat&paged=' + pageno;
        $form.attr("action", action);
        $form.submit();

    });

    //////////////////// Save catagories for multiple images////
    $("#save_cat_mulitple").on('click', function() {
        if ($("input[name^=category_multiple]:checked").length < 1) {
            alert("Please select atleast one category");
            return false;
        }
        var $form = $("#cat_form_multiple");
        var form_data = $form.serializeArray();
        var selected = $(".ui-selected");
        var ids = new Array();
        var imgs = '';
        $.each(selected, function(i) {
            var slices = this.getAttribute("id").split("-");
            imgs += slices[1] + ',';
            ids.push(slices[1]);
        });
        var pageno = $("#pageno").val();
        if (pageno == '') {
            pageno = 1;
        }
        var action = admin_url + '/admin.php?page=bca_actions&action=savecatmultiple&paged=' + pageno;
        $form.attr('action', action);
        $("#multiimages").val(imgs);
        $form.submit();

    });

    /*$("select#cat option").each(function(){
      if ($(this).val() == ""){
        $(this).attr("selected","selected");
        }
    });*/

});