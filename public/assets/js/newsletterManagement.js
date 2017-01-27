
var imageSrc = $('#imageUrl').val();

var imageCropper = $('.image-editor').cropit({
    imageState: { src: imageSrc }
});

$('#rotate-cw').click(function() {
    imageCropper.cropit('rotateCW');
});
$('#rotate-ccw').click(function() {
    imageCropper.cropit('rotateCCW');
});


function confirmSubmission()
{
    $( "#dialog-confirm" ).dialog({
        resizable: false,
        height: "auto",
        width: 400,
        modal: true,
        buttons: {
            "Save article": function() {

                $( "#article" ).validate();

                //validate form first
                var form_valid = $( "#article" ).valid();
                if (form_valid){

                    var imageData = imageCropper.cropit('export');

                    if (imageData != null){

                        var params = {
                            imageData: imageData,
                            fileName: $('#fileName').val()
                        };
                        // console.log(params);
                        $.ajax({url: window.location.protocol + "//" + window.location.host + "/api-user/v1/saveImage",
                            data: JSON.stringify(params),
                            // data: imageData,
                            type: 'POST',
                            success: function(result){

                                console.log(result);
                                if( result.exception == false){

                                    $('#imageUrl').val(result.url);
                                    $('#fileName').val(result.fileName);

                                    // All tests passed, submit form
                                    $( "#article" ).submit();
                                }
                                else{
                                    $("#notification").notify(
                                        result.message,
                                        {
                                            position:"top left",
                                            className: "warn" }
                                    );
                                }
                            }});
                    }
                    else{
                        $("#image_preview").notify(
                            'Please select an image',
                            {
                                position:"top left",
                                className: "error" }
                        );
                    }
                }//end validation

                $( this ).dialog( "close" );
            },
            Cancel: function() {
                $( this ).dialog( "close" );
            }
        }
    });
}

function assignRemoveArticle(newsletterId, articleIds)
{
    var action = $("#"+articleIds[0]).val();

    var params = {
        assign: action == "true",
        newsletterId: newsletterId,
        articleIds: articleIds
    };

    console.log(params);

    // console.log(params);
    $.ajax({url: window.location.protocol + "//" + window.location.host + "/api/v1/assignArticlesToNewsletter",
        data: JSON.stringify(params),
        // data: imageData,
        type: 'POST',
        success: function(response){

            console.log(response);

            if (response.exception == false){

                if (response.results[0].exception == false){

                    if (response.results[0].assign){
                        $("#tr_"+response.results[0].articleId).addClass('success');
                    }
                    else{
                        $("#tr_"+response.results[0].articleId).removeClass('success');
                    }
                    notify('alert-info', response.results[0].message);

                }
                else{
                    notify('alert-warning', response.results[0].message);
                }
            }
            else{
                notify('alert-warning', response.message);
            }
        }});

}

function deleteArticle(articleId)
{
    var params = {
        articleIds: [articleId]
    };

    console.log(params);

    // console.log(params);
    $.ajax({url: window.location.protocol + "//" + window.location.host + "/api/v1/deleteNewsletterArticle",
        data: JSON.stringify(params),
        // data: imageData,
        type: 'POST',
        success: function(response){

            console.log(response);

            if (response.exception == false){

                if (response.results[0].exception == false){

                    $("#tr_"+response.results[0].articleId).remove();
                    notify('alert-info', response.results[0].message);

                }
                else{
                    notify('alert-warning', response.results[0].message);
                }
            }
            else{
                notify('alert-warning', response.message);
            }
        }});
}

function confirmArticleDelete(id){
    $( "#dialog-confirm_deleteArticle" ).dialog({
        resizable: false,
        height: "auto",
        width: 400,
        modal: true,
        buttons: {
            "Delete article": function() {

                //delete article
                deleteArticle(id);
                $( this ).dialog( "close" );
            },
            Cancel: function() {
                $( this ).dialog( "close" );
            }
        }
    });
}