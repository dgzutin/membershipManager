/**
 * Created by garbi on 14.12.16.
 */

api_version = "v1";

function membershipQuickRenew()
{
    var params = {
        membershipId: $('#membershipId').val()
    };

    $.ajax({url: window.location.protocol + "//" + window.location.host + "/api/"+api_version+"/membershipQuickRenew",
        data: JSON.stringify(params),
        type: 'POST',
        success: function(result){

            console.log(result);
            if( result.exception == false){
                $("#validUntil").html(result.validUntil);
                $("#validUntil").notify(
                                    result.message,
                                    {
                                        position:"top center",
                                        className: "success" }
                );
            }
            else{
                $("#validUntil").notify(
                      result.message,
                      {
                          position:"top center",
                          className: "warn" }
                );
            }

        }});
}

function deleteValidities(ids)
{
    var params = {
        ids: ids
    };

    $.ajax({url: window.location.protocol + "//" + window.location.host + "/api/"+api_version+"/deleteValidities",
        data: JSON.stringify(params),
        type: 'POST',
        success: function(result){


            for (i=0; i<result.results.length; i++) {

                console.log(result.results[i]);

                if( result.results[i].exception == false){
                    $("#validUntil").notify(
                        result.results[i].message,
                        {
                            position:"top center",
                            className: "success" }
                    );
                    //delete table row
                    $('#tableRow_'+result.results[i].validityId).remove();

                }
                else{
                    $("#validUntil").notify(
                        result.results[i].message,
                        {
                            position:"top center",
                            className: "warn" }
                    );
                }

            }
            // location.reload();


        }});
}

function renewMembership()
{
    var params = {
        membershipId:  $('#membershipId').val(),
        from:  $('#from').val(),
        until:  $('#until').val()

    };

    $.ajax({url: window.location.protocol + "//" + window.location.host + "/api/"+api_version+"/renewMembership",
        data: JSON.stringify(params),
        type: 'POST',
        success: function(result){

            console.log(result);

            if( result.exception == false){
                $("#validUntil").notify(
                    result.message,
                    {
                        position:"top center",
                        className: "success" }
                );
                //add table row
                $('#renewals').append('<tr class="" id="tableRow_'+result.id+'">' +
                    '<td><input type="checkbox" id="checkbox_'+result.id+'"></td>' +
                    '<td>'+result.id+'</td>' +
                    '<td>'+result.validFrom+'</td>' +
                    '<td><div id="validUntil_'+result.id+'">'+result.validUntil+'</div></td>' +
                    '<td>'+result.dateCreated+'</td>' +
                    '<td><a href="#" onclick="deleteValidities(['+result.id+'])"><span class="fa fa-trash-o"> </span></a></td>' +
                    ' </tr>');
            }
            else{
                $("#validUntil").notify(
                    result.message,
                    {
                        position:"top center",
                        className: "warning" }
                );
            }

        }});

}