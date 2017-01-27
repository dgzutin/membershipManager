
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