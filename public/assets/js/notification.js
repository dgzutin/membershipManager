/**
 * Created by garbi on 27.01.17.
 */

function notify(type, message)
{
    document.getElementById("message").className = 'alert '+type;
    //$("#message").toggleClass(type);
    $("#message").html('<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>' +
                        '<strong>'+message+'</strong>');
    $("#message").show();
    // Set return button to visible
}
