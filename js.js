/**
 * Created by jamesskywalker on 31/03/2020.
 */


function ajaxRequest(method,call,response,data,file) {
    var xmlhttp;
    if (window.XMLHttpRequest)  {
        xmlhttp=new XMLHttpRequest();
    } else {
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange=function() {
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            if(xmlhttp.responseText != '') eval(response + "('"+xmlhttp.responseText+"')");
        }
    }
    var requested = new Date().getTime();
    xmlhttp.open(method,"/quizatthedicks/ajax.php?endpoint="+call+"&data="+encodeURIComponent(data)+"&requested="+requested,true);
    if(file) {
        xmlhttp.send(file);
    } else {
        xmlhttp.send();
    }
}


function markAnswer(quizId, teamId, answerId, correct) {
    var data = JSON.stringify({quizId:quizId,teamId:teamId,answerId:answerId,correct:correct});

    ajaxRequest('GET','mark_answer','answerMarked',data);
}

function answerMarked(json) {
    var response = JSON.parse(json);


    $('#' + response.team + '_total_score').html(response.total);

    $('#' + response.answer + '_correct, #' + response.answer + '_incorrect').removeClass('active');

    if(response.correct) {
        $('#' + response.answer + '_correct').addClass('active');
    } else {
        $('#' + response.answer + '_incorrect').addClass('active');
    }


}