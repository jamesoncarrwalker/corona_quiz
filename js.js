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


function getMarkedAnswers(quiz,round) {
    var data = JSON.stringify({quiz:quiz,round:round});
    setInterval(ajaxRequest.bind(this,'GET','getMarkedAnswers','updateMarkedAnswers',data),3000);
}

function answerMarked(json) {
    var response = JSON.parse(json);


    $('#' + response.team + '_round_total_score').html(response.round_total);
    $('#' + response.team + '_total_score').html(response.total);

    removeActiveClassForAnswer(response.answer);
    setActivePoint(response.answer,response.correct,response.half,response.pointsVal);
}

function updateMarkedAnswers(json) {
    var response = JSON.parse(json);


    $.each(response,function(key,answers) {
        $.each(answers,function(key,answer){
            $.each(answer,function(key,an) {
                removeActiveClassForAnswer(an.UUID,true);
                const correct = an.points > 0;
                const half = an.points == 0.5;
                setActivePoint(an.UUID,correct,half,an.points);
            });

        });

    });


}

function removeActiveClassForAnswer(answer) {
    $('#' + answer + '_correct, #' + answer + '_select, #' + answer + '_incorrect, #' + answer + '_half, #' + answer).removeClass('correct incorrect half-point');
}

function setActivePoint(answer, correct, half, pointsVal) {
    if(half) {
        $('#' + answer + '_half').addClass('half-point');
        $('#' + answer + '_select').addClass('half-point');
    } else if(correct) {
        $('#' + answer + '_correct').addClass('correct');
        $('#' + answer + '_select').addClass('correct');
        if(pointsVal !== undefined) {
            $('#' + answer + '_select').val(pointsVal);
        }
        $('#' + answer ).addClass('correct')
    } else {
        $('#' + answer + '_incorrect').addClass('incorrect');
        $('#' + answer + '_select' ).val(0);
    }

    $('#' + answer +'_points_awarded').text(pointsVal > 0 ? pointsVal : 0);

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

function getTeamScoresUpdate(quiz,round,team) {
    setInterval(function(){
        var json = JSON.stringify({round:round,quiz:quiz,team:team});
        ajaxRequest('GET','getUpdatedScores','updateAllScores',json);
    }, 2000)
}

function updateAllScores(json) {
    var response = JSON.parse(json);
    if(response.forTeam) {

        $.each(response.scores,function(key,score){
            $('#' + key + '_total_score').text(score);
            $('#team_total_score').text(score.total);
        });
    } else {
        $.each(response.scores,function(key,score){
            const team = score.team;
            $('#' + team + '_total_score').text(score.quizTotal);
            $('#' + team + '_round_total_score').text(score.roundTotal);
        });
    }


}

function showPanel(className) {
    const panelToShow = $('.' + className);

    if($(panelToShow).hasClass('hidden')) {
        $('.toggle_panel').addClass('hidden');
        $(panelToShow).removeClass('hidden');
    }

    $('.toggle_panel_control').each(function(){
        if($(this).hasClass('active')) {
            $(this).removeClass('active');
        } else {
            $(this).addClass('active');
        }
    });
}

var marksheetListener = null;

function listenForAnswerSheet(quiz,round,team,marksheetTeam){
    if(marksheetListener !== null) {
        clearInterval(marksheetListener);
    }

    marksheetListener = setInterval(checkForMarksheet.bind(this,quiz,round,team,marksheetTeam),5000);

}

function checkForMarksheet(quiz,round,team,marksheetTeam) {

    const json = JSON.stringify({quiz:quiz,round:round,team:team,marksheetTeam:marksheetTeam});
    ajaxRequest('GET','checkForMarksheet','checkForMarksheetResponse',json);
}

function checkForMarksheetResponse(json) {
    const response = JSON.parse(json);
    if(response.update) {
        listenForAnswerSheet(response.quiz,response.round,response.team,(response.teamToMark === false ? null : response.teamToMark));
        setMarksheet(response.teamAnswers);

    }

}

function setMarksheet(teamAnswers) {

    if(teamAnswers.length < 1) {
        $('#marksheetMessage').removeClass('hidden');
        $('#marksheetAnswers').addClass('hidden').html("");
    } else {
        var html = "";
        $.each(teamAnswers,function(key,answer){
            html += '<li><strong>' + answer.title + "</strong></br>" +
                    "" + answer.answer;
            if(answer.points_available == 1) {
                html += '<span id="' + answer.UUID + '_correct" class="glyphicon glyphicon-ok marksheet award_glyph ' + (answer.points > 0.5 ? 'correct' : '') + '" onclick="markAnswer(\''+ answer.quiz_UUID + '\',\'' + answer.team_UUID + '\', \'' +  answer.UUID + '\',\'' +  answer.round + '\', true)"></span> '+
                        '<span id="' + answer.UUID + '_half" data-id="' + answer.UUID + '" data-quiz="' + answer.quiz_UUID + '"  data-team="' +  answer.team_UUID + '" data-round="' + answer.round + '?>" onclick="markAnswerWithHalf(this)" class=" marksheet award_glyph ' + (answer.points == 0.5 ? 'half-point' : '') + ' ">1/2</span>';
            } else {

           html +='<select id="' +  answer.UUID + '_select" data-id="' + answer.UUID + '" data-quiz="' + answer.quiz_UUID + '"  data-team="' + answer.team_UUID + '" data-round="' + answer.round + '" class="' + (answer.points == 0.5 ? 'half-point' : (answer.points > 0.5 ? 'correct' : '')) + '" onchange="markAnswerWithPoints(this)">';



                for(var i = 0; i <= answer.points_available; i += 0.5) {
                    html += '<option ' + (i == answer.points ? "selected=\"true\"" : "" ) + ' value="' + i + '">' + i + '</option>';
                }
            html += '</select>';
            }

            html += '<span id="' + answer.UUID + '_incorrect" class="glyphicon glyphicon-remove marksheet award_glyph ' +  (answer.points == -1 ? 'incorrect' : '') + '" onclick="markAnswer(\''+ answer.quiz_UUID + '\',\'' +  answer.team_UUID + '\', \'' + answer.UUID + '\',\'' + answer.round + '\', false)"></span>';

            html +=  "</li>";
        });
        $('#marksheetAnswers').html(html);
        $('#marksheetMessage').addClass('hidden');
        $('#marksheetAnswers').removeClass('hidden');
    }
}