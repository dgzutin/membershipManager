/**
 * Created by garbi on 14.12.16.
 * Manages the bulk e-mail operation in the browser
 */


// =============   Event Listeners ====================================
$("#checkbox_all").change(function () {
    $("input:checkbox").prop('checked', $(this).prop("checked"));

    for (i=0; i<members.length; i++){
        members[i].selected = $(this).prop("checked");
    }
});

//Send emails
$("#button_verify_recipients").click(function(){

    getFilteredMembers();
});

$("#button_sendBulkMailMembers").click(function(){

    $("#button_sendBulkMailMembers").prop('disabled', true);
    sendBulkEmailsMembers(prepareMembersListToSend());
});
//Send newsletters
$("#button_sendNewsletterMembers").click(function(){

    $("#button_sendNewsletterMembers").prop('disabled', true);
    sendNewsletterToMembers(prepareMembersListToSend());

});

//========================================================================


function prepareMembersListToSend()
{
    if (members == null){
        notify('alert-danger', 'No members selected');
    }
    for (i=0; i<members.length; i++){
        $('#checkbox_'+i).prop('disabled', true);
    }
    $("#button_sendMail").prop('disabled', true);
    $("#checkbox_all").prop('disabled', true);
    //===========================================

    //Notify user
    notify('alert-info', 'Sending e-mails. <strong>Do not close this window. </strong> This operation might take a while... <i class="fa fa-refresh fa-spin" style="font-size:24px"></i>');

    var membersSend = [];
    var j = 0;
    var k = 1;

    for (i = 0; i<members.length; i++){

        if (members[i].selected == true){

            membersSend[j] = members[i];
            j++;
            if (k % 2 == 0){
                /*
                 console.log('Sending: ');
                 console.log(userIds);
                 sendBulkEmails(userIds);
                 userIds = [];
                 j = 0;
                 */
            }
            k++;
        }
    }
    return membersSend;
}

function getFilteredMembers()
{
    var filter = {
        membershipTypeId: $('#membershipTypeId').val(),
        membershipGrade: $('#membershipGrade').val(),
        validity: $('#validity').val(),
        country: $('#country').val()
    };

    $.ajax({url: window.location.protocol + "//" + window.location.host + "/api/v1/getFilteredMembers",
        data: JSON.stringify(filter),
        type: 'POST',
        success: function(result){
            //console.log(JSON.stringify(result[0]));
            members = result.members;
            console.log(result);
            fillTableMembers('recipients', members);
            

            // Set return button to visible
            //===============================================
            $('#button_return').css('visibility', 'visible');
            //===============================================
            $('#button_verify_recipients').prop('disabled', true);
            $('#button_sendBulkMailMembers').prop('disabled', false);
            $('#button_sendNewsletterMembers').prop('disabled', false);
        }});

}

function fillTableMembers(tableId, members)
{
    var table = document.getElementById(tableId).getElementsByTagName('tbody')[0];

    if (members != null){

        for (i=0; i<members.length; i++) {

            members[i].selected = true;

            userId = members[i].user.id;
            memberId = members[i].membership.memberId;
            country = members[i].user.country;
            membershipId = members[i].membership.id;
            type = members[i].membershipTypeName;
            prefix = members[i].membershipTypePrefix;
            expiryDate = members[i].validity_string;
            name = members[i].user.last_name + ', ' + members[i].user.first_name;
            email_1 = members[i].user.email_1;

            var row = table.insertRow(table.rows.length); //can start at the beginning, argument = 0

            row.insertCell(0).innerHTML = '<input type="checkbox" id="checkbox_'+i+'" checked onchange="updateSelectedUser('+i+')" class="select_recipient">';
            row.insertCell(1).innerHTML = memberId+' ('+prefix+')';
            row.insertCell(2).innerHTML = country;
            row.insertCell(3).innerHTML = expiryDate;
            row.insertCell(4).innerHTML = name;
            row.insertCell(5).innerHTML = '<div id="message_'+membershipId+'">'+email_1+'</div>';
            // row.insertCell(5).innerHTML = '<div id="message_'+membershipId+'"></div>';
        }
    }
}

function updateSelectedUser(updateIndex)
{

    members[updateIndex].selected = $('#checkbox_'+updateIndex).prop("checked");
    console.log('Item '+updateIndex+' select was set to '+members[updateIndex].selected);

}

function sendBulkEmailsMembers(membersSend)
{
    console.log('Sending mails now... ');
    var request = {
        members : membersSend,
        mailSubject: $('#subject').val(),
        mailBody: $('#emailBody').val(),
        replyTo: $('#replyTo').val()
    };

    console.log('Request: ');
    //console.log(JSON.stringify(request));

    $.ajax({url:window.location.protocol + "//" + window.location.host + "/api/v1/sendBulkMailMembers",
        data: JSON.stringify(request),
        type: 'POST',
        success: function(responseJson){

            processSendMailResults(responseJson);
        }});
}

function sendNewsletterToMembers(membersSend)
{
    console.log('Sending mails now... ');
    var request = {
        members : membersSend,
        mailSubject: $("#subject").val(),
        key: $("#publicKey").val(),
        replyTo: $("#replyTo").val()
    };

     console.log('Request: ');
     console.log(JSON.stringify(request));

    $.ajax({url:window.location.protocol + "//" + window.location.host + "/api/v1/sendNewsletter",
        data: JSON.stringify(request),
        type: 'POST',
        success: function(responseJson){

            processSendMailResults(responseJson);
        }});
}



function processSendMailResults(responseJson)
{
    console.log('Response:');
    console.log(responseJson);
    //var responseJson = JSON.parse(response);
    var resultJson = responseJson.results;

    var n = 0;
    if (responseJson.exception == false){

        if (resultJson != null){

            for (i=0; i<resultJson.length; i++){
                //console.log(result[i].userId);
                if (resultJson[i].exception == true){
                    document.getElementById('message_'+resultJson[i].membershipId).innerHTML = '<p class="text-danger">'+resultJson[i].message+'</p>';
                }
                else{
                    document.getElementById('message_'+resultJson[i].membershipId).innerHTML = '<p class="text-success">'+resultJson[i].message+'</p>';
                    n++;
                }

                $("#message").html('Process completed. <strong>'+n+' e-mail(s) sent.</strong>');
                // Set return button to visible
                //===============================================
                $('#button_return').css('visibility', 'visible');
                //===============================================
            }
        }
        console.log('Result: ');
        console.log(resultJson);
    }
    else{

        notify('alert-danger', responseJson.message);
        // Set return button to visible
        //===============================================
        $('#button_return').css('visibility', 'visible');
        //===============================================
    }
}