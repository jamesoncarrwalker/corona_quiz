/**
 * Created by jamesskywalker on 31/03/2020.
 */

var scoreUpdatesListener = null;
var currentRound = null;
var currentQuiz = null;

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
    };
    var requested = new Date().getTime();
    xmlhttp.open(method,root + "ajax.php?endpoint="+call+"&data="+encodeURIComponent(data)+"&requested="+requested,true);
    if(file) {
        xmlhttp.send(file);
    } else {
        xmlhttp.send();
    }
}


function markAnswer(quizId, teamId, answerId, round,correct) {
    var data = JSON.stringify({quizId:quizId,teamId:teamId,answerId:answerId,correct:correct,round:round});

    ajaxRequest('GET','markAnswer','answerMarked',data);
}

function markAnswerWithPoints(input) {
    var answerId = $(input).data('id');
    var points = $(input).val();
    var quiz = $(input).data('quiz');
    var team = $(input).data('team');
    var round = $(input).data('round');
    var data = JSON.stringify({quizId:quiz,teamId:team,answerId:answerId,points:points,round:round});

    ajaxRequest('GET','markAnswer','answerMarked',data);
}

function markAnswerWithHalf(input) {
    var answerId = $(input).data('id');
    var points = 0.5;
    var quiz = $(input).data('quiz');
    var team = $(input).data('team');
    var round = $(input).data('round');
    var data = JSON.stringify({quizId:quiz,teamId:team,answerId:answerId,points:points,round:round,half:true,correct:true});

    ajaxRequest('GET','markAnswer','answerMarked',data);
}

function answerMarked(json) {
    var response = JSON.parse(json);


    $('#' + response.team + '_round_total_score').html(response.round_total);
    $('#' + response.team + '_total_score').html(response.total);

    $('#' + response.answer + '_correct, #' + response.answer + '_select, #' + response.answer + '_incorrect, #' + response.answer + '_half, #' + response.answer).removeClass('correct incorrect half-point');
    if(response.half) {
        $('#' + response.answer + '_half').addClass('half-point');
        $('#' + response.answer + '_select').addClass('half-point');
    } else if(response.correct) {
        $('#' + response.answer + '_correct').addClass('correct');
        $('#' + response.answer + '_select').addClass('correct');
        $('#' + response.answer ).addClass('correct')
    } else {
        $('#' + response.answer + '_incorrect').addClass('incorrect');
        $('#' + response.answer + '_select' ).val(0);
    }


}

function saveQuestions(quizMasterId, quizId,roundId) {
    var questions = [];
    var points = [];
    $('form#round_' + roundId + '_form :input[type=text]').each(function(){
       questions.push(this.value);
    });
    $('form#round_' + roundId + '_form :input[type=number]').each(function(){
       points.push(this.value);
    });
    var json = JSON.stringify({host:quizMasterId,quiz:quizId,round:roundId, question:questions,points:points});

    ajaxRequest('GET','addRoundQuestions','questionsAddedForRound',json);

}

function questionsAddedForRound(json) {
    var response = JSON.parse(json);
    var questions = response.questions;
    var html = '';
    $(questions).each(function() {
        html += '<li>' + this.title + ' <span class="available_points_for_question">' + this.points + '</span></li>';
    });
    html += '';

    $('#round_' + response.round + '_form').trigger('reset');
    var inputs = $('#round_' + response.round + '_form :input[type=text]');
    var points = $('#round_' + response.round + '_form :input[type=number]');
    var i = 1;

    while(i < inputs.length) {
        $(inputs[i]).remove();
        $(points[i]).remove();
        i++;
    }

    $('#round_' + response.round + '_questions_list').html(html);
}

function updateShowRoundQuestions(quiz,round,show) {
    var json = JSON.stringify({quiz:quiz,round:round, show:show});

    ajaxRequest('GET','setRoundQuestionVisibility','updatedRoundQuestionVisibility',json);
}

function updatedRoundQuestionVisibility(json) {
    var response = JSON.parse(json);
    if (response.updated === false) return;

    var show = $('#' + response.round + '_questions_visible').removeClass('correct');
    var hide = $('#' + response.round + '_questions_hidden').removeClass('incorrect');


    if (response.show) {
        show.addClass('correct');
    } else {
        hide.addClass('incorrect');
    }
}

function handOutMarksheets(quiz,round,reAssign) {
    var json = JSON.stringify({quiz:quiz,round:round});
    var endpoint = reAssign ? 'reassignMarksheets' : 'assignMarksheets';
    ajaxRequest('GET',endpoint,'updateMarksheetResponse',json);
}

function removeMarksheets(quiz,round) {
    var json = JSON.stringify({quiz:quiz,round:round});
    ajaxRequest('GET','removeMarksheets','updateMarksheetResponse',json);
}

function updateMarksheetResponse(json) {

    var response = JSON.parse(json);
    if(
        (response.hasOwnProperty('removed') && response.removed === true) ||
        (response.hasOwnProperty('assigned') && response.assigned === true)

    ) {
        var assigned = $('#' + response.round + '_marksheets_set').removeClass('correct');
        var removed = $('#' + response.round + '_marksheets_unset').removeClass('incorrect');

        if (response.assigned) {
            assigned.addClass('correct');
            $('#' + response.round + '_marksheets_label').text("Reassign marksheets");
            listenForScoreUpdates(response.quiz, response.round)
        } else {
            removed.addClass('incorrect');
            $('#' + response.round + '_marksheets_label').text("Assign marksheets");
            if(scoreUpdatesListener !== null) {
                clearInterval(scoreUpdatesListener);
                scoreUpdatesListener = null;
            }
        }
    }

}


function listenForScoreUpdates(quiz,round) {
    currentRound = round;
    currentQuiz = quiz;
    if(scoreUpdatesListener !== null) {
        clearInterval(scoreUpdatesListener);
        scoreUpdatesListener = null;
    }
    scoreUpdatesListener = setInterval(sendScoresUpdateRequest,5000);
}

function sendScoresUpdateRequest() {
    if(currentRound === null) {
        return;
    }
    var json = JSON.stringify({round:currentRound,quiz:currentQuiz});
    ajaxRequest('GET','getUpdatedScores','updateAllScores',json);
}

function updateAllScores(json) {
    var response = JSON.parse(json);
    $.each(response.scores,function(key,score){
        const team = score.team;
        $('#' + team + '_total_score').text(score.quizTotal);
        $('#' + team + '_round_total_score').text(score.roundTotal);
    });

}